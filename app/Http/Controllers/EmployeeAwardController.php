<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAward;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeeAwardController extends Controller
{
    // Get awards for a specific employee
    public function getEmployeeAwards($employeeId)
    {
        try {
            $awards = EmployeeAward::where('employee_id', $employeeId)
                ->with('employee')
                ->orderBy('award_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'awards' => $awards,
                'total_awards' => $awards->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching employee awards: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch awards'
            ], 500);
        }
    }

    // Store new award request
    public function store(Request $request)
    {
        try {
            // Verify admin password first
            if (!Hash::check($request->password, Auth::user()->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password'
                ], 401);
            }

            $validated = $request->validate([
                'employee_id' => 'required|string',
                'award_type' => 'required|string',
                'award_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'award_date' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            $validated['awarded_by'] = Auth::user()->name;
            $validated['status'] = 'pending';

            $award = EmployeeAward::create($validated);

            // Log activity
            Log::info('Award request created', [
                'award_id' => $award->id,
                'employee_id' => $validated['employee_id'],
                'award_type' => $validated['award_type'],
                'created_by' => Auth::user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Award request submitted successfully',
                'award' => $award->load('employee')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating award: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create award request'
            ], 500);
        }
    }

    // Update award status
    public function updateStatus(Request $request, $awardId)
    {
        try {
            // Verify admin password first
            if (!Hash::check($request->password, Auth::user()->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password'
                ], 401);
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected',
                'notes' => 'nullable|string'
            ]);

            $award = EmployeeAward::findOrFail($awardId);
            $award->update($validated);

            // Log activity
            Log::info('Award status updated', [
                'award_id' => $award->id,
                'old_status' => $award->getOriginal('status'),
                'new_status' => $validated['status'],
                'updated_by' => Auth::user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Award status updated successfully',
                'award' => $award->load('employee')
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating award status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update award status'
            ], 500);
        }
    }

    // Delete award
    public function destroy(Request $request, $awardId)
    {
        try {
            // Verify admin password first
            if (!Hash::check($request->password, Auth::user()->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password'
                ], 401);
            }

            $award = EmployeeAward::findOrFail($awardId);
            
            // Log activity before deletion
            Log::info('Award deleted', [
                'award_id' => $award->id,
                'employee_id' => $award->employee_id,
                'award_type' => $award->award_type,
                'deleted_by' => Auth::user()->name
            ]);

            $award->delete();

            return response()->json([
                'success' => true,
                'message' => 'Award deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting award: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete award'
            ], 500);
        }
    }

    // Get all awards
    public function getAllAwards()
    {
        try {
            $awards = EmployeeAward::with('employee')
                ->orderBy('award_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'awards' => $awards,
                'total_awards' => $awards->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching all awards: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch awards'
            ], 500);
        }
    }

    // Get award statistics
    public function getStatistics($employeeId = null)
    {
        try {
            $query = EmployeeAward::query();
            
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            $statistics = [
                'total_awards' => $query->count(),
                'pending_awards' => $query->where('status', 'pending')->count(),
                'approved_awards' => $query->where('status', 'approved')->count(),
                'rejected_awards' => $query->where('status', 'rejected')->count(),
                'awards_by_type' => $query->groupBy('award_type')
                    ->selectRaw('award_type, count(*) as count')
                    ->pluck('count', 'award_type')
            ];

            return response()->json([
                'success' => true,
                'statistics' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching award statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics'
            ], 500);
        }
    }
}
