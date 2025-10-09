<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\Employee;
use App\Models\ActivityLog;
use App\Services\ExternalLeaveApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LeaveApplicationApiController extends Controller
{
    /**
     * Submit a new leave request via API
     */
    public function submitLeaveRequest(Request $request)
    {
        try {
            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|exists:employees,employee_id',
                'leave_type' => 'required|in:Vacation,Sick,Emergency',
                'leave_days' => 'required|integer|min:1|max:365',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:500',
                'contact_info' => 'nullable|string|max:255',
                'api_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify API key (you should implement proper API key authentication)
            if (!$this->validateApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

            // Get employee
            $employee = Employee::where('employee_id', $request->employee_id)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            // Check leave balance
            $leaveBalances = $this->calculateLeaveBalances($employee->employee_id);
            $requestedType = $request->leave_type;
            
            if ($request->leave_days > $leaveBalances[$requestedType]['available']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient leave balance. Available: {$leaveBalances[$requestedType]['available']} days",
                    'available_balance' => $leaveBalances[$requestedType]['available'],
                    'requested_days' => $request->leave_days
                ], 400);
            }

            // Generate unique leave ID
            $leaveId = 'LV' . date('Y') . str_pad(LeaveApplication::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create leave application
            $leaveApplication = LeaveApplication::create([
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
            ]);

            // Log activity
            ActivityLog::create([
                'employee_id' => $employee->employee_id,
                'module' => 'Leave Management API',
                'action' => 'Leave Application Submitted via API',
                'description' => "API submission: {$request->leave_days} days of {$request->leave_type} leave from {$request->start_date} to {$request->end_date}",
                'timestamp' => Carbon::now()
            ]);

            // Send to external HR3 system
            $externalService = new ExternalLeaveApiService();
            $hr3Result = $externalService->sendLeaveRequest($leaveApplication, $employee);
            
            // Log HR3 integration result
            if ($hr3Result['success']) {
                Log::info('Leave request successfully sent to HR3', [
                    'leave_id' => $leaveId,
                    'hr3_response' => $hr3Result
                ]);
            } else {
                Log::warning('Failed to send leave request to HR3', [
                    'leave_id' => $leaveId,
                    'hr3_error' => $hr3Result
                ]);
            }

            $responseData = [
                'leave_id' => $leaveId,
                'application_id' => $leaveApplication->id,
                'status' => 'Pending',
                'employee_id' => $employee->employee_id,
                'leave_type' => $request->leave_type,
                'days_requested' => $request->leave_days,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'submitted_at' => $leaveApplication->created_at->toISOString(),
                'remaining_balance' => $leaveBalances[$requestedType]['available'] - $request->leave_days,
                'hr3_integration' => [
                    'sent_to_hr3' => $hr3Result['success'],
                    'hr3_message' => $hr3Result['message']
                ]
            ];

            // Include HR3 response data if successful
            if ($hr3Result['success'] && isset($hr3Result['hr3_response'])) {
                $responseData['hr3_integration']['hr3_response'] = $hr3Result['hr3_response'];
            }

            return response()->json([
                'success' => true,
                'message' => 'Leave application submitted successfully' . 
                    ($hr3Result['success'] ? ' and sent to HR3 system' : ' (HR3 integration failed)'),
                'data' => $responseData
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Leave application submission error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error occurred while processing leave request',
                'error_code' => 'LEAVE_SUBMISSION_ERROR'
            ], 500);
        }
    }

    /**
     * Get leave application status
     */
    public function getLeaveStatus(Request $request, $leaveId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['leave_id' => $leaveId]), [
                'leave_id' => 'required|string',
                'api_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

            $leaveApplication = LeaveApplication::where('leave_id', $leaveId)
                ->orWhere('id', $leaveId)
                ->with('employee')
                ->first();

            if (!$leaveApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave application not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'leave_id' => $leaveApplication->leave_id,
                    'application_id' => $leaveApplication->id,
                    'employee_id' => $leaveApplication->employee_id,
                    'employee_name' => $leaveApplication->employee ? 
                        $leaveApplication->employee->first_name . ' ' . $leaveApplication->employee->last_name : 'N/A',
                    'leave_type' => $leaveApplication->leave_type,
                    'days_requested' => $leaveApplication->days_requested ?? $leaveApplication->leave_days,
                    'start_date' => $leaveApplication->start_date,
                    'end_date' => $leaveApplication->end_date,
                    'reason' => $leaveApplication->reason,
                    'status' => $leaveApplication->status,
                    'submitted_at' => $leaveApplication->created_at->toISOString(),
                    'approved_by' => $leaveApplication->approved_by,
                    'approved_date' => $leaveApplication->approved_date ? 
                        Carbon::parse($leaveApplication->approved_date)->toISOString() : null,
                    'remarks' => $leaveApplication->remarks
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Leave status retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving leave status'
            ], 500);
        }
    }

    /**
     * Approve or reject leave application (Admin only)
     */
    public function updateLeaveStatus(Request $request, $leaveId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['leave_id' => $leaveId]), [
                'leave_id' => 'required|string',
                'status' => 'required|in:Approved,Rejected',
                'approved_by' => 'required|string|max:255',
                'remarks' => 'nullable|string|max:1000',
                'admin_api_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateAdminApiKey($request->admin_api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid admin API key'
                ], 401);
            }

            $leaveApplication = LeaveApplication::where('leave_id', $leaveId)
                ->orWhere('id', $leaveId)
                ->first();

            if (!$leaveApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave application not found'
                ], 404);
            }

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

            // Store old status for HR3 notification
            $oldStatus = $leaveApplication->status;

            // Update leave application
            $leaveApplication->update([
                'status' => $request->status,
                'approved_by' => $request->approved_by,
                'approved_date' => Carbon::now(),
                'remarks' => $request->remarks
            ]);

            // Send status update to HR3 system
            $externalService = new ExternalLeaveApiService();
            $hr3Result = $externalService->sendStatusUpdate($leaveApplication, $oldStatus, $request->status);
            
            // Log HR3 status update result
            if ($hr3Result['success']) {
                Log::info('Status update successfully sent to HR3', [
                    'leave_id' => $leaveApplication->leave_id,
                    'status_change' => "$oldStatus -> {$request->status}",
                    'hr3_response' => $hr3Result
                ]);
            } else {
                Log::warning('Failed to send status update to HR3', [
                    'leave_id' => $leaveApplication->leave_id,
                    'status_change' => "$oldStatus -> {$request->status}",
                    'hr3_error' => $hr3Result
                ]);
            }

            // Log activity
            ActivityLog::create([
                'employee_id' => $leaveApplication->employee_id,
                'module' => 'Leave Management API',
                'action' => 'Leave Application ' . $request->status . ' via API',
                'description' => "API {$request->status}: Leave application {$leaveApplication->leave_id} by {$request->approved_by}. Remarks: " . ($request->remarks ?? 'None'),
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

            $responseData = [
                'leave_id' => $leaveApplication->leave_id,
                'application_id' => $leaveApplication->id,
                'status' => $request->status,
                'approved_by' => $request->approved_by,
                'approved_date' => $leaveApplication->approved_date->toISOString(),
                'remarks' => $request->remarks,
                'new_balance' => $newBalance,
                'hr3_integration' => [
                    'status_sent_to_hr3' => $hr3Result['success'],
                    'hr3_message' => $hr3Result['message']
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => "Leave application {$request->status} successfully" . 
                    ($hr3Result['success'] ? ' and status sent to HR3 system' : ' (HR3 status update failed)'),
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('API Leave status update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating leave status'
            ], 500);
        }
    }

    /**
     * Get employee leave balance
     */
    public function getLeaveBalance(Request $request, $employeeId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['employee_id' => $employeeId]), [
                'employee_id' => 'required|string',
                'api_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

            $employee = Employee::where('employee_id', $employeeId)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $leaveBalances = $this->calculateLeaveBalances($employeeId);

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'leave_balances' => $leaveBalances,
                    'as_of_date' => Carbon::now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Leave balance retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving leave balance'
            ], 500);
        }
    }

    /**
     * Get employee leave history
     */
    public function getLeaveHistory(Request $request, $employeeId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['employee_id' => $employeeId]), [
                'employee_id' => 'required|string',
                'api_key' => 'required|string',
                'limit' => 'nullable|integer|min:1|max:100',
                'status' => 'nullable|in:Pending,Approved,Rejected,Cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

            $employee = Employee::where('employee_id', $employeeId)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $query = LeaveApplication::where('employee_id', $employeeId)
                ->orderByDesc('created_at');

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $limit = $request->limit ?? 50;
            $leaveApplications = $query->limit($limit)->get();

            $history = $leaveApplications->map(function ($leave) {
                return [
                    'leave_id' => $leave->leave_id,
                    'application_id' => $leave->id,
                    'leave_type' => $leave->leave_type,
                    'days_requested' => $leave->days_requested ?? $leave->leave_days,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'reason' => $leave->reason,
                    'status' => $leave->status,
                    'submitted_at' => $leave->created_at->toISOString(),
                    'approved_by' => $leave->approved_by,
                    'approved_date' => $leave->approved_date ? 
                        Carbon::parse($leave->approved_date)->toISOString() : null,
                    'remarks' => $leave->remarks
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'leave_history' => $history,
                    'total_records' => $history->count(),
                    'limit_applied' => $limit
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Leave history retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving leave history'
            ], 500);
        }
    }

    /**
     * Calculate leave balances for an employee
     */
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

            $used = $query->sum('days_requested') ?: $query->sum('leave_days');
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
     * Calculate leave balances for a specific year (for historical reporting)
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

            $used = $query->sum('days_requested') ?: $query->sum('leave_days');
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

    /**
     * Validate API key (implement your own logic)
     */
    private function validateApiKey($apiKey)
    {
        // Implement your API key validation logic here
        // For now, using a simple check - replace with proper implementation
        $validApiKeys = [
            'hr2ess_api_key_2025',
            'leave_management_api_v1',
            env('LEAVE_API_KEY', 'default_api_key')
        ];

        return in_array($apiKey, $validApiKeys);
    }

    /**
     * Validate admin API key (implement your own logic)
     */
    private function validateAdminApiKey($apiKey)
    {
        // Implement your admin API key validation logic here
        $validAdminApiKeys = [
            'hr2ess_admin_api_key_2025',
            'leave_admin_api_v1',
            env('LEAVE_ADMIN_API_KEY', 'default_admin_api_key')
        ];

        return in_array($apiKey, $validAdminApiKeys);
    }

    /**
     * Webhook endpoint for external systems to receive status updates
     */
    public function webhookStatusUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'webhook_secret' => 'required|string',
                'leave_id' => 'required|string',
                'callback_url' => 'required|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate webhook secret
            if (!$this->validateWebhookSecret($request->webhook_secret)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook secret'
                ], 401);
            }

            $leaveApplication = LeaveApplication::where('leave_id', $request->leave_id)
                ->orWhere('id', $request->leave_id)
                ->first();

            if (!$leaveApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave application not found'
                ], 404);
            }

            // Store webhook callback URL for future notifications
            // You might want to create a webhooks table to store this information
            $webhookData = [
                'leave_id' => $leaveApplication->leave_id,
                'callback_url' => $request->callback_url,
                'registered_at' => Carbon::now()->toISOString()
            ];

            // For now, we'll just return success. In a real implementation,
            // you'd store this in a webhooks table and use it when status changes
            Log::info('Webhook registered for leave application', $webhookData);

            return response()->json([
                'success' => true,
                'message' => 'Webhook registered successfully',
                'data' => $webhookData
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook registration error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error registering webhook'
            ], 500);
        }
    }

    /**
     * Validate webhook secret
     */
    private function validateWebhookSecret($secret)
    {
        $validSecrets = [
            'hr2ess_webhook_secret_2025',
            env('LEAVE_WEBHOOK_SECRET', 'default_webhook_secret')
        ];

        return in_array($secret, $validSecrets);
    }

    /**
     * Test HR3 API connection
     */
    public function testHR3Connection(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'api_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

            $externalService = new ExternalLeaveApiService();
            $result = $externalService->testConnection();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'hr3_connection_test' => $result,
                'timestamp' => Carbon::now()->toISOString()
            ], $result['success'] ? 200 : 500);

        } catch (\Exception $e) {
            Log::error('HR3 connection test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error testing HR3 connection: ' . $e->getMessage()
            ], 500);
        }
    }
}
