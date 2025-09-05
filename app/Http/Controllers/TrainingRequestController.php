<?php

namespace App\Http\Controllers;

use App\Models\TrainingRequest;
use App\Models\EmployeeTrainingDashboard;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TrainingRequestController extends Controller
{
    /**
     * Ensure training_requests table exists
     */
    private function ensureTableExists()
    {
        if (!Schema::hasTable('training_requests')) {
            Log::warning('training_requests table does not exist. Creating it now...');
            
            Schema::create('training_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id('request_id');
                $table->string('employee_id', 20);
                $table->unsignedBigInteger('course_id')->nullable();
                $table->string('training_title');
                $table->text('reason');
                $table->string('status')->default('Pending');
                $table->date('requested_date');
                $table->timestamps();
                
                // Add foreign key constraint if course_management table exists
                if (Schema::hasTable('course_management')) {
                    $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('set null');
                }
                
                // Add foreign key constraint if employees table exists
                if (Schema::hasTable('employees')) {
                    $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
                }
            });
            
            Log::info('training_requests table created successfully');
            
            // Insert sample data for testing if table is empty
            $this->createSampleData();
        }
    }

    /**
     * Create sample training request data for testing
     */
    private function createSampleData()
    {
        try {
            $count = \Illuminate\Support\Facades\DB::table('training_requests')->count();
            
            if ($count == 0) {
                Log::info('Creating sample training request data...');
                
                \Illuminate\Support\Facades\DB::table('training_requests')->insert([
                    'employee_id' => 'ID-ESP001',
                    'course_id' => 1,
                    'training_title' => 'BAESA',
                    'reason' => 'IWANT TO DEVELOPMENT MY SKILLS',
                    'status' => 'Pending',
                    'requested_date' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                Log::info('Sample training request created successfully');
            }
        } catch (\Exception $e) {
            Log::warning('Could not create sample data: ' . $e->getMessage());
        }
    }

    /**
     * Approve a training request
     */
    public function approve(Request $request, $requestId)
    {
        try {
            // Ensure table exists before proceeding
            $this->ensureTableExists();
            
            $trainingRequest = TrainingRequest::find($requestId);

            if (!$trainingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training request not found'
                ], 404);
            }

            if ($trainingRequest->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Training request has already been processed'
                ], 400);
            }

            // Update the training request status
            $trainingRequest->update([
                'status' => 'Approved'
            ]);

            // Create an entry in the employee training dashboard
            $existingTraining = EmployeeTrainingDashboard::where('employee_id', $trainingRequest->employee_id)
                ->where('course_id', $trainingRequest->course_id)
                ->first();

            if (!$existingTraining) {
                EmployeeTrainingDashboard::create([
                    'employee_id' => $trainingRequest->employee_id,
                    'course_id' => $trainingRequest->course_id,
                    'training_date' => now(),
                    'status' => 'Assigned',
                    'progress' => 0,
                    'assigned_by' => Auth::id(),
                    'last_accessed' => now(),
                    'remarks' => 'Approved from training request #' . $trainingRequest->request_id
                ]);
            }

            // Log the approval
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Training Request',
                'action' => 'Approve Request',
                'description' => "Approved training request #{$trainingRequest->request_id} for employee {$trainingRequest->employee_id} - Course: {$trainingRequest->training_title}",
                'model_type' => 'App\\Models\\TrainingRequest',
                'model_id' => $trainingRequest->request_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Training request approved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving training request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving training request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a training request
     */
    public function reject(Request $request, $requestId)
    {
        try {
            // Ensure table exists before proceeding
            $this->ensureTableExists();
            
            $trainingRequest = TrainingRequest::find($requestId);

            if (!$trainingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training request not found'
                ], 404);
            }

            if ($trainingRequest->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Training request has already been processed'
                ], 400);
            }

            $reason = $request->input('reason', 'No reason provided');

            // Update the training request status
            $trainingRequest->update([
                'status' => 'Rejected'
            ]);

            // Log the rejection
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Training Request',
                'action' => 'Reject Request',
                'description' => "Rejected training request #{$trainingRequest->request_id} for employee {$trainingRequest->employee_id} - Course: {$trainingRequest->training_title}. Reason: {$reason}",
                'model_type' => 'App\\Models\\TrainingRequest',
                'model_id' => $trainingRequest->request_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Training request rejected successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting training request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting training request: ' . $e->getMessage()
            ], 500);
        }
    }
}
