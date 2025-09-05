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
        // Ensure table exists before proceeding
        $this->ensureTableExists();

        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();
        
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
        }

        $request->validate([
            'claim_type' => 'required|in:Travel Expense,Meal Allowance,Transportation,Accommodation,Medical Expense,Office Supplies,Training Materials,Communication Expense,Other',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'claim_date' => 'required|date|before_or_equal:today',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120' // 5MB max
        ]);

        try {
            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $receiptPath = $request->file('receipt_file')->store('claim_receipts', 'public');
            }

            $claim = ClaimReimbursement::create([
                'employee_id' => $employee->employee_id,
                'claim_type' => $request->claim_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date,
                'receipt_file' => $receiptPath,
                'status' => 'Pending'
            ]);

            // Log activity
            ActivityLog::create([
                'employee_id' => $employee->employee_id,
                'activity_type' => 'Claim Reimbursement',
                'description' => "Submitted new claim reimbursement: {$claim->claim_type} - ₱" . number_format((float)$claim->amount, 2),
                'activity_date' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim reimbursement submitted successfully!',
                'claim_id' => $claim->claim_id,
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error creating claim reimbursement: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false, 
                'message' => 'Error submitting claim. Please try again.',
                'error' => $e->getMessage(),
                'status' => 'error'
            ], 500);
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
        $employee = Employee::where('employee_id', Auth::guard('employee')->id())->first();
        
        $claim = ClaimReimbursement::where('id', $id)
            ->where('employee_id', $employee->employee_id)
            ->first();

        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'Claim not found.'], 404);
        }

        if (!$claim->canBeCancelled()) {
            return response()->json(['success' => false, 'message' => 'This claim cannot be cancelled.'], 403);
        }

        try {
            $claim->update([
                'status' => 'Rejected',
                'rejected_reason' => 'Cancelled by employee',
                'processed_date' => now()
            ]);

            // Log activity
            ActivityLog::create([
                'employee_id' => $employee->employee_id,
                'activity_type' => 'Claim Reimbursement',
                'description' => "Cancelled claim reimbursement: {$claim->claim_id} - {$claim->claim_type}",
                'activity_date' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim cancelled successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling claim reimbursement: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error cancelling claim. Please try again.'], 500);
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
}
