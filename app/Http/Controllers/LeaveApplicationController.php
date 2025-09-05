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
            $employee = Auth::guard('employee')->user();
            if (!$employee) {
                return response()->json(['error' => 'Employee not authenticated.'], 401);
            }

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
            } catch (\Exception $e) {
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

    private function calculateLeaveBalances($employeeId, $excludeApplicationId = null)
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
                ->whereIn('status', ['Approved', 'Pending']);

            if ($excludeApplicationId) {
                $query->where('id', '!=', $excludeApplicationId);
            }

            $used = $query->sum('days_requested');
            $available = max(0, $total - $used);

            $balances[$type] = [
                'total' => $total,
                'used' => $used,
                'available' => $available,
                'percentage' => $total > 0 ? round(($available / $total) * 100) : 0
            ];
        }

        return $balances;
    }
}
