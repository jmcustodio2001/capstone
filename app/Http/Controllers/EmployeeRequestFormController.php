<?php


namespace App\Http\Controllers;

use App\Models\RequestForm;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeRequestFormController extends Controller
{
    public function index()
    {
        $requests = RequestForm::with('employee')->orderByDesc('requested_date')->get();
        return view('Employee_Self_Service.employee_request_form', compact('requests'));
    }

    public function create()
    {
        return view('Employee_Self_Service.employee_request_form');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required',
            'request_type' => 'required',
            'request_details' => 'required',
            'status' => 'required',
        ]);
        $requestForm = RequestForm::create($data);
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Employee Request Form',
            'description' => 'Created a new employee request form (ID: ' . $requestForm->id . ')',
        ]);
        return redirect()->route('employee_request_forms.index')->with('success', 'Request submitted successfully.');
    }

    public function show($id)
    {
        $requestForm = RequestForm::findOrFail($id);
        return view('Employee_Self_Service.employee_request_form', compact('requestForm'));
    }

    public function edit($id)
    {
        $requestForm = RequestForm::findOrFail($id);
        return view('Employee_Self_Service.employee_request_form', compact('requestForm'));
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string',
            'request_type' => 'required|string',
            'reason' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
            'requested_date' => 'required|date',
            'rejection_reason' => 'nullable|string|max:500',
            'password' => 'required|string|min:3'
        ]);

        // Verify admin password
        if (!$this->verifyAdminPassword($validated['password'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid password. Please enter your correct admin password.'
            ], 401);
        }
        
        $requestForm = RequestForm::findOrFail($id);
        
        // Remove password from update data
        unset($validated['password']);
        $requestForm->update($validated);
        
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'module' => 'Employee Request Form',
            'description' => 'Updated employee request form (ID: ' . $requestForm->request_id . ') - ' . $validated['request_type'],
        ]);
        
        return response()->json(['success' => true, 'message' => 'Request updated successfully']);
    }


    public function destroy(Request $request, $id)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:3'
        ]);

        // Verify admin password
        if (!$this->verifyAdminPassword($validated['password'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid password. Please enter your correct admin password.'
            ], 401);
        }

        $requestForm = RequestForm::findOrFail($id);
        $requestFormId = $requestForm->request_id;
        $requestForm->delete();
        
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Employee Request Form',
            'description' => 'Deleted employee request form (ID: ' . $requestFormId . ')',
        ]);
        
        return response()->json(['success' => true, 'message' => 'Request deleted successfully']);
    }

    /**
     * Update request status (approve/reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
            'password' => 'required|string|min:3'
        ]);

        // Verify admin password
        if (!$this->verifyAdminPassword($validated['password'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid password. Please enter your correct admin password.'
            ], 401);
        }

        $requestForm = RequestForm::findOrFail($id);
        
        $updateData = ['status' => $validated['status']];
        
        // Add rejection reason if status is rejected
        if ($validated['status'] === 'rejected') {
            $updateData['rejection_reason'] = $validated['rejection_reason'];
        } else {
            // Clear rejection reason if approving
            $updateData['rejection_reason'] = null;
        }
        
        $requestForm->update($updateData);

        // Log activity
        $description = 'Updated request status to ' . $validated['status'] . ' for request ID: ' . $id;
        if ($validated['status'] === 'rejected' && isset($validated['rejection_reason'])) {
            $description .= '. Reason: ' . $validated['rejection_reason'];
        }
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_status',
            'module' => 'Employee Request Form',
            'description' => $description,
        ]);

        return response()->json(['success' => true, 'message' => 'Request status updated successfully']);
    }

    /**
     * Fix status case sensitivity issue - convert 'Pending' to 'pending'
     */
    public function fixStatusCase()
    {
        try {
            // Update all records with 'Pending' to 'pending'
            $updatedPending = DB::table('request_forms')
                ->where('status', 'Pending')
                ->update(['status' => 'pending']);

            // Update all records with 'Approved' to 'approved'  
            $updatedApproved = DB::table('request_forms')
                ->where('status', 'Approved')
                ->update(['status' => 'approved']);

            // Update all records with 'Rejected' to 'rejected'
            $updatedRejected = DB::table('request_forms')
                ->where('status', 'Rejected')
                ->update(['status' => 'rejected']);

            $totalUpdated = $updatedPending + $updatedApproved + $updatedRejected;

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'fix_status_case',
                'module' => 'Employee Request Form',
                'description' => "Fixed status case sensitivity. Updated {$totalUpdated} records: {$updatedPending} pending, {$updatedApproved} approved, {$updatedRejected} rejected",
            ]);

            return response()->json([
                'success' => true, 
                'message' => "Status case fixed successfully. Updated {$totalUpdated} records.",
                'details' => [
                    'pending' => $updatedPending,
                    'approved' => $updatedApproved,
                    'rejected' => $updatedRejected,
                    'total' => $totalUpdated
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error fixing status case: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify admin password
     */
    private function verifyAdminPassword($password)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return false;
        }
        return Hash::check($password, $admin->password);
    }
}
