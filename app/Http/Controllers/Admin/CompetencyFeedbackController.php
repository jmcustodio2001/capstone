<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetencyFeedbackRequest;
use App\Models\Employee;
use App\Models\CompetencyLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CompetencyFeedbackController extends Controller
{
    /**
     * Display competency feedback requests
     */
    public function index(Request $request)
    {
        // Get all feedback requests with relationships
        $feedbackRequests = CompetencyFeedbackRequest::with(['employee', 'competency', 'manager'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $feedbackRequests->where('status', $request->status);
        }

        if ($request->filled('employee')) {
            $feedbackRequests->where('employee_id', $request->employee);
        }

        if ($request->filled('date_range')) {
            $dateRange = $request->date_range;
            switch ($dateRange) {
                case 'today':
                    $feedbackRequests->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $feedbackRequests->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $feedbackRequests->whereMonth('created_at', Carbon::now()->month);
                    break;
            }
        }

        $allRequests = $feedbackRequests->get();

        // Calculate statistics
        $totalRequests = $allRequests->count();
        $pendingRequests = $allRequests->where('status', 'pending')->count();
        $respondedRequests = $allRequests->where('status', 'responded')->count();
        $thisWeekRequests = $allRequests->where('created_at', '>=', Carbon::now()->startOfWeek())->count();

        // Get unique employees for filter
        $employees = Employee::orderBy('first_name')->get();

        // Get unique competencies for filter
        $competencies = CompetencyLibrary::orderBy('competency_name')->get();

        return view('Employee_Self_Service.competency_feedback', compact(
            'allRequests',
            'totalRequests',
            'pendingRequests',
            'respondedRequests',
            'thisWeekRequests',
            'employees',
            'competencies'
        ));
    }

    /**
     * Show specific feedback request details
     */
    public function show($id)
    {
        $request = CompetencyFeedbackRequest::with(['employee', 'competency', 'manager'])->findOrFail($id);
        
        return response()->json([
            'id' => $request->id,
            'employee' => [
                'employee_id' => $request->employee->employee_id ?? 'N/A',
                'first_name' => $request->employee->first_name ?? 'Unknown',
                'last_name' => $request->employee->last_name ?? 'User',
                'department' => $request->employee->department ?? 'N/A'
            ],
            'competency' => [
                'competency_name' => $request->competency->competency_name ?? 'Unknown Competency',
                'description' => $request->competency->description ?? 'No description',
                'category' => $request->competency->category ?? 'General'
            ],
            'request_message' => $request->request_message,
            'status' => $request->status,
            'manager_response' => $request->manager_response,
            'manager' => $request->manager ? [
                'name' => $request->manager->name,
                'email' => $request->manager->email
            ] : null,
            'created_at' => $request->created_at->format('Y-m-d H:i:s'),
            'responded_at' => $request->responded_at ? $request->responded_at->format('Y-m-d H:i:s') : null
        ]);
    }

    /**
     * Respond to a feedback request
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'manager_response' => 'required|string|max:2000'
        ]);

        try {
            $feedbackRequest = CompetencyFeedbackRequest::findOrFail($id);
            
            $feedbackRequest->update([
                'manager_response' => $request->manager_response,
                'manager_id' => Auth::id(),
                'status' => 'responded',
                'responded_at' => now()
            ]);

            Log::info('Competency feedback request responded', [
                'request_id' => $id,
                'manager_id' => Auth::id(),
                'employee_id' => $feedbackRequest->employee_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Response sent successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to respond to competency feedback request', [
                'error' => $e->getMessage(),
                'request_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send response. Please try again.'
            ], 500);
        }
    }

    /**
     * Mark request as reviewed/closed
     */
    public function markAsReviewed($id)
    {
        try {
            $feedbackRequest = CompetencyFeedbackRequest::findOrFail($id);
            
            $feedbackRequest->update([
                'status' => 'closed',
                'manager_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request marked as reviewed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update request status.'
            ], 500);
        }
    }

    /**
     * Export feedback requests to CSV
     */
    public function export()
    {
        $requests = CompetencyFeedbackRequest::with(['employee', 'competency', 'manager'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'competency_feedback_requests_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Request ID',
                'Employee ID',
                'Employee Name',
                'Department',
                'Competency',
                'Category',
                'Request Message',
                'Status',
                'Manager Response',
                'Manager Name',
                'Requested Date',
                'Responded Date'
            ]);

            // CSV data
            foreach ($requests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->employee->employee_id ?? 'N/A',
                    ($request->employee->first_name ?? 'Unknown') . ' ' . ($request->employee->last_name ?? 'User'),
                    $request->employee->department ?? 'N/A',
                    $request->competency->competency_name ?? 'Unknown',
                    $request->competency->category ?? 'General',
                    $request->request_message,
                    ucfirst($request->status),
                    $request->manager_response,
                    $request->manager->name ?? 'N/A',
                    $request->created_at->format('Y-m-d H:i:s'),
                    $request->responded_at ? $request->responded_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
