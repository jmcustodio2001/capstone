<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use App\Models\DestinationKnowledgeTraining;
use App\Models\Employee;
use App\Models\ActivityLog;
use App\Models\TrainingNotification;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyLibrary;
use App\Models\CompetencyGap;
use App\Models\EmployeeTrainingDashboard;
use App\Models\DestinationMaster;
use Illuminate\Routing\Controller as BaseController;

class DestinationKnowledgeTrainingController extends BaseController
{
    private const MODULE_NAME = 'Destination Knowledge Training';

    private const VALIDATION_RULES_CREATE = [
        'employee_id' => 'required|exists:employees,employee_id',
        'date_completed' => 'nullable|date',
        'expired_date' => 'nullable|date',
        'progress_level' => 'nullable|integer|min:0|max:5',
        'remarks' => 'nullable|string',
        'destination_name' => 'required|string|max:255',
        'details' => 'required|string',
        'objectives' => 'required|string',
        'duration' => 'required|string',
        'delivery_mode' => 'required|string|in:On-site Training,Online Training,Blended Learning,Workshop,Seminar,Field Training',
        'status' => 'nullable|string|in:not-started,in-progress,completed',
    ];

    private const VALIDATION_RULES_UPDATE = [
        'employee_id' => 'required|exists:employees,employee_id',
        'date_completed' => 'nullable|date',
        'expired_date' => 'nullable|date',
        'progress_level' => 'required|integer|min:0|max:5',
        'remarks' => 'nullable|string',
        'destination_name' => 'required|string|max:255',
        'details' => 'required|string',
        'delivery_mode' => 'nullable|string|in:On-site Training,Online Training,Blended Learning,Workshop,Seminar,Field Training',
        'status' => 'required|string|in:not-started,in-progress,completed',
    ];
    public function __construct()
    {
        // Middleware is handled in routes
    }

    /**
     * Clean destination name for comparison
     */
    private function cleanDestinationName($destinationName)
    {
        return trim(str_replace([' Training', 'Training', ' Island', 'Island'], '', $destinationName));
    }

    /**
     * Convert progress level (0-5) to percentage
     */
    private function convertLevelToPercentage($level)
    {
        $levelMap = [
            0 => 0,   // Not Started
            1 => 20,  // Beginner
            2 => 40,  // Developing
            3 => 60,  // Proficient
            4 => 80,  // Advanced
            5 => 100  // Expert
        ];

        return $levelMap[$level] ?? 0;
    }

    /**
     * Assign Destination Knowledge Training to an employee based on a competency gap.
     */
    public function assignFromGap(Request $request)
    {
        try {
            Log::info('assignFromGap called - Creating destination knowledge training record from competency gap');

            $request->validate([
                'employee_id' => 'required|string|exists:employees,employee_id',
                'gap_id' => 'required|integer|exists:competency_gaps,id',
            ]);

            $gap = CompetencyGap::with(['competency', 'employee'])->find($request->gap_id);
            if (!$gap || !$gap->competency) {
                return response()->json(['success' => false, 'message' => 'Gap or competency not found.'], 404);
            }

            // Check if destination knowledge training already exists for this employee and competency
            // Use consolidated single table approach
            $existingRecord = DestinationKnowledgeTraining::where('employee_id', $request->employee_id)
                ->where('destination_name', 'LIKE', '%' . $gap->competency->competency_name . '%')
                ->where('training_type', 'destination')
                ->where('is_active', true)
                ->first();

            if ($existingRecord) {
                // If record exists but is not active, activate it
                if (!$existingRecord->is_active) {
                    $existingRecord->is_active = true;
                    $existingRecord->status = 'in-progress';
                    $existingRecord->save();

                    Log::info('Activated existing destination knowledge training record', ['id' => $existingRecord->id]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Destination Knowledge Training activated successfully!',
                        'redirect_url' => route('admin.destination-knowledge-training.index')
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Training already assigned and active for this destination.'
                ]);
            }

            // Create new destination knowledge training record using consolidated approach
            $destinationRecord = DestinationKnowledgeTraining::create([
                'employee_id' => $request->employee_id,
                'training_type' => 'destination',
                'training_title' => $gap->competency->competency_name,
                'destination_name' => $gap->competency->competency_name,
                'details' => 'Training assigned from competency gap analysis - ' . ($gap->competency->description ?? 'Competency development training'),
                'progress' => max(0, round(($gap->current_level / max(1, $gap->required_level)) * 100)),
                'status' => 'in-progress',
                'delivery_mode' => 'Online Training',
                'is_active' => true,
                'admin_approved_for_upcoming' => false,
                'source' => 'destination_knowledge_training',
            ]);

            // Create notification
            TrainingNotification::create([
                'employee_id' => $request->employee_id,
                'message' => 'You have been assigned Destination Knowledge Training: ' . $gap->competency->competency_name,
                'sent_at' => now()
            ]);

            // Create Employee Training Dashboard record for auto-assigned training (but don't create duplicate competency entries)
            $this->createEmployeeTrainingDashboardRecord($destinationRecord, $gap);

            // Log activity
            ActivityLog::createLog([
                'action' => 'create',
                'module' => self::MODULE_NAME,
                'description' => 'Created and activated destination knowledge training from competency gap (ID: ' . $destinationRecord->id . ') for ' . $gap->competency->competency_name,
            ]);

            Log::info('Successfully created and activated destination knowledge training record', [
                'record_id' => $destinationRecord->id,
                'employee_id' => $request->employee_id,
                'competency' => $gap->competency->competency_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Destination Knowledge Training assigned successfully! Use "Assign to Upcoming Training" button to make it visible to employee.',
                'redirect_url' => route('admin.destination-knowledge-training.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error in assignFromGap: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning destination knowledge training: ' . $e->getMessage()
            ], 500);
        }
    }
    public function index()
    {
        // Fix expiration dates for destination trainings that don't have proper expired_date
        $this->fixExpirationDates();

        $destinations = DestinationKnowledgeTraining::with(['employee'])->orderBy('created_at', 'desc')->get();

        // Don't automatically update progress from competency gap - preserve admin's input
        // foreach ($destinations as $destination) {
        //     $this->updateProgressFromCompetencyGap($destination);
        // }

        // Get all employees for destination knowledge training
        $employees = Employee::all();

        // Get destination masters for auto-population (with fallback if table doesn't exist)
        try {
            $destinationMasters = DestinationMaster::active()->orderBy('destination_name')->get();
        } catch (\Exception $e) {
            // Fallback: create empty collection if table doesn't exist
            $destinationMasters = collect([]);
            \Illuminate\Support\Facades\Log::warning('destination_masters table not found, using empty collection');
        }

        // Get possible destinations from destination_masters for display
        $possibleDestinations = $destinationMasters;

        // Get recent notifications for admin with error handling
        try {
            // Ensure table exists before querying
            TrainingNotification::ensureTableExists();

            $notifications = TrainingNotification::where('employee_id', 'ADMIN')
                ->orderBy('sent_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            // Handle gracefully if training_notifications table doesn't exist
            Log::warning('Training notifications table not found or error occurred: ' . $e->getMessage());
            $notifications = collect(); // Return empty collection
        }

        return view('training_management.destination_knowledge_training', compact('destinations', 'employees', 'destinationMasters', 'possibleDestinations', 'notifications'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Store method called with data:', $request->all());

            $validated = $request->validate(self::VALIDATION_RULES_CREATE);

            Log::info('Validation passed. Creating destination knowledge training record with data:', $validated);

            // Check for existing active record for the SAME EMPLOYEE only (allow same destination for different employees)
            $existingRecord = DestinationKnowledgeTraining::where('employee_id', $validated['employee_id'])
                ->where('destination_name', $validated['destination_name'])
                ->where('delivery_mode', $validated['delivery_mode'])
                ->where('is_active', true)
                ->first();

            if ($existingRecord) {
                // Only prevent duplicate if it's the SAME employee with SAME destination and delivery mode
                Log::info('Found existing record for same employee, updating instead of creating duplicate', [
                    'existing_id' => $existingRecord->id,
                    'employee_id' => $validated['employee_id'],
                    'destination_name' => $validated['destination_name']
                ]);

                // Update the existing record with new data
                $existingRecord->update([
                    'details' => $validated['details'],
                    'objectives' => $validated['objectives'] ?? $existingRecord->objectives,
                    'duration' => $validated['duration'] ?? $existingRecord->duration,
                    'expired_date' => $validated['expired_date'] ?? $existingRecord->expired_date,
                    'remarks' => $validated['remarks'] ?? $existingRecord->remarks,
                    'is_active' => true
                ]);

                // Log activity
                ActivityLog::createLog([
                    'action' => 'update',
                    'module' => self::MODULE_NAME,
                    'description' => 'Updated existing destination knowledge training record (ID: ' . $existingRecord->id . ') for same employee instead of creating duplicate',
                ]);

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Existing training record updated successfully for this employee.',
                        'record' => $existingRecord->load('employee')
                    ]);
                }

                return redirect()
                    ->route('admin.destination-knowledge-training.index')
                    ->with('success', 'Existing training record updated successfully for this employee.');
            }

            // Convert progress level to percentage
            $progressPercentage = $this->convertLevelToPercentage($validated['progress_level']);

            // Only process progress for Online Training delivery mode
            if (isset($validated['delivery_mode']) && $validated['delivery_mode'] === 'Online Training') {
                // Require progress_level for Online Training
                if (!isset($validated['progress_level'])) {
                    throw new \Exception('Progress level is required for Online Training');
                }
            } else {
                // For non-Online Training, set default progress values
                $validated['progress_level'] = 0;
                $progressPercentage = 0;
            }

            // Keep the status as selected by user in the form

            // Auto-set status based on progress if not provided
            if (!isset($validated['status']) || empty($validated['status'])) {
                $validated['status'] = 'not-started';
            }

            // Check if all required fields are present
            $requiredFields = ['employee_id', 'destination_name', 'details', 'objectives', 'duration', 'delivery_mode'];
            foreach ($requiredFields as $field) {
                if (!isset($validated[$field]) || empty($validated[$field])) {
                    throw new \Exception("Missing required field: $field");
                }
            }

            // Try to create with minimal data first to test database connection
            try {
                $testData = [
                    'employee_id' => $validated['employee_id'],
                    'destination_name' => $validated['destination_name'],
                    'details' => $validated['details'],
                    'objectives' => $validated['objectives'],
                    'duration' => $validated['duration'],
                    'delivery_mode' => $validated['delivery_mode'],
                    'progress' => $progressPercentage,
                    'status' => $validated['status'],
                    'is_active' => true,
                    'admin_approved_for_upcoming' => false, // Default to false - requires Auto-Assign button click
                    'training_type' => 'destination',
                    'source' => 'destination_knowledge_training'
                ];

                Log::info('Attempting to create record with test data:', $testData);
                $record = DestinationKnowledgeTraining::create($testData);

                // Update additional fields for all delivery modes
                if (isset($validated['date_completed']) && $validated['date_completed']) {
                    $record->date_completed = $validated['date_completed'];
                }
                if (isset($validated['expired_date']) && $validated['expired_date']) {
                    $record->expired_date = $validated['expired_date'];
                }

                // Set completion date if status is completed
                if ($validated['status'] === 'completed' && !$record->date_completed) {
                    $record->date_completed = now();
                }

                if (isset($validated['remarks']) && $validated['remarks']) {
                    $record->remarks = $validated['remarks'];
                }

                $record->save();

            } catch (\Exception $createException) {
                Log::error('Database create error: ' . $createException->getMessage());
                throw new \Exception('Database table error: ' . $createException->getMessage());
            }

            if (!$record) {
                Log::error('Failed to create record - create() returned null');
                throw new \Exception('Failed to create destination knowledge training record');
            }

            Log::info('Record created successfully with ID: ' . $record->id);

            // Create notification
            TrainingNotification::create([
                'employee_id' => $request->employee_id,
                'message' => 'You have been assigned a new ' . self::MODULE_NAME . ': ' . $request->destination_name,
                'sent_at' => now()
            ]);

            // CRITICAL FIX: Create competency profile entry when destination training is created
            $this->createCompetencyProfileFromDestination($record);

            // DO NOT create upcoming training record automatically - admin must use "Assign to Upcoming Training" button

            // Log activity
            ActivityLog::createLog([
                'action' => 'create',
                'module' => self::MODULE_NAME,
                'description' => 'Added destination knowledge training record (ID: ' . $record->id . ') and created competency profile',
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Training record added successfully.',
                    'record' => $record->load('employee')
                ]);
            }

            return redirect()
                ->route('admin.destination-knowledge-training.index')
                ->with('success', 'Training record added successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed for destination knowledge training creation:', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Please check the form for errors.');

        } catch (\Exception $e) {
            Log::error('Error creating destination knowledge training record: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate(self::VALIDATION_RULES_UPDATE);

            // Convert progress level to percentage
            $progressPercentage = $this->convertLevelToPercentage($validated['progress_level']);
            $validated['progress'] = $progressPercentage;
            unset($validated['progress_level']); // Remove the level field since we're storing percentage

            // Auto-update status based on progress
            if ($progressPercentage >= 100) {
                $validated['status'] = 'completed';
            } elseif ($progressPercentage > 0) {
                $validated['status'] = 'in-progress';
            } else {
                $validated['status'] = 'not-started';
            }

            $record = DestinationKnowledgeTraining::findOrFail($id);
            $record->update($validated);

            // Set completion date if status is completed
            if ($validated['status'] === 'completed' && !$record->date_completed) {
                $record->date_completed = now();
                $record->save();

                // AUTO-TRANSFER to Completed Trainings section when 100% complete
                $this->moveDestinationTrainingToCompleted($record);
            }

            // CRITICAL: Sync progress with Employee Training Dashboard using admin's input progress
            $this->syncProgressWithEmployeeTraining($record);

            // CRITICAL FIX: Update competency profile when destination training is updated
            $this->updateCompetencyProfileFromDestination($record);

            // Sync progress with Employee Competency Profile and Competency Gap
            $this->syncProgressWithCompetencyProfile($record);
            $this->syncProgressWithCompetencyGap($record);

            // Create notification
            TrainingNotification::create([
                'employee_id' => $record->employee_id,
                'message' => 'Your Destination Knowledge Training record has been updated: ' . $record->destination_name,
                'sent_at' => now(),
            ]);

            // Log activity
            ActivityLog::createLog([
                'action' => 'update',
                'module' => 'Destination Knowledge Training',
                'description' => 'Updated destination knowledge training record (ID: ' . $record->id . ') and synced status with Employee Training Dashboard',
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Training record updated and status synced successfully.',
                    'record' => $record
                ]);
            }

            return redirect()->route('admin.destination-knowledge-training.index')
                ->with('success', 'Training record updated and status synced successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Update destination knowledge training error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the record. Please try again.');
        }
    }

    /**
     * Move completed destination training to Completed Trainings section
     */
    private function moveDestinationTrainingToCompleted($destinationRecord)
    {
        try {
            // Check if completed training record already exists
            $existingRecord = \App\Models\CompletedTraining::where('employee_id', $destinationRecord->employee_id)
                ->where('training_title', $destinationRecord->destination_name)
                ->first();

            if (!$existingRecord) {
                // Create completed training record
                \App\Models\CompletedTraining::create([
                    'employee_id' => $destinationRecord->employee_id,
                    'training_title' => $destinationRecord->destination_name,
                    'completion_date' => $destinationRecord->date_completed ?? now()->format('Y-m-d'),
                    'remarks' => 'Automatically moved from destination knowledge training upon 100% completion',
                    'status' => 'Verified',
                    'certificate_path' => null // Will be updated after certificate generation
                ]);

                Log::info("Automatically moved destination training to completed section", [
                    'employee_id' => $destinationRecord->employee_id,
                    'destination_name' => $destinationRecord->destination_name,
                    'progress' => $destinationRecord->progress
                ]);

                // Log activity
                ActivityLog::createLog([
                    'action' => 'create',
                    'module' => 'Completed Training Auto-Transfer',
                    'description' => "Automatically moved {$destinationRecord->destination_name} to Completed Trainings for employee {$destinationRecord->employee_id}",
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error moving destination training to completed section: ' . $e->getMessage());
        }
    }

    /**
     * Update destination knowledge training progress based on competency gap current level
     */
    private function updateProgressFromCompetencyGap($destinationRecord)
    {
        try {
            // Find the corresponding competency gap
            $competencyGap = CompetencyGap::with('competency')
                ->where('employee_id', $destinationRecord->employee_id)
                ->whereHas('competency', function($q) use ($destinationRecord) {
                    $q->where('competency_name', 'LIKE', '%' . $destinationRecord->destination_name . '%')
                      ->orWhere('competency_name', $destinationRecord->destination_name);
                })
                ->first();

            if ($competencyGap && $competencyGap->required_level > 0) {
                // Calculate progress as percentage: (current_level / required_level) * 100
                $calculatedProgress = min(100, round(($competencyGap->current_level / $competencyGap->required_level) * 100));

                // Only update if the calculated progress is different
                if ($destinationRecord->progress != $calculatedProgress) {
                    $destinationRecord->progress = $calculatedProgress;

                    // Update status based on progress
                    if ($calculatedProgress >= 100) {
                        $destinationRecord->status = 'completed';
                        if (!$destinationRecord->date_completed) {
                            $destinationRecord->date_completed = now();
                        }
                    } elseif ($calculatedProgress > 0) {
                        $destinationRecord->status = 'in-progress';
                    } else {
                        $destinationRecord->status = 'not-started';
                    }

                    $destinationRecord->save();

                    // Log the progress update
                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'update',
                        'module' => self::MODULE_NAME,
                        'description' => sprintf(
                            'Auto-updated progress to %d%% based on competency gap (Current: %d, Required: %d) for %s',
                            $calculatedProgress,
                            $competencyGap->current_level,
                            $competencyGap->required_level,
                            $destinationRecord->destination_name
                        ),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error updating progress from competency gap: ' . $e->getMessage());
        }
    }

    /**
     * Sync progress from Destination Knowledge Training to Employee Training Dashboard
     */
    private function syncProgressWithEmployeeTraining($destinationRecord)
    {
        try {
            // Find corresponding employee training record
            $employeeTraining = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                ->whereHas('course', function($q) use ($destinationRecord) {
                    $q->where('course_title', 'LIKE', '%' . $destinationRecord->destination_name . '%');
                })
                ->first();

            if ($employeeTraining) {
                // Update progress
                $employeeTraining->progress = $destinationRecord->progress ?? 0;

                // Update status based on progress - CRITICAL: Ensure status matches destination record status
                if ($destinationRecord->status === 'completed' || $destinationRecord->progress >= 100) {
                    $employeeTraining->status = 'Completed';
                    $employeeTraining->last_accessed = now();
                } elseif ($destinationRecord->status === 'in-progress' || $destinationRecord->progress > 0) {
                    $employeeTraining->status = 'In Progress';
                    $employeeTraining->last_accessed = now();
                } else {
                    $employeeTraining->status = 'Not Started';
                }

                $employeeTraining->save();

                // Log the sync activity
                ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'sync',
                    'module' => 'Training Progress Sync',
                    'description' => "Synced status '{$employeeTraining->status}' and progress ({$destinationRecord->progress}%) from Destination Knowledge Training to Employee Training Dashboard for {$destinationRecord->destination_name}",
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error syncing progress with employee training: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $record = DestinationKnowledgeTraining::findOrFail($id);
        $record->delete();
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Destination Knowledge Training',
            'description' => 'Deleted destination knowledge training record (ID: ' . $record->id . ')',
        ]);
        return redirect()->route('admin.destination-knowledge-training.index')->with('success', 'Training record deleted successfully.');
    }



    /**
     * Update progress when training dashboard is updated
     */
    public function progressUpdate($destinationId, $trainingId)
    {
        try {
            Log::info('Updating destination knowledge progress', [
                'destinationId' => $destinationId,
                'trainingId' => $trainingId
            ]);

            // Get the training dashboard entry
            $training = \App\Models\EmployeeTrainingDashboard::findOrFail($trainingId);

            // Get the destination knowledge entry
            $destination = DestinationKnowledgeTraining::findOrFail($destinationId);

            // Update destination knowledge progress to match training progress
            $destination->progress = $training->progress;

            // If training is completed, mark destination knowledge as completed
            if ($training->progress >= 100) {
                $destination->date_completed = now();
                $destination->status = 'completed';

                // AUTO-TRANSFER to Completed Trainings section when 100% complete
                $this->moveDestinationTrainingToCompleted($destination);
            } elseif ($training->progress > 0) {
                $destination->status = 'in-progress';
            }

            $destination->save();

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id() ?? 1, // Default to system user if no auth
                'action' => 'update',
                'module' => self::MODULE_NAME,
                'description' => sprintf(
                    'Updated destination knowledge training progress to %d%% (ID: %d)',
                    $destination->progress,
                    $destination->id
                ),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'progress' => $destination->progress,
                'status' => $destination->status
            ]);

        } catch (\Exception $e) {
            Log::error('Progress update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Progress update failed: ' . $e->getMessage()
            ], 500);
        }
    }


/**
 * Request activation for a destination knowledge training course
 * Routes through Course Management for approval workflow
 */
public function requestActivation(Request $request, $id)
{
    try {
        $destination = DestinationKnowledgeTraining::findOrFail($id);

        // Update destination training status to show request is pending
        $destination->update([
            'status' => 'activation-requested',
            'activation_requested_at' => now(),
            'activation_requested_by' => Auth::id()
        ]);

        // Check if course already exists in Course Management
        $course = \App\Models\CourseManagement::where('course_title', $destination->destination_name)->first();

        if (!$course) {
            // Create course in Course Management with "Pending Approval" status
            $course = \App\Models\CourseManagement::create([
                'course_title' => $destination->destination_name,
                'description' => $destination->details,
                'objectives' => $destination->objectives,
                'duration' => $destination->duration,
                'delivery_mode' => $destination->delivery_mode,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'status' => 'Pending Approval', // Requires admin approval
                'requested_by' => Auth::id(),
                'requested_at' => now(),
                'source_type' => 'destination_knowledge_training',
                'source_id' => $destination->id
            ]);
        } else {
            // If course already exists and is Active, return success with course info
            if ($course->status === 'Active') {
                return response()->json([
                    'success' => true,
                    'message' => 'Course is already active and ready for training!',
                    'course_status' => 'Active',
                    'course_id' => $course->course_id,
                    'redirect_url' => route('admin.course_management.index')
                ]);
            }

            // If course exists but not active, update to pending approval
            if ($course->status !== 'Active') {
                $course->update([
                    'status' => 'Pending Approval',
                    'requested_by' => Auth::id(),
                    'requested_at' => now()
                ]);
            }
        }

        // Log the request
        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => self::MODULE_NAME,
            'action' => 'Request Course Activation',
            'description' => "Submitted course activation request for: {$destination->destination_name} (Employee: {$destination->employee_id})",
            'model_type' => 'App\\Models\\CourseManagement',
            'model_id' => $course->course_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Course activation request submitted successfully! Please wait for admin approval in Course Management.',
            'redirect_url' => route('admin.course_management.index'),
            'course_id' => $course->course_id,
            'status' => 'Pending Approval'
        ]);

    } catch (\Exception $e) {
        Log::error('Request activation failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Request activation failed: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Sync progress from Destination Knowledge Training to Employee Competency Profile
     */
    private function syncProgressWithCompetencyProfile($destinationRecord)
    {
        try {
            // Use the new helper method to update competency profile
            $this->updateCompetencyProfileFromDestination($destinationRecord);
        } catch (\Exception $e) {
            Log::error('Error syncing progress with competency profile: ' . $e->getMessage());
        }
    }

    /**
     * Sync progress from Destination Knowledge Training to Competency Gap
     */
    private function syncProgressWithCompetencyGap($destinationRecord)
    {
        try {
            // Extract competency name from destination name
            $competencyName = $this->extractCompetencyName($destinationRecord->destination_name);

            // Find competency in library
            $competency = $this->findCompetencyByName($competencyName);

            if ($competency) {
                // Find corresponding competency gap
                $competencyGap = CompetencyGap::where('employee_id', $destinationRecord->employee_id)
                    ->where('competency_id', $competency->id)
                    ->first();

                if ($competencyGap) {
                    // Convert progress percentage to current level (1-5 scale)
                    $currentLevel = $this->convertProgressToProficiencyLevel($destinationRecord->progress ?? 0);

                    $competencyGap->current_level = $currentLevel;
                    $competencyGap->gap = max(0, $competencyGap->required_level - $currentLevel);
                    $competencyGap->save();

                    // Log the sync activity
                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'sync',
                        'module' => 'Competency Gap Sync',
                        'description' => "Synced current level to {$currentLevel} ({$destinationRecord->progress}%) from Destination Knowledge Training for {$destinationRecord->destination_name}",
                    ]);
                } else {
                    // Create competency gap if it doesn't exist
                    $this->createCompetencyGapFromDestination($destinationRecord, $competency);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error syncing progress with competency gap: ' . $e->getMessage());
        }
    }

    /**
     * Get destination details by name for auto-population
     */
    public function getDestinationDetails($destinationName)
    {
        try {
            $destination = DestinationMaster::where('destination_name', $destinationName)
                ->where('is_active', true)
                ->first();

            if ($destination) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'details' => $destination->details,
                        'objectives' => $destination->objectives,
                        'duration' => $destination->duration,
                        'delivery_mode' => $destination->delivery_mode
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Destination not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching destination details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching destination details'
            ], 500);
        }
    }

    /**
     * Store destination master data
     */
    public function storeDestinationMaster(Request $request)
    {
        try {
            $validated = $request->validate([
                'destination' => 'required|string|max:255',
                'details' => 'required|string',
                'objectives' => 'required|string',
                'duration' => 'required|string|max:100',
                'delivery_mode' => 'required|string|max:100'
            ]);

            $destinationMaster = DestinationMaster::create([
                'destination_name' => $validated['destination'],
                'details' => $validated['details'],
                'objectives' => $validated['objectives'],
                'duration' => $validated['duration'],
                'delivery_mode' => $validated['delivery_mode'],
                'is_active' => true
            ]);

            // Log activity
            ActivityLog::createLog([
                'action' => 'create',
                'module' => 'Destination Master',
                'description' => 'Added new destination master: ' . $destinationMaster->destination_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Destination added successfully!',
                'data' => $destinationMaster
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating destination master: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating destination: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create upcoming training record when destination knowledge training is assigned
     */
    private function createUpcomingTraining($destinationRecord)
    {
        try {
            // Check if upcoming training already exists
            $existingUpcoming = \App\Models\UpcomingTraining::where('employee_id', $destinationRecord->employee_id)
                ->where('training_title', $destinationRecord->destination_name)
                ->first();

            if (!$existingUpcoming) {
                // Create upcoming training record
                \App\Models\UpcomingTraining::create([
                    'employee_id' => $destinationRecord->employee_id,
                    'training_title' => $destinationRecord->destination_name,
                    'start_date' => now(),
                    'end_date' => now()->addMonths(3),
                    'status' => 'Scheduled',
                    'source' => 'destination_assigned',
                    'assigned_by' => Auth::id(),
                    'assigned_by_name' => Auth::user()->name ?? 'System Admin',
                    'assigned_date' => now()
                ]);

                Log::info("Created upcoming training record for employee {$destinationRecord->employee_id} - {$destinationRecord->destination_name}");
            }
        } catch (\Exception $e) {
            Log::error('Error creating upcoming training record: ' . $e->getMessage());
        }
    }

    /**
     * Store a new possible destination
     */
    public function storePossibleDestination(Request $request)
    {
        $request->validate([
            'destination' => 'required|string|max:255',
            'details' => 'required|string',
            'objectives' => 'required|string',
            'duration' => 'required|string|max:100',
            'delivery_mode' => 'required|string|in:On-site Training,Online Training,Blended Learning,Workshop,Seminar,Field Training'
        ]);

        try {
            // Store in destination_masters table
            DestinationMaster::create([
                'destination_name' => $request->destination,
                'details' => $request->details,
                'objectives' => $request->objectives,
                'duration' => $request->duration,
                'delivery_mode' => $request->delivery_mode
            ]);

            // Log the activity
            ActivityLog::createLog([
                'user_type' => 'admin',
                'action' => 'create',
                'module' => self::MODULE_NAME,
                'description' => "Added new possible destination: {$request->destination}",
                'changes' => json_encode([
                    'destination' => $request->destination,
                    'delivery_mode' => $request->delivery_mode
                ])
            ]);

            return redirect()->back()->with('success', 'Possible training destination added successfully!');

        } catch (\Exception $e) {
            Log::error('Error storing possible destination: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add possible destination. Please try again.');
        }
    }

    /**
     * Delete a possible destination
     */
    public function destroyPossibleDestination($id)
    {
        try {
            $destination = DestinationMaster::findOrFail($id);
            $destinationName = $destination->destination_name;

            $destination->delete();

            // Log the activity
            ActivityLog::createLog([
                'user_type' => 'admin',
                'action' => 'delete',
                'module' => self::MODULE_NAME,
                'description' => "Deleted possible destination: {$destinationName}",
                'changes' => json_encode([
                    'destination' => $destinationName,
                    'id' => $id
                ])
            ]);

            return redirect()->back()->with('success', 'Possible destination deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting possible destination: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete possible destination. Please try again.');
        }
    }

    /**
     * Create Employee Training Dashboard record for auto-assigned destination knowledge training
     */
    private function createEmployeeTrainingDashboardRecord($destinationRecord, $gap)
    {
        try {
            // Check if course already exists to prevent duplicates
            $course = \App\Models\CourseManagement::where('course_title', $destinationRecord->destination_name)->first();

            // Only create if it doesn't exist
            if (!$course) {
                $course = \App\Models\CourseManagement::create([
                    'course_title' => $destinationRecord->destination_name,
                    'description' => $destinationRecord->details,
                    'start_date' => now(),
                    'end_date' => now()->addMonths(3),
                    'status' => 'Active'
                ]);
            }

            // Calculate initial progress based on competency gap
            $initialProgress = 0;
            $status = 'Assigned';

            if ($gap) {
                $initialProgress = round(($gap->current_level / 5) * 100);
                if ($gap->current_level >= 3) {
                    $status = 'In Progress';
                }
            }

            // Check for existing assignment to prevent duplicates
            $existingAssignment = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                ->where('course_id', $course->course_id)
                ->first();

            if (!$existingAssignment) {
                // Create Employee Training Dashboard record
                \App\Models\EmployeeTrainingDashboard::create([
                    'employee_id' => $destinationRecord->employee_id,
                    'course_id' => $course->course_id,
                    'training_date' => now(),
                    'status' => $status,
                    'progress' => $initialProgress,
                    'remarks' => 'Auto-assigned from competency gap analysis'
                ]);
            }

            Log::info("Created Employee Training Dashboard record for {$destinationRecord->employee_id} - {$destinationRecord->destination_name}");

        } catch (\Exception $e) {
            Log::error('Error creating Employee Training Dashboard record: ' . $e->getMessage());
        }
    }

    /**
     * Assign destination knowledge training to employee's upcoming training list
     */
    public function assignToUpcomingTraining(Request $request)
    {
        try {
            Log::info('assignToUpcomingTraining called', ['request_data' => $request->all()]);

            // Validate the request
            $request->validate([
                'destination_id' => 'required|integer|exists:destination_knowledge_trainings,id'
            ]);

            Log::info('Validation passed', ['destination_id' => $request->destination_id]);

            // Get the destination record with employee relationship
            $destinationRecord = DestinationKnowledgeTraining::with('employee')->findOrFail($request->destination_id);

            Log::info('Found destination record', [
                'id' => $destinationRecord->id,
                'employee_id' => $destinationRecord->employee_id,
                'destination_name' => $destinationRecord->destination_name,
                'delivery_mode' => $destinationRecord->delivery_mode,
                'has_employee' => $destinationRecord->employee ? 'yes' : 'no'
            ]);

            // CRITICAL FIX: Check delivery mode before proceeding
            // For Online Training, only proceed if Auto-Assign button is explicitly clicked
            if ($destinationRecord->delivery_mode === 'Online Training') {
                Log::info('Online Training detected - proceeding with manual assignment to upcoming training');
            } else {
                Log::info('Non-Online Training detected - will auto-complete upon acceptance');
            }

            // Check if admin_approved_for_upcoming column exists, if not add it
            if (!Schema::hasColumn('destination_knowledge_trainings', 'admin_approved_for_upcoming')) {
                Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                    $table->boolean('admin_approved_for_upcoming')->default(false)->after('is_active');
                });
                // Refresh the model instance
                $destinationRecord = DestinationKnowledgeTraining::with('employee')->findOrFail($request->destination_id);
            }
            
            // Check if assigned_by_name column exists in upcoming_trainings, if not add it
            if (!Schema::hasColumn('upcoming_trainings', 'assigned_by_name')) {
                Schema::table('upcoming_trainings', function (Blueprint $table) {
                    $table->string('assigned_by_name')->nullable()->after('assigned_by');
                });
                Log::info('Added assigned_by_name column to upcoming_trainings table');
            }

            // Check if upcoming_trainings table exists, if not create it
            if (!Schema::hasTable('upcoming_trainings')) {
                Log::info('Creating upcoming_trainings table as it does not exist');
                Schema::create('upcoming_trainings', function (Blueprint $table) {
                    $table->id('upcoming_id');
                    $table->string('employee_id');
                    $table->string('training_title');
                    $table->date('start_date');
                    $table->date('end_date')->nullable();
                    $table->date('expired_date')->nullable(); // Add expired_date column
                    $table->string('status')->default('Assigned');
                    $table->string('source')->nullable();
                    $table->string('assigned_by')->nullable();
                    $table->timestamp('assigned_date')->nullable();
                    $table->unsignedBigInteger('destination_training_id')->nullable();
                    $table->boolean('needs_response')->default(false);
                    $table->timestamps();

                    // Add indexes
                    $table->index('employee_id');
                    $table->index('destination_training_id');
                });
                Log::info('upcoming_trainings table created successfully');
            }

            // Check if expired_date column exists, if not add it
            if (!Schema::hasColumn('upcoming_trainings', 'expired_date')) {
                Schema::table('upcoming_trainings', function (Blueprint $table) {
                    $table->date('expired_date')->nullable()->after('end_date');
                });
                Log::info('Added expired_date column to upcoming_trainings table');

                // Sync existing records with destination training expired dates
                $this->syncExistingUpcomingTrainingExpiredDates();
            }

            // Mark as approved for upcoming training and set status to pending response
            $destinationRecord->admin_approved_for_upcoming = true;
            $destinationRecord->status = 'pending-response';
            $destinationRecord->is_active = false; // Employee needs to accept first
            $destinationRecord->save();

            Log::info('About to create/update UpcomingTraining record');

            // Check for existing UpcomingTraining record to prevent duplicates
            $existingUpcoming = null;
            try {
                $existingUpcoming = \App\Models\UpcomingTraining::where('employee_id', $destinationRecord->employee_id)
                    ->where('training_title', $destinationRecord->destination_name)
                    ->where('source', 'destination_assigned')
                    ->first();
                Log::info('Checked for existing upcoming training', ['found' => $existingUpcoming ? 'yes' : 'no']);
            } catch (\Exception $queryException) {
                Log::error('Error querying existing upcoming training: ' . $queryException->getMessage());
                $existingUpcoming = null;
            }

            // Get assigned_by value safely with proper admin name
            $assignedBy = 'Admin User';
            $assignedByName = 'Admin User';
            try {
                if (Auth::check() && Auth::user()) {
                    $user = Auth::user();
                    
                    // Get name from user table
                    if (isset($user->name) && !empty($user->name)) {
                        $assignedByName = $user->name;
                    }
                    // Try to get from employee record using email
                    elseif (isset($user->email)) {
                        try {
                            $employee = \App\Models\Employee::where('email', $user->email)->first();
                            if ($employee) {
                                $assignedByName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                                if (empty(trim($assignedByName))) {
                                    $assignedByName = $employee->employee_id ?? 'Admin User';
                                }
                            }
                        } catch (\Exception $empException) {
                            Log::warning('Employee lookup failed: ' . $empException->getMessage());
                        }
                    }
                    
                    // Clean up the name
                    $assignedByName = trim($assignedByName);
                    if (empty($assignedByName)) {
                        $assignedByName = 'Admin User';
                    }
                    
                    $assignedBy = $assignedByName;
                }
            } catch (\Exception $authException) {
                Log::warning('Auth issue in assignToUpcomingTraining: ' . $authException->getMessage());
                $assignedBy = 'Admin User';
                $assignedByName = 'Admin User';
            }

            Log::info('Prepared data for upcoming training', [
                'employee_id' => $destinationRecord->employee_id,
                'training_title' => $destinationRecord->destination_name,
                'assigned_by' => $assignedByName,
                'existing_record' => $existingUpcoming ? 'update' : 'create'
            ]);

            try {
                if ($existingUpcoming) {
                    Log::info('Updating existing upcoming training record');
                    // Update existing record
                    $existingUpcoming->update([
                        'status' => 'Assigned',
                        'source' => 'destination_assigned',
                        'assigned_by' => Auth::id() ?? 1,
                        'assigned_by_name' => $assignedByName,
                        'assigned_date' => now(),
                        'destination_training_id' => $destinationRecord->id,
                        'needs_response' => true,
                        'expired_date' => $destinationRecord->expired_date
                    ]);
                    Log::info('Successfully updated existing upcoming training record');
                } else {
                    Log::info('Creating new upcoming training record');
                    // Prepare data for new record with proper validation
                    $upcomingData = [
                        'employee_id' => $destinationRecord->employee_id,
                        'training_title' => $destinationRecord->destination_name,
                        'start_date' => $destinationRecord->created_at ? $destinationRecord->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                        'end_date' => null, // Keep end_date separate from expired_date
                        'expired_date' => $destinationRecord->expired_date,
                        'status' => 'Assigned',
                        'source' => 'destination_assigned',
                        'assigned_by' => Auth::id() ?? 1,
                        'assigned_by_name' => $assignedByName,
                        'assigned_date' => now(),
                        'destination_training_id' => $destinationRecord->id,
                        'needs_response' => true
                    ];

                    Log::info('Creating upcoming training with data:', $upcomingData);

                    // Create new record
                    $newUpcoming = \App\Models\UpcomingTraining::create($upcomingData);
                    Log::info('Successfully created new upcoming training record', ['id' => $newUpcoming->upcoming_id]);
                }
            } catch (\Exception $upcomingException) {
                Log::error('Error creating/updating upcoming training record: ' . $upcomingException->getMessage());
                Log::error('Stack trace: ' . $upcomingException->getTraceAsString());

                // Don't throw the exception - the training assignment was successful
                // Just log the error and continue with success response
                Log::warning('Continuing with success response despite upcoming training record error');
            }

            // Get employee name safely
            $employeeName = 'Employee';
            try {
                if ($destinationRecord->employee) {
                    $employeeName = trim(($destinationRecord->employee->first_name ?? '') . ' ' . ($destinationRecord->employee->last_name ?? '')) ?: 'Employee';
                }
            } catch (\Exception $empException) {
                Log::warning('Employee relationship issue in assignToUpcomingTraining: ' . $empException->getMessage());
                $employeeName = 'Employee';
            }

            // Log the activity
            ActivityLog::createLog([
                'action' => 'approve',
                'module' => self::MODULE_NAME,
                'description' => "Approved {$destinationRecord->destination_name} for {$employeeName}'s upcoming trainings",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned {$destinationRecord->destination_name} to {$employeeName}'s upcoming trainings! Employee can now accept or decline the training."
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving for upcoming training: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve training: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Categorize competency based on name keywords
     */
    private function categorizeCompetency($competencyName)
    {
        $name = strtolower($competencyName);

        if (strpos($name, 'destination') !== false || strpos($name, 'location') !== false ||
            strpos($name, 'baesa') !== false || strpos($name, 'quezon') !== false ||
            strpos($name, 'baguio') !== false || strpos($name, 'boracay') !== false ||
            strpos($name, 'cebu') !== false || strpos($name, 'davao') !== false) {
            return 'Destination Knowledge';
        } elseif (strpos($name, 'customer') !== false || strpos($name, 'service') !== false) {
            return 'Customer Service';
        } elseif (strpos($name, 'communication') !== false) {
            return 'Communication';
        } elseif (strpos($name, 'leadership') !== false || strpos($name, 'management') !== false) {
            return 'Leadership';
        } elseif (strpos($name, 'technical') !== false || strpos($name, 'system') !== false) {
            return 'Technical';
        }

        return 'General';
    }

    /**
     * Delete a possible destination record
     */
    public function destroyPossible($id)
    {
        try {
            // Find the destination master record
            $destinationMaster = \App\Models\DestinationMaster::findOrFail($id);

            // Store destination name for logging
            $destinationName = $destinationMaster->destination_name;

            // Delete the record
            $destinationMaster->delete();

            // Log activity
            ActivityLog::createLog([
                'action' => 'delete',
                'module' => 'Destination Master',
                'description' => "Deleted possible destination: {$destinationName} (ID: {$id})",
            ]);

            return response()->json(['success' => true, 'message' => 'Possible destination deleted successfully.']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Possible destination not found.'], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting possible destination: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting possible destination: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Sync existing upcoming training records with destination training expired dates
     */
    private function syncExistingUpcomingTrainingExpiredDates()
    {
        try {
            Log::info('Starting sync of existing upcoming training expired dates');

            $syncedCount = 0;

            // Get all upcoming trainings that are destination assigned but missing expired_date
            $upcomingTrainings = \App\Models\UpcomingTraining::where('source', 'destination_assigned')
                ->whereNull('expired_date')
                ->whereNotNull('destination_training_id')
                ->get();

            foreach ($upcomingTrainings as $upcoming) {
                // Find the corresponding destination training record
                $destinationTraining = DestinationKnowledgeTraining::find($upcoming->destination_training_id);

                if ($destinationTraining && $destinationTraining->expired_date) {
                    // Update the upcoming training with the expired date
                    $upcoming->expired_date = $destinationTraining->expired_date;
                    $upcoming->save();

                    $syncedCount++;
                    Log::info("Synced expired date for upcoming training ID {$upcoming->upcoming_id}: {$destinationTraining->expired_date}");
                }
            }

            Log::info("Completed sync of existing upcoming training expired dates. Synced: {$syncedCount} records");

        } catch (\Exception $e) {
            Log::error('Error syncing existing upcoming training expired dates: ' . $e->getMessage());
        }
    }

    /**
     * Fix expiration dates for destination training records
     */
    /**
     * Consolidate destination knowledge training tables - run this once
     */
    public function consolidateDestinationTraining()
    {
        try {
            // Drop the old view if it exists
            DB::statement('DROP VIEW IF EXISTS destination_knowledge_training');

            // Ensure all records have proper training_type and source
            DB::table('destination_knowledge_trainings')
                ->whereNull('training_type')
                ->orWhere('training_type', '')
                ->update([
                    'training_type' => 'destination',
                    'source' => 'destination_knowledge_training'
                ]);

            // Update training_title from destination_name if missing
            DB::table('destination_knowledge_trainings')
                ->whereNull('training_title')
                ->orWhere('training_title', '')
                ->update([
                    'training_title' => DB::raw('destination_name')
                ]);

            $updated = DB::table('destination_knowledge_trainings')
                ->where('training_type', 'destination')
                ->count();

            return response()->json([
                'success' => true,
                'message' => "Destination training consolidated successfully - {$updated} records updated to use single table approach"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error consolidating: ' . $e->getMessage()
            ], 500);
        }
    }

    private function fixExpirationDates()
    {
        try {
            $updated = 0;

            // Get all destination training records without proper expiration dates
            $records = DestinationKnowledgeTraining::destinationTrainings()
                ->where(function($query) {
                    $query->whereNull('expired_date')
                          ->orWhere('expired_date', '0000-00-00 00:00:00')
                          ->orWhere('expired_date', '');
                })
                ->get();

            foreach ($records as $record) {
                // Set expiration date to 3 months from creation date
                $expirationDate = $record->created_at->addMonths(3);

                $record->expired_date = $expirationDate;

                // Ensure the record is properly marked for upcoming if active
                if ($record->is_active && !$record->admin_approved_for_upcoming) {
                    $record->admin_approved_for_upcoming = true;
                }

                $record->save();
                $updated++;
            }

            if ($updated > 0) {
                Log::info("Fixed expiration dates for {$updated} destination training records");
            }

        } catch (\Exception $e) {
            Log::error('Error fixing expiration dates: ' . $e->getMessage());
        }
    }

    /**
     * Create competency profile entry when destination training is created
     */
    private function createCompetencyProfileFromDestination($destinationRecord)
    {
        try {
            // Extract competency name from destination name
            $competencyName = $this->extractCompetencyName($destinationRecord->destination_name);

            // Find or create competency in library
            $competency = $this->findOrCreateCompetency($competencyName, $destinationRecord->destination_name);

            if ($competency) {
                // Check if competency profile already exists
                $existingProfile = EmployeeCompetencyProfile::where('employee_id', $destinationRecord->employee_id)
                    ->where('competency_id', $competency->id)
                    ->first();

                if (!$existingProfile) {
                    // Convert progress to proficiency level (1-5 scale)
                    $proficiencyLevel = $this->convertProgressToProficiencyLevel($destinationRecord->progress ?? 0);

                    // Create competency profile
                    EmployeeCompetencyProfile::create([
                        'employee_id' => $destinationRecord->employee_id,
                        'competency_id' => $competency->id,
                        'proficiency_level' => $proficiencyLevel,
                        'assessment_date' => now(),
                    ]);

                    Log::info("Created competency profile for {$destinationRecord->employee_id} - {$competency->competency_name} with proficiency level {$proficiencyLevel}");

                    // Also create competency gap if it doesn't exist
                    $this->createCompetencyGapFromDestination($destinationRecord, $competency);
                } else {
                    Log::info("Competency profile already exists for {$destinationRecord->employee_id} - {$competency->competency_name}");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error creating competency profile from destination: ' . $e->getMessage());
        }
    }

    /**
     * Update competency profile entry when destination training is updated
     */
    private function updateCompetencyProfileFromDestination($destinationRecord)
    {
        try {
            // Extract competency name from destination name
            $competencyName = $this->extractCompetencyName($destinationRecord->destination_name);

            // Find competency in library
            $competency = $this->findCompetencyByName($competencyName);

            if ($competency) {
                // Find existing competency profile
                $competencyProfile = EmployeeCompetencyProfile::where('employee_id', $destinationRecord->employee_id)
                    ->where('competency_id', $competency->id)
                    ->first();

                if ($competencyProfile) {
                    // Convert progress to proficiency level (1-5 scale)
                    $proficiencyLevel = $this->convertProgressToProficiencyLevel($destinationRecord->progress ?? 0);

                    // Update competency profile
                    $competencyProfile->update([
                        'proficiency_level' => $proficiencyLevel,
                        'assessment_date' => now(),
                    ]);

                    Log::info("Updated competency profile for {$destinationRecord->employee_id} - {$competency->competency_name} to proficiency level {$proficiencyLevel}");
                } else {
                    // Create if it doesn't exist
                    $this->createCompetencyProfileFromDestination($destinationRecord);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error updating competency profile from destination: ' . $e->getMessage());
        }
    }

    /**
     * Extract competency name from destination name
     */
    private function extractCompetencyName($destinationName)
    {
        // Remove common training suffixes
        $competencyName = str_replace([' Training', ' Course', ' Program'], '', $destinationName);

        // For destination knowledge, format as "Destination Knowledge - [Location]"
        if ($this->isDestinationKnowledge($destinationName)) {
            return 'Destination Knowledge - ' . $competencyName;
        }

        return $competencyName;
    }

    /**
     * Check if this is destination knowledge training
     */
    private function isDestinationKnowledge($destinationName)
    {
        $destinationKeywords = [
            'BAESA', 'QUEZON', 'BAGUIO', 'BORACAY', 'CEBU', 'DAVAO', 'MANILA',
            'DESTINATION', 'LOCATION', 'PLACE', 'CITY', 'TERMINAL', 'STATION'
        ];

        $upperName = strtoupper($destinationName);

        foreach ($destinationKeywords as $keyword) {
            if (strpos($upperName, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find or create competency in library
     */
    private function findOrCreateCompetency($competencyName, $originalDestinationName)
    {
        // First try exact match
        $competency = CompetencyLibrary::where('competency_name', $competencyName)->first();

        if (!$competency) {
            // Try partial match
            $competency = CompetencyLibrary::where('competency_name', 'LIKE', '%' . $competencyName . '%')->first();
        }

        if (!$competency) {
            // Create new competency
            $category = $this->categorizeCompetency($competencyName);

            $competency = CompetencyLibrary::create([
                'competency_name' => $competencyName,
                'description' => 'Auto-created from destination knowledge training: ' . $originalDestinationName,
                'category' => $category,
            ]);

            Log::info("Created new competency: {$competencyName}");
        }

        return $competency;
    }

    /**
     * Find competency by name
     */
    private function findCompetencyByName($competencyName)
    {
        // First try exact match
        $competency = CompetencyLibrary::where('competency_name', $competencyName)->first();

        if (!$competency) {
            // Try partial match
            $competency = CompetencyLibrary::where('competency_name', 'LIKE', '%' . $competencyName . '%')->first();
        }

        return $competency;
    }

    /**
     * Convert progress percentage to proficiency level (1-5 scale)
     */
    private function convertProgressToProficiencyLevel($progress)
    {
        if ($progress >= 90) return 5; // Expert
        if ($progress >= 70) return 4; // Advanced
        if ($progress >= 50) return 3; // Proficient
        if ($progress >= 30) return 2; // Developing
        if ($progress > 0) return 1;   // Beginner
        return 1; // Default minimum level
    }

    /**
     * Create competency gap from destination training
     */
    private function createCompetencyGapFromDestination($destinationRecord, $competency)
    {
        try {
            // Check if competency gap already exists
            $existingGap = CompetencyGap::where('employee_id', $destinationRecord->employee_id)
                ->where('competency_id', $competency->id)
                ->first();

            if (!$existingGap) {
                // Convert progress to current level (1-5 scale)
                $currentLevel = $this->convertProgressToProficiencyLevel($destinationRecord->progress ?? 0);
                $requiredLevel = 5; // Default required level

                CompetencyGap::create([
                    'employee_id' => $destinationRecord->employee_id,
                    'competency_id' => $competency->id,
                    'current_level' => $currentLevel,
                    'required_level' => $requiredLevel,
                    'gap' => max(0, $requiredLevel - $currentLevel),
                    'expired_date' => $destinationRecord->expired_date,
                ]);

                Log::info("Created competency gap for {$destinationRecord->employee_id} - {$competency->competency_name}");
            }
        } catch (\Exception $e) {
            Log::error('Error creating competency gap from destination: ' . $e->getMessage());
        }
    }

    /**
     * Sync all existing destination knowledge training records with competency profiles
     */
    public function syncAllWithCompetencyProfiles()
    {
        try {
            $syncedCount = 0;
            $createdCount = 0;
            $errors = [];

            // Get all destination knowledge training records
            $destinationRecords = DestinationKnowledgeTraining::with('employee')->get();

            if ($destinationRecords->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No destination knowledge training records found to sync.'
                ]);
            }

            foreach ($destinationRecords as $record) {
                try {
                    // Use the helper methods to create/update competency profiles
                    $this->createCompetencyProfileFromDestination($record);
                    $createdCount++;
                    $syncedCount++;

                } catch (\Exception $recordError) {
                    $errors[] = "Error syncing record {$record->id}: " . $recordError->getMessage();
                    Log::error("Error syncing destination record {$record->id}: " . $recordError->getMessage());
                }
            }

            // Log the sync activity
            ActivityLog::createLog([
                'action' => 'sync_all',
                'module' => self::MODULE_NAME,
                'description' => "Synced {$syncedCount} destination knowledge training records with competency profiles. Created/updated {$createdCount} competency profiles.",
            ]);

            $message = "Successfully processed {$syncedCount} destination knowledge training records. Created/updated {$createdCount} competency profiles.";
            if (!empty($errors)) {
                $message .= " Errors encountered: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " and " . (count($errors) - 3) . " more...";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'synced_count' => $syncedCount,
                'created_count' => $createdCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error in syncAllWithCompetencyProfiles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing all records with competency profiles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix missing columns in destination_knowledge_trainings table
     */
    public function fixMissingColumns()
    {
        try {
            $result = DestinationKnowledgeTraining::fixMissingColumns();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error fixing missing columns: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fixing missing columns: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export destination knowledge training data to Excel (CSV format)
     */
    public function exportExcel()
    {
        try {
            // Get all destination knowledge training records with employee relationships
            $destinations = DestinationKnowledgeTraining::with(['employee'])->orderBy('created_at', 'desc')->get();

            // Create CSV content
            $csv = "ID,Employee Name,Employee ID,Destination,Details,Delivery Mode,Date Created,Expired Date,Status,Progress,Remarks\n";
            
            foreach ($destinations as $record) {
                $employeeName = $record->employee ? ($record->employee->first_name . ' ' . $record->employee->last_name) : 'Unknown Employee';
                $employeeId = $record->employee ? $record->employee->employee_id : 'N/A';
                $expiredDate = $record->expired_date ? $record->expired_date->format('Y-m-d') : 'Not Set';
                $createdDate = $record->created_at ? $record->created_at->format('Y-m-d') : 'N/A';
                
                $csv .= '"' . $record->id . '","' . $employeeName . '","' . $employeeId . '","' . 
                        $record->destination_name . '","' . str_replace('"', '""', $record->details) . '","' . 
                        $record->delivery_mode . '","' . $createdDate . '","' . $expiredDate . '","' . 
                        $record->status . '","' . ($record->progress ?? 0) . '%","' . 
                        str_replace('"', '""', $record->remarks ?? '') . '"' . "\n";
            }

            // Log activity
            ActivityLog::createLog([
                'action' => 'export',
                'module' => self::MODULE_NAME,
                'description' => 'Exported destination knowledge training data to Excel (CSV format)',
            ]);

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="destination_knowledge_training_' . date('Y-m-d_H-i-s') . '.csv"');

        } catch (\Exception $e) {
            Log::error('Error exporting destination knowledge training to Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Export destination knowledge training data to PDF
     */
    public function exportPdf()
    {
        try {
            // Get all destination knowledge training records with employee relationships
            $destinations = DestinationKnowledgeTraining::with(['employee'])->orderBy('created_at', 'desc')->get();

            // Create a view for PDF generation
            $data = [
                'destinations' => $destinations,
                'title' => 'Destination Knowledge Training Report',
                'generated_at' => date('Y-m-d H:i:s'),
                'total_records' => $destinations->count()
            ];

            // Log activity
            ActivityLog::createLog([
                'action' => 'export',
                'module' => self::MODULE_NAME,
                'description' => 'Exported destination knowledge training data to PDF',
            ]);

            // Return a view that will be rendered as PDF-ready HTML
            return view('exports.destination_knowledge_training_pdf', $data);

        } catch (\Exception $e) {
            Log::error('Error exporting destination knowledge training to PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }
}
