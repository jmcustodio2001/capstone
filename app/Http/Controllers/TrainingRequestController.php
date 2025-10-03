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
                    [
                        'employee_id' => 'ID-ESP001',
                        'course_id' => 1,
                        'training_title' => 'BAESA',
                        'reason' => 'I want to develop my skills',
                        'status' => 'Pending',
                        'requested_date' => now()->format('Y-m-d'),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s')
                    ],
                    [
                        'employee_id' => 'ID-ESP002',
                        'course_id' => 2,
                        'training_title' => 'Communication Skills',
                        'reason' => 'Need to improve communication abilities',
                        'status' => 'Pending',
                        'requested_date' => now()->format('Y-m-d'),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s')
                    ]
                ]);
                
                Log::info('Sample training request created successfully');
            }
        } catch (\Exception $e) {
            Log::warning('Could not create sample data: ' . $e->getMessage());
        }
    }

    /**
     * Ensure employee_training_dashboards table exists
     */
    private function ensureEmployeeTrainingDashboardTableExists()
    {
        if (!Schema::hasTable('employee_training_dashboards')) {
            Log::info('Creating employee_training_dashboards table...');
            
            Schema::create('employee_training_dashboards', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('employee_id', 20);
                $table->unsignedBigInteger('course_id')->nullable();
                $table->timestamp('training_date')->nullable();
                $table->string('status')->default('Assigned');
                $table->text('remarks')->nullable();
                $table->integer('progress')->default(0);
                $table->timestamp('last_accessed')->nullable();
                $table->string('assigned_by')->nullable();
                $table->timestamp('expired_date')->nullable();
                $table->string('source')->nullable();
                $table->timestamps();
                
                // Add indexes for performance
                $table->index('employee_id');
                $table->index('course_id');
                $table->index('status');
                
                // Add foreign key constraints if tables exist
                if (Schema::hasTable('employees')) {
                    $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
                }
                if (Schema::hasTable('course_management')) {
                    $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('set null');
                }
            });
            
            Log::info('employee_training_dashboards table created successfully');
        }
    }

    /**
     * Approve a training request
     */
    public function approve(Request $request, $requestId)
    {
        try {
            Log::info('Approval attempt - Request ID: ' . $requestId . ' (type: ' . gettype($requestId) . ')');
            Log::info('Approval attempt - User ID: ' . Auth::id());
            
            // Ensure table exists before proceeding
            $this->ensureTableExists();
            
            // Check if any training requests exist at all
            $totalRequests = \Illuminate\Support\Facades\DB::table('training_requests')->count();
            Log::info('Total training requests in database: ' . $totalRequests);
            
            // Try to find the training request
            $trainingRequest = TrainingRequest::find($requestId);
            Log::info('Training request found: ' . ($trainingRequest ? 'Yes' : 'No'));
            
            // If not found, try to find by different methods
            if (!$trainingRequest) {
                Log::info('Attempting alternative search methods...');
                $allRequests = \Illuminate\Support\Facades\DB::table('training_requests')->get();
                Log::info('All training request IDs: ' . $allRequests->pluck('request_id')->implode(', '));
                
                // Try finding with string cast
                $trainingRequest = TrainingRequest::where('request_id', (string)$requestId)->first();
                Log::info('Found with string cast: ' . ($trainingRequest ? 'Yes' : 'No'));
                
                // Try finding with integer cast
                if (!$trainingRequest) {
                    $trainingRequest = TrainingRequest::where('request_id', (int)$requestId)->first();
                    Log::info('Found with integer cast: ' . ($trainingRequest ? 'Yes' : 'No'));
                }
            }

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
            try {
                // Ensure employee_training_dashboards table exists
                $this->ensureEmployeeTrainingDashboardTableExists();
                
                $existingTraining = EmployeeTrainingDashboard::where('employee_id', $trainingRequest->employee_id)
                    ->where('course_id', $trainingRequest->course_id)
                    ->first();

                if (!$existingTraining) {
                    // Create training dashboard record with proper fields
                    $dashboardRecord = EmployeeTrainingDashboard::create([
                        'employee_id' => $trainingRequest->employee_id,
                        'course_id' => $trainingRequest->course_id,
                        'training_title' => $trainingRequest->training_title, // Add training title
                        'training_date' => now(),
                        'status' => 'Assigned',
                        'progress' => 0,
                        'assigned_by' => Auth::id(),
                        'last_accessed' => now(),
                        'expired_date' => \Carbon\Carbon::parse(now()->addDays(90)), // Set expiration date
                        'source' => 'approved_request',
                        'remarks' => 'Approved from training request #' . $trainingRequest->request_id
                    ]);

                    Log::info('Training dashboard record created for approved request', [
                        'dashboard_id' => $dashboardRecord->id,
                        'employee_id' => $trainingRequest->employee_id,
                        'course_id' => $trainingRequest->course_id,
                        'training_title' => $trainingRequest->training_title
                    ]);
                } else {
                    // Update existing record to ensure it's properly configured
                    $existingTraining->update([
                        'status' => 'Assigned',
                        'training_title' => $trainingRequest->training_title,
                        'source' => 'approved_request',
                        'remarks' => 'Updated from approved training request #' . $trainingRequest->request_id,
                        'last_accessed' => now()
                    ]);

                    Log::info('Existing training dashboard record updated for approved request', [
                        'dashboard_id' => $existingTraining->id,
                        'employee_id' => $trainingRequest->employee_id,
                        'course_id' => $trainingRequest->course_id
                    ]);
                }
            } catch (\Exception $dashboardError) {
                Log::error('Could not create/update employee training dashboard record: ' . $dashboardError->getMessage());
                // Continue with approval even if dashboard creation fails
            }

            // Log the approval
            try {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'module' => 'Training Request',
                    'action' => 'Approve Request',
                    'description' => "Approved training request #{$trainingRequest->request_id} for employee {$trainingRequest->employee_id} - Course: {$trainingRequest->training_title}",
                    'model_type' => 'App\\Models\\TrainingRequest',
                    'model_id' => $trainingRequest->request_id
                ]);
            } catch (\Exception $logError) {
                Log::warning('Could not create activity log: ' . $logError->getMessage());
                // Continue with approval even if logging fails
            }

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

    /**
     * Sync approved training requests with employee progress
     */
    public function syncApprovedRequestsWithProgress()
    {
        try {
            Log::info('Starting sync of approved training requests with progress...');
            
            // Get all approved training requests that don't have corresponding dashboard records
            $approvedRequests = TrainingRequest::where('status', 'Approved')
                ->whereHas('course') // Only process requests with valid courses
                ->get();
            
            $syncCount = 0;
            $errorCount = 0;
            
            foreach ($approvedRequests as $request) {
                try {
                    // Check if dashboard record already exists
                    $existingDashboard = EmployeeTrainingDashboard::where('employee_id', $request->employee_id)
                        ->where('course_id', $request->course_id)
                        ->first();
                    
                    if (!$existingDashboard) {
                        // Create dashboard record for approved request
                        EmployeeTrainingDashboard::create([
                            'employee_id' => $request->employee_id,
                            'course_id' => $request->course_id,
                            'training_title' => $request->training_title,
                            'training_date' => now(),
                            'status' => 'Assigned',
                            'progress' => 0,
                            'assigned_by' => 1, // System user
                            'last_accessed' => now(),
                            'expired_date' => \Carbon\Carbon::parse(now()->addDays(90)),
                            'source' => 'approved_request',
                            'remarks' => 'Auto-synced from approved training request #' . $request->request_id
                        ]);
                        
                        $syncCount++;
                        Log::info('Created dashboard record for approved request', [
                            'request_id' => $request->request_id,
                            'employee_id' => $request->employee_id,
                            'course_id' => $request->course_id
                        ]);
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Error syncing approved request', [
                        'request_id' => $request->request_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Completed sync of approved training requests', [
                'total_approved' => $approvedRequests->count(),
                'synced' => $syncCount,
                'errors' => $errorCount
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Synced {$syncCount} approved training requests with progress tracking",
                'details' => [
                    'total_approved' => $approvedRequests->count(),
                    'synced' => $syncCount,
                    'errors' => $errorCount
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in syncApprovedRequestsWithProgress: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing approved requests: ' . $e->getMessage()
            ], 500);
        }
    }
}
