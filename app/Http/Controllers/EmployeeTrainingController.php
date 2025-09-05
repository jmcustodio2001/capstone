<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTraining;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\CourseManagement;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeTrainingDashboard;

class EmployeeTrainingController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $employeeId = Auth::user()->employee_id;
        $upcoming = EmployeeTraining::where('employee_id', $employeeId)->where('status', 'Upcoming')->get();
        $completed = EmployeeTraining::where('employee_id', $employeeId)->where('status', 'Completed')->get();
        $trainingRequests = EmployeeTraining::where('employee_id', $employeeId)->where('status', 'Requested')->get();
        $progress = EmployeeTraining::where('employee_id', $employeeId)->whereNotNull('progress')->get();
        $feedback = EmployeeTraining::where('employee_id', $employeeId)->whereNotNull('feedback')->get();
        $notifications = EmployeeTraining::where('employee_id', $employeeId)->whereNotNull('notification_type')->get();
        return view('employee_ess_modules.my_trainings.index', compact('upcoming', 'completed', 'trainingRequests', 'progress', 'feedback', 'notifications'));
    }

    public function create()
    {
        return view('employee_ess_modules.my_trainings.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|numeric',
            'training_title' => 'required|string',
            'training_date' => 'required|date',
            'status' => 'required|string',
        ]);
        EmployeeTraining::create($validated);
        return redirect()->route('employee.my_trainings.index')->with('success', 'Training record added successfully!');
    }

    public function show(int $id)
    {
        return redirect()->route('employee.my_trainings.index');
    }

    public function edit(int $id)
    {
        return redirect()->route('employee.my_trainings.index');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $training = EmployeeTraining::findOrFail($id);
        $validated = $request->validate([
            'training_title' => 'required|string',
            'training_date' => 'required|date',
            'status' => 'required|string',
            'progress' => 'nullable|integer',
            'feedback' => 'nullable|string',
            'notification_type' => 'nullable|string',
            'notification_message' => 'nullable|string',
        ]);
        $training->update($validated);
        return redirect()->route('employee.my_trainings.index')->with('success', 'Training record updated successfully!');
    }


    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $training = EmployeeTraining::findOrFail($id);
        $training->delete();
        return redirect()->route('employee.my_trainings.index')->with('success', 'Training record deleted successfully!');
    }

    /**
     * Sync readiness score for an employee based on latest training progress (Live Data Sync)
     *
     * Formula:
     *   readiness_score = 60 + 0.4 × (average training progress)
     *   - average training progress is the mean of all progress values for the employee (0-100)
     *   - readiness_score is rounded and clamped between 1 and 100
     */
    private function syncReadinessScore(int $employee_id): void
    {
        // Get latest average progress for the employee
        $avgProgress = EmployeeTrainingDashboard::where('employee_id', $employee_id)->avg('progress');
        // Example logic: readiness score is 60 + 0.4 * avg training progress (customize as needed)
        $score = round(60 + 0.4 * ($avgProgress ?? 0));
        $score = max(1, min(100, $score));
        $assessmentDate = now()->toDateString();
        \App\Models\SuccessionReadinessRating::updateOrCreate(
            ['employee_id' => $employee_id],
            [
                'readiness_score' => $score,
                'assessment_date' => $assessmentDate,
            ]
        );
    }
}
