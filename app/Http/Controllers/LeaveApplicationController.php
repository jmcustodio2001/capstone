<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LeaveApplicationController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return redirect()->route('employee.login')->with('error', 'Please login to access this page.');
        }

        // Get current employee's leave applications only
        $leave_records = LeaveApplication::where('employee_id', $employee->employee_id)
            ->orderByDesc('created_at')
            ->get();

        // Calculate leave balances
        $leaveBalances = $this->calculateLeaveBalances($employee->employee_id);

        return view('employee_ess_modules.leave_balance.leave_application_balance', [
            'leave_records' => $leave_records,
            'leave_balances' => $leaveBalances,
            'error_message' => null
        ]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Leave application submission started', ['request_data' => $request->all()]);
            
            $employee = Auth::guard('employee')->user();
            if (!$employee) {
                Log::error('Employee not authenticated in leave application');
                return response()->json(['error' => 'Employee not authenticated.'], 401);
            }
            
            Log::info('Employee authenticated', ['employee_id' => $employee->employee_id]);

            $request->validate([
                'leave_type' => 'required|in:Vacation,Sick,Emergency',
                'leave_days' => 'required|integer|min:1',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:500',
                'contact_info' => 'nullable|string|max:255'
            ]);

            // Check leave balance
            $leaveBalances = $this->calculateLeaveBalances($employee->employee_id);
            $requestedType = $request->leave_type;
            
            if ($request->leave_days > $leaveBalances[$requestedType]['available']) {
                return response()->json([
                    'error' => "Insufficient leave balance. You only have {$leaveBalances[$requestedType]['available']} days available for {$requestedType} leave."
                ], 400);
            }

            // Generate unique leave ID
            $leaveId = 'LV' . date('Y') . str_pad(LeaveApplication::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create leave application - try with all columns first, fallback if needed
            $leaveData = [
                'employee_id' => $employee->employee_id,
                'leave_id' => $leaveId,
                'leave_type' => $request->leave_type,
                'leave_days' => $request->leave_days,
                'days_requested' => $request->leave_days,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'reason' => $request->reason,
                'contact_info' => $request->contact_info,
                'status' => 'Pending',
                'applied_date' => Carbon::now(),
                'application_date' => Carbon::now()
            ];

            try {
                $leaveApplication = LeaveApplication::create($leaveData);
                Log::info('Leave application created successfully', ['leave_id' => $leaveId, 'application_id' => $leaveApplication->id]);
            } catch (\Exception $e) {
                Log::error('Failed to create leave application with full data: ' . $e->getMessage());
                // If full creation fails, try with minimal columns
                $minimalData = [
                    'employee_id' => $employee->employee_id,
                    'leave_type' => $request->leave_type,
                    'days_requested' => $request->leave_days,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'reason' => $request->reason,
                    'status' => 'Pending'
                ];
                
                $leaveApplication = LeaveApplication::create($minimalData);
                
                // Try to update with additional fields if possible
                try {
                    $leaveApplication->update([
                        'leave_id' => $leaveId,
                        'leave_days' => $request->leave_days,
                        'contact_info' => $request->contact_info
                    ]);
                } catch (\Exception $updateE) {
                    // Log the update failure but continue
                    Log::warning('Could not update leave application with additional fields: ' . $updateE->getMessage());
                }
            }

            // Log activity
            ActivityLog::create([
                'employee_id' => $employee->employee_id,
                'module' => 'Leave Management',
                'action' => 'Leave Application Submitted',
                'description' => "Applied for {$request->leave_days} days of {$request->leave_type} leave from {$request->start_date} to {$request->end_date}",
                'timestamp' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave application submitted successfully!',
                'leave_id' => $leaveId
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Leave application submission error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return response()->json(['error' => 'Employee not authenticated.'], 401);
        }
        
        $leave_application = LeaveApplication::where('employee_id', $employee->employee_id)
            ->where('id', $id)
            ->with('employee')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $leave_application
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return response()->json(['error' => 'Employee not authenticated.'], 401);
        }
        
        $leave_application = LeaveApplication::where('employee_id', $employee->employee_id)
            ->where('id', $id)
            ->firstOrFail();

        // Only allow editing if status is Pending
        if ($leave_application->status !== 'Pending') {
            return response()->json([
                'error' => 'Cannot edit leave application that is already ' . strtolower($leave_application->status)
            ], 400);
        }

        $request->validate([
            'leave_type' => 'required|in:Vacation,Sick,Emergency',
            'leave_days' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'contact_info' => 'nullable|string|max:255'
        ]);

        // Check leave balance (excluding current application)
        $leaveBalances = $this->calculateLeaveBalances($employee->employee_id, $id);
        $requestedType = $request->leave_type;
        
        if ($request->leave_days > $leaveBalances[$requestedType]['available']) {
            return response()->json([
                'error' => "Insufficient leave balance. You only have {$leaveBalances[$requestedType]['available']} days available for {$requestedType} leave."
            ], 400);
        }

        $leave_application->update([
            'leave_type' => $request->leave_type,
            'leave_days' => $request->leave_days,
            'days_requested' => $request->leave_days,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'contact_info' => $request->contact_info
        ]);

        // Log activity
        ActivityLog::create([
            'employee_id' => $employee->employee_id,
            'module' => 'Leave Management',
            'action' => 'Leave Application Updated',
            'description' => "Updated leave application {$leave_application->leave_id}: {$request->leave_days} days of {$request->leave_type} leave from {$request->start_date} to {$request->end_date}",
            'timestamp' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave application updated successfully!'
        ]);
    }

    public function cancel($id)
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not authenticated.');
        }
        
        $leave_application = LeaveApplication::where('employee_id', $employee->employee_id)
            ->where('id', $id)
            ->firstOrFail();

        // Only allow cancellation if status is Pending
        if ($leave_application->status !== 'Pending') {
            return redirect()->back()->with('error', 'Cannot cancel leave application that is already ' . strtolower($leave_application->status));
        }

        $leave_application->update(['status' => 'Cancelled']);

        // Log activity
        ActivityLog::create([
            'employee_id' => $employee->employee_id,
            'module' => 'Leave Management',
            'action' => 'Leave Application Cancelled',
            'description' => "Cancelled leave application {$leave_application->leave_id}",
            'timestamp' => Carbon::now()
        ]);

        return redirect()->back()->with('success', 'Leave application cancelled successfully!');
    }

    /**
     * Admin method to approve/reject leave applications
     */
    public function adminUpdateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:Approved,Rejected',
                'approved_by' => 'required|string|max:255',
                'remarks' => 'nullable|string|max:1000'
            ]);

            $leaveApplication = LeaveApplication::findOrFail($id);

            if ($leaveApplication->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave application has already been processed',
                    'current_status' => $leaveApplication->status
                ], 400);
            }

            // If approving, check if employee still has sufficient balance
            if ($request->status === 'Approved') {
                $leaveBalances = $this->calculateLeaveBalances(
                    $leaveApplication->employee_id, 
                    $leaveApplication->id
                );
                $requestedType = $leaveApplication->leave_type;
                $requestedDays = $leaveApplication->days_requested ?? $leaveApplication->leave_days;
                
                if ($requestedDays > $leaveBalances[$requestedType]['available']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot approve: Insufficient leave balance. Available: {$leaveBalances[$requestedType]['available']} days",
                        'available_balance' => $leaveBalances[$requestedType]['available'],
                        'requested_days' => $requestedDays
                    ], 400);
                }
            }

            // Update leave application
            $leaveApplication->update([
                'status' => $request->status,
                'approved_by' => $request->approved_by,
                'approved_date' => Carbon::now(),
                'remarks' => $request->remarks
            ]);

            // Log activity
            ActivityLog::create([
                'employee_id' => $leaveApplication->employee_id,
                'module' => 'Leave Management',
                'action' => 'Leave Application ' . $request->status,
                'description' => "{$request->status}: Leave application {$leaveApplication->leave_id} by {$request->approved_by}. Remarks: " . ($request->remarks ?? 'None'),
                'timestamp' => Carbon::now()
            ]);

            // Calculate new balance after approval
            $newBalance = null;
            if ($request->status === 'Approved') {
                $leaveBalances = $this->calculateLeaveBalances($leaveApplication->employee_id);
                $newBalance = $leaveBalances[$leaveApplication->leave_type]['available'];
                
                // Trigger webhook notification if configured
                $this->triggerWebhookNotification($leaveApplication, $request->status);
            }

            return response()->json([
                'success' => true,
                'message' => "Leave application {$request->status} successfully",
                'data' => [
                    'leave_id' => $leaveApplication->leave_id,
                    'status' => $request->status,
                    'approved_by' => $request->approved_by,
                    'approved_date' => $leaveApplication->approved_date->toISOString(),
                    'remarks' => $request->remarks,
                    'new_balance' => $newBalance
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Leave status update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating leave status'
            ], 500);
        }
    }

    /**
     * Trigger webhook notification for external systems
     */
    private function triggerWebhookNotification($leaveApplication, $status)
    {
        try {
            // In a real implementation, you'd retrieve webhook URLs from a database
            // For now, we'll just log the notification
            $webhookData = [
                'leave_id' => $leaveApplication->leave_id,
                'employee_id' => $leaveApplication->employee_id,
                'status' => $status,
                'leave_type' => $leaveApplication->leave_type,
                'days_requested' => $leaveApplication->days_requested ?? $leaveApplication->leave_days,
                'start_date' => $leaveApplication->start_date,
                'end_date' => $leaveApplication->end_date,
                'approved_by' => $leaveApplication->approved_by,
                'approved_date' => $leaveApplication->approved_date->toISOString(),
                'timestamp' => Carbon::now()->toISOString()
            ];

            Log::info('Leave status webhook notification triggered', $webhookData);

            // Here you would make HTTP requests to registered webhook URLs
            // Example:
            // Http::post($webhookUrl, $webhookData);

        } catch (\Exception $e) {
            Log::error('Webhook notification error: ' . $e->getMessage());
        }
    }

    private function calculateLeaveBalances($employeeId, $excludeApplicationId = null)
    {
        // Default annual leave allocations
        $allocations = [
            'Vacation' => 15,
            'Sick' => 10,
            'Emergency' => 5
        ];

        $balances = [];
        
        // Get current year for annual reset functionality
        $currentYear = Carbon::now()->year;

        foreach ($allocations as $type => $total) {
            $query = LeaveApplication::where('employee_id', $employeeId)
                ->where('leave_type', $type)
                ->whereIn('status', ['Approved', 'Pending'])
                ->whereYear('start_date', $currentYear); // Only count leave days used in current year

            if ($excludeApplicationId) {
                $query->where('id', '!=', $excludeApplicationId);
            }

            $used = $query->sum('days_requested');
            $available = max(0, $total - $used);

            $balances[$type] = [
                'total' => $total,
                'used' => $used,
                'available' => $available,
                'percentage' => $total > 0 ? round(($available / $total) * 100) : 0,
                'reset_year' => $currentYear // Track which year this balance is for
            ];
        }

        return $balances;
    }

    /**
     * Calculate leave balances for a specific year
     * Useful for historical reporting and year-end summaries
     */
    private function calculateLeaveBalancesForYear($employeeId, $year, $excludeApplicationId = null)
    {
        // Default annual leave allocations
        $allocations = [
            'Vacation' => 15,
            'Sick' => 10,
            'Emergency' => 5
        ];

        $balances = [];

        foreach ($allocations as $type => $total) {
            $query = LeaveApplication::where('employee_id', $employeeId)
                ->where('leave_type', $type)
                ->whereIn('status', ['Approved', 'Pending'])
                ->whereYear('start_date', $year); // Count leave days used in specified year

            if ($excludeApplicationId) {
                $query->where('id', '!=', $excludeApplicationId);
            }

            $used = $query->sum('days_requested');
            $available = max(0, $total - $used);

            $balances[$type] = [
                'total' => $total,
                'used' => $used,
                'available' => $available,
                'percentage' => $total > 0 ? round(($available / $total) * 100) : 0,
                'year' => $year
            ];
        }

        return $balances;
    }
}
