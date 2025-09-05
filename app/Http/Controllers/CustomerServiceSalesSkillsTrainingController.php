<?php

namespace App\Http\Controllers;

use App\Models\CustomerServiceSalesSkillsTraining;
use App\Models\Employee;
use App\Models\EmployeeTrainingDashboard; // or CourseManagement if you want courses
use App\Models\CompetencyGap;
use App\Models\CompetencyLibrary;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class CustomerServiceSalesSkillsTrainingController extends Controller
{
    public function index()
    {
        // Removed redundant assignment to $records
        $employees = Employee::all();
        $trainings = EmployeeTrainingDashboard::all(); // or CourseManagement::all();

        // Fetch all gaps and recommend trainings
        $gaps = CompetencyGap::with(['employee', 'competency'])
            ->get()
            ->map(function($gap) {
                $recommendedTraining = EmployeeTrainingDashboard::whereHas('course', function($q) use ($gap) {
                    $q->where('course_title', 'LIKE', '%' . $gap->competency->competency_name . '%');
                })->first();
                return (object) [
                    'employee' => $gap->employee,
                    'competency' => $gap->competency,
                    'required_level' => $gap->required_level,
                    'current_level' => $gap->current_level,
                    'gap' => $gap->gap,
                    'recommended_training' => $recommendedTraining ? $recommendedTraining->course : null,
                ];
            });

        // Content Reference: List of skills from competency library for training assignment
    $skills = CompetencyLibrary::orderBy('id', 'desc')->get();

        // Training Records: Only show non-deleted records
        $records = CustomerServiceSalesSkillsTraining::with(['employee', 'training'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($record) {
                // Try to find matching EmployeeTrainingDashboard record for progress/status
                $dashboard = \App\Models\EmployeeTrainingDashboard::where('employee_id', $record->employee_id)
                    ->where('course_id', $record->training_id)
                    ->first();
                $record->progress = $dashboard ? $dashboard->progress : null;
                $record->status = $dashboard ? $dashboard->status : null;
                return $record;
            });
        if ($records->isEmpty()) {
            $records = EmployeeTrainingDashboard::with(['employee', 'course'])->orderBy('created_at', 'desc')->get()->map(function($item) {
                return (object)[
                    'employee' => $item->employee,
                    'training' => (object)[
                        'course_title' => $item->course ? $item->course->course_title : ($item->title ?? 'Training'),
                        'title' => $item->title ?? ($item->course ? $item->course->course_title : 'Training'),
                        'course' => $item->course,
                    ],
                    'date_completed' => $item->training_date,
                    'id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'training_id' => $item->course_id,
                    'progress' => $item->progress,
                    'status' => $item->status,
                ];
            });
        }

        return view('training_management.customer_service_sales_skills_training', compact('records', 'employees', 'trainings', 'gaps', 'skills'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'training_id' => 'required|integer',
            'date_completed' => 'required|date',
        ]);

        $record = CustomerServiceSalesSkillsTraining::create($request->only([
            'employee_id', 'training_id', 'date_completed'
        ]));

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Customer Service Sales Skills Training',
            'description' => 'Added customer service sales skills training record (ID: ' . $record->id . ')',
        ]);

        // Automatically update progress in EmployeeTrainingDashboard if completed
        if (!empty($record->date_completed) && $record->date_completed != '1970-01-01') {
            $dashboard = EmployeeTrainingDashboard::where('employee_id', $record->employee_id)
                ->where('course_id', $record->training_id)
                ->first();
            if ($dashboard) {
                $dashboard->progress = 100;
                $dashboard->status = 'Completed';
                $dashboard->save();
            }
        }

        return redirect()->route('customer_service_sales_skills_training.index')
            ->with('success', 'Training added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'training_id' => 'required|integer',
            'date_completed' => 'required|date',
        ]);

        $training = CustomerServiceSalesSkillsTraining::findOrFail($id);
        $training->update($request->only([
            'employee_id', 'training_id', 'date_completed'
        ]));

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'module' => 'Customer Service Sales Skills Training',
            'description' => 'Updated customer service sales skills training record (ID: ' . $training->id . ')',
        ]);

        // Automatically update progress in EmployeeTrainingDashboard if completed
        if (!empty($training->date_completed) && $training->date_completed != '1970-01-01') {
            $dashboard = \App\Models\EmployeeTrainingDashboard::where('employee_id', $training->employee_id)
                ->where('course_id', $training->training_id)
                ->first();
            if ($dashboard) {
                $dashboard->progress = 100;
                $dashboard->status = 'Completed';
                $dashboard->save();
            }
        }

        return redirect()->route('customer_service_sales_skills_training.index')
            ->with('success', 'Training updated successfully.');
    }

    public function destroy($id)
    {
        $training = CustomerServiceSalesSkillsTraining::findOrFail($id);
        $training->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Customer Service Sales Skills Training',
            'description' => 'Deleted customer service sales skills training record (ID: ' . $training->id . ')',
        ]);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Training deleted successfully.']);
        }
        return redirect()->route('customer_service_sales_skills_training.index')
            ->with('success', 'Training deleted successfully.');
    }
}
