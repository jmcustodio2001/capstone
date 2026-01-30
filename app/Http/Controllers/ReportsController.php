<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CourseManagement;
use App\Models\EmployeeTrainingDashboard;
use App\Models\CompletedTraining;
use App\Models\ExamAttempt;
use App\Models\TrainingFeedback;
use App\Models\Employee;
use App\Models\OrganizationalPosition;
use App\Models\CompetencyLibrary;
use App\Models\EmployeeCompetencyProfile;
use Illuminate\Support\Facades\Schema;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Compute summary metrics from real tables (best-effort using available models)

        // Total training hours: sum of training_duration in training_feedback
        $totalTrainingHours = (int) TrainingFeedback::query()->sum('training_duration');

        // Completion rate: completed / total from employee_training_dashboards
        $totalAssigned = EmployeeTrainingDashboard::query()->count();
        $totalCompleted = EmployeeTrainingDashboard::query()->where(function($q) {
            $q->whereRaw("LOWER(status) = 'completed'")->orWhere('progress', '>=', 100);
        })->count();

        $completionRate = $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100) : 0;

        // Avg skill score: use ExamAttempt average score (0-100) converted to 0-10 scale if available
        $avgExamScore = ExamAttempt::query()->avg('score');
        $avgSkillScore = $avgExamScore ? round($avgExamScore / 10, 1) : null;

        // Cost per training: not available in schema by default — try to read from course_management.cost if exists
        $costPerTraining = null;
        if (Schema::hasTable('course_management') && Schema::hasColumn('course_management', 'cost')) {
            $avgCost = DB::table('course_management')->avg('cost');
            $costPerTraining = $avgCost ? ('$' . number_format($avgCost, 2)) : null;
        }

        // Certifications awarded - count of completed_trainings (assumed to represent certificates)
        $certifications = CompletedTraining::query()->count();

        // Avg training time - average training_duration from training_feedback
        $avgTrainingTimeVal = TrainingFeedback::query()->avg('training_duration');
        $avgTrainingTime = $avgTrainingTimeVal ? round($avgTrainingTimeVal, 1) . ' hrs' : null;

        // Training ROI - not available by default; leave null
        $trainingRoi = null;

        // Competencies: for each competency, compute participants and completion (proficiency >= 3)
        $competencyStats = CompetencyLibrary::query()->get()->map(function($competency) {
            $participants = EmployeeCompetencyProfile::where('competency_id', $competency->id)->count();

            // Assuming completed means proficiency level >= 3 (Competent)
            $completed = EmployeeCompetencyProfile::where('competency_id', $competency->id)
                ->where('proficiency_level', '>=', 3)
                ->count();

            // Avg score is the average proficiency level (1-5)
            $avgScore = EmployeeCompetencyProfile::where('competency_id', $competency->id)->avg('proficiency_level');
            $avgScore = $avgScore ? round($avgScore, 1) : 0;

            // Completion Rate: % of participants who are competent (>= 3)
            $completionPercent = $participants > 0 ? round(($completed / $participants) * 100) : 0;

            $statusClass = $completionPercent >= 80 ? 'bg-success' : ($completionPercent >= 50 ? 'bg-warning' : 'bg-danger');
            $statusText = $completionPercent >= 80 ? 'Strong' : ($completionPercent >= 50 ? 'Developing' : 'Needs Focus');

            return [
                'name' => $competency->competency_name,
                'department' => $competency->category ?? 'General',
                'participants' => $participants,
                'completed' => $completed,
                'completion_percent' => $completionPercent,
                'avg_score' => $avgScore, // Scale 1-5
                'status_class' => $statusClass,
                'status_text' => $statusText,
            ];
        })->toArray();

        // Keep courses for the count metric
        $courses = CourseManagement::all();

        // Departments: try to detect department field and compute completion per department
        $departments = [];
        $deptField = null;
        if (Schema::hasColumn('employees', 'department')) {
            $deptField = 'department';
        } elseif (Schema::hasColumn('employees', 'department_id')) {
            $deptField = 'department_id';
        }

        if ($deptField) {
            $deptValues = Employee::select($deptField)->groupBy($deptField)->get()->pluck($deptField)->filter()->values();
            foreach ($deptValues as $deptVal) {
                if ($deptField === 'department_id') {
                    $deptName = OrganizationalPosition::where('id', $deptVal)->value('department') ?? (string)$deptVal;
                    $deptEmployees = Employee::where('department_id', $deptVal)->pluck('employee_id');
                } else {
                    $deptName = $deptVal;
                    $deptEmployees = Employee::where('department', $deptVal)->pluck('employee_id');
                }

                $total = EmployeeTrainingDashboard::whereIn('employee_id', $deptEmployees)->count();
                $done = EmployeeTrainingDashboard::whereIn('employee_id', $deptEmployees)
                    ->where(function($q){ $q->whereRaw("LOWER(status) = 'completed'")->orWhere('progress', '>=', 100); })
                    ->count();
                $percent = $total > 0 ? round(($done / $total) * 100) : 0;
                $departments[] = ['name' => $deptName, 'percent' => $percent, 'color' => '#3498db'];
            }
        }

        // Employees: recent employees with counts
        $employees = Employee::limit(10)->get()->map(function($emp) {
            $assigned = EmployeeTrainingDashboard::where('employee_id', $emp->employee_id)->count();
            $completed = EmployeeTrainingDashboard::where('employee_id', $emp->employee_id)
                ->where(function($q){ $q->whereRaw("LOWER(status) = 'completed'")->orWhere('progress', '>=', 100); })
                ->count();
            $avgScore = ExamAttempt::where('employee_id', $emp->employee_id)->avg('score');
            $avgScore = $avgScore ? round($avgScore / 10, 1) : null;
            $progressAvg = EmployeeTrainingDashboard::where('employee_id', $emp->employee_id)->avg('progress');
            $progressAvg = $progressAvg ? round($progressAvg) : 0;
            $lastActivityVal = EmployeeTrainingDashboard::where('employee_id', $emp->employee_id)->orderBy('updated_at', 'desc')->value('updated_at');
            $lastActivity = $lastActivityVal ? \Illuminate\Support\Carbon::parse($lastActivityVal)->diffForHumans() : null;

            return [
                'id' => $emp->employee_id,
                'name' => trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')),
                'department' => $emp->department,
                'assigned' => $assigned,
                'completed' => $completed,
                'avg_score' => $avgScore,
                'progress_percent' => $progressAvg,
                'last_activity' => $lastActivity ? $lastActivity->diffForHumans() : null,
            ];
        })->toArray();

        return view('reports', compact(
            'courses', 'competencyStats', 'departments', 'employees',
            'totalTrainingHours', 'completionRate', 'avgSkillScore', 'costPerTraining',
            'certifications', 'avgTrainingTime', 'trainingRoi'
        ));
    }

    /**
     * Export report data as CSV or Excel (XLS) or render print view
     */
    public function export(Request $request)
    {
        $type = $request->query('type', 'csv');

        // Reuse same competency data logic
        $courses = CompetencyLibrary::query()->get()->map(function($competency) {
            $participants = EmployeeCompetencyProfile::where('competency_id', $competency->id)->count();

            // Assuming completed means proficiency level >= 3 (Competent)
            $completed = EmployeeCompetencyProfile::where('competency_id', $competency->id)
                ->where('proficiency_level', '>=', 3)
                ->count();

            // Avg score is the average proficiency level (1-5)
            $avgScore = EmployeeCompetencyProfile::where('competency_id', $competency->id)->avg('proficiency_level');
            $avgScore = $avgScore ? round($avgScore, 1) : 0;

            // Completion Rate: % of participants who are competent (>= 3)
            $completionPercent = $participants > 0 ? round(($completed / $participants) * 100) : 0;

            $statusText = $completionPercent >= 80 ? 'Strong' : ($completionPercent >= 50 ? 'Developing' : 'Needs Focus');

            return [
                'name' => $competency->competency_name,
                'department' => $competency->category ?? 'General',
                'participants' => $participants,
                'completed' => $completed,
                'completion_percent' => $completionPercent,
                'avg_score' => $avgScore,
                'status_text' => $statusText,
            ];
        })->toArray();

        if ($type === 'print') {
            return view('reports_print', ['courses' => $courses]);
        }

        // Build CSV content
        $filename = 'competency_reports_' . date('Ymd_His');
        $columns = ['Competency Name', 'Category', 'Participants', 'Competent/Completed', 'Competency Rate %', 'Avg. Proficiency', 'Status'];

        $callback = function() use ($courses, $columns) {
            $fh = fopen('php://output', 'w');
            // BOM for Excel compatibility in some locales
            fwrite($fh, "\xEF\xBB\xBF");
            fputcsv($fh, $columns);
            foreach ($courses as $row) {
                fputcsv($fh, [
                    $row['name'],
                    $row['department'],
                    $row['participants'],
                    $row['completed'],
                    $row['completion_percent'] . '%',
                    $row['avg_score'],
                    $row['status_text']
                ]);
            }
            fclose($fh);
        };

        if ($type === 'excel') {
            $file = $filename . '.xls';
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$file}\"",
            ];
            return response()->stream($callback, 200, $headers);
        }

        // default csv
        $file = $filename . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$file}\"",
        ];
        return response()->stream($callback, 200, $headers);
    }
}
