<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\CourseManagement;
use App\Models\CompetencyGap;
use App\Models\PotentialSuccessor;
use App\Models\TrainingRecordCertificateTracking;
use App\Models\EmployeeCompetencyProfile;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $totalEmployees = Employee::count();
        $totalUsers = User::count();
        $activeCourses = CourseManagement::where('status', 'active')->count();
        $trainingSessions = TrainingRecordCertificateTracking::count();
        $employeeUsers = User::where('role', 'employee')->count();
        $successionPlans = PotentialSuccessor::count();
        $competencies = \App\Models\CompetencyLibrary::count();
        $attendanceLogs = \App\Models\AttendanceTimeLog::count();
        $completedTrainings = TrainingRecordCertificateTracking::where('status', 'Completed')->count();
        $pendingGapAnalyses = CompetencyGap::where('gap', '>', 0)->count();
        
        
        // Get recent activities
        $recentEmployees = Employee::latest()->take(5)->get();
        $recentGapAnalyses = CompetencyGap::with('employee')
            ->latest()
            ->take(5)
            ->get();
        $recentTrainings = TrainingRecordCertificateTracking::with(['employee', 'course'])
            ->latest('training_date')
            ->take(5)
            ->get()
            ->map(function($training) {
                // Calculate participant count for this course
                $participantCount = TrainingRecordCertificateTracking::where('course_id', $training->course_id)
                    ->distinct('employee_id')
                    ->count('employee_id');
                
                $training->participant_count = $participantCount;
                return $training;
            });
        
        // Recent Competency Updates (last 7 days)
        $recentCompetencyUpdates = EmployeeCompetencyProfile::with(['employee', 'competency'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($profile) {
                return (object) [
                    'id' => $profile->id,
                    'employee' => $profile->employee,
                    'competency' => $profile->competency,
                    'current_level' => (int) $profile->proficiency_level,
                    'progress_percentage' => ((int) $profile->proficiency_level / 5) * 100,
                    'gap_score' => max(0, 5 - (int) $profile->proficiency_level),
                    'last_updated' => $profile->updated_at
                ];
            });

        // Get top skills in demand based on competency gaps
        $topSkillsData = CompetencyGap::join('competency_library', 'competency_gaps.competency_id', '=', 'competency_library.id')
            ->selectRaw('competency_library.competency_name, COUNT(*) as gap_count, AVG(competency_gaps.gap) as avg_gap')
            ->where('competency_gaps.gap', '>', 0)
            ->groupBy('competency_library.id', 'competency_library.competency_name')
            ->orderByDesc('gap_count')
            ->orderByDesc('avg_gap')
            ->take(5)
            ->get();

        $topSkills = $topSkillsData->map(function($skill, $index) use ($topSkillsData) {
            $maxCount = $topSkillsData->max('gap_count');
            $percent = $maxCount > 0 ? round(($skill->gap_count / $maxCount) * 100) : 0;
            return [
                'name' => $skill->competency_name,
                'percent' => max($percent, 10) // Minimum 10% for visibility
            ];
        });

        return view('admin_dashboard', compact(
            'totalEmployees',
            'totalUsers', 
            'activeCourses',
            'trainingSessions',
            'employeeUsers',
            'successionPlans',
            'competencies',
            'attendanceLogs',
            'completedTrainings',
            'pendingGapAnalyses',
            'recentEmployees',
            'recentGapAnalyses',
            'recentTrainings',
            'topSkills'
        ));
    }
}
