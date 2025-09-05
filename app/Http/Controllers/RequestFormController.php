<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestForm;
use App\Models\CourseManagement;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class RequestFormController extends Controller
{
    public function index()
    {
        $employeeId = Auth::user()->employee_id;
        $requests = RequestForm::where('employee_id', $employeeId)->orderByDesc('requested_date')->get();
        return view('employee_ess_modules.request_form.request_forms', compact('requests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string',
            'request_type' => 'required|string',
            'reason' => 'required|string',
            'status' => 'required|string',
            'requested_date' => 'required|date',
        ]);
        RequestForm::create($validated);
        return redirect()->route('employee.requests.index')->with('success', 'Request submitted successfully!');
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
