<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClaimReimbursement;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClaimReimbursementApiController extends Controller
{
    /**
     * Get claim reimbursements for an employee
     */
    public function getClaimReimbursements(Request $request, $employeeId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['employee_id' => $employeeId]), [
                'employee_id' => 'required|string',
                'api_key' => 'required|string',
                'status' => 'nullable|in:Pending,Approved,Rejected,Processed',
                'claim_type' => 'nullable|string|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'limit' => 'nullable|integer|min:1|max:100'
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

            $query = ClaimReimbursement::where('employee_id', $employeeId)
                ->orderByDesc('created_at');

            // Apply filters
            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->claim_type) {
                $query->where('claim_type', 'like', '%' . $request->claim_type . '%');
            }

            if ($request->start_date) {
                $query->where('claim_date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->where('claim_date', '<=', $request->end_date);
            }

            $limit = $request->limit ?? 50;
            $claims = $query->limit($limit)->get();

            $claimData = $claims->map(function ($claim) {
                return [
                    'id' => $claim->id,
                    'claim_id' => $claim->claim_id,
                    'employee_id' => $claim->employee_id,
                    'claim_type' => $claim->claim_type,
                    'description' => $claim->description,
                    'amount' => $claim->amount,
                    'formatted_amount' => $claim->getFormattedAmount(),
                    'claim_date' => $claim->claim_date,
                    'status' => $claim->status,
                    'approved_by' => $claim->approved_by,
                    'approved_date' => $claim->approved_date,
                    'rejected_reason' => $claim->rejected_reason,
                    'processed_date' => $claim->processed_date,
                    'payment_method' => $claim->payment_method,
                    'reference_number' => $claim->reference_number,
                    'remarks' => $claim->remarks,
                    'receipt_file' => $claim->receipt_file,
                    'has_receipt' => !empty($claim->receipt_file),
                    'can_be_edited' => $claim->canBeEdited(),
                    'can_be_cancelled' => $claim->canBeCancelled(),
                    'created_at' => $claim->created_at->toISOString(),
                    'updated_at' => $claim->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'claims' => $claimData,
                    'total_records' => $claimData->count(),
                    'limit_applied' => $limit,
                    'filters_applied' => [
                        'status' => $request->status,
                        'claim_type' => $request->claim_type,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Claim reimbursements retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving claim reimbursements'
            ], 500);
        }
    }

    /**
     * Create a new claim reimbursement
     */
    public function createClaimReimbursement(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|exists:employees,employee_id',
                'claim_type' => 'required|string|max:100',
                'description' => 'required|string|max:1000',
                'amount' => 'required|numeric|min:0.01|max:999999.99',
                'claim_date' => 'required|date|before_or_equal:today',
                'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
                'payment_method' => 'nullable|string|max:100',
                'remarks' => 'nullable|string|max:1000',
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

            $employee = Employee::where('employee_id', $request->employee_id)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            // Handle file upload if present
            $receiptFileName = null;
            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                $receiptFileName = 'receipt_' . time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/receipts', $receiptFileName);
            }

            // Create claim reimbursement (claim_id will be auto-generated)
            $claimReimbursement = ClaimReimbursement::create([
                'employee_id' => $request->employee_id,
                'claim_type' => $request->claim_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date,
                'receipt_file' => $receiptFileName,
                'status' => 'Pending',
                'payment_method' => $request->payment_method,
                'remarks' => $request->remarks
            ]);

            // Log activity
            ActivityLog::create([
                'employee_id' => $request->employee_id,
                'module' => 'Claim Reimbursement API',
                'action' => 'Claim Reimbursement Created via API',
                'description' => "API creation: {$request->claim_type} claim for ₱{$request->amount} on {$request->claim_date}",
                'timestamp' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim reimbursement created successfully',
                'data' => [
                    'id' => $claimReimbursement->id,
                    'claim_id' => $claimReimbursement->claim_id,
                    'employee_id' => $claimReimbursement->employee_id,
                    'claim_type' => $claimReimbursement->claim_type,
                    'description' => $claimReimbursement->description,
                    'amount' => $claimReimbursement->amount,
                    'formatted_amount' => $claimReimbursement->getFormattedAmount(),
                    'claim_date' => $claimReimbursement->claim_date,
                    'status' => $claimReimbursement->status,
                    'has_receipt' => !empty($receiptFileName),
                    'created_at' => $claimReimbursement->created_at->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Claim reimbursement creation error: ' . $e->getMessage(), [
                'request_data' => $request->except(['receipt_file']),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error occurred while creating claim reimbursement',
                'error_code' => 'CLAIM_CREATION_ERROR'
            ], 500);
        }
    }

    /**
     * Update claim reimbursement status (Admin only)
     */
    public function updateClaimStatus(Request $request, $claimId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['claim_id' => $claimId]), [
                'claim_id' => 'required|string',
                'status' => 'required|in:Approved,Rejected,Processed',
                'approved_by' => 'required|string|max:255',
                'rejected_reason' => 'nullable|string|max:1000',
                'payment_method' => 'nullable|string|max:100',
                'reference_number' => 'nullable|string|max:255',
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

            $claimReimbursement = ClaimReimbursement::where('claim_id', $claimId)
                ->orWhere('id', $claimId)
                ->first();

            if (!$claimReimbursement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim reimbursement not found'
                ], 404);
            }

            if ($claimReimbursement->status !== 'Pending' && $claimReimbursement->status !== 'Approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim reimbursement cannot be updated in current status',
                    'current_status' => $claimReimbursement->status
                ], 400);
            }

            // Prepare update data
            $updateData = [
                'status' => $request->status,
                'approved_by' => $request->approved_by,
                'remarks' => $request->remarks
            ];

            if ($request->status === 'Approved') {
                $updateData['approved_date'] = Carbon::now();
            } elseif ($request->status === 'Rejected') {
                $updateData['rejected_reason'] = $request->rejected_reason;
            } elseif ($request->status === 'Processed') {
                $updateData['processed_date'] = Carbon::now();
                $updateData['payment_method'] = $request->payment_method;
                $updateData['reference_number'] = $request->reference_number;
            }

            $claimReimbursement->update($updateData);

            // Log activity
            ActivityLog::create([
                'employee_id' => $claimReimbursement->employee_id,
                'module' => 'Claim Reimbursement API',
                'action' => 'Claim Status Updated via API',
                'description' => "API {$request->status}: Claim {$claimReimbursement->claim_id} by {$request->approved_by}. Remarks: " . ($request->remarks ?? 'None'),
                'timestamp' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Claim reimbursement {$request->status} successfully",
                'data' => [
                    'id' => $claimReimbursement->id,
                    'claim_id' => $claimReimbursement->claim_id,
                    'status' => $claimReimbursement->status,
                    'approved_by' => $claimReimbursement->approved_by,
                    'approved_date' => $claimReimbursement->approved_date,
                    'rejected_reason' => $claimReimbursement->rejected_reason,
                    'processed_date' => $claimReimbursement->processed_date,
                    'payment_method' => $claimReimbursement->payment_method,
                    'reference_number' => $claimReimbursement->reference_number,
                    'remarks' => $claimReimbursement->remarks,
                    'updated_at' => $claimReimbursement->updated_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Claim status update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating claim status'
            ], 500);
        }
    }

    /**
     * Get claim reimbursement details
     */
    public function getClaimDetails(Request $request, $claimId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['claim_id' => $claimId]), [
                'claim_id' => 'required|string',
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

            $claimReimbursement = ClaimReimbursement::where('claim_id', $claimId)
                ->orWhere('id', $claimId)
                ->with('employee', 'approver')
                ->first();

            if (!$claimReimbursement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim reimbursement not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $claimReimbursement->id,
                    'claim_id' => $claimReimbursement->claim_id,
                    'employee_id' => $claimReimbursement->employee_id,
                    'employee_name' => $claimReimbursement->employee ? 
                        $claimReimbursement->employee->first_name . ' ' . $claimReimbursement->employee->last_name : 'N/A',
                    'claim_type' => $claimReimbursement->claim_type,
                    'description' => $claimReimbursement->description,
                    'amount' => $claimReimbursement->amount,
                    'formatted_amount' => $claimReimbursement->getFormattedAmount(),
                    'claim_date' => $claimReimbursement->claim_date,
                    'status' => $claimReimbursement->status,
                    'approved_by' => $claimReimbursement->approved_by,
                    'approved_date' => $claimReimbursement->approved_date,
                    'rejected_reason' => $claimReimbursement->rejected_reason,
                    'processed_date' => $claimReimbursement->processed_date,
                    'payment_method' => $claimReimbursement->payment_method,
                    'reference_number' => $claimReimbursement->reference_number,
                    'remarks' => $claimReimbursement->remarks,
                    'receipt_file' => $claimReimbursement->receipt_file,
                    'has_receipt' => !empty($claimReimbursement->receipt_file),
                    'receipt_url' => $claimReimbursement->receipt_file ? 
                        Storage::url('receipts/' . $claimReimbursement->receipt_file) : null,
                    'can_be_edited' => $claimReimbursement->canBeEdited(),
                    'can_be_cancelled' => $claimReimbursement->canBeCancelled(),
                    'created_at' => $claimReimbursement->created_at->toISOString(),
                    'updated_at' => $claimReimbursement->updated_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Claim details retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving claim details'
            ], 500);
        }
    }

    /**
     * Get claim reimbursement summary for an employee
     */
    public function getClaimSummary(Request $request, $employeeId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['employee_id' => $employeeId]), [
                'employee_id' => 'required|string',
                'api_key' => 'required|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
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

            $query = ClaimReimbursement::where('employee_id', $employeeId);

            // Apply date filters
            $startDate = $request->start_date ?? Carbon::now()->startOfYear()->toDateString();
            $endDate = $request->end_date ?? Carbon::now()->endOfYear()->toDateString();

            $query->whereBetween('claim_date', [$startDate, $endDate]);

            $claims = $query->get();

            // Calculate summary statistics
            $summary = [
                'total_claims' => $claims->count(),
                'pending_claims' => $claims->where('status', 'Pending')->count(),
                'approved_claims' => $claims->where('status', 'Approved')->count(),
                'rejected_claims' => $claims->where('status', 'Rejected')->count(),
                'processed_claims' => $claims->where('status', 'Processed')->count(),
                'total_amount_claimed' => $claims->sum('amount'),
                'total_amount_approved' => $claims->whereIn('status', ['Approved', 'Processed'])->sum('amount'),
                'total_amount_processed' => $claims->where('status', 'Processed')->sum('amount'),
                'average_claim_amount' => $claims->count() > 0 ? round($claims->sum('amount') / $claims->count(), 2) : 0
            ];

            // Format amounts
            $summary['formatted_total_claimed'] = '₱' . number_format($summary['total_amount_claimed'], 2);
            $summary['formatted_total_approved'] = '₱' . number_format($summary['total_amount_approved'], 2);
            $summary['formatted_total_processed'] = '₱' . number_format($summary['total_amount_processed'], 2);
            $summary['formatted_average_amount'] = '₱' . number_format($summary['average_claim_amount'], 2);

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'summary' => $summary,
                    'generated_at' => Carbon::now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Claim summary retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving claim summary'
            ], 500);
        }
    }

    /**
     * Validate API key
     */
    private function validateApiKey($apiKey)
    {
        $validApiKey = env('CLAIM_API_KEY');
        return $validApiKey && $apiKey === $validApiKey;
    }

    /**
     * Validate admin API key
     */
    private function validateAdminApiKey($apiKey)
    {
        $validAdminApiKey = env('CLAIM_ADMIN_API_KEY');
        return $validAdminApiKey && $apiKey === $validAdminApiKey;
    }
}
