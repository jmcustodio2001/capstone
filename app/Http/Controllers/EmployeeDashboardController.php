<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

        // Get employee's training statistics from multiple sources
        $trainings = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
            ->with('course')
            ->get();

        // Also include completed trainings from employee self-reports
        $completedTrainingsSelfReported = \App\Models\CompletedTraining::where('employee_id', $employeeId)->count();

        $completedTrainings = $trainings->where('progress', 100)->count() + $completedTrainingsSelfReported;
        $inProgressTrainings = $trainings->where('progress', '>', 0)->where('progress', '<', 100)->count();
        $totalTrainings = $trainings->count() + $completedTrainingsSelfReported;
        $avgTrainingProgress = $trainings->avg('progress') ?? 0;

        // Get recent notifications (with fallback if table doesn't exist)
        try {
            $notifications = \App\Models\TrainingNotification::where('employee_id', $employeeId)
                ->orderBy('sent_at', 'desc')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            // If training_notifications table doesn't exist, use empty collection
            $notifications = collect();
        }

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

        // Get latest payslip amount from real payslip data
        $latestPayslip = $this->getLatestPayslipAmount($employeeId);
        $payslipMonth = $this->getLatestPayslipMonth($employeeId);

        // Get upcoming trainings count using the same logic as MyTrainingController
        $upcomingTrainings = $this->getAccurateUpcomingTrainingsCount($employeeId);

        // Get upcoming trainings list for the view
        $upcomingTrainingsList = $this->getUpcomingTrainingsFromMyTrainingController($employeeId);

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

        // Get company announcements
        $announcements = $this->getCompanyAnnouncements();

        // Get rewards data
        $rewards = $this->getEmployeeRewards($employeeId);

        // Fetch detailed employee data from API to sync with employee_list
        $apiEmployee = null;
        try {
            $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            if ($response->successful()) {
                $apiData = $response->json();
                $employeesList = isset($apiData['data']) ? $apiData['data'] : $apiData;

                if (is_array($employeesList)) {
                    foreach ($employeesList as $emp) {
                        $empId = $emp['employee_id'] ?? $emp['id'] ?? null;
                        $empEmail = $emp['email'] ?? '';

                        // Match by ID or Email (case-insensitive)
                        if (($empId && $empId == $employeeId) ||
                            ($empEmail && strtolower($empEmail) === strtolower($employee->email))) {

                            $apiEmployee = $emp;
                            // Normalize date field
                            if (isset($apiEmployee['date_hired'])) {
                                $apiEmployee['hire_date'] = date('Y-m-d', strtotime($apiEmployee['date_hired']));
                            }

                            // Debug API Employee Department Data
                            error_log('API Employee found for ID ' . $employeeId . ' (Matched via ' . ($empId == $employeeId ? 'ID' : 'Email') . ')');
                            error_log('API Department Data: ' . json_encode($apiEmployee['department'] ?? 'MISSING'));

                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('Failed to fetch employee data from API: ' . $e->getMessage());
        }

        // Debug: Log the upcoming trainings being passed to view
        error_log('Passing ' . $upcomingTrainingsList->count() . ' upcoming trainings to view for employee: ' . $employeeId);

        // Debug: Local Employee Department Data
        error_log('Local Employee Department ID: ' . $employee->department_id);
        error_log('Local Employee Department Relation: ' . json_encode($employee->department));

        return view('employee_ess_modules.employee_dashboard', compact(
            'employee',
            'apiEmployee',
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
            'announcements',
            'rewards'
        ));
    }

    /**
     * Get upcoming trainings using the same logic as MyTrainingController
     */
    private function getUpcomingTrainingsFromMyTrainingController($employeeId)
    {
        // Fix expiration dates for destination trainings before retrieving them
        $this->fixDestinationExpirationDates();

        // Fix expiration dates for competency gap trainings
        $this->fixCompetencyGapExpirationDates();

        // Use the exact same logic as MyTrainingController

        // Get ONLY competency gap sourced upcoming trainings (exclude admin assigned)
        $manualUpcoming = \App\Models\UpcomingTraining::where('employee_id', $employeeId)
            ->whereIn('source', ['competency_gap', 'competency_assigned', 'competency_auto_assigned'])
            ->get();

        // REMOVED: Admin-assigned trainings to eliminate duplicates
        // Only show competency gap sourced trainings in Recent Trainings
        $adminAssigned = collect(); // Empty collection

        // Get destination knowledge training assignments
        $existingUpcomingDestinations = \App\Models\UpcomingTraining::where('employee_id', $employeeId)
            ->where('source', 'destination_assigned')
            ->pluck('training_title')
            ->toArray();

        $destinationAssigned = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
            ->where('admin_approved_for_upcoming', true)
            ->whereNotIn('status', ['completed', 'declined'])
            ->whereNotIn('destination_name', $existingUpcomingDestinations)
            ->get()
            ->map(function($training) {
                // Generate proper Training ID for destination training
                $destinationYear = $training->created_at->format('Y');
                $sequentialNumber = str_pad($training->id, 4, '0', STR_PAD_LEFT);
                $properDestinationId = "DT{$destinationYear}{$sequentialNumber}";

                // Use proper expired date or calculate one
                $expiredDate = $training->expired_date;
                if (!$expiredDate) {
                    $expiredDate = $training->created_at->addMonths(3);
                }

                return (object)[
                    'upcoming_id' => $properDestinationId,
                    'training_title' => $training->destination_name,
                    'start_date' => $training->created_at,
                    'end_date' => $expiredDate,
                    'expired_date' => $expiredDate,
                    'status' => ucfirst($training->status),
                    'progress' => $training->progress ?? 0,
                    'source' => 'destination_assigned',
                    'assigned_by' => 'Admin',
                    'assigned_by_name' => 'Admin',
                    'assigned_date' => $training->created_at,
                    'destination_training_id' => $training->id,
                    'employee_id' => $training->employee_id,
                    'delivery_mode' => $training->delivery_mode ?? 'On-site Training',
                    'needs_response' => $training->needs_response ?? false
                ];
            });

        // Combine all upcoming trainings
        $upcoming = collect()
            ->merge($manualUpcoming->toArray())
            ->merge($adminAssigned->toArray())
            ->merge($destinationAssigned->toArray());

        return $upcoming;
    }

    /**
     * Get accurate upcoming trainings count using the same logic as MyTrainingController
     */
    private function getAccurateUpcomingTrainingsCount($employeeId)
    {
        // Clear any cached data first to ensure fresh counts
        Cache::forget("employee_training_counts_{$employeeId}");
        Cache::forget("upcoming_trainings_{$employeeId}");

        // Use the exact same logic as MyTrainingController
        $manualUpcoming = \App\Models\UpcomingTraining::where('employee_id', $employeeId)->get();

        $adminAssigned = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
            ->whereIn('status', ['Assigned', 'In Progress', 'Not Started'])
            ->whereHas('course')
            ->get();

        $competencyAssigned = \App\Models\CompetencyCourseAssignment::where('employee_id', $employeeId)
            ->whereIn('status', ['Assigned', 'In Progress', 'Not Started'])
            ->whereHas('course')
            ->get();

        // Apply the same deduplication logic as MyTrainingController
        $allTrainings = collect()
            ->merge($manualUpcoming->toArray())
            ->merge($adminAssigned->toArray())
            ->merge($competencyAssigned->toArray());

        $seenTitles = [];
        $seenCourseIds = [];
        $deduplicated = $allTrainings->filter(function($item) use (&$seenTitles, &$seenCourseIds) {
            $item = (object) $item;

            $rawTitle = $item->training_title ?? '';
            if (empty(trim($rawTitle))) {
                return false;
            }

            $normalizedTitle = strtolower(trim($rawTitle));
            $normalizedTitle = preg_replace('/\b(training|course|program|skills|knowledge|development)\b/i', '', $normalizedTitle);
            $normalizedTitle = preg_replace('/\s+/', ' ', trim($normalizedTitle));

            $courseId = $item->course_id ?? null;
            if ($courseId && in_array($courseId, $seenCourseIds)) {
                return false;
            }

            if (in_array($normalizedTitle, $seenTitles)) {
                return false;
            }

            if ($courseId) {
                $seenCourseIds[] = $courseId;
            }
            $seenTitles[] = $normalizedTitle;

            return true;
        });

        return $deduplicated->count();
    }

    /**
     * Get fresh dashboard counts for AJAX refresh
     */
    public function getDashboardCounts()
    {
        $employeeId = Auth::guard('employee')->user()->employee_id;

        // Get fresh upcoming trainings count
        $upcomingTrainings = $this->getAccurateUpcomingTrainingsCount($employeeId);

        // Get training requests count
        $requestsCount = \App\Models\TrainingRequest::where('employee_id', $employeeId)->count();

        // Get training progress count (only approved requests)
        $progressCount = \App\Models\TrainingRequest::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->count();

        return response()->json([
            'success' => true,
            'counts' => [
                'upcoming' => $upcomingTrainings,
                'requests' => $requestsCount,
                'progress' => $progressCount
            ]
        ]);
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

        // Get recent notifications (with fallback if table doesn't exist)
        try {
            $notifications = \App\Models\TrainingNotification::where('employee_id', $employeeId)
                ->orderBy('sent_at', 'desc')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            // If training_notifications table doesn't exist, use empty collection
            $notifications = collect();
        }

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

        // Get latest payslip amount from real payslip data
        $latestPayslip = $this->getLatestPayslipAmount($employeeId);
        $payslipMonth = $this->getLatestPayslipMonth($employeeId);

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

    /**
     * Fix expiration dates for destination training records
     */
    private function fixDestinationExpirationDates()
    {
        try {
            $updated = 0;

            // Get all destination training records without proper expiration dates
            $records = \App\Models\DestinationKnowledgeTraining::destinationTrainings()
                ->where(function($query) {
                    $query->whereNull('expired_date')
                          ->orWhere('expired_date', '0000-00-00 00:00:00')
                          ->orWhere('expired_date', '');
                })
                ->get();

            foreach ($records as $record) {
                // Set expiration date to 3 months from creation date
                $expirationDate = $record->created_at->addMonths(3);

                $record->expired_date = $expirationDate;

                // Ensure the record is properly marked for upcoming if active
                if ($record->is_active && !$record->admin_approved_for_upcoming) {
                    $record->admin_approved_for_upcoming = true;
                }

                $record->save();
                $updated++;
            }

            if ($updated > 0) {
                error_log("Fixed expiration dates for {$updated} destination training records in EmployeeDashboardController");
            }

        } catch (\Exception $e) {
            error_log('Error fixing destination expiration dates in EmployeeDashboardController: ' . $e->getMessage());
        }
    }

    /**
     * Fix expiration dates for competency gap trainings
     */
    private function fixCompetencyGapExpirationDates()
    {
        try {
            $updated = 0;

            // Get all competency gaps without proper expiration dates
            $competencyGaps = \App\Models\CompetencyGap::where(function($query) {
                $query->whereNull('expired_date')
                      ->orWhere('expired_date', '0000-00-00 00:00:00')
                      ->orWhere('expired_date', '');
            })->get();

            foreach ($competencyGaps as $gap) {
                // Set expiration date to 6 months from creation date for competency gaps
                $expirationDate = $gap->created_at->addMonths(6);

                $gap->expired_date = $expirationDate;
                $gap->save();
                $updated++;
            }

            // Also fix upcoming trainings that are competency gap assigned
            $upcomingTrainings = \App\Models\UpcomingTraining::where('source', 'competency_assigned')
                ->where(function($query) {
                    $query->whereNull('expired_date')
                          ->orWhere('expired_date', '0000-00-00 00:00:00')
                          ->orWhere('expired_date', '');
                })
                ->get();

            foreach ($upcomingTrainings as $training) {
                // Try to find matching competency gap
                $competencyName = str_replace([' Training', ' Course', ' Program'], '', $training->training_title);
                $competencyGap = \App\Models\CompetencyGap::whereHas('competency', function($query) use ($competencyName) {
                    $query->where('competency_name', 'LIKE', '%' . $competencyName . '%');
                })->where('employee_id', $training->employee_id)->first();

                if ($competencyGap && $competencyGap->expired_date) {
                    $training->expired_date = $competencyGap->expired_date;
                } else {
                    // Fallback: set to 6 months from creation
                    $training->expired_date = $training->created_at->addMonths(6);
                }

                $training->save();
                $updated++;
            }

            if ($updated > 0) {
                error_log("Fixed expiration dates for {$updated} competency gap training records in EmployeeDashboardController");
            }

        } catch (\Exception $e) {
            error_log('Error fixing competency gap expiration dates in EmployeeDashboardController: ' . $e->getMessage());
        }
    }

    /**
     * Get upcoming trainings list for admin view
     */
    private function getUpcomingTrainingsList($employeeId)
    {
        return $this->getUpcomingTrainingsFromMyTrainingController($employeeId);
    }

    /**
     * Get latest payslip amount for employee
     */
    private function getLatestPayslipAmount($employeeId)
    {
        try {
            // Try to get from payslips table if it exists
            if (Schema::hasTable('payslips')) {
                $latestPayslip = DB::table('payslips')
                    ->where('employee_id', $employeeId)
                    ->orderBy('period_end', 'desc')
                    ->first();

                if ($latestPayslip) {
                    return $latestPayslip->net_pay ?? $latestPayslip->gross_pay ?? 0;
                }
            }

            // Fallback to 0 if no payslip found
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get latest payslip month for employee
     */
    private function getLatestPayslipMonth($employeeId)
    {
        try {
            // Try to get from payslips table if it exists
            if (Schema::hasTable('payslips')) {
                $latestPayslip = DB::table('payslips')
                    ->where('employee_id', $employeeId)
                    ->orderBy('period_end', 'desc')
                    ->first();

                if ($latestPayslip && $latestPayslip->period_end) {
                    return Carbon::parse($latestPayslip->period_end)->format('F Y');
                }
            }

            // Fallback if no payslip found
            return 'No Record';
        } catch (\Exception $e) {
            return 'No Record';
        }
    }

    /**
     * Get company announcements
     */
    private function getCompanyAnnouncements()
    {
        try {
            // Try to get from announcements table if it exists
            if (Schema::hasTable('announcements')) {
                return DB::table('announcements')
                    ->where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
            }

            // Try alternative table names
            if (Schema::hasTable('company_announcements')) {
                return DB::table('company_announcements')
                    ->where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
            }

            // Create sample announcements for demonstration
            return collect([
                (object)[
                    'id' => 1,
                    'title' => 'Welcome to HR2ESS System',
                    'message' => 'Welcome to the new HR2ESS Employee Self-Service System. Please explore all the features available to you.',
                    'priority' => 'important',
                    'created_at' => Carbon::now()->subDays(1)->toDateTimeString(),
                ],
                (object)[
                    'id' => 2,
                    'title' => 'Training Completion Reminder',
                    'message' => 'Please complete your assigned trainings before the expiration dates to maintain compliance.',
                    'priority' => 'normal',
                    'created_at' => Carbon::now()->subDays(3)->toDateTimeString(),
                ],
                (object)[
                    'id' => 3,
                    'title' => 'System Maintenance Notice',
                    'message' => 'The system will undergo maintenance this weekend. Please save your work and log out by Friday evening.',
                    'priority' => 'urgent',
                    'created_at' => Carbon::now()->subDays(5)->toDateTimeString(),
                ]
            ]);
        } catch (\Exception $e) {
            // Return empty collection if there's an error
            return collect();
        }
    }

    /**
     * Get announcement details for modal view
     */
    public function getAnnouncementDetails($announcementId)
    {
        try {
            $announcement = null;

            // Try to get from announcements table if it exists
            if (Schema::hasTable('announcements')) {
                $announcement = DB::table('announcements')
                    ->where('id', $announcementId)
                    ->first();
            }

            // Try alternative table names
            if (!$announcement && Schema::hasTable('company_announcements')) {
                $announcement = DB::table('company_announcements')
                    ->where('id', $announcementId)
                    ->first();
            }

            // Fallback to sample data
            if (!$announcement) {
                $sampleAnnouncements = [
                    1 => [
                        'id' => 1,
                        'title' => 'Welcome to HR2ESS System',
                        'message' => 'Welcome to the new HR2ESS Employee Self-Service System. This comprehensive platform provides you with access to all your employment-related information and services.\n\nKey features include:\n• Training management and progress tracking\n• Leave application and approval workflow\n• Payslip access and download\n• Competency profile management\n• Attendance tracking\n• Request forms and document management\n\nPlease take some time to explore all the features available to you. If you have any questions or need assistance, please contact the HR department.',
                        'priority' => 'important',
                        'created_at' => Carbon::now()->subDays(1)->toDateTimeString(),
                        'author' => 'HR Department'
                    ],
                    2 => [
                        'id' => 2,
                        'title' => 'Training Completion Reminder',
                        'message' => 'This is a friendly reminder to complete your assigned trainings before the expiration dates.\n\nPlease note:\n• Training completion is mandatory for compliance\n• Expired trainings may affect your performance evaluation\n• Contact your supervisor if you need assistance\n• Training materials are available in the My Trainings section\n\nThank you for your cooperation in maintaining our training standards.',
                        'priority' => 'normal',
                        'created_at' => Carbon::now()->subDays(3)->toDateTimeString(),
                        'author' => 'Training Department'
                    ],
                    3 => [
                        'id' => 3,
                        'title' => 'System Maintenance Notice',
                        'message' => 'IMPORTANT: The HR2ESS system will undergo scheduled maintenance this weekend.\n\nMaintenance Details:\n• Date: This Saturday, 11:00 PM - Sunday, 6:00 AM\n• Expected Duration: 7 hours\n• Services Affected: All HR2ESS modules\n\nAction Required:\n• Please save all your work before Friday evening\n• Log out of the system completely\n• Do not attempt to access the system during maintenance\n\nWe apologize for any inconvenience and appreciate your understanding.',
                        'priority' => 'urgent',
                        'created_at' => Carbon::now()->subDays(5)->toDateTimeString(),
                        'author' => 'IT Department'
                    ]
                ];

                $announcement = $sampleAnnouncements[$announcementId] ?? null;
            }

            if ($announcement) {
                return response()->json([
                    'success' => true,
                    'announcement' => $announcement
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Announcement not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving announcement details'
            ], 500);
        }
    }

    /**
     * Handle employee profile update
     */
    public function updateProfile(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();

            // Verify password first
            if (!Hash::check($request->verify_password, $employee->password)) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_password',
                    'message' => 'Invalid password provided'
                ], 400);
            }

            // Validate input
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:employees,email,' . $employee->id,
                'phone_number' => 'nullable|string|max:20',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Update employee data
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone_number = $request->phone_number;

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = 'profile_' . $employee->employee_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('profile_pictures', $filename, 'public');
                $employee->profile_picture = 'profile_pictures/' . $filename;
            }

            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle leave application submission
     */
    public function submitLeaveApplication(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();

            // Validate input
            $request->validate([
                'leave_type' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:1000'
            ]);

            // Create leave application
            $leaveApplication = new \App\Models\RequestForm();
            $leaveApplication->employee_id = $employee->employee_id;
            $leaveApplication->request_type = 'Leave Application';
            $leaveApplication->requested_date = Carbon::now();
            $leaveApplication->status = 'Pending';
            $leaveApplication->reason = $request->reason;

            // Store additional leave details in a JSON field or separate fields
            $leaveApplication->details = json_encode([
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);

            $leaveApplication->save();

            return response()->json([
                'success' => true,
                'message' => 'Leave application submitted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting leave application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle attendance logging
     */
    public function logAttendance(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();

            // Validate input
            $request->validate([
                'timestamp' => 'required|date'
            ]);

            $timestamp = Carbon::parse($request->timestamp);
            $today = $timestamp->format('Y-m-d');

            // Check if attendance already logged for today
            $existingLog = \App\Models\AttendanceTimeLog::where('employee_id', $employee->employee_id)
                ->whereDate('log_date', $today)
                ->first();

            if ($existingLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance already logged for today'
                ], 400);
            }

            // Create attendance log
            $attendanceLog = new \App\Models\AttendanceTimeLog();
            $attendanceLog->employee_id = $employee->employee_id;
            $attendanceLog->log_date = $today; // Laravel will handle the date casting
            $attendanceLog->time_in = $timestamp->format('H:i:s');
            $attendanceLog->status = 'Present';
            $attendanceLog->save();

            return response()->json([
                'success' => true,
                'message' => 'Attendance logged successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error logging attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee rewards from API
     */
    private function getEmployeeRewards($employeeId)
    {
        try {
            $response = Http::get('https://hr1.jetlougetravels-ph.com/api/give-rewards');

            if(!$response->successful()) {
                error_log('Failed to fetch rewards from API for employee: ' . $employeeId);
                return collect();
            }

            $data = $response->json();

            // Process the data according to the structure
            $rewards = collect();

            if (is_array($data)) {
                foreach($data as $item) {
                    $employeeName = $item['employee_name'] ?? '';
                    $rewardName = $item['reward']['name'] ?? '';
                    $benefits = $item['reward']['benefits'] ?? '';
                    $rewardDescription = $item['reward']['description'] ?? 'Certificate of achievement';
                    $rewardType = $item['reward']['type'] ?? 'standard';
                    $createdAt = isset($item['created_at']) ? Carbon::parse($item['created_at']) : Carbon::now();

                    // Only add rewards for the current employee
                    if ($employeeName || isset($item['employee_id']) && $item['employee_id'] == $employeeId) {
                        $rewards->push((object)[
                            'name' => $rewardName,
                            'benefits' => $benefits,
                            'description' => $rewardDescription,
                            'type' => $rewardType,
                            'created_at' => $createdAt,
                            'employee_name' => $employeeName,
                            'given_date' => $item['given_date'] ?? $createdAt,
                            'given_by' => $item['given_by'] ?? 'N/A',
                            'status' => $item['status'] ?? 'approved',
                            'notes' => $item['notes'] ?? $item['reason'] ?? '',
                            'reward' => (object)[
                                'name' => $rewardName,
                                'description' => $rewardDescription,
                                'type' => $rewardType,
                                'benefits' => $benefits
                            ]
                        ]);
                    }
                }
            }

            return $rewards;

        } catch (\Exception $e) {
            error_log('Error fetching rewards for employee ' . $employeeId . ': ' . $e->getMessage());
            return collect();
        }
    }

    public function fetchGivenRewards() {
        try {
            $response = Http::get('https://hr1.jetlougetravels-ph.com/api/give-rewards');

            if(!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch rewards data'
                ], 500);
            }

            $data = $response->json();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching rewards: ' . $e->getMessage()
            ], 500);
        }
    }
}

