<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:employee')->except(['viewEmployeeDashboard']);
        $this->middleware('auth:admin')->only(['viewEmployeeDashboard']);
    }

    public function index()
    {
        $employee = Auth::guard('employee')->user();
        $employeeId = $employee->employee_id;

        // Debug: Log the employee ID being used
        error_log('Employee dashboard accessed by employee ID: ' . $employeeId);

        // Get employee's training statistics
        $trainings = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
            ->with('course')
            ->get();

        $completedTrainings = $trainings->where('progress', 100)->count();
        $inProgressTrainings = $trainings->where('progress', '>', 0)->where('progress', '<', 100)->count();
        $totalTrainings = $trainings->count();
        $avgTrainingProgress = $trainings->avg('progress') ?? 0;

        // Get recent notifications
        $notifications = \App\Models\TrainingNotification::where('employee_id', $employeeId)
            ->orderBy('sent_at', 'desc')
            ->take(5)
            ->get();

        // Get pending leave requests count
        $pendingLeaveRequests = \App\Models\RequestForm::where('employee_id', $employeeId)
            ->where('request_type', 'Leave Application')
            ->where('status', 'Pending')
            ->count();

        // Calculate attendance rate for current month
        $currentMonth = Carbon::now()->format('Y-m');
        $workingDaysThisMonth = Carbon::now()->startOfMonth()->diffInWeekdays(Carbon::now()) + 1;
        $attendanceRecords = \App\Models\AttendanceTimeLog::where('employee_id', $employeeId)
            ->whereYear('log_date', Carbon::now()->year)
            ->whereMonth('log_date', Carbon::now()->month)
            ->where('status', 'Present')
            ->count();
        $attendanceRate = $workingDaysThisMonth > 0 ? round(($attendanceRecords / $workingDaysThisMonth) * 100) : 0;

        // Get latest payslip amount (simulated for now)
        $latestPayslip = 28500; // This would come from payroll system
        $payslipMonth = 'July 2025';

        // Get upcoming trainings count (simplified for now)
        $upcomingTrainings = 0;

        // Get recent requests with mixed types
        $recentRequests = collect();
        
        // Get leave applications
        $leaveRequests = \App\Models\RequestForm::where('employee_id', $employeeId)
            ->where('request_type', 'Leave Application')
            ->orderBy('requested_date', 'desc')
            ->take(2)
            ->get()
            ->map(function($request) {
                return [
                    'type' => 'Leave Application',
                    'date' => Carbon::parse($request->requested_date)->format('M j, Y'),
                    'status' => $request->status,
                    'remarks' => $request->reason ?? 'Vacation leave'
                ];
            });

        // Get other request forms
        $otherRequests = \App\Models\RequestForm::where('employee_id', $employeeId)
            ->where('request_type', '!=', 'Leave Application')
            ->orderBy('requested_date', 'desc')
            ->take(2)
            ->get()
            ->map(function($request) {
                return [
                    'type' => $request->request_type,
                    'date' => Carbon::parse($request->requested_date)->format('M j, Y'),
                    'status' => $request->status,
                    'remarks' => $request->reason ?? 'General request'
                ];
            });

        // Add training enrollment requests (check if table exists and has created_at)
        $trainingRequests = collect();
        try {
            if (Schema::hasTable('training_requests')) {
                $trainingRequests = \App\Models\TrainingRequest::where('employee_id', $employeeId)
                    ->orderBy(Schema::hasColumn('training_requests', 'created_at') ? 'created_at' : 'id', 'desc')
                    ->take(2)
                    ->get()
                    ->map(function($request) {
                        $dateField = Schema::hasColumn('training_requests', 'created_at') ? $request->created_at : now();
                        return [
                            'type' => 'Training Enrollment',
                            'date' => Carbon::parse($dateField)->format('M j, Y'),
                            'status' => $request->status ?? 'Approved',
                            'remarks' => $request->training_title ?? ($request->reason ?? 'Training request')
                        ];
                    });
            }
        } catch (\Exception $e) {
            // If training_requests table doesn't exist, just use empty collection
            $trainingRequests = collect();
        }

        $recentRequests = $leaveRequests->concat($otherRequests)->concat($trainingRequests)
            ->sortByDesc('date')
            ->take(4)
            ->values();

        // Get competency data for progress tracking
        $competencyProfiles = \App\Models\EmployeeCompetencyProfile::where('employee_id', $employeeId)->get();
        $totalCompetencies = $competencyProfiles->count();
        $competencyGoalsAchieved = $competencyProfiles->where('proficiency_level', '>=', 4)->count();
        $competencyProgress = $totalCompetencies > 0 ? round(($competencyGoalsAchieved / $totalCompetencies) * 100) : 0;

        // Calculate training completion percentage
        $trainingCompletionRate = $totalTrainings > 0 ? round(($completedTrainings / $totalTrainings) * 100) : 0;

        // Get upcoming trainings list (including destination knowledge training)
        $upcomingTrainingsList = $this->getUpcomingTrainingsList($employeeId);
        
        // Debug: Log the upcoming trainings being passed to view
        error_log('Passing ' . $upcomingTrainingsList->count() . ' upcoming trainings to view for employee: ' . $employeeId);

        return view('employee_ess_modules.employee_dashboard', compact(
            'employee',
            'trainings',
            'completedTrainings',
            'inProgressTrainings',
            'totalTrainings',
            'avgTrainingProgress',
            'notifications',
            'pendingLeaveRequests',
            'attendanceRate',
            'latestPayslip',
            'payslipMonth',
            'upcomingTrainings',
            'recentRequests',
            'competencyProfiles',
            'totalCompetencies',
            'competencyGoalsAchieved',
            'competencyProgress',
            'trainingCompletionRate',
            'upcomingTrainingsList'
        ));
    }

    /**
     * Get upcoming trainings list including destination knowledge training
     */
    private function getUpcomingTrainingsList($employeeId)
    {
        $upcomingTrainings = collect();

        // Get Employee Training Dashboard records (include all assigned trainings)
        $employeeTrainings = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
            ->with('course')
            ->get();
            
        // Debug: Log the training records found
        error_log('Found ' . $employeeTrainings->count() . ' training records for employee: ' . $employeeId);

        foreach ($employeeTrainings as $training) {
            // Show all trainings regardless of progress to debug the issue
            $source = $training->source ?? 'admin_assigned';
            $assignedByName = 'Admin';
            
            // Determine source and assigned by based on the training source
            if ($source === 'competency_assigned') {
                $assignedByName = 'System Auto-Assign (competency_auto_assigned)';
            }
            
            $upcomingTrainings->push([
                'upcoming_id' => 'ETD-' . $training->id,
                'id' => 'ETD-' . $training->id,
                'training_title' => $training->course->course_title ?? $training->training_title ?? 'Training Course',
                'title' => $training->course->course_title ?? $training->training_title ?? 'Training Course',
                'start_date' => $training->course->start_date ?? $training->training_date ?? $training->created_at->format('Y-m-d'),
                'end_date' => $training->course->end_date ?? null,
                'expired_date' => $training->expired_date ?? $training->course->expired_date ?? null,
                'progress' => $training->progress ?? 0,
                'status' => $training->status ?? ($training->progress == 0 ? 'Not Started' : 'In Progress'),
                'status_class' => $training->progress == 0 ? 'bg-secondary' : 'bg-warning',
                'source' => $source,
                'assigned_by_name' => $assignedByName,
                'assigned_by' => $assignedByName,
                'assigned_date' => $training->created_at,
                'delivery_mode' => $training->course->delivery_mode ?? 'Online',
                'course_id' => $training->course_id
            ]);
            
            // Debug log each training record
            error_log('Training record: ' . ($training->course->course_title ?? 'No title') . ' - Progress: ' . ($training->progress ?? 0) . '%');
        }

        // Get Destination Knowledge Training records (include all active trainings)
        $destinationTrainings = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->get();

        foreach ($destinationTrainings as $destination) {
            $progress = $destination->progress ?? 0;
            $status = $progress == 0 ? 'Not Started' : ($progress == 100 ? 'Completed' : 'In Progress');
            $statusClass = $progress == 0 ? 'bg-secondary' : ($progress == 100 ? 'bg-success' : 'bg-warning');
            
            // Only show trainings that are not completed (progress < 100%)
            if ($progress < 100) {
                $upcomingTrainings->push([
                    'upcoming_id' => 'DKT-' . $destination->id,
                    'id' => 'DKT-' . $destination->id,
                    'training_title' => $destination->destination_name,
                    'title' => $destination->destination_name,
                    'start_date' => $destination->created_at->format('Y-m-d'),
                    'end_date' => null,
                    'expired_date' => $destination->expired_date,
                    'progress' => $progress,
                    'status' => $status,
                    'status_class' => $statusClass,
                    'source' => 'destination_assigned',
                    'assigned_by_name' => 'Admin',
                    'assigned_by' => 'Admin',
                    'assigned_date' => $destination->created_at,
                    'delivery_mode' => $destination->delivery_mode ?? 'On-site Training',
                    'destination_training_id' => $destination->id,
                    'is_active' => $destination->is_active,
                    'needs_response' => false
                ]);
            }
        }

        // Get Training Requests for this employee
        $trainingRequests = \App\Models\TrainingRequest::where('employee_id', $employeeId)
            ->where('status', '!=', 'Completed')
            ->with('course')
            ->get();

        foreach ($trainingRequests as $request) {
            // Only show non-completed training requests
            if ($request->status != 'Completed') {
                $upcomingTrainings->push([
                    'upcoming_id' => 'TR-' . $request->id,
                    'id' => 'TR-' . $request->id,
                    'training_title' => $request->course->course_title ?? $request->training_title ?? 'Training Request',
                    'title' => $request->course->course_title ?? $request->training_title ?? 'Training Request',
                    'start_date' => $request->course->start_date ?? $request->created_at->format('Y-m-d'),
                    'end_date' => $request->course->end_date ?? null,
                    'expired_date' => $request->expired_date ?? null,
                    'progress' => 0,
                    'status' => $request->status,
                    'status_class' => $request->status == 'Approved' ? 'bg-success' : 'bg-warning',
                    'source' => 'employee_requested',
                    'assigned_by_name' => 'Self-Requested',
                    'assigned_by' => 'Self-Requested',
                    'assigned_date' => $request->created_at,
                    'delivery_mode' => $request->course->delivery_mode ?? 'Online',
                    'course_id' => $request->course_id
                ]);
            }
        }

        // Get Competency Course Assignments (from auto-assign feature)
        // Only include assignments that don't already have EmployeeTrainingDashboard records to avoid duplicates
        $courseAssignments = \App\Models\CompetencyCourseAssignment::where('employee_id', $employeeId)
            ->where('status', '!=', 'Completed')
            ->with('course')
            ->get();

        // Get list of course IDs that already have EmployeeTrainingDashboard records
        $existingTrainingCourseIds = $employeeTrainings->pluck('course_id')->toArray();

        foreach ($courseAssignments as $assignment) {
            // Only show non-completed course assignments that don't already have EmployeeTrainingDashboard records
            if (($assignment->progress ?? 0) < 100 && 
                $assignment->status != 'Completed' && 
                !in_array($assignment->course_id, $existingTrainingCourseIds)) {
                
                $upcomingTrainings->push([
                    'upcoming_id' => 'CCA-' . $assignment->id,
                    'id' => 'CCA-' . $assignment->id,
                    'training_title' => $assignment->course->course_title ?? 'Course Assignment',
                    'title' => $assignment->course->course_title ?? 'Course Assignment',
                    'start_date' => $assignment->course->start_date ?? $assignment->created_at->format('Y-m-d'),
                    'end_date' => $assignment->course->end_date ?? null,
                    'expired_date' => $assignment->expired_date ?? null,
                    'progress' => $assignment->progress ?? 0,
                    'status' => $assignment->status,
                    'status_class' => $assignment->status == 'Active' ? 'bg-primary' : 'bg-warning',
                    'source' => 'competency_auto_assigned',
                    'assigned_by_name' => 'System Auto-Assign',
                    'assigned_by' => 'System Auto-Assign',
                    'assigned_date' => $assignment->created_at,
                    'delivery_mode' => $assignment->course->delivery_mode ?? 'Online',
                    'course_id' => $assignment->course_id
                ]);
            }
        }

        // Debug: Log final upcoming trainings count
        error_log('Final upcoming trainings count: ' . $upcomingTrainings->count() . ' for employee: ' . $employeeId);
        
        return $upcomingTrainings->sortBy('start_date');
    }

    /**
     * View a specific employee's dashboard (for admin use)
     */
    public function viewEmployeeDashboard($employeeId)
    {
        // Find the employee
        $employee = \App\Models\Employee::where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        // Get employee's training statistics
        $trainings = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
            ->with('course')
            ->get();

        $completedTrainings = $trainings->where('progress', 100)->count();
        $inProgressTrainings = $trainings->where('progress', '>', 0)->where('progress', '<', 100)->count();
        $totalTrainings = $trainings->count();
        $avgTrainingProgress = $trainings->avg('progress') ?? 0;

        // Get recent notifications
        $notifications = \App\Models\TrainingNotification::where('employee_id', $employeeId)
            ->orderBy('sent_at', 'desc')
            ->take(5)
            ->get();

        // Get pending leave requests count
        $pendingLeaveRequests = \App\Models\RequestForm::where('employee_id', $employeeId)
            ->where('request_type', 'Leave Application')
            ->where('status', 'Pending')
            ->count();

        // Calculate attendance rate for current month
        $currentMonth = Carbon::now()->format('Y-m');
        $workingDaysThisMonth = Carbon::now()->startOfMonth()->diffInWeekdays(Carbon::now()) + 1;
        $attendanceRecords = \App\Models\AttendanceTimeLog::where('employee_id', $employeeId)
            ->whereYear('log_date', Carbon::now()->year)
            ->whereMonth('log_date', Carbon::now()->month)
            ->where('status', 'Present')
            ->count();
        $attendanceRate = $workingDaysThisMonth > 0 ? round(($attendanceRecords / $workingDaysThisMonth) * 100) : 0;

        // Get latest payslip amount (simulated for now)
        $latestPayslip = 28500; // This would come from payroll system
        $payslipMonth = 'July 2025';

        // Get upcoming trainings
        $upcomingTrainings = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
            ->where('progress', '<', 100)
            ->whereHas('course', function($query) {
                $query->where('status', 'Active');
            })
            ->count();

        // Get recent requests with mixed types
        $recentRequests = collect();
        
        // Get leave applications
        $leaveRequests = \App\Models\RequestForm::where('employee_id', $employeeId)
            ->where('request_type', 'Leave Application')
            ->orderBy('requested_date', 'desc')
            ->take(2)
            ->get()
            ->map(function($request) {
                return [
                    'type' => 'Leave Application',
                    'date' => Carbon::parse($request->requested_date)->format('M j, Y'),
                    'status' => $request->status,
                    'remarks' => $request->reason ?? 'Vacation leave'
                ];
            });

        // Get other request forms
        $otherRequests = \App\Models\RequestForm::where('employee_id', $employeeId)
            ->where('request_type', '!=', 'Leave Application')
            ->orderBy('requested_date', 'desc')
            ->take(2)
            ->get()
            ->map(function($request) {
                return [
                    'type' => $request->request_type,
                    'date' => Carbon::parse($request->requested_date)->format('M j, Y'),
                    'status' => $request->status,
                    'remarks' => $request->reason ?? 'General request'
                ];
            });

        // Add training enrollment requests (check if table exists and has created_at)
        $trainingRequests = collect();
        try {
            if (Schema::hasTable('training_requests')) {
                $trainingRequests = \App\Models\TrainingRequest::where('employee_id', $employeeId)
                    ->orderBy(Schema::hasColumn('training_requests', 'created_at') ? 'created_at' : 'id', 'desc')
                    ->take(2)
                    ->get()
                    ->map(function($request) {
                        $dateField = Schema::hasColumn('training_requests', 'created_at') ? $request->created_at : now();
                        return [
                            'type' => 'Training Enrollment',
                            'date' => Carbon::parse($dateField)->format('M j, Y'),
                            'status' => $request->status ?? 'Approved',
                            'remarks' => $request->training_title ?? ($request->reason ?? 'Training request')
                        ];
                    });
            }
        } catch (\Exception $e) {
            // If training_requests table doesn't exist, just use empty collection
            $trainingRequests = collect();
        }

        $recentRequests = $leaveRequests->concat($otherRequests)->concat($trainingRequests)
            ->sortByDesc('date')
            ->take(4)
            ->values();

        // Get competency data for progress tracking
        $competencyProfiles = \App\Models\EmployeeCompetencyProfile::where('employee_id', $employeeId)->get();
        $totalCompetencies = $competencyProfiles->count();
        $competencyGoalsAchieved = $competencyProfiles->where('proficiency_level', '>=', 4)->count();
        $competencyProgress = $totalCompetencies > 0 ? round(($competencyGoalsAchieved / $totalCompetencies) * 100) : 0;

        // Calculate training completion percentage
        $trainingCompletionRate = $totalTrainings > 0 ? round(($completedTrainings / $totalTrainings) * 100) : 0;

        // Get upcoming trainings list (including destination knowledge training)
        $upcomingTrainingsList = $this->getUpcomingTrainingsList($employeeId);

        // Add admin view flag
        $isAdminView = true;

        return view('employee_ess_modules.employee_dashboard', compact(
            'employee',
            'trainings',
            'completedTrainings',
            'inProgressTrainings',
            'totalTrainings',
            'avgTrainingProgress',
            'notifications',
            'pendingLeaveRequests',
            'attendanceRate',
            'latestPayslip',
            'payslipMonth',
            'upcomingTrainings',
            'recentRequests',
            'competencyProfiles',
            'totalCompetencies',
            'competencyGoalsAchieved',
            'competencyProgress',
            'trainingCompletionRate',
            'upcomingTrainingsList',
            'isAdminView'
        ));
    }

    /**
     * Verify employee password for secure operations
     */
    public function verifyPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string'
            ]);

            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if the provided password matches the employee's password
            if (Hash::check($request->password, $employee->password)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password verified successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password verification failed'
            ], 500);
        }
    }
}
