<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestForm;
use App\Models\CourseManagement;
use App\Models\ActivityLog;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RequestFormController extends Controller
{
    /**
     * Get the authenticated employee from Guard or Session
     */
    private function getAuthenticatedEmployee()
    {
        // 1. Try to get from Employee Guard
        if (Auth::guard('employee')->check()) {
            return Auth::guard('employee')->user();
        }

        // 2. Try to get from Default Guard
        if (Auth::check()) {
            return Auth::user();
        }

        // 3. Try to get from Session (for external employees)
        $externalData = session('external_employee_data');
        if ($externalData) {
            // Create a temporary object or hydration
            $employee = new Employee();
            $employee->forceFill($externalData);
            // Ensure employee_id is set
            if (!isset($employee->employee_id) && isset($externalData['employee_id'])) {
                $employee->employee_id = $externalData['employee_id'];
            }
            return $employee;
        }

        return null;
    }

    public function index()
    {
        $employee = $this->getAuthenticatedEmployee();

        if (!$employee) {
            // Redirect to login if no employee found
            return redirect()->route('login');
        }

        $employeeId = $employee->employee_id;
        $requests = RequestForm::where('employee_id', $employeeId)->orderByDesc('requested_date')->get();

        return view('employee_ess_modules.request_form.request_forms', compact('requests', 'employee'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('RequestForm store method called', [
                'request_data' => $request->all(),
                'auth_guard_check' => Auth::guard('employee')->check(),
                'auth_user_id' => Auth::guard('employee')->id(),
                'session_id' => session()->getId(),
                'csrf_token' => $request->header('X-CSRF-TOKEN'),
                'content_type' => $request->header('Content-Type'),
                'expects_json' => $request->expectsJson()
            ]);

            // Always return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {

                // Validate the request data including password
                try {
                    $validated = $request->validate([
                        'password' => 'required|string|min:3',
                        'employee_id' => 'required|string',
                        'request_type' => 'required|string',
                        'reason' => 'required|string',
                        'status' => 'required|string',
                        'requested_date' => 'required|date',
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Log::error('Validation failed', ['errors' => $e->errors()]);
                    $errorMessages = [];
                    foreach ($e->errors() as $field => $messages) {
                        $errorMessages = array_merge($errorMessages, $messages);
                    }
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed: ' . implode(', ', $errorMessages)
                    ], 422);
                }

                Log::info('Request validation passed', ['validated_data' => $validated]);

                // Get authenticated user (supports external employees)
                $employee = $this->getAuthenticatedEmployee();

                if (!$employee) {
                    Log::error('Unauthenticated user attempting to create request', [
                        'session_data' => session()->all(),
                        'guards' => [
                            'employee' => Auth::guard('employee')->check(),
                            'web' => Auth::guard('web')->check(),
                            'admin' => Auth::guard('admin')->check()
                        ]
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Your session has expired. Please refresh the page and log in again.'
                    ], 401);
                }

                Log::info('Employee authenticated for request creation', [
                    'employee_id' => $employee->employee_id,
                    'employee_email' => $employee->email
                ]);

                // Verify employee password
                if (!$this->verifyEmployeePassword($validated['password'])) {
                    Log::warning('Invalid password attempt for employee', ['employee_id' => $employee->employee_id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password. Please enter your correct password.'
                    ], 401);
                }

                Log::info('Password verification passed');

                // Remove password from data before storing
                unset($validated['password']);

                // Create the request
                $requestForm = RequestForm::create($validated);
                Log::info('Request form created successfully', ['request_id' => $requestForm->request_id]);

                // Log the activity using ActivityLog's proper method
                try {
                    ActivityLog::createLog([
                        'module' => 'Request Management',
                        'action' => 'create',
                        'description' => 'Created document request: ' . $validated['request_type'] . ' for employee ID: ' . $validated['employee_id'],
                    ]);
                } catch (\Exception $logException) {
                    Log::warning('Failed to create activity log', [
                        'error' => $logException->getMessage(),
                        'request_id' => $requestForm->request_id ?? 'unknown'
                    ]);
                    // Don't fail the create operation if logging fails
                }

                // Always return JSON response for AJAX requests
                return response()->json([
                    'success' => true,
                    'message' => 'Document request submitted successfully!'
                ]);
            }

            // Non-AJAX request handling (fallback)
            $validated = $request->validate([
                'password' => 'required|string|min:3',
                'employee_id' => 'required|string',
                'request_type' => 'required|string',
                'reason' => 'required|string',
                'status' => 'required|string',
                'requested_date' => 'required|date',
            ]);

            if (!Auth::guard('employee')->check()) {
                return redirect()->route('employee.login')->with('error', 'Please log in to continue.');
            }

            $employee = Auth::guard('employee')->user();
            if (!$employee || !$this->verifyEmployeePassword($validated['password'])) {
                return redirect()->back()->with('error', 'Invalid password. Please try again.');
            }

            unset($validated['password']);
            $requestForm = RequestForm::create($validated);

            try {
                ActivityLog::createLog([
                    'module' => 'Request Management',
                    'action' => 'create',
                    'description' => 'Created document request: ' . $validated['request_type'] . ' for employee ID: ' . $validated['employee_id'],
                ]);
            } catch (\Exception $logException) {
                Log::warning('Failed to create activity log', ['error' => $logException->getMessage()]);
            }

            return redirect()->route('employee.requests.index')->with('success', 'Request submitted successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating request form', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit request: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified request
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('RequestForm update method called', ['request_id' => $id, 'request_data' => $request->all()]);

            // Validate the request data including password
            try {
                $validated = $request->validate([
                    'password' => 'required|string|min:3',
                    'request_type' => 'required|string',
                    'reason' => 'required|string',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Update validation failed', ['errors' => $e->errors()]);
                $errorMessages = [];
                foreach ($e->errors() as $field => $messages) {
                    $errorMessages = array_merge($errorMessages, $messages);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errorMessages)
                ], 422);
            }

            Log::info('Update validation passed', ['validated_data' => $validated]);

            // Get authenticated user (supports external employees)
            $employee = $this->getAuthenticatedEmployee();

            if (!$employee) {
                Log::error('Unauthenticated user attempting to update request');
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired. Please refresh the page and log in again.'
                ], 401);
            }

            // Verify employee password
            if (!$this->verifyEmployeePassword($validated['password'])) {
                Log::warning('Invalid password attempt for employee during update', ['employee_id' => $employee->employee_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password. Please enter your correct password.'
                ], 401);
            }

            Log::info('Password verification passed for update');

            // Find the request and verify ownership
            $requestForm = RequestForm::where('request_id', $id)
                ->where('employee_id', $employee->employee_id)
                ->first();

            if (!$requestForm) {
                Log::warning('Request not found or not owned by employee', ['request_id' => $id, 'employee_id' => $employee->employee_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Request not found or you do not have permission to edit it.'
                ], 404);
            }

            // Only allow editing of pending requests
            if (strtolower($requestForm->status) !== 'pending') {
                Log::warning('Attempt to edit non-pending request', ['request_id' => $id, 'status' => $requestForm->status]);
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be edited.'
                ], 403);
            }

            // Remove password from data before updating
            unset($validated['password']);

            // Update the request
            $requestForm->update($validated);
            Log::info('Request form updated successfully', ['request_id' => $id]);

            // Log the activity using ActivityLog's proper method
            try {
                ActivityLog::createLog([
                    'module' => 'Request Management',
                    'action' => 'update',
                    'description' => 'Updated document request ID: ' . $id . ' for employee ID: ' . $employee->employee_id,
                ]);
            } catch (\Exception $logException) {
                Log::warning('Failed to create activity log', [
                    'error' => $logException->getMessage(),
                    'request_id' => $id
                ]);
                // Don't fail the update operation if logging fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Document request updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating request form', ['request_id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the specified request
     */
    public function destroy(Request $request, $id)
    {
        try {
            Log::info('RequestForm destroy method called', [
                'request_id' => $id,
                'request_id_type' => gettype($id),
                'request_data' => $request->all(),
                'auth_check' => Auth::guard('employee')->check(),
                'user_id' => Auth::guard('employee')->id()
            ]);

            // Validate password
            try {
                $validated = $request->validate([
                    'password' => 'required|string|min:3',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Delete validation failed', ['errors' => $e->errors()]);
                $errorMessages = [];
                foreach ($e->errors() as $field => $messages) {
                    $errorMessages = array_merge($errorMessages, $messages);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errorMessages)
                ], 422);
            }

            Log::info('Delete validation passed');

            // Get authenticated user (supports external employees)
            $employee = $this->getAuthenticatedEmployee();

            if (!$employee) {
                Log::error('Unauthenticated user attempting to delete request');
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired. Please refresh the page and log in again.'
                ], 401);
            }

            // Verify employee password
            if (!$this->verifyEmployeePassword($validated['password'])) {
                Log::warning('Invalid password attempt for employee during delete', ['employee_id' => $employee->employee_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password. Please enter your correct password.'
                ], 401);
            }

            Log::info('Password verification passed for delete');

            // Find the request and verify ownership
            Log::info('Looking for request', [
                'request_id' => $id,
                'employee_id' => $employee->employee_id,
                'all_requests_for_employee' => RequestForm::where('employee_id', $employee->employee_id)->pluck('request_id')->toArray()
            ]);

            $requestForm = RequestForm::where('request_id', $id)
                ->where('employee_id', $employee->employee_id)
                ->first();

            if (!$requestForm) {
                Log::warning('Request not found or not owned by employee during delete', [
                    'request_id' => $id,
                    'employee_id' => $employee->employee_id,
                    'request_exists_globally' => RequestForm::where('request_id', $id)->exists()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Request not found or you do not have permission to delete it.'
                ], 404);
            }

            // Only allow deletion of pending requests
            if (strtolower($requestForm->status) !== 'pending') {
                Log::warning('Attempt to delete non-pending request', ['request_id' => $id, 'status' => $requestForm->status]);
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be deleted.'
                ], 403);
            }

            // Store request details for logging
            $requestType = $requestForm->request_type;

            try {
                // Delete the request
                $requestForm->delete();
                Log::info('Request form deleted successfully', ['request_id' => $id]);

                // Log the activity using ActivityLog's proper method
                try {
                    ActivityLog::createLog([
                        'module' => 'Request Management',
                        'action' => 'delete',
                        'description' => 'Deleted document request ID: ' . $id . ' (' . $requestType . ') for employee ID: ' . $employee->employee_id,
                    ]);
                } catch (\Exception $logException) {
                    Log::warning('Failed to create activity log', [
                        'error' => $logException->getMessage(),
                        'request_id' => $id
                    ]);
                    // Don't fail the delete operation if logging fails
                }
            } catch (\Exception $deleteException) {
                Log::error('Error deleting request from database', [
                    'request_id' => $id,
                    'error' => $deleteException->getMessage(),
                    'trace' => $deleteException->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete request from database: ' . $deleteException->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Document request deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting request form', ['request_id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify employee password
     */
    private function verifyEmployeePassword($password)
    {
        try {
            // Get authenticated user (supports external employees)
            $employee = $this->getAuthenticatedEmployee();

            if (!$employee) {
                Log::error('No authenticated employee found');
                return false;
            }

            Log::info('Verifying password for employee', ['employee_id' => $employee->employee_id]);

            // If employee has no password (external employee via session), skip verification
            if (empty($employee->password)) {
                Log::info('Skipping password verification for external employee (no stored password)');
                return true;
            }

            // Check password against employee's stored password
            $isValid = Hash::check($password, $employee->password);

            Log::info('Password verification result', ['is_valid' => $isValid]);

            return $isValid;
        } catch (\Exception $e) {
            Log::error('Error verifying employee password', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Handle request activation and redirect to course management
     */
    public function activate($requestId)
    {
        try {
            $request = RequestForm::findOrFail($requestId);

            // Update request status to approved/activated
            $request->update(['status' => 'Approved']);

            // Log the activation
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Request Management',
                'action' => 'activate',
                'description' => 'Activated request ID: ' . $requestId . ' for employee ID: ' . $request->employee_id,
            ]);

            // Redirect to course management with activation context
            return redirect()->route('admin.course_management.index')
                ->with('success', 'Request activated successfully! You can now manage course activation.')
                ->with('activated_request', $request);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to activate request: ' . $e->getMessage());
        }
    }

    /**
     * Show all requests for admin management
     */
    public function adminIndex()
    {
        $requests = RequestForm::with('employee')->orderByDesc('requested_date')->get();
        return view('Employee_Self_Service.employee_request_form', compact('requests'));
    }
}
