<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetencyGap;
use App\Models\Employee;
use App\Models\CompetencyLibrary;
use App\Models\EmployeeCompetencyProfile;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CompetencyGapAnalysisController extends Controller
{
    // AJAX: Recalculate readiness scores and return updated gap data
    public function refreshScore(Request $request)
    {
        $gaps = \App\Models\CompetencyGap::with(['employee', 'competency'])->get();
        $gapData = $gaps->map(function($gap) {
            return [
                'id' => $gap->id,
                'employee_id' => $gap->employee_id,
                'competency_id' => $gap->competency_id,
                'competency_rate' => $gap->competency ? $gap->competency->rate : 0,
                'required_level' => $gap->required_level,
                'current_level' => $gap->current_level,
                'gap' => $gap->gap,
            ];
        });
        return response()->json(['gaps' => $gapData]);
    }
    public function index()
    {
        // Ensure table exists before proceeding
        $this->ensureTableExists();

        // Fix missing categories for existing competencies
        $this->fixMissingCategories();

        // Eager load related employee and competency to avoid N+1 problem
        // Only show accessible (active and non-expired) gaps by default
        $gaps = CompetencyGap::with(['employee', 'competency'])->accessible()->get();
        $employees = Employee::all()->map(function($emp) {
            return [
                'id' => $emp->employee_id,
                'name' => $emp->first_name . ' ' . $emp->last_name
            ];
        });
        $competencies = CompetencyLibrary::select('id', 'competency_name', 'description', 'category', 'rate')->get();

        // Check for existing training assignments for each employee-competency combination
        $employeeTrainingAssignments = [];

        // Build a map of employee -> competency assignments to prevent duplicates
        foreach ($gaps as $gap) {
            $employeeId = $gap->employee_id;
            $competencyId = $gap->competency_id;
            $competencyName = $gap->competency->competency_name ?? '';

            // Check if THIS SPECIFIC EMPLOYEE already has training assigned for THIS SPECIFIC COMPETENCY
            // Use EmployeeTrainingDashboard with fuzzy matching on course titles to find related training
            $hasSpecificTraining = \App\Models\EmployeeTrainingDashboard::where('employee_training_dashboards.employee_id', $employeeId)
                ->join('course_management', 'employee_training_dashboards.course_id', '=', 'course_management.course_id')
                ->where(function($query) use ($competencyName) {
                    $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                    $query->where('course_management.course_title', 'LIKE', '%' . $cleanCompetency . '%')
                          ->orWhere('course_management.course_title', 'LIKE', '%' . $competencyName . '%');
                })
                ->exists();

            // Store the specific assignment status for this employee-competency pair
            $employeeTrainingAssignments[$employeeId . '_' . $competencyId] = $hasSpecificTraining;
        }

        // Get expired gaps for display
        $expiredGaps = CompetencyGap::with(['employee', 'competency'])->expired()->get();

        return view('competency_management.competency_gap', compact('gaps', 'employees', 'competencies', 'employeeTrainingAssignments', 'expiredGaps'));
    }

    public function store(Request $request)
    {
        try {
            // Enhanced debug logging
            Log::info('=== CompetencyGap Store Request START ===');
            Log::info('Request Method: ' . $request->method());
            Log::info('Request URL: ' . $request->fullUrl());
            Log::info('Request Headers: ', $request->headers->all());
            Log::info('Request Data: ', $request->all());
            Log::info('Is AJAX: ' . ($request->ajax() ? 'YES' : 'NO'));
            Log::info('Wants JSON: ' . ($request->wantsJson() ? 'YES' : 'NO'));

            // Ensure table exists before proceeding
            $this->ensureTableExists();

            Log::info('Table exists, proceeding with validation...');

            $validated = $request->validate([
                'employee_id'    => 'required|exists:employees,employee_id',
                'competency_id'  => 'required|exists:competency_library,id',
                'required_level' => 'required|integer|min:1|max:5',
                'current_level'  => 'required|integer|min:0|max:5',
                'gap'            => 'required|integer',
                'gap_description'=> 'nullable|string|max:1000',
                'expired_date'   => 'nullable|date',
            ]);

            Log::info('Validation passed. Validated Data: ', $validated);

            // Set default expired date if not provided
            if (!isset($validated['expired_date']) || empty($validated['expired_date'])) {
                $validated['expired_date'] = now()->addDays(30);
                Log::info('Set default expired_date: ' . $validated['expired_date']->format('Y-m-d H:i:s'));
            }

            Log::info('Attempting to create CompetencyGap record...');

            $gap = CompetencyGap::create($validated);

            Log::info('CompetencyGap created successfully with ID: ' . $gap->id);

            // Log creation with expiration info
            try {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'module' => 'Competency Management',
                    'action' => 'create',
                    'description' => 'Added competency gap for employee ID: ' . $gap->employee_id . ' (Expires: ' . ($gap->expired_date ? $gap->expired_date->format('Y-m-d g:i A') : 'No expiry') . ')',
                    'model_type' => CompetencyGap::class,
                    'model_id' => $gap->id,
                ]);
                Log::info('Activity log created successfully');
            } catch (\Exception $logError) {
                Log::warning('Activity log creation failed: ' . $logError->getMessage());
                // Don't fail the main operation if logging fails
            }

            Log::info('=== CompetencyGap Store Request SUCCESS ===');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gap record added successfully!',
                    'gap' => $gap->load(['employee', 'competency'])
                ]);
            }
            return redirect()->back()->with('success', 'Gap record added!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error: ', $e->errors());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('=== CompetencyGap Store Request FAILED ===');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                    'debug' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]
                ], 500);
            }
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $gap = CompetencyGap::findOrFail($id);
            $validated = $request->validate([
                'employee_id'    => 'required|exists:employees,employee_id',
                'competency_id'  => 'required|exists:competency_library,id',
                'required_level' => 'required|integer|min:1|max:5',
                'current_level'  => 'required|integer|min:0|max:5',
                'gap'            => 'required|integer',
                'gap_description'=> 'nullable|string|max:1000',
                'expired_date'   => 'nullable|date',
            ]);
            $gap->update($validated);

            // SYNC WITH TRAINING RECORD: Update expired dates in both systems
            $this->syncExpiredDateWithTraining($gap);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'update',
                'description' => 'Updated competency gap for employee ID: ' . $gap->employee_id,
                'model_type' => CompetencyGap::class,
                'model_id' => $gap->id,
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gap record updated successfully!',
                    'gap' => $gap->load(['employee', 'competency'])
                ]);
            }
            return redirect()->back()->with('success', 'Gap record updated!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $gap = CompetencyGap::findOrFail($id);
            $employeeId = $gap->employee_id;
            $gap->delete();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'delete',
                'description' => 'Deleted competency gap for employee ID: ' . $employeeId,
                'model_type' => CompetencyGap::class,
                'model_id' => $id,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gap record deleted successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Gap record deleted!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    // Export gaps to CSV
    public function export()
    {
        $gaps = CompetencyGap::with(['employee', 'competency'])->get();

        $filename = 'competency_gaps_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $columns = [
            'ID', 'Employee', 'Competency', 'Rate', 'Required Level', 'Current Level', 'Gap'
        ];

        $callback = function() use ($gaps, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($gaps as $gap) {
                fputcsv($file, [
                    $gap->id,
                    $gap->employee ? ($gap->employee->first_name . ' ' . $gap->employee->last_name) : 'N/A',
                    $gap->competency ? $gap->competency->competency_name : 'N/A',
                    $gap->competency ? $gap->competency->rate : 'N/A',
                    $gap->required_level,
                    $gap->current_level,
                    $gap->gap,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function ajaxGapTable()
    {
        $customerServiceCompetencies = \App\Models\CompetencyLibrary::where('category', 'Customer Service & Sales')->pluck('id');
        $gaps = \App\Models\CompetencyGap::whereIn('competency_id', $customerServiceCompetencies)
            ->where('gap', '>', 0)
            ->with(['employee', 'competency'])
            ->get()
            ->map(function($gap) {
                $recommendedTraining = \App\Models\EmployeeTrainingDashboard::whereHas('course', function($q) use ($gap) {
                    $q->where('course_title', 'LIKE', '%' . $gap->competency->competency_name . '%');
                })->first();
                return (object) [
                    'employee' => $gap->employee,
                    'competency' => $gap->competency,
                    'required_level' => $gap->required_level,
                    'current_level' => $gap->current_level,
                    'gap' => $gap->gap,
                    'recommended_training' => $recommendedTraining ? $recommendedTraining->course : null,
                ];
            });
        $gapTableHtml = view('partials.gap_table', ['gaps' => $gaps])->render();
        return response($gapTableHtml);
    }

    /**
     * Approve and activate destination knowledge training from competency gap management
     */
    public function approveDestinationKnowledge(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|string|exists:employees,employee_id',
                'destination_name' => 'required|string'
            ]);

            // Find the destination knowledge training record
            $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $request->employee_id)
                ->where('destination_name', $request->destination_name)
                ->first();

            if (!$destinationTraining) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destination knowledge training record not found.'
                ], 404);
            }

            // Activate the destination knowledge training
            $destinationTraining->is_active = true;
            $destinationTraining->remarks = str_replace('Pending approval', 'Approved by competency gap management', $destinationTraining->remarks);
            $destinationTraining->save();

            // Log the approval activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'approve',
                'description' => "Approved destination knowledge training for {$request->destination_name} - Employee ID: {$request->employee_id}",
                'model_type' => \App\Models\DestinationKnowledgeTraining::class,
                'model_id' => $destinationTraining->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Destination knowledge training approved and activated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving destination knowledge training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign competency gap training to upcoming training with accurate data flow
     */
    public function assignToUpcomingTraining(Request $request)
    {
        try {
            $request->validate([
                'gap_id' => 'required|integer|exists:competency_gaps,id'
            ]);

            // Find the competency gap record
            $competencyGap = CompetencyGap::with(['employee', 'competency'])->findOrFail($request->gap_id);

            // Get current user for assigned_by field
            $assignedBy = 'Admin';
            try {
                if (Auth::check() && Auth::user()) {
                    $assignedBy = Auth::user()->name ?? Auth::user()->username ?? 'Admin';
                }
            } catch (\Exception $authException) {
                Log::warning('Auth issue in assignToUpcomingTraining: ' . $authException->getMessage());
                $assignedBy = 'Admin';
            }

            // Ensure upcoming_trainings table exists
            if (!Schema::hasTable('upcoming_trainings')) {
                Schema::create('upcoming_trainings', function (Blueprint $table) {
                    $table->id('upcoming_id');
                    $table->string('employee_id');
                    $table->string('training_title');
                    $table->date('start_date');
                    $table->date('end_date')->nullable();
                    $table->string('status')->default('Assigned');
                    $table->string('source')->nullable();
                    $table->string('assigned_by')->nullable();
                    $table->timestamp('assigned_date')->nullable();
                    $table->unsignedBigInteger('destination_training_id')->nullable();
                    $table->boolean('needs_response')->default(false);
                    $table->timestamps();
                    
                    $table->index('employee_id');
                    $table->index('destination_training_id');
                });
            }

            // Check if training already exists in upcoming trainings
            $existingUpcoming = \App\Models\UpcomingTraining::where('employee_id', $competencyGap->employee_id)
                ->where('training_title', $competencyGap->competency->competency_name)
                ->first();

            // Prepare accurate data from competency gap
            $upcomingData = [
                'employee_id' => $competencyGap->employee_id,
                'training_title' => $competencyGap->competency->competency_name,
                'start_date' => now()->format('Y-m-d'), // Start immediately when assigned
                'end_date' => $competencyGap->expired_date ? \Carbon\Carbon::parse($competencyGap->expired_date)->format('Y-m-d') : now()->addMonths(3)->format('Y-m-d'), // Use gap expiration or 3 months
                'status' => 'Assigned',
                'source' => 'competency_gap',
                'assigned_by' => $assignedBy, // Accurate assigned by from current user
                'assigned_date' => now(), // Accurate assignment timestamp
                'needs_response' => true
            ];

            if ($existingUpcoming) {
                // Update existing record with accurate data
                $existingUpcoming->update($upcomingData);
                $message = "Updated existing training assignment for {$competencyGap->competency->competency_name}";
            } else {
                // Create new record with accurate data
                \App\Models\UpcomingTraining::create($upcomingData);
                $message = "Successfully assigned {$competencyGap->competency->competency_name} to upcoming trainings";
            }

            // Log the activity with accurate information
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'assign_training',
                'description' => "Assigned {$competencyGap->competency->competency_name} training to {$competencyGap->employee->first_name} {$competencyGap->employee->last_name} from competency gap analysis. Start date: " . $upcomingData['start_date'] . ", Expiration: " . $upcomingData['end_date'] . ", Assigned by: " . $assignedBy,
                'model_type' => CompetencyGap::class,
                'model_id' => $competencyGap->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message . " with accurate start date, expiration, and assigned by information."
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning competency gap to upcoming training: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get competency profile data for an employee and competency
     */
    public function getCompetencyData(Request $request)
    {
        try {
            // Handle both GET and POST requests
            if ($request->isMethod('get')) {
                $request->validate([
                    'employee_id' => 'required|string|exists:employees,employee_id',
                    'competency_id' => 'required|integer|exists:competency_library,id'
                ]);
            } else {
                $request->validate([
                    'employee_id' => 'required|string|exists:employees,employee_id',
                    'competency_id' => 'required|integer|exists:competency_library,id'
                ]);
            }

            // Find existing competency profile
            $competencyProfile = EmployeeCompetencyProfile::where('employee_id', $request->employee_id)
                ->where('competency_id', $request->competency_id)
                ->first();

            // Debug logging
            Log::info('getCompetencyData debug', [
                'employee_id' => $request->employee_id,
                'competency_id' => $request->competency_id,
                'profile_found' => $competencyProfile ? true : false,
                'profile_level' => $competencyProfile ? $competencyProfile->proficiency_level : null
            ]);

            // Also check if we can calculate current level even without a profile
            $competency = CompetencyLibrary::find($request->competency_id);
            $competencyName = $competency->competency_name;

            $actualProgress = 0;
            $progressSource = 'none';
            $currentLevel = 0;

            // Check if this is a destination knowledge competency
            $isDestinationCompetency = stripos($competencyName, 'Destination Knowledge') !== false;

            if ($isDestinationCompetency) {
                // For destination competencies, check training data even without profile
                $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                $locationName = trim($locationName);

                if (!empty($locationName)) {
                    // Find matching destination knowledge training record
                    $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $request->employee_id)
                        ->where('destination_name', 'LIKE', '%' . $locationName . '%')
                        ->first();

                    if ($destinationRecord) {
                        // Use EXACT same progress calculation as destination knowledge training view
                        $destinationNameClean = str_replace([' Training', 'Training'], '', $destinationRecord->destination_name);

                        // Find matching course ID for this destination
                        $matchingCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $destinationNameClean . '%')->first();
                        $courseId = $matchingCourse ? $matchingCourse->course_id : null;

                        // Get exam progress
                        $combinedProgress = 0;
                        if ($courseId) {
                            $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($destinationRecord->employee_id, $courseId);
                        }

                        // Fall back to training dashboard progress if no exam data
                        if ($combinedProgress == 0) {
                            $trainingProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                                ->where('course_id', $courseId)
                                ->value('progress');
                            $combinedProgress = $trainingProgress ?? $destinationRecord->progress ?? 0;
                        }

                        $actualProgress = min(100, round($combinedProgress));
                        $progressSource = 'destination';
                    }
                }
            } else {
                // For non-destination competencies, check training dashboard
                $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $request->employee_id)->get();

                foreach ($trainingRecords as $record) {
                    $courseTitle = $record->training_title ?? '';

                    // General competency matching
                    $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                    $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                    if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                        // Get progress from this training record
                        $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($request->employee_id, $record->course_id);
                        $trainingProgress = $record->progress ?? 0;

                        // Priority: Exam progress > Training record progress
                        $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                        $progressSource = 'training';
                        break;
                    }
                }
            }

            // If we have training progress, use it regardless of profile existence
            if ($actualProgress > 0) {
                $displayProgress = $actualProgress;
            } else if ($competencyProfile) {
                // ALWAYS use stored proficiency if profile exists - don't check if manually set
                $storedProficiency = ($competencyProfile->proficiency_level / 5) * 100;
                $displayProgress = $storedProficiency;
                $progressSource = 'profile';
            } else {
                $displayProgress = 0;
                $progressSource = 'none';
            }

            // Convert percentage to level (1-5) - Fixed logic for proficiency levels
            if ($displayProgress >= 80) $currentLevel = 5;
            elseif ($displayProgress >= 60) $currentLevel = 4;
            elseif ($displayProgress >= 40) $currentLevel = 3;
            elseif ($displayProgress >= 20) $currentLevel = 2;
            elseif ($displayProgress > 0) $currentLevel = 1;
            else $currentLevel = 0;

            // For competency profiles, use direct proficiency level if available
            if ($competencyProfile && $progressSource === 'profile') {
                $currentLevel = $competencyProfile->proficiency_level;
            }

            if ($currentLevel > 0) {
                $sourceLabel = [
                    'destination' => 'destination training',
                    'training' => 'employee training',
                    'profile' => 'competency profile'
                ][$progressSource] ?? 'training data';

                return response()->json([
                    'success' => true,
                    'current_level' => $currentLevel,
                    'current_percentage' => round($displayProgress),
                    'progress_source' => $progressSource,
                    'has_profile' => $competencyProfile ? true : false,
                    'message' => "Auto-populated level {$currentLevel} from {$sourceLabel} ({$displayProgress}%)"
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'current_level' => 0,
                    'has_profile' => $competencyProfile ? true : false,
                    'message' => $competencyProfile ?
                        'Competency profile found but no training progress detected. Please enter current level manually.' :
                        'No existing competency profile found. Please enter current level manually.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching competency data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk sync all employees' competency profiles from training data
     */
    public function bulkSyncCompetencyProfiles()
    {
        try {
            $createdProfiles = 0;
            $updatedProfiles = 0;
            $errors = [];

            // Get all employees
            $employees = \App\Models\Employee::all();

            // Get all competencies
            $competencies = CompetencyLibrary::all();

            foreach ($employees as $employee) {
                foreach ($competencies as $competency) {
                    try {
                        $competencyName = $competency->competency_name;
                        $actualProgress = 0;
                        $progressSource = 'none';

                        // Check if this is a destination knowledge competency
                        $isDestinationCompetency = stripos($competencyName, 'Destination Knowledge') !== false;

                        if ($isDestinationCompetency) {
                            // For destination competencies
                            $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                            $locationName = trim($locationName);

                            if (!empty($locationName)) {
                                $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employee->employee_id)
                                    ->where('destination_name', 'LIKE', '%' . $locationName . '%')
                                    ->first();

                                if ($destinationRecord) {
                                    $destinationNameClean = str_replace([' Training', 'Training'], '', $destinationRecord->destination_name);
                                    $matchingCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $destinationNameClean . '%')->first();
                                    $courseId = $matchingCourse ? $matchingCourse->course_id : null;

                                    $combinedProgress = 0;
                                    if ($courseId) {
                                        $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($destinationRecord->employee_id, $courseId);
                                    }

                                    if ($combinedProgress == 0) {
                                        $trainingProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                                            ->where('course_id', $courseId)
                                            ->value('progress');
                                        $combinedProgress = $trainingProgress ?? $destinationRecord->progress ?? 0;
                                    }

                                    $actualProgress = min(100, round($combinedProgress));
                                    $progressSource = 'destination';
                                }
                            }
                        } else {
                            // For non-destination competencies
                            $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employee->employee_id)->get();

                            foreach ($trainingRecords as $record) {
                                $courseTitle = $record->training_title ?? '';
                                $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                                $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                                if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                                    $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employee->employee_id, $record->course_id);
                                    $trainingProgress = $record->progress ?? 0;
                                    $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                                    $progressSource = 'training';
                                    break;
                                }
                            }
                        }

                        // Only create/update if we have actual progress
                        if ($actualProgress > 0) {
                            // Convert percentage to level (1-5)
                            $currentLevel = 1;
                            if ($actualProgress >= 90) $currentLevel = 5;
                            elseif ($actualProgress >= 70) $currentLevel = 4;
                            elseif ($actualProgress >= 50) $currentLevel = 3;
                            elseif ($actualProgress >= 30) $currentLevel = 2;
                            elseif ($actualProgress > 0) $currentLevel = 1;

                            // Check if profile already exists
                            $existingProfile = EmployeeCompetencyProfile::where('employee_id', $employee->employee_id)
                                ->where('competency_id', $competency->id)
                                ->first();

                            if ($existingProfile) {
                                // Only update if the new level is higher or if it's from training data
                                if ($currentLevel > $existingProfile->proficiency_level || $progressSource !== 'none') {
                                    $existingProfile->update([
                                        'proficiency_level' => $currentLevel,
                                        'assessment_date' => now(),
                                        'notes' => "Auto-updated from {$progressSource} data ({$actualProgress}%)"
                                    ]);
                                    $updatedProfiles++;
                                }
                            } else {
                                // Create new profile
                                EmployeeCompetencyProfile::create([
                                    'employee_id' => $employee->employee_id,
                                    'competency_id' => $competency->id,
                                    'proficiency_level' => $currentLevel,
                                    'assessment_date' => now(),
                                    'notes' => "Auto-created from {$progressSource} data ({$actualProgress}%)"
                                ]);
                                $createdProfiles++;
                            }
                        }

                    } catch (\Exception $e) {
                        $errors[] = "Error processing {$employee->first_name} {$employee->last_name} - {$competency->competency_name}: " . $e->getMessage();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk sync completed successfully!",
                'created_profiles' => $createdProfiles,
                'updated_profiles' => $updatedProfiles,
                'total_processed' => $createdProfiles + $updatedProfiles,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during bulk sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix missing categories for existing competencies
     */
    private function fixMissingCategories()
    {
        // Only fix competencies that have NULL or empty categories
        $competencies = CompetencyLibrary::whereNull('category')
            ->orWhere('category', '')
            ->get();

        foreach ($competencies as $comp) {
            $name = strtolower($comp->competency_name);
            $description = strtolower($comp->description ?? '');
            $category = 'General'; // Default

            // Comprehensive destination knowledge detection
            $destinationKeywords = [
                'destination', 'location', 'place', 'city', 'terminal', 'station', 'route', 'area',
                'baguio', 'baesa', 'quezon', 'cubao', 'manila', 'cebu', 'davao', 'boracay',
                'pasay', 'makati', 'taguig', 'ortigas', 'alabang', 'fairview', 'novaliches',
                'geography', 'travel', 'transport', 'bus', 'terminal', 'depot'
            ];

            $isDestination = false;
            foreach ($destinationKeywords as $keyword) {
                if (strpos($name, $keyword) !== false || strpos($description, $keyword) !== false) {
                    $isDestination = true;
                    break;
                }
            }

            if ($isDestination) {
                $category = 'Destination Knowledge';
            } elseif (strpos($name, 'customer') !== false || strpos($name, 'service') !== false) {
                $category = 'Customer Service';
            } elseif (strpos($name, 'communication') !== false) {
                $category = 'Communication';
            } elseif (strpos($name, 'leadership') !== false || strpos($name, 'management') !== false) {
                $category = 'Leadership';
            } elseif (strpos($name, 'technical') !== false || strpos($name, 'system') !== false) {
                $category = 'Technical';
            }

            // Update the category for competencies that don't have one
            $comp->category = $category;
            $comp->save();
        }
    }

    /**
     * Extend expiration date for a competency gap assignment
     */
    public function extendExpiration(Request $request, $id)
    {
        try {
            $gap = CompetencyGap::findOrFail($id);

            $request->validate([
                'extension_days' => 'required|integer|min:1|max:30'
            ]);

            $extensionDays = $request->extension_days;
            $gap->expired_date = now()->addDays($extensionDays)->toDateTimeString();
            $gap->is_active = true; // Reactivate if it was expired
            $gap->save();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'extend',
                'description' => "Extended competency gap expiration by {$extensionDays} days for employee ID: {$gap->employee_id} (New expiry: " . $gap->expired_date->format('Y-m-d g:i A') . ")",
                'model_type' => CompetencyGap::class,
                'model_id' => $gap->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Expiration extended by {$extensionDays} days",
                'new_expiry' => $gap->expired_date->format('Y-m-d g:i A')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error extending expiration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if employee can access a specific competency gap
     */
    public function checkAccess(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|string|exists:employees,employee_id',
                'competency_id' => 'required|integer|exists:competency_library,id'
            ]);

            $gap = CompetencyGap::where('employee_id', $request->employee_id)
                ->where('competency_id', $request->competency_id)
                ->first();

            if (!$gap) {
                return response()->json([
                    'success' => false,
                    'message' => 'No competency gap assignment found.'
                ], 404);
            }

            $isAccessible = $gap->isAccessible();
            $isExpired = $gap->isExpired();

            return response()->json([
                'success' => true,
                'accessible' => $isAccessible,
                'expired' => $isExpired,
                'expiry_date' => $gap->expired_date ? $gap->expired_date->format('Y-m-d g:i A') : null,
                'message' => $isExpired ? 'This competency gap assignment has expired and is no longer accessible.' : 'Access granted.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking access: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix existing competency gap records that don't have expiration dates
     */
    public function fixExpiredDates()
    {
        try {
            // Find all competency gaps with NULL expired_date
            $gapsWithoutExpiredDate = CompetencyGap::whereNull('expired_date')->get();

            $updated = 0;

            foreach ($gapsWithoutExpiredDate as $gap) {
                // Set expiration date to 30 days from now for existing records
                $gap->expired_date = now()->addDays(30)->toDateTimeString();
                $gap->is_active = true; // Ensure they're active
                $gap->save();
                $updated++;
            }

            // Log the fix
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'bulk_fix',
                'module' => 'Competency Gap Management',
                'description' => "Fixed expiration dates for {$updated} competency gap records. All gaps now have proper expiration dates.",
                'model_type' => null,
                'model_id' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updated} competency gap records with expiration dates.",
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fixing expiration dates: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Sync expired dates between competency gap and training records
     */
    private function syncExpiredDateWithTraining($gap)
    {
        try {
            // Find matching training record
            $competency = $gap->competency;
            if (!$competency) return;

            $courseTitle = str_replace([' Training', ' Course', ' Program'], '', $competency->competency_name);

            $trainingRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $gap->employee_id)
                ->whereHas('course', function($q) use ($courseTitle, $competency) {
                    $q->where('course_title', 'LIKE', '%' . $courseTitle . '%')
                      ->orWhere('course_title', 'LIKE', '%' . $competency->competency_name . '%');
                })
                ->first();

            if ($trainingRecord) {
                // Sync expired dates - Competency Gap is the source of truth
                if ($gap->expired_date) {
                    // Always propagate gap's expired date to training record
                    $trainingRecord->expired_date = $gap->expired_date;
                    $trainingRecord->save();

                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'sync',
                        'module' => 'Expired Date Sync',
                        'description' => "Updated training record expired date from competency gap for {$gap->employee->first_name} {$gap->employee->last_name} - {$competency->competency_name}. Training now uses gap date: " . $gap->expired_date->format('Y-m-d g:i:s A'),
                    ]);
                } elseif (!$gap->expired_date && $trainingRecord->expired_date) {
                    // Only if gap has no date, use training date for gap (fallback)
                    $gap->expired_date = $trainingRecord->expired_date;
                    $gap->save();

                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'sync',
                        'module' => 'Expired Date Sync',
                        'description' => "Set competency gap expired date from training record for {$gap->employee->first_name} {$gap->employee->last_name} - {$competency->competency_name}. Gap now uses: " . $gap->expired_date->format('Y-m-d g:i:s A'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error syncing expired dates: ' . $e->getMessage());
        }
    }

    /**
     * Ensure the competency_gaps table exists with proper structure
     */
    private function ensureTableExists()
    {
        try {
            if (!Schema::hasTable('competency_gaps')) {
                Log::info('Creating competency_gaps table...');

                Schema::create('competency_gaps', function (Blueprint $table) {
                    $table->id();
                    $table->string('employee_id', 20);
                    $table->unsignedBigInteger('competency_id');
                    $table->integer('required_level');
                    $table->integer('current_level');
                    $table->integer('gap');
                    $table->text('gap_description')->nullable();
                    $table->timestamp('expired_date')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();

                    // Add foreign key constraints with error handling
                    try {
                        $table->foreign('employee_id')
                            ->references('employee_id')
                            ->on('employees')
                            ->onDelete('cascade');
                    } catch (\Exception $e) {
                        Log::warning('Could not create employee_id foreign key: ' . $e->getMessage());
                    }

                    try {
                        $table->foreign('competency_id')
                            ->references('id')
                            ->on('competency_library')
                            ->onDelete('cascade');
                    } catch (\Exception $e) {
                        Log::warning('Could not create competency_id foreign key: ' . $e->getMessage());
                    }

                    // Add indexes for better performance
                    $table->index(['employee_id', 'competency_id']);
                    $table->index('is_active');
                    $table->index('expired_date');
                });

                Log::info('competency_gaps table created successfully');
            } else {
                // Check if required columns exist and add them if missing
                if (!Schema::hasColumn('competency_gaps', 'expired_date')) {
                    Schema::table('competency_gaps', function (Blueprint $table) {
                        $table->timestamp('expired_date')->nullable();
                    });
                    Log::info('Added expired_date column to competency_gaps table');
                }

                if (!Schema::hasColumn('competency_gaps', 'is_active')) {
                    Schema::table('competency_gaps', function (Blueprint $table) {
                        $table->boolean('is_active')->default(true);
                    });
                    Log::info('Added is_active column to competency_gaps table');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error ensuring competency_gaps table exists: ' . $e->getMessage());
            // Continue execution - table might already exist
        }
    }

    /**
     * Assign competency gap to employee's upcoming training
     */
    public function assignToTraining(Request $request)
    {
        try {
            $request->validate([
                'gap_id' => 'required|integer|exists:competency_gaps,id',
                'employee_id' => 'required|string|exists:employees,employee_id',
                'competency' => 'required|string',
                'expired_date' => 'nullable|string'
            ]);

            // Find the competency gap record
            $competencyGap = CompetencyGap::with(['employee', 'competency'])->findOrFail($request->gap_id);

            // Get current user for assigned_by field
            $assignedBy = 'Admin';
            try {
                if (Auth::check() && Auth::user()) {
                    $assignedBy = Auth::user()->name ?? Auth::user()->username ?? 'Admin';
                }
            } catch (\Exception $authException) {
                Log::warning('Auth issue in assignToTraining: ' . $authException->getMessage());
                $assignedBy = 'Admin';
            }

            // Ensure upcoming_trainings table exists
            if (!Schema::hasTable('upcoming_trainings')) {
                Schema::create('upcoming_trainings', function (Blueprint $table) {
                    $table->id('upcoming_id');
                    $table->string('employee_id', 20);
                    $table->string('training_title');
                    $table->timestamp('start_date')->nullable();
                    $table->timestamp('end_date')->nullable();
                    $table->string('status')->default('Assigned');
                    $table->string('source')->nullable();
                    $table->string('assigned_by')->nullable();
                    $table->timestamp('assigned_date')->nullable();
                    $table->unsignedBigInteger('destination_training_id')->nullable();
                    $table->boolean('needs_response')->default(true);
                    $table->timestamps();
                    
                    $table->index('employee_id');
                    $table->foreign('employee_id')->references('employee_id')->on('employees');
                });
            }

            // Check if training already exists in upcoming trainings
            $existingUpcoming = \App\Models\UpcomingTraining::where('employee_id', $request->employee_id)
                ->where('training_title', $request->competency)
                ->first();

            // Prepare training data
            $trainingData = [
                'employee_id' => $request->employee_id,
                'training_title' => $request->competency,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => $request->expired_date ? \Carbon\Carbon::parse($request->expired_date)->format('Y-m-d') : now()->addMonths(3)->format('Y-m-d'),
                'status' => 'Assigned',
                'source' => 'competency_gap',
                'assigned_by' => $assignedBy,
                'assigned_date' => now(),
                'needs_response' => true
            ];

            if ($existingUpcoming) {
                // Update existing record
                $existingUpcoming->update($trainingData);
                $message = "Updated existing training assignment for {$request->competency}";
            } else {
                // Create new record
                \App\Models\UpcomingTraining::create($trainingData);
                $message = "Successfully assigned {$request->competency} to upcoming trainings";
            }

            // Log the activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'assign_training',
                'description' => "Assigned '{$request->competency}' training to {$competencyGap->employee->first_name} {$competencyGap->employee->last_name} from competency gap analysis. Assigned by: {$assignedBy}",
                'model_type' => CompetencyGap::class,
                'model_id' => $competencyGap->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning competency gap to training: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning training: ' . $e->getMessage()
            ], 500);
        }
    }

}
