<?php

namespace App\Http\Controllers;

use App\Models\ClaimReimbursement;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClaimReimbursementController extends Controller
{

    /**
     * Display employee's claim reimbursements
     */
    public function index()
    {
        // Ensure table exists before proceeding
        $this->ensureTableExists();

        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();

        if (!$employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee profile not found.');
        }

        $claims = ClaimReimbursement::where('employee_id', $employee->employee_id)
            ->with(['approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate statistics
        $totalClaims = ClaimReimbursement::where('employee_id', $employee->employee_id)->count();
        $pendingClaims = ClaimReimbursement::where('employee_id', $employee->employee_id)->pending()->count();
        $approvedClaims = ClaimReimbursement::where('employee_id', $employee->employee_id)->approved()->count();
        $totalAmount = ClaimReimbursement::where('employee_id', $employee->employee_id)
            ->approved()
            ->sum('amount');

        return view('employee_ess_modules.claim_reimbursement.claim_reimbursement', compact(
            'claims', 'employee', 'totalClaims', 'pendingClaims', 'approvedClaims', 'totalAmount'
        ));
    }

    /**
     * Ensure the claim_reimbursements table exists
     */
    private function ensureTableExists()
    {
        if (!Schema::hasTable('claim_reimbursements')) {
            try {
                Schema::create('claim_reimbursements', function ($table) {
                    $table->id();
                    $table->string('employee_id', 20)->index();
                    $table->string('claim_id', 20)->unique();
                    $table->enum('claim_type', [
                        'Travel Expense',
                        'Meal Allowance',
                        'Transportation',
                        'Accommodation',
                        'Medical Expense',
                        'Office Supplies',
                        'Training Materials',
                        'Communication Expense',
                        'Other'
                    ]);
                    $table->text('description');
                    $table->decimal('amount', 10, 2);
                    $table->date('claim_date');
                    $table->string('receipt_file')->nullable();
                    $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Processed'])->default('Pending');
                    $table->unsignedBigInteger('approved_by')->nullable();
                    $table->datetime('approved_date')->nullable();
                    $table->text('rejected_reason')->nullable();
                    $table->datetime('processed_date')->nullable();
                    $table->enum('payment_method', ['Bank Transfer', 'Cash', 'Check', 'Payroll Deduction'])->nullable();
                    $table->string('reference_number')->nullable();
                    $table->text('remarks')->nullable();
                    $table->timestamps();
                    $table->softDeletes();

                    // Indexes for better performance
                    $table->index(['employee_id', 'status']);
                    $table->index(['claim_date', 'status']);
                    $table->index('claim_type');
                    $table->index('status');
                });

                Log::info('Created claim_reimbursements table successfully');
            } catch (\Exception $e) {
                Log::error('Error creating claim_reimbursements table: ' . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Store a new claim reimbursement
     */
    public function store(Request $request)
    {
        // Enhanced logging for debugging
        Log::info("Store claim request received");
        Log::info("Request data: " . json_encode($request->all()));
        Log::info("Request headers: " . json_encode($request->headers->all()));
        Log::info("Is AJAX request: " . ($request->ajax() ? 'Yes' : 'No'));
        Log::info("Expects JSON: " . ($request->expectsJson() ? 'Yes' : 'No'));

        // Ensure table exists before proceeding
        $this->ensureTableExists();

        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();

        if (!$employee) {
            Log::error("Employee not found for auth ID: " . Auth::guard('employee')->id());

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
            }

            return redirect()->back()->with('error', 'Employee profile not found.');
        }

        Log::info("Employee found: {$employee->employee_id}");

        try {
            $request->validate([
                'claim_type' => 'required|in:Travel Expense,Meal Allowance,Transportation,Accommodation,Medical Expense,Office Supplies,Training Materials,Communication Expense,Other',
                'description' => 'required|string|max:1000',
                'amount' => 'required|numeric|min:0.01|max:999999.99',
                'claim_date' => 'required|date|before_or_equal:today',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120' // 5MB max
            ]);

            Log::info("Validation passed");
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Validation failed: " . json_encode($e->errors()));
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errorMessages),
                    'errors' => $e->errors(),
                    'status' => 'validation_error'
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                Log::info("Processing receipt file upload");
                try {
                    $receiptPath = $request->file('receipt_file')->store('claim_receipts', 'public');
                    Log::info("Receipt file stored at: {$receiptPath}");
                } catch (\Exception $fileError) {
                    Log::error("File upload error: " . $fileError->getMessage());
                    // Continue without file if upload fails
                    $receiptPath = null;
                }
            }

            Log::info("Creating claim record with data: " . json_encode([
                'employee_id' => $employee->employee_id,
                'claim_type' => $request->claim_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date,
                'receipt_file' => $receiptPath,
                'status' => 'Pending'
            ]));

            $claim = ClaimReimbursement::create([
                'employee_id' => $employee->employee_id,
                'claim_type' => $request->claim_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date,
                'receipt_file' => $receiptPath,
                'status' => 'Pending'
            ]);

            Log::info("Claim created successfully with ID: {$claim->id}, Claim ID: {$claim->claim_id}");

            // Try to log activity, but don't fail if it doesn't work
            try {
                ActivityLog::create([
                    'employee_id' => $employee->employee_id,
                    'activity_type' => 'Claim Reimbursement',
                    'description' => "Submitted new claim reimbursement: {$claim->claim_type} - ₱" . number_format((float)$claim->amount, 2),
                    'activity_date' => now()
                ]);
                Log::info("Activity log created for claim: {$claim->claim_id}");
            } catch (\Exception $activityError) {
                Log::warning("Activity log creation failed: " . $activityError->getMessage());
                // Continue without activity log
            }

            $response = [
                'success' => true,
                'message' => 'Claim reimbursement submitted successfully!',
                'claim_id' => $claim->claim_id,
                'status' => 'success',
                'debug_info' => [
                    'employee_id' => $employee->employee_id,
                    'claim_internal_id' => $claim->id,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]
            ];

            Log::info("Returning success response: " . json_encode($response));

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json($response, 200);
            }

            return redirect()->back()->with('success', $response['message']);

        } catch (\Exception $e) {
            Log::error('Error creating claim reimbursement: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Request data at error: ' . json_encode($request->all()));

            // Check if it's a database connection issue
            $errorMessage = 'Error submitting claim. Please try again.';
            if (strpos($e->getMessage(), 'Connection refused') !== false) {
                $errorMessage = 'Database connection error. Please contact administrator.';
            } elseif (strpos($e->getMessage(), 'SQLSTATE') !== false) {
                $errorMessage = 'Database error occurred. Please try again or contact administrator.';
            } elseif (strpos($e->getMessage(), 'claim_id') !== false) {
                $errorMessage = 'Error generating claim ID. Please try again.';
            }

            $errorResponse = [
                'success' => false,
                'message' => $errorMessage,
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
                'status' => 'error',
                'debug_info' => app()->environment('local') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'employee_id' => $employee->employee_id ?? 'unknown'
                ] : null
            ];

            Log::error("Returning error response: " . json_encode($errorResponse));

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json($errorResponse, 500);
            }

            return redirect()->back()->with('error', $errorResponse['message']);
        }
    }

    /**
     * Display specific claim details
     */
    public function show($id)
    {
        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();

        $claim = ClaimReimbursement::where('id', $id)
            ->where('employee_id', $employee->employee_id)
            ->with(['employee', 'approver'])
            ->first();

        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'Claim not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'claim' => [
                'id' => $claim->id,
                'claim_id' => $claim->claim_id,
                'claim_type' => $claim->claim_type,
                'description' => $claim->description,
                'amount' => $claim->getFormattedAmount(),
                'claim_date' => $claim->claim_date ? \Carbon\Carbon::parse($claim->claim_date)->format('M d, Y') : null,
                'status' => $claim->status,
                'status_badge_class' => $claim->getStatusBadgeClass(),
                'receipt_file' => $claim->receipt_file,
                'approved_by' => $claim->approver ? $claim->approver->name : null,
                'approved_date' => $claim->approved_date ? $claim->approved_date->format('M d, Y g:i A') : null,
                'rejected_reason' => $claim->rejected_reason,
                'processed_date' => $claim->processed_date ? $claim->processed_date->format('M d, Y g:i A') : null,
                'payment_method' => $claim->payment_method,
                'reference_number' => $claim->reference_number,
                'remarks' => $claim->remarks,
                'can_edit' => $claim->canBeEdited(),
                'can_cancel' => $claim->canBeCancelled()
            ]
        ]);
    }

    /**
     * Update claim reimbursement (only pending claims)
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();

        $claim = ClaimReimbursement::where('id', $id)
            ->where('employee_id', $employee->employee_id)
            ->first();

        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'Claim not found.'], 404);
        }

        if (!$claim->canBeEdited()) {
            return response()->json(['success' => false, 'message' => 'Only pending claims can be edited.'], 403);
        }

        $request->validate([
            'claim_type' => 'required|in:Travel Expense,Meal Allowance,Transportation,Accommodation,Medical Expense,Office Supplies,Training Materials,Communication Expense,Other',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'claim_date' => 'required|date|before_or_equal:today',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        try {
            $updateData = [
                'claim_type' => $request->claim_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date
            ];

            // Handle file upload
            if ($request->hasFile('receipt_file')) {
                // Delete old file if exists
                if ($claim->receipt_file) {
                    Storage::disk('public')->delete($claim->receipt_file);
                }
                $updateData['receipt_file'] = $request->file('receipt_file')->store('claim_receipts', 'public');
            }

            $claim->update($updateData);

            // Log activity
            ActivityLog::create([
                'employee_id' => $employee->employee_id,
                'activity_type' => 'Claim Reimbursement',
                'description' => "Updated claim reimbursement: {$claim->claim_id} - {$claim->claim_type}",
                'activity_date' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim reimbursement updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating claim reimbursement: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating claim. Please try again.'], 500);
        }
    }

    /**
     * Cancel claim reimbursement
     */
    public function cancel($id)
    {
        // Enhanced logging for debugging
        Log::info("Cancel claim request received for ID: {$id}");

        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();

        if (!$employee) {
            Log::error("Employee not found for auth ID: " . Auth::guard('employee')->id());
            return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
        }

        Log::info("Employee found: {$employee->employee_id}");

        $claim = ClaimReimbursement::where('id', $id)
            ->where('employee_id', $employee->employee_id)
            ->first();

        if (!$claim) {
            Log::error("Claim not found for ID: {$id}, Employee: {$employee->employee_id}");
            return response()->json(['success' => false, 'message' => 'Claim not found.'], 404);
        }

        Log::info("Claim found: {$claim->claim_id}, Status: {$claim->status}");

        if (!$claim->canBeCancelled()) {
            Log::error("Claim cannot be cancelled. Status: {$claim->status}");
            return response()->json(['success' => false, 'message' => 'This claim cannot be cancelled.'], 403);
        }

        try {
            Log::info("Attempting to cancel claim: {$claim->claim_id}");

            $claim->update([
                'status' => 'Rejected',
                'rejected_reason' => 'Cancelled by employee',
                'processed_date' => now()
            ]);

            Log::info("Claim updated successfully: {$claim->claim_id}");

            // Log activity
            ActivityLog::create([
                'employee_id' => $employee->employee_id,
                'activity_type' => 'Claim Reimbursement',
                'description' => "Cancelled claim reimbursement: {$claim->claim_id} - {$claim->claim_type}",
                'activity_date' => now()
            ]);

            Log::info("Activity log created for cancelled claim: {$claim->claim_id}");

            $response = [
                'success' => true,
                'message' => 'Claim cancelled successfully!',
                'claim_id' => $claim->claim_id,
                'status' => 'success'
            ];

            Log::info("Returning success response: " . json_encode($response));

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Error cancelling claim reimbursement: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            $errorResponse = [
                'success' => false,
                'message' => 'Error cancelling claim. Please try again.',
                'error' => $e->getMessage(),
                'status' => 'error'
            ];

            Log::error("Returning error response: " . json_encode($errorResponse));

            return response()->json($errorResponse, 500);
        }
    }

    /**
     * Download receipt file
     */
    public function downloadReceipt($id)
    {
        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();

        $claim = ClaimReimbursement::where('id', $id)
            ->where('employee_id', $employee->employee_id)
            ->first();

        if (!$claim || !$claim->receipt_file) {
            return redirect()->back()->with('error', 'Receipt file not found.');
        }

        $filePath = storage_path('app/public/' . $claim->receipt_file);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Receipt file not found on server.');
        }

        return response()->download($filePath, 'Receipt_' . $claim->claim_id . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
    }

    /**
     * Get claim statistics for dashboard
     */
    public function getStatistics()
    {
        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $currentYear = date('Y');
        $currentMonth = date('m');

        $stats = [
            'total_claims' => ClaimReimbursement::where('employee_id', $employee->employee_id)->count(),
            'pending_claims' => ClaimReimbursement::where('employee_id', $employee->employee_id)->pending()->count(),
            'approved_claims' => ClaimReimbursement::where('employee_id', $employee->employee_id)->approved()->count(),
            'total_amount_claimed' => ClaimReimbursement::where('employee_id', $employee->employee_id)->sum('amount'),
            'total_amount_approved' => ClaimReimbursement::where('employee_id', $employee->employee_id)->approved()->sum('amount'),
            'monthly_claims' => ClaimReimbursement::where('employee_id', $employee->employee_id)
                ->whereYear('claim_date', $currentYear)
                ->whereMonth('claim_date', $currentMonth)
                ->count(),
            'yearly_amount' => ClaimReimbursement::where('employee_id', $employee->employee_id)
                ->whereYear('claim_date', $currentYear)
                ->approved()
                ->sum('amount')
        ];

        return response()->json(['success' => true, 'statistics' => $stats]);
    }

    /**
     * Test submission method for debugging
     */
    public function testSubmission(Request $request)
    {
        Log::info("Test submission called");
        Log::info("Request method: " . $request->method());
        Log::info("Request headers: " . json_encode($request->headers->all()));
        Log::info("Request data: " . json_encode($request->all()));
        Log::info("Auth check: " . (Auth::guard('employee')->check() ? 'Yes' : 'No'));
        Log::info("Auth user: " . (Auth::guard('employee')->user() ? Auth::guard('employee')->user()->employee_id : 'None'));

        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
                'debug' => [
                    'auth_id' => Auth::guard('employee')->id(),
                    'guard_check' => Auth::guard('employee')->check()
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Test successful!',
            'debug' => [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'request_method' => $request->method(),
                'is_ajax' => $request->ajax(),
                'expects_json' => $request->expectsJson(),
                'wants_json' => $request->wantsJson(),
                'csrf_token' => $request->header('X-CSRF-TOKEN') ? 'Present' : 'Missing',
                'content_type' => $request->header('Content-Type'),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]
        ]);
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
