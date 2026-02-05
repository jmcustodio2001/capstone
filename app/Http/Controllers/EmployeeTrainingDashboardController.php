<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\EmployeeTrainingDashboard;
use App\Models\Employee;
use App\Models\CourseManagement;
use App\Models\CompetencyLibrary;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyGap;
use App\Models\DestinationKnowledgeTraining;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CertificateGenerationController;

class EmployeeTrainingDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Fetch local and API employees and merge them
        $localEmployees = \App\Models\Employee::all();
        $apiEmployees = $this->getEmployeesFromAPI();
        
        $employees = $localEmployees->concat($apiEmployees)->unique('employee_id');
        $employeeMap = $employees->keyBy('employee_id');

        // Filter out destination training courses from the dropdown
        $courses = \App\Models\CourseManagement::where(function($query) {
            $query->where('course_title', 'NOT LIKE', '%ITALY%')
                  ->where('course_title', 'NOT LIKE', '%BESTLINK%')
                  ->where('course_title', 'NOT LIKE', '%BORACAY%')
                  ->where('course_title', 'NOT LIKE', '%destination%')
                  ->where('course_title', 'NOT LIKE', '%Destination%')
                  ->where('course_title', 'NOT LIKE', '%DESTINATION%')
                  ->where('description', 'NOT LIKE', '%destination knowledge%')
                  ->where('description', 'NOT LIKE', '%Destination Knowledge%')
                  ->where('description', 'NOT LIKE', '%DESTINATION KNOWLEDGE%');
        })->get();

        // SIMPLIFIED APPROACH: Use a single deduplication map to prevent duplicates
        $uniqueRecords = collect();
        $seenCombinations = collect();

        // Helper function to create multiple unique keys for employee-course combination
        $createUniqueKeys = function($employeeId, $courseId, $trainingTitle = null, $courseTitle = null) {
            $keys = [];

            // Primary key: employee + course_id (if exists and numeric)
            if (!empty($courseId) && is_numeric($courseId)) {
                $keys[] = $employeeId . '_course_' . $courseId;
            }

            // Secondary key: employee + normalized training_title
            if (!empty($trainingTitle)) {
                $normalized = strtolower(trim($trainingTitle));
                $normalized = preg_replace('/\b(training|course|program|skills|knowledge|development|workshop|seminar)\b/i', '', $normalized);
                $normalized = preg_replace('/\s+/', ' ', trim($normalized));
                if (!empty($normalized)) {
                    $keys[] = $employeeId . '_title_' . $normalized;
                }
            }

            // Tertiary key: employee + normalized course_title (from relationship)
            if (!empty($courseTitle) && $courseTitle !== $trainingTitle) {
                $normalized = strtolower(trim($courseTitle));
                $normalized = preg_replace('/\b(training|course|program|skills|knowledge|development|workshop|seminar)\b/i', '', $normalized);
                $normalized = preg_replace('/\s+/', ' ', trim($normalized));
                if (!empty($normalized)) {
                    $keys[] = $employeeId . '_coursetitle_' . $normalized;
                }
            }

            return array_unique($keys);
        };

        // 1. PRIORITY 1: Get existing dashboard records (highest priority) - with deduplication at database level
        // Filter out destination training records
        $dashboardRecords = \App\Models\EmployeeTrainingDashboard::with(['employee', 'course'])
            ->leftJoin('users', 'employee_training_dashboards.assigned_by', '=', 'users.id')
            ->select('employee_training_dashboards.*', 'users.name as assigned_by_name')
            ->where(function($query) {
                // Filter records with course relationship
                $query->whereHas('course', function($courseQuery) {
                    $courseQuery->where('course_title', 'NOT LIKE', '%ITALY%')
                              ->where('course_title', 'NOT LIKE', '%BESTLINK%')
                              ->where('course_title', 'NOT LIKE', '%BORACAY%')
                              ->where('course_title', 'NOT LIKE', '%destination%')
                              ->where('course_title', 'NOT LIKE', '%Destination%')
                              ->where('course_title', 'NOT LIKE', '%DESTINATION%')
                              ->where('description', 'NOT LIKE', '%destination knowledge%')
                              ->where('description', 'NOT LIKE', '%Destination Knowledge%')
                              ->where('description', 'NOT LIKE', '%DESTINATION KNOWLEDGE%');
                })
                // OR filter records without course relationship by training_title
                ->orWhere(function($titleQuery) {
                    $titleQuery->whereNull('course_id')
                              ->where('training_title', 'NOT LIKE', '%ITALY%')
                              ->where('training_title', 'NOT LIKE', '%BESTLINK%')
                              ->where('training_title', 'NOT LIKE', '%BORACAY%')
                              ->where('training_title', 'NOT LIKE', '%destination%')
                              ->where('training_title', 'NOT LIKE', '%Destination%')
                              ->where('training_title', 'NOT LIKE', '%DESTINATION%');
                });
            })
            ->get()
            ->unique(function ($record) {
                // Remove duplicates at the database level first
                return $record->employee_id . '_' . $record->course_id . '_' . ($record->training_title ?? '');
            });

        foreach ($dashboardRecords as $record) {
            // Fix missing employee relationship for external employees
            if (!$record->employee && $record->employee_id) {
                $foundEmployee = $employeeMap->get($record->employee_id);
                if ($foundEmployee) {
                    $record->setRelation('employee', $foundEmployee);
                    // Also manually set the attribute just in case
                    $record->employee = $foundEmployee;
                }
            }

            // Skip records without valid employee
            if (!$record->employee_id || !$record->employee) {
                continue;
            }

            // Create unique keys for this record (multiple keys for better deduplication)
            $courseTitle = $record->course->course_title ?? null;
            $uniqueKeys = $createUniqueKeys($record->employee_id, $record->course_id, $record->training_title, $courseTitle);

            // Check if any of the keys already exist
            $isDuplicate = false;
            $matchingKey = null;
            foreach ($uniqueKeys as $key) {
                if ($seenCombinations->has($key)) {
                    $isDuplicate = true;
                    $matchingKey = $key;
                    break;
                }
            }

            if ($isDuplicate) {
                Log::info('Skipped duplicate dashboard record', [
                    'employee_id' => $record->employee_id,
                    'course_id' => $record->course_id,
                    'training_title' => $record->training_title,
                    'course_title' => $courseTitle,
                    'matching_key' => $matchingKey,
                    'all_keys' => $uniqueKeys,
                    'record_id' => $record->id,
                    'existing_source' => $seenCombinations->get($matchingKey)
                ]);
                continue; // Skip if already seen
            }

            // Sync with latest exam progress for accurate display
            $realProgress = $this->calculateRealProgress($record->employee_id, $record->course_id, $record->training_title, $record->progress);
            if ($realProgress != $record->progress) {
                // Update the dashboard record with accurate progress
                $record->progress = $realProgress;
                $record->status = $realProgress >= 80 ? 'Completed' : 'In Progress';
                $record->save();

                // Also sync with competency systems
                $this->syncWithCompetencyProfile($record);
                $this->syncWithCompetencyGap($record);
            }

            // ENHANCED: Force load course relationship if missing
            if (!$record->course && $record->course_id) {
                $record->load('course');
                // If still no course, try direct query
                if (!$record->course) {
                    $record->course = \App\Models\CourseManagement::find($record->course_id);
                }
            }

            // ENHANCED: Fix missing training_title from course relationship
            if ($record->course && !$record->training_title) {
                $record->update(['training_title' => $record->course->course_title]);
            }

            // Mark source as dashboard and add to unique records
            $record->source = 'employee_training_dashboard';
            $uniqueRecords->push($record);

            // Track all unique keys for this record to prevent future duplicates
            foreach ($uniqueKeys as $key) {
                $seenCombinations->put($key, 'dashboard');
            }

            Log::info('Added dashboard record', [
                'employee_id' => $record->employee_id,
                'course_id' => $record->course_id,
                'training_title' => $record->training_title,
                'course_title' => $courseTitle,
                'unique_keys' => $uniqueKeys,
                'record_id' => $record->id
            ]);
        }

        // PRIORITY 1.5: Get employee competency profiles (if EmployeeTrainingDashboard is empty)
        // This bridges the gap when data is stored in EmployeeCompetencyProfile instead
        if ($dashboardRecords->isEmpty()) {
            $competencyProfiles = \App\Models\EmployeeCompetencyProfile::with(['employee', 'competency'])->get();

            foreach ($competencyProfiles as $profile) {
                // Fix missing employee relationship
                if (!$profile->employee && $profile->employee_id) {
                    $foundEmployee = $employeeMap->get($profile->employee_id);
                    if ($foundEmployee) {
                        $profile->setRelation('employee', $foundEmployee);
                    }
                }

                if (!$profile->employee || !$profile->competency) {
                    continue;
                }

                // Find matching course by competency name
                $course = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $profile->competency->competency_name . '%')
                    ->where('course_title', 'NOT LIKE', '%ITALY%')
                    ->where('course_title', 'NOT LIKE', '%BESTLINK%')
                    ->where('course_title', 'NOT LIKE', '%BORACAY%')
                    ->where('course_title', 'NOT LIKE', '%destination%')
                    ->where('course_title', 'NOT LIKE', '%Destination%')
                    ->where('course_title', 'NOT LIKE', '%DESTINATION%')
                    ->first();

                // Create pseudo-record from competency profile
                $pseudoRecord = new \stdClass();
                $pseudoRecord->id = 'competency_profile_' . $profile->id;
                $pseudoRecord->employee_id = $profile->employee->employee_id;
                $pseudoRecord->course_id = $course ? $course->course_id : null;
                $pseudoRecord->training_title = $profile->competency->competency_name;
                $pseudoRecord->progress = round(($profile->proficiency_level / 5) * 100);
                $pseudoRecord->status = $pseudoRecord->progress >= 100 ? 'Completed' : ($pseudoRecord->progress >= 50 ? 'In Progress' : 'Not Started');
                $pseudoRecord->created_at = $profile->updated_at ?? now();
                $pseudoRecord->updated_at = $profile->updated_at ?? now();
                $pseudoRecord->last_accessed = $profile->updated_at ?? now();
                $pseudoRecord->expired_date = null;
                $pseudoRecord->assigned_by_name = 'Competency Profile';
                $pseudoRecord->source = 'employee_competency_profile';
                $pseudoRecord->employee = $profile->employee;
                $pseudoRecord->course = $course;

                // Create unique keys for this competency profile
                $courseTitle = $course ? $course->course_title : null;
                $uniqueKeys = $createUniqueKeys($pseudoRecord->employee_id, $pseudoRecord->course_id, $pseudoRecord->training_title, $courseTitle);

                // Check if any keys already exist
                $isDuplicate = false;
                foreach ($uniqueKeys as $key) {
                    if ($seenCombinations->has($key)) {
                        $isDuplicate = true;
                        break;
                    }
                }

                if (!$isDuplicate) {
                    $uniqueRecords->push($pseudoRecord);

                    // Track all unique keys
                    foreach ($uniqueKeys as $key) {
                        $seenCombinations->put($key, 'employee_competency_profile');
                    }

                    Log::info('Added competency profile record', [
                        'employee_id' => $pseudoRecord->employee_id,
                        'competency' => $pseudoRecord->training_title,
                        'progress' => $pseudoRecord->progress,
                        'unique_keys' => $uniqueKeys
                    ]);
                }
            }
        }

        // 2. PRIORITY 2: Get approved training requests (only if not already in dashboard)
        // Filter out destination training requests
        $approvedRequests = \App\Models\TrainingRequest::with(['employee', 'course'])
            ->where('status', 'Approved')
            ->where(function($query) {
                // Filter records with course relationship
                $query->whereHas('course', function($courseQuery) {
                    $courseQuery->where('course_title', 'NOT LIKE', '%ITALY%')
                              ->where('course_title', 'NOT LIKE', '%BESTLINK%')
                              ->where('course_title', 'NOT LIKE', '%BORACAY%')
                              ->where('course_title', 'NOT LIKE', '%destination%')
                              ->where('course_title', 'NOT LIKE', '%Destination%')
                              ->where('course_title', 'NOT LIKE', '%DESTINATION%');
                })
                // OR filter records without course relationship by training_title
                ->orWhere(function($titleQuery) {
                    $titleQuery->whereNull('course_id')
                              ->where('training_title', 'NOT LIKE', '%ITALY%')
                              ->where('training_title', 'NOT LIKE', '%BESTLINK%')
                              ->where('training_title', 'NOT LIKE', '%BORACAY%')
                              ->where('training_title', 'NOT LIKE', '%destination%')
                              ->where('training_title', 'NOT LIKE', '%Destination%')
                              ->where('training_title', 'NOT LIKE', '%DESTINATION%');
                });
            })
            ->get();

        foreach ($approvedRequests as $request) {
            // Fix missing employee relationship for external employees
            if (!$request->employee && $request->employee_id) {
                $foundEmployee = $employeeMap->get($request->employee_id);
                if ($foundEmployee) {
                    $request->setRelation('employee', $foundEmployee);
                    // Also manually set the attribute just in case
                    $request->employee = $foundEmployee;
                }
            }

            // Skip if no valid employee
            if (!$request->employee_id || !$request->employee) {
                continue;
            }

            // Create unique keys for this request
            $courseTitle = $request->course->course_title ?? null;
            $uniqueKeys = $createUniqueKeys($request->employee_id, $request->course_id, $request->training_title, $courseTitle);

            // Check if any of the keys already exist
            $isDuplicate = false;
            $matchingKey = null;
            foreach ($uniqueKeys as $key) {
                if ($seenCombinations->has($key)) {
                    $isDuplicate = true;
                    $matchingKey = $key;
                    break;
                }
            }

            if ($isDuplicate) {
                Log::info('Skipped duplicate training request', [
                    'employee_id' => $request->employee_id,
                    'course_id' => $request->course_id,
                    'training_title' => $request->training_title,
                    'course_title' => $courseTitle,
                    'matching_key' => $matchingKey,
                    'all_keys' => $uniqueKeys,
                    'existing_source' => $seenCombinations->get($matchingKey)
                ]);
                continue; // Skip if already exists
            }

            // Get accurate progress from all sources
            $realProgress = $this->calculateRealProgress($request->employee_id, $request->course_id, $request->training_title, 0);

            // Create pseudo record from training request
            $pseudoRecord = new \stdClass();
            $pseudoRecord->id = 'request_' . $request->request_id;
            $pseudoRecord->employee_id = $request->employee_id;
            $pseudoRecord->course_id = $request->course_id;
            $pseudoRecord->training_title = $request->training_title;
            $pseudoRecord->progress = $realProgress;
            $pseudoRecord->status = $realProgress >= 80 ? 'Completed' : 'In Progress';
            $pseudoRecord->created_at = $request->created_at;
            $pseudoRecord->updated_at = $request->updated_at;
            $pseudoRecord->last_accessed = $request->updated_at ?? null;
            $pseudoRecord->expired_date = null;
            $pseudoRecord->assigned_by_name = 'Employee Request';
            $pseudoRecord->source = 'Training Request (Approved)';

            // Load relationships
            $pseudoRecord->employee = $request->employee;
            $pseudoRecord->course = $request->course;

            $uniqueRecords->push($pseudoRecord);

            // Track all unique keys for this request to prevent future duplicates
            foreach ($uniqueKeys as $key) {
                $seenCombinations->put($key, 'approved_request');
            }

            Log::info('Added approved request record', [
                'employee_id' => $request->employee_id,
                'course_id' => $request->course_id,
                'training_title' => $request->training_title,
                'course_title' => $courseTitle,
                'unique_keys' => $uniqueKeys
            ]);
        }

        // 3. PRIORITY 3: Get competency-based training assignments (only if not already in dashboard or requests)
        // Filter out destination training competency assignments
        $competencyAssignments = collect();
        try {
            if (class_exists('\App\Models\CompetencyCourseAssignment')) {
                $competencyAssignments = \App\Models\CompetencyCourseAssignment::with(['employee', 'course'])
                    ->whereHas('course', function($courseQuery) {
                        $courseQuery->where('course_title', 'NOT LIKE', '%ITALY%')
                                  ->where('course_title', 'NOT LIKE', '%BESTLINK%')
                                  ->where('course_title', 'NOT LIKE', '%BORACAY%')
                                  ->where('course_title', 'NOT LIKE', '%destination%')
                                  ->where('course_title', 'NOT LIKE', '%Destination%')
                                  ->where('course_title', 'NOT LIKE', '%DESTINATION%');
                    })
                    ->get();
            }
        } catch (\Exception $e) {
            // Model doesn't exist, skip
        }

        foreach ($competencyAssignments as $assignment) {
            // Skip if no valid employee
            if (!$assignment->employee_id || !$assignment->employee) {
                continue;
            }

            // Create unique keys for this assignment
            $courseTitle = $assignment->course->course_title ?? 'Competency Training';
            $uniqueKeys = $createUniqueKeys($assignment->employee_id, $assignment->course_id, $courseTitle, $courseTitle);

            // Check if any of the keys already exist
            $isDuplicate = false;
            $matchingKey = null;
            foreach ($uniqueKeys as $key) {
                if ($seenCombinations->has($key)) {
                    $isDuplicate = true;
                    $matchingKey = $key;
                    break;
                }
            }

            if ($isDuplicate) {
                Log::info('Skipped duplicate competency assignment', [
                    'employee_id' => $assignment->employee_id,
                    'course_id' => $assignment->course_id,
                    'course_title' => $courseTitle,
                    'matching_key' => $matchingKey,
                    'all_keys' => $uniqueKeys,
                    'existing_source' => $seenCombinations->get($matchingKey)
                ]);
                continue; // Skip if already exists
            }

            // Get accurate progress
            $realProgress = $this->calculateRealProgress($assignment->employee_id, $assignment->course_id, $courseTitle, $assignment->progress ?? 0);

            // Create pseudo record from competency assignment
            $pseudoRecord = new \stdClass();
            $pseudoRecord->id = 'competency_' . $assignment->id;
            $pseudoRecord->employee_id = $assignment->employee_id;
            $pseudoRecord->course_id = $assignment->course_id;
            $pseudoRecord->training_title = $assignment->course->course_title ?? 'Competency Training';
            $pseudoRecord->progress = $realProgress;
            $pseudoRecord->status = $realProgress >= 80 ? 'Completed' : ($assignment->status ?? 'In Progress');
            $pseudoRecord->created_at = $assignment->created_at;
            $pseudoRecord->updated_at = $assignment->updated_at;
            $pseudoRecord->last_accessed = $assignment->updated_at ?? null;
            $pseudoRecord->expired_date = null;
            $pseudoRecord->assigned_by_name = 'Competency System';
            $pseudoRecord->source = 'competency_assigned';

            // Load relationships
            $pseudoRecord->employee = $assignment->employee;
            $pseudoRecord->course = $assignment->course;

            $uniqueRecords->push($pseudoRecord);

            // Track all unique keys for this assignment to prevent future duplicates
            foreach ($uniqueKeys as $key) {
                $seenCombinations->put($key, 'competency_assigned');
            }

            Log::info('Added competency assignment record', [
                'employee_id' => $assignment->employee_id,
                'course_id' => $assignment->course_id,
                'course_title' => $courseTitle,
                'unique_keys' => $uniqueKeys
            ]);
        }

        // 4. PRIORITY 4: Get competency gap training assignments from upcoming_trainings
        $competencyGapTrainings = \App\Models\UpcomingTraining::where('source', 'competency_gap')
            ->get();

        foreach ($competencyGapTrainings as $gapTraining) {
            // Skip if no valid employee
            if (!$gapTraining->employee_id) {
                continue;
            }

            // Get or create employee object
            $employee = \App\Models\Employee::where('employee_id', $gapTraining->employee_id)->first();
            if (!$employee) {
                continue;
            }

            // Create unique keys for this gap training
            $courseTitle = $gapTraining->training_title;
            $uniqueKeys = $createUniqueKeys($gapTraining->employee_id, null, $gapTraining->training_title, $courseTitle);

            // Check if any of the keys already exist
            $isDuplicate = false;
            $matchingKey = null;
            foreach ($uniqueKeys as $key) {
                if ($seenCombinations->has($key)) {
                    $isDuplicate = true;
                    $matchingKey = $key;
                    break;
                }
            }

            if ($isDuplicate) {
                Log::info('Skipped duplicate competency gap training', [
                    'employee_id' => $gapTraining->employee_id,
                    'training_title' => $gapTraining->training_title,
                    'matching_key' => $matchingKey,
                    'all_keys' => $uniqueKeys,
                    'existing_source' => $seenCombinations->get($matchingKey)
                ]);
                continue; // Skip if already exists
            }

            // Check for progress in employee_training_dashboard
            $dashboardProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $gapTraining->employee_id)
                ->where('training_title', $gapTraining->training_title)
                ->first();

            $progress = 0;
            if ($dashboardProgress) {
                $progress = $dashboardProgress->progress ?? 0;
            }

            // Get accurate progress
            $realProgress = $this->calculateRealProgress($gapTraining->employee_id, $gapTraining->destination_training_id, $gapTraining->training_title, $progress);

            // Create pseudo record from competency gap training
            $pseudoRecord = new \stdClass();
            $pseudoRecord->id = 'gap_' . $gapTraining->upcoming_id;
            $pseudoRecord->employee_id = $gapTraining->employee_id;
            $pseudoRecord->course_id = $gapTraining->destination_training_id;
            $pseudoRecord->training_title = $gapTraining->training_title;
            $pseudoRecord->progress = $realProgress;
            $pseudoRecord->status = $realProgress >= 80 ? 'Completed' : ($realProgress > 0 ? 'In Progress' : 'Not Started');
            $pseudoRecord->created_at = $gapTraining->assigned_date ?? now();
            $pseudoRecord->updated_at = $gapTraining->updated_at ?? now();
            $pseudoRecord->last_accessed = $gapTraining->updated_at ?? null;
            $pseudoRecord->expired_date = $gapTraining->end_date;
            $pseudoRecord->assigned_by_name = 'Competency Gap System';
            $pseudoRecord->source = 'competency_gap';

            // Load relationships
            $pseudoRecord->employee = $employee;
            $pseudoRecord->course = null; // No course relationship for competency gaps

            $uniqueRecords->push($pseudoRecord);

            // Track all unique keys for this gap training to prevent future duplicates
            foreach ($uniqueKeys as $key) {
                $seenCombinations->put($key, 'competency_gap');
            }

            Log::info('Added competency gap training record', [
                'employee_id' => $gapTraining->employee_id,
                'training_title' => $gapTraining->training_title,
                'progress' => $progress,
                'unique_keys' => $uniqueKeys
            ]);
        }

        // 5. PRIORITY 5: DESTINATION TRAINING ASSIGNMENTS EXCLUDED
        // Destination training assignments are now excluded from the Employee Training Dashboard
        // to focus only on customer service & sales skills training records
        // These records are managed separately in the Destination Knowledge Training system

        // FINAL DEDUPLICATION PASS: Ensure absolutely no duplicates remain for ANY employee
        $finalUniqueRecords = collect();
        $finalSeenKeys = collect();

        foreach ($uniqueRecords as $record) {
            // Create comprehensive final unique key
            $employeeId = $record->employee_id;
            $courseId = $record->course_id ?? 'null';
            $trainingTitle = $record->training_title ?? 'unknown';

            // Generate multiple final keys to catch any remaining duplicates
            $finalKeys = [
                $employeeId . '_course_' . $courseId,
                $employeeId . '_title_' . strtolower(trim($trainingTitle)),
                $employeeId . '_normalized_' . strtolower(preg_replace('/\b(training|course|program|skills|knowledge|development|workshop|seminar)\b/i', '', $trainingTitle))
            ];

            $isDuplicateInFinal = false;
            $matchingFinalKey = null;

            foreach ($finalKeys as $key) {
                if ($finalSeenKeys->has($key)) {
                    $isDuplicateInFinal = true;
                    $matchingFinalKey = $key;
                    break;
                }
            }

            if ($isDuplicateInFinal) {
                Log::warning('FINAL PASS: Removed duplicate record for ALL employees check', [
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'training_title' => $trainingTitle,
                    'matching_key' => $matchingFinalKey,
                    'record_source' => $record->source ?? 'unknown',
                    'record_id' => $record->id ?? 'unknown'
                ]);
                continue; // Skip this duplicate
            }

            // Add to final unique records
            $finalUniqueRecords->push($record);

            // Track all final keys
            foreach ($finalKeys as $key) {
                $finalSeenKeys->put($key, $record->source ?? 'unknown');
            }
        }

        // Sort all unique records by creation date (newest first)
        $trainingRecords = $finalUniqueRecords->sortByDesc(function($record) {
            return $record->created_at;
        });

        // Apply Filters
        if ($request->filled('employee_id')) {
            $trainingRecords = $trainingRecords->where('employee_id', $request->employee_id);
        }

        if ($request->filled('course_id')) {
            $trainingRecords = $trainingRecords->filter(function ($record) use ($request) {
                return $record->course_id == $request->course_id;
            });
        }

        if ($request->filled('status')) {
            $statusFilter = strtolower($request->status);
            $trainingRecords = $trainingRecords->filter(function ($record) use ($statusFilter) {
                $status = strtolower($record->status ?? '');
                $progress = $record->progress ?? 0;
                
                if ($statusFilter === 'completed') {
                    return $status === 'completed' || $progress >= 100;
                } elseif ($statusFilter === 'in-progress' || $statusFilter === 'in_progress') {
                    return ($status === 'in progress' || $status === 'in_progress') || ($progress > 0 && $progress < 100);
                } elseif ($statusFilter === 'not-started' || $statusFilter === 'not_started') {
                    return ($status === 'not started' || $status === 'not_started') || $progress == 0;
                }
                return true;
            });
        }

        // Enhanced Debug: Log comprehensive statistics for ALL employees
        $employeeStats = [];
        foreach ($trainingRecords->groupBy('employee_id') as $empId => $empRecords) {
            $employee = $empRecords->first()->employee ?? null;
            $employeeName = $employee ? ($employee->first_name . ' ' . $employee->last_name) : 'Unknown';
            $employeeStats[$empId] = [
                'name' => $employeeName,
                'record_count' => $empRecords->count(),
                'courses' => $empRecords->pluck('training_title')->unique()->values()->toArray()
            ];
        }

        Log::info('Employee Training Dashboard - COMPREHENSIVE FINAL STATS FOR ALL EMPLOYEES', [
            'dashboard_records_fetched' => $dashboardRecords->count(),
            'approved_requests_fetched' => $approvedRequests->count(),
            'competency_assignments_fetched' => $competencyAssignments->count(),
            'competency_gap_trainings_fetched' => $competencyGapTrainings->count(),
            'destination_trainings_excluded' => 'Destination trainings are now excluded from this dashboard',
            'initial_unique_records' => $uniqueRecords->count(),
            'final_unique_records_after_deduplication' => $trainingRecords->count(),
            'unique_employees_with_training' => $trainingRecords->pluck('employee_id')->unique()->count(),
            'total_employees_in_system' => $employees->count(),
            'deduplication_keys_used' => $seenCombinations->count(),
            'final_deduplication_keys' => $finalSeenKeys->count(),
            'duplicates_removed_in_final_pass' => $uniqueRecords->count() - $trainingRecords->count(),
            'employee_breakdown' => $employeeStats
        ]);

        // Group records by employee for pagination
        $groupedCollection = $trainingRecords->groupBy('employee_id');
        
        // Pagination logic
        $page = request()->get('page', 1);
        $perPage = 10; // Show 10 employees per page
        
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedCollection->forPage($page, $perPage),
            $groupedCollection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('learning_management.employee_training_dashboard', [
            'employees' => $employees, 
            'courses' => $courses, 
            'trainingRecords' => $trainingRecords,
            'groupedRecords' => $paginatedItems
        ]);
    }

    /**
     * Calculate real progress for a training record by checking multiple sources
     * Consistent with _progress.blade.php logic
     */
    private function calculateRealProgress($employeeId, $courseId, $trainingTitle, $currentProgress = 0)
    {
        $progressValue = (float)$currentProgress;
        $effectiveCourseId = $courseId;

        // 1. Try to find course_id by title if missing
        if (!$effectiveCourseId && $trainingTitle) {
            $foundCourse = \App\Models\CourseManagement::where('course_title', $trainingTitle)
                ->orWhere('course_title', 'LIKE', '%' . $trainingTitle . '%')
                ->first();
            if ($foundCourse) {
                $effectiveCourseId = $foundCourse->course_id;
            }
        }

        // 2. Check for exam progress (Priority 1)
        if ($effectiveCourseId) {
            $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employeeId, $effectiveCourseId);
            if ($examProgress > 0) {
                $progressValue = max($progressValue, $examProgress);
            }
        }

        // 3. Check for competency-based progress (Priority 2)
        if ($progressValue < 100 && $trainingTitle) {
            $competencyName = str_replace([' Training', ' Course', ' Program'], '', $trainingTitle);
            $competencyProfile = \App\Models\EmployeeCompetencyProfile::where('employee_id', $employeeId)
                ->whereHas('competency', function($query) use ($competencyName) {
                    $query->where('competency_name', 'LIKE', '%' . $competencyName . '%');
                })->first();

            if ($competencyProfile && $competencyProfile->proficiency_level > 0) {
                $compProgress = min(100, round(($competencyProfile->proficiency_level / 5) * 100));
                $progressValue = max($progressValue, $compProgress);
            }
        }

        // 4. Check destination knowledge training progress (Priority 3)
        if ($progressValue < 100 && $trainingTitle) {
            $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                ->where('destination_name', 'LIKE', '%' . $trainingTitle . '%')
                ->first();

            if ($destinationRecord && $destinationRecord->progress > 0) {
                $destProgress = min(100, round($destinationRecord->progress));
                $progressValue = max($progressValue, (float)$destProgress);
            }
        }

        return $progressValue;
    }

    /**
     * Helper method to get competency name for a training record
     */
    private function getCompetencyNameForRecord($record)
    {
        // Try to find matching competency gap
        $competencyGap = \App\Models\CompetencyGap::with('competency')
            ->where('employee_id', $record->employee_id)
            ->first();

        if ($competencyGap && $competencyGap->competency) {
            return $competencyGap->competency->competency_name;
        }

        return null;
    }

    /**
     * Clean up duplicate records in the database for ALL employees
     */
    public function cleanupDuplicateRecords()
    {
        try {
            $duplicatesFound = [];
            $duplicatesRemoved = 0;

            // Find all duplicate records in employee_training_dashboard table
            $allRecords = \App\Models\EmployeeTrainingDashboard::with(['employee', 'course'])->get();
            $seenCombinations = [];

            foreach ($allRecords as $record) {
                if (!$record->employee_id) continue;

                // Create unique identifier for this employee-course combination
                $uniqueKey = $record->employee_id . '_' . ($record->course_id ?? 'null') . '_' . strtolower(trim($record->training_title ?? ''));

                if (isset($seenCombinations[$uniqueKey])) {
                    // This is a duplicate - mark for removal
                    $duplicatesFound[] = [
                        'employee_id' => $record->employee_id,
                        'employee_name' => $record->employee ? ($record->employee->first_name . ' ' . $record->employee->last_name) : 'Unknown',
                        'course_id' => $record->course_id,
                        'training_title' => $record->training_title,
                        'record_id' => $record->id,
                        'duplicate_of' => $seenCombinations[$uniqueKey]['id'],
                        'created_at' => $record->created_at
                    ];

                    // Remove the duplicate record
                    $record->delete();
                    $duplicatesRemoved++;

                } else {
                    // First occurrence - keep it
                    $seenCombinations[$uniqueKey] = [
                        'id' => $record->id,
                        'employee_name' => $record->employee ? ($record->employee->first_name . ' ' . $record->employee->last_name) : 'Unknown',
                        'training_title' => $record->training_title
                    ];
                }
            }

            // Log the cleanup activity
            \App\Models\ActivityLog::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                'action' => 'cleanup_duplicates',
                'module' => 'Employee Training Dashboard',
                'description' => "Cleaned up {$duplicatesRemoved} duplicate training records for ALL employees.",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$duplicatesRemoved} duplicate records for ALL employees.",
                'duplicates_removed' => $duplicatesRemoved,
                'duplicates_found' => $duplicatesFound,
                'total_records_processed' => $allRecords->count(),
                'unique_records_remaining' => $allRecords->count() - $duplicatesRemoved
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cleaning up duplicate records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix existing training records with missing course information
     */
    public function fixMissingCourseInfo()
    {
        try {
            $trainingRecords = EmployeeTrainingDashboard::with(['course'])->get();
            $updated = 0;
            $courseDescriptionsFixed = 0;

            foreach ($trainingRecords as $record) {
                $wasUpdated = false;

                // Fix missing training_title
                if (!$record->training_title && $record->course_id) {
                    $course = \App\Models\CourseManagement::find($record->course_id);
                    if ($course && $course->course_title) {
                        $record->update(['training_title' => $course->course_title]);
                        $wasUpdated = true;
                    }
                }

                // Fix missing course descriptions
                if ($record->course_id) {
                    $course = \App\Models\CourseManagement::find($record->course_id);
                    if ($course && (!$course->description || trim($course->description) === '' || $course->description === 'No description')) {
                        $courseTitle = $course->course_title ?? $record->training_title ?? 'Course';
                        $course->description = 'Training course: ' . $courseTitle;
                        $course->save();
                        $courseDescriptionsFixed++;
                    }
                }

                if ($wasUpdated) {
                    $record->save();
                    $updated++;
                }
            }

            // Log the fix
            \App\Models\ActivityLog::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                'action' => 'bulk_fix',
                'module' => 'Employee Training Dashboard',
                'description' => "Fixed course information for {$updated} training records and {$courseDescriptionsFixed} course descriptions.",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updated} training records and fixed {$courseDescriptionsFixed} course descriptions.",
                'updated_count' => $updated,
                'descriptions_fixed' => $courseDescriptionsFixed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fixing course information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix existing training records that don't have expiration dates by syncing with Destination Knowledge Training
     */
    public function fixExpiredDates(Request $request)
    {
        try {
            // Password verification for security - require password for enhanced UI
            if ($request->has('password')) {
                $user = Auth::guard('admin')->user();
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password. Please enter your correct admin password.'
                    ], 401);
                }
            } else {
                // For backward compatibility, allow without password but log it
                Log::info('Fix expired dates called without password verification');
            }
            $trainingRecords = EmployeeTrainingDashboard::with(['employee', 'course'])->get();
            $updated = 0;
            $synced = 0;

            foreach ($trainingRecords as $record) {
                $originalExpiredDate = $record->expired_date;
                $newExpiredDate = null;

                // Try to sync with Destination Knowledge Training first
                if ($record->course) {
                    $courseTitle = str_replace(['Training', 'Course', 'Program'], '', $record->course->course_title);
                    $courseTitle = trim($courseTitle);

                    $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $record->employee_id)
                        ->where(function($query) use ($courseTitle) {
                            $query->where('destination_name', 'LIKE', '%' . $courseTitle . '%')
                                  ->orWhere('destination_name', 'LIKE', '%' . strtoupper($courseTitle) . '%');
                        })
                        ->first();

                    if ($destinationTraining && $destinationTraining->expired_date) {
                        $newExpiredDate = \Carbon\Carbon::parse($destinationTraining->expired_date);
                        $synced = (int)$synced + 1;
                    }
                }

                // If no destination training match, check competency gap
                if (!$newExpiredDate) {
                    $competencyGap = \App\Models\CompetencyGap::with('competency')
                        ->where('employee_id', $record->employee_id)
                        ->whereHas('competency', function($q) use ($record) {
                            if ($record->course) {
                                $courseTitle = str_replace([' Training', ' Course', ' Program'], '', $record->course->course_title);
                                $q->where('competency_name', 'LIKE', '%' . $courseTitle . '%');
                            }
                        })
                        ->first();

                    if ($competencyGap && $competencyGap->expired_date) {
                        $newExpiredDate = \Carbon\Carbon::parse($competencyGap->expired_date);
                    }
                }

                // If still no date, set default expiration (90 days from now)
                if (!$newExpiredDate) {
                    $newExpiredDate = now();
                }

                // Update if different from current
                $shouldUpdate = false;
                if ($originalExpiredDate && $newExpiredDate) {
                    $shouldUpdate = $originalExpiredDate->format('Y-m-d H:i:s') != $newExpiredDate->format('Y-m-d H:i:s');
                } elseif (!$originalExpiredDate && $newExpiredDate) {
                    $shouldUpdate = true;
                }

                if ($shouldUpdate) {
                    $record->expired_date = $newExpiredDate;
                    $record->save();
                    $updated++;
                }
            }

            // Log the fix
            \App\Models\ActivityLog::createLog([
                'action' => 'bulk_fix',
                'module' => 'Employee Training Dashboard',
                'description' => "Fixed expiration dates for {$updated} training records. Synced {$synced} records with Destination Knowledge Training system.",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updated} training records with expiration dates. Synced {$synced} records with Destination Knowledge Training.",
                'updated_count' => $updated,
                'synced_count' => $synced
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fixing expiration dates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReadinessScore($employeeId)
    {
        try {
            // First try to find employee by name if not found by ID
            $employee = \App\Models\Employee::where('employee_id', $employeeId)
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) = ?", [$employeeId])
                ->first();

            if (!$employee) {
                return response()->json([
                    'readiness_score' => 0,
                    'has_data' => false,
                    'error' => 'Employee not found'
                ]);
            }

            $actualEmployeeId = $employee->employee_id;

            // Get employee competency data
            $competencyProfiles = \App\Models\EmployeeCompetencyProfile::with('competency')
                ->where('employee_id', $actualEmployeeId)
                ->get();

            // Get training data
            $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $actualEmployeeId)->get();

            // Get certificate data
            $certificates = \App\Models\TrainingRecordCertificateTracking::where('employee_id', $actualEmployeeId)->count();

            // Always calculate readiness score even if some data is missing
            $hasAnyData = !$competencyProfiles->isEmpty() || !$trainingRecords->isEmpty() || $certificates > 0;

            // Use EXACT same calculation logic as Employee Training Dashboard frontend

            // Calculate competency metrics
            $avgProficiency = 0;
            $leadershipCount = 0;
            $totalCompetencies = $competencyProfiles->count();

            if ($totalCompetencies > 0) {
                $totalProficiency = 0;
                foreach ($competencyProfiles as $profile) {
                    $totalProficiency += $profile->proficiency_level;

                    // Check if it's a leadership competency
                    $competencyName = strtolower($profile->competency->competency_name ?? '');
                    if (strpos($competencyName, 'leadership') !== false ||
                        strpos($competencyName, 'management') !== false ||
                        strpos($competencyName, 'supervisor') !== false) {
                        $leadershipCount++;
                    }
                }
                $avgProficiency = $totalProficiency / $totalCompetencies; // Keep as level (1-5)
            }

            // Get training data
            $totalCourses = $trainingRecords->count();
            $completedCourses = $trainingRecords->where('progress', '>=', 100)->count();
            $totalProgress = $trainingRecords->sum('progress');
            $avgTrainingProgress = $totalCourses > 0 ? $totalProgress / $totalCourses : 0;
            $completionRate = $totalCourses > 0 ? ($completedCourses / $totalCourses) * 100 : 0;

            // Use ultra-conservative algorithm matching frontend (70% competency + 30% training)

            // Calculate competency profile component (70% weight) - Ultra-conservative approach
            $competencyProfileScore = 0;
            if ($totalCompetencies > 0) {
                // Ultra-conservative proficiency scoring - cap at 20% for level 5
                $proficiencyScore = min(($avgProficiency / 5) * 20, 20);

                // Leadership score - ultra-conservative, requires extremely high ratio
                $leadershipRatio = $leadershipCount / max($totalCompetencies, 1);
                $leadershipScore = min($leadershipRatio * 12, 12); // Max 12%

                // Competency breadth - ultra-conservative, requires 100+ competencies for max
                $competencyBreadthScore = min(($totalCompetencies / 100) * 8, 8); // Max 8%

                // Ultra-conservative weighted average
                $competencyProfileScore = ($proficiencyScore * 0.7) + ($leadershipScore * 0.2) + ($competencyBreadthScore * 0.1);
            }

            // Calculate training records component (30% weight) - Ultra-conservative approach
            $trainingRecordsScore = 0;
            if ($totalCourses > 0) {
                // Cap training progress at 15% to prevent inflation
                $trainingProgressScore = min($avgTrainingProgress * 0.15, 15);

                // Cap completion rate at 12%
                $completionRateScore = min($completionRate * 0.12, 12);

                // Ultra-conservative assignment scoring - requires 50+ courses for max
                $assignmentScore = min(($totalCourses / 50) * 8, 8); // Max 8%

                // Ultra-conservative certificate scoring - requires 15+ certificates for max
                $certificateScore = $certificates > 0 ? min(($certificates / 15) * 5, 5) : 0; // Max 5%

                // Ultra-conservative weighted average
                $trainingRecordsScore = ($trainingProgressScore * 0.6) + ($completionRateScore * 0.25) + ($assignmentScore * 0.1) + ($certificateScore * 0.05);
            }

            // Final weighted calculation: 70% competency + 30% training (capped at 100%)
            if ($totalCompetencies > 0 && $totalCourses > 0) {
                // Both competency and training data available
                $readiness = ($competencyProfileScore * 0.70) + ($trainingRecordsScore * 0.30);
            } elseif ($totalCompetencies > 0) {
                // Only competency data available
                $readiness = $competencyProfileScore;
            } elseif ($totalCourses > 0) {
                // Only training data available
                $readiness = $trainingRecordsScore;
            } else {
                // No data available
                $readiness = 0; // Baseline score for employees with no data
            }

            $readinessScore = round($readiness);

            return response()->json([
                'readiness_score' => $readinessScore,
                'has_data' => $hasAnyData, // Use the calculated flag
                'competency_profile_score' => round($competencyProfileScore),
                'training_records_score' => round($trainingRecordsScore),
                'certificate_count' => $certificates
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating readiness score: ' . $e->getMessage());
            return response()->json([
                'readiness_score' => 0,
                'has_data' => false,
                'error' => 'Unable to calculate readiness score'
            ]);
        }
    }

    public function create(Request $request)
    {
        // Redirect to index as the dashboard handles creation via modal
        return redirect()->route('admin.employee_trainings_dashboard.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'course_id' => 'required|exists:course_management,course_id',
            'progress' => 'nullable|integer|min:0|max:100',
            'training_date' => 'required|date',
            'expired_date' => 'nullable|date',
        ]);
        $data['last_accessed'] = now();
        $data['assigned_by'] = Auth::id();

        // ENHANCED: Get course information and populate training_title
        $course = \App\Models\CourseManagement::find($data['course_id']);
        if ($course) {
            $data['training_title'] = $course->course_title;
        }

        // Set default expired date if not provided
        if (!isset($data['expired_date'])) {
            $data['expired_date'] = now()->addDays(90); // Default 90 days expiration
        }

        // Check for existing assignment to prevent duplicates
        $existingAssignment = EmployeeTrainingDashboard::where('employee_id', $data['employee_id'])
            ->where('course_id', $data['course_id'])
            ->first();

        if ($existingAssignment) {
            return redirect()->back()->with('warning', 'This course is already assigned to this employee.');
        }

        // Reset exam attempts when course is assigned/reassigned
        \App\Models\ExamAttempt::resetAttemptsForCourse($data['employee_id'], $data['course_id']);

        $record = EmployeeTrainingDashboard::create($data);

        // ENHANCED: Ensure course relationship is loaded after creation
        $record->load('course');

        // Log the assignment for tracking
        \App\Models\ActivityLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'assign',
            'module' => 'Employee Training Dashboard',
            'description' => "Assigned course '{$course->course_title}' to employee {$data['employee_id']}",
        ]);

        // Skip auto-create competency entries to prevent phantom records
        // $this->autoCreateCompetencyEntries($record);

        // Sync progress with Destination Knowledge Training
        $this->syncProgressWithDestinationKnowledge($record);

        return redirect()->back()->with('success', 'Training assigned successfully!');
    }
    public function update(Request $request, $id)
    {
        $record = EmployeeTrainingDashboard::findOrFail($id);
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'course_id' => 'required|exists:course_management,course_id',
            'progress' => 'nullable|integer|min:0|max:100',
            'training_date' => 'required|date',
            'last_accessed' => 'nullable|date',
            'expired_date' => 'nullable|date',
        ]);
        $data['assigned_by'] = Auth::id();

        // Check if course_id changed (reassignment) and reset exam attempts
        if ($record->course_id != $data['course_id']) {
            \App\Models\ExamAttempt::resetAttemptsForCourse($data['employee_id'], $data['course_id']);
        }

        $record->update($data);

        // Skip auto-create competency entries to prevent phantom records
        // $this->autoCreateCompetencyEntries($record);

        // Sync progress with Destination Knowledge Training
        $this->syncProgressWithDestinationKnowledge($record);

        // ALWAYS sync with Competency Profile and Gap regardless of progress level
        $this->syncWithCompetencyProfile($record);
        $this->syncWithCompetencyGap($record);

        return redirect()->back()->with('success', 'Training record updated successfully!');
    }

    /**
     * Clean up phantom training records - Force delete specific record
     */
    public function cleanupPhantomRecords()
    {
        try {
            $deletedCount = 0;

            // Direct approach - delete records with generic titles
            $specificRecord = EmployeeTrainingDashboard::where('training_title', 'LIKE', '%Training Course%')
                ->where('employee_id', 'EMP001') // Assuming this is the employee
                ->first();

            if ($specificRecord) {
                Log::info('Force deleting specific phantom record TR20250001:', [
                    'id' => $specificRecord->id,
                    'employee_id' => $specificRecord->employee_id,
                    'training_title' => $specificRecord->training_title,
                    'course_id' => $specificRecord->course_id
                ]);

                $specificRecord->delete();
                $deletedCount++;
            }

            // Also clean up any other phantom records
            $phantomRecords = EmployeeTrainingDashboard::where(function($query) {
                $query->where('training_title', 'LIKE', '%Training Course%')
                      ->orWhere('training_title', 'Unknown Course')
                      ->orWhere('training_title', 'N/A')
                      ->orWhere('training_title', 'Course');
            })
            ->get();

            foreach ($phantomRecords as $record) {
                // Skip if already deleted above
                if ($specificRecord && $record->id == $specificRecord->id) {
                    continue;
                }

                Log::info('Force deleting phantom training record:', [
                    'id' => $record->id,
                    'employee_id' => $record->employee_id,
                    'training_title' => $record->training_title,
                    'course_id' => $record->course_id
                ]);

                $record->delete();
                $deletedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Force deleted {$deletedCount} phantom training records including TR20250001",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error cleaning up phantom records: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cleaning up phantom records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual sync for existing training records
     */
    public function syncExistingRecords()
    {
        try {
            $syncedCount = 0;
            $trainingRecords = EmployeeTrainingDashboard::with(['employee', 'course'])->get();

            foreach ($trainingRecords as $record) {
                // Skip auto-create competency entries to prevent phantom records
                // $this->autoCreateCompetencyEntries($record);

                // Sync with competency profile and gap
                $this->syncWithCompetencyProfile($record);
                $this->syncWithCompetencyGap($record);

                $syncedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$syncedCount} training records with competency systems.",
                'synced_count' => $syncedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing training records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync training progress with Competency Profile
     */
    private function syncWithCompetencyProfile($trainingRecord)
    {
        try {
            $course = $trainingRecord->course;
            if (!$course) return;

            // Extract competency name from course title with better matching
            $courseTitle = str_replace([' Training', ' Course', ' Program'], '', $course->course_title);

            // Find matching competency profile with multiple search strategies
            $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($courseTitle, $course) {
                // Try exact match first
                $q->where('competency_name', $courseTitle)
                  // Then try partial matches
                  ->orWhere('competency_name', 'LIKE', '%' . $courseTitle . '%')
                  // Try original course title
                  ->orWhere('competency_name', 'LIKE', '%' . $course->course_title . '%')
                  // Try specific matches for known patterns
                  ->orWhere(function($subQ) use ($courseTitle) {
                      if (stripos($courseTitle, 'Communication') !== false) {
                          $subQ->where('competency_name', 'LIKE', '%Communication%');
                      }
                      if (stripos($courseTitle, 'BAESA') !== false || stripos($courseTitle, 'QUEZON') !== false) {
                          $subQ->orWhere('competency_name', 'LIKE', '%Destination Knowledge%')
                               ->orWhere('competency_name', 'LIKE', '%Baesa%')
                               ->orWhere('competency_name', 'LIKE', '%Quezon%');
                      }
                  });
            })
            ->where('employee_id', $trainingRecord->employee_id)
            ->first();

            if ($competencyProfile) {
                // Get actual progress using same priority as display logic: Exam > Training record
                $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($trainingRecord->employee_id, $trainingRecord->course_id);
                $actualProgress = $examProgress > 0 ? $examProgress : ($trainingRecord->progress ?? 0);

                // Convert actual progress to proficiency level (1-5 scale) using same logic as autoCreateCompetencyEntries
                $proficiencyLevel = 0;
                if ($actualProgress >= 90) $proficiencyLevel = 5;
                elseif ($actualProgress >= 70) $proficiencyLevel = 4;
                elseif ($actualProgress >= 50) $proficiencyLevel = 3;
                elseif ($actualProgress >= 30) $proficiencyLevel = 2;
                elseif ($actualProgress > 0) $proficiencyLevel = 1;
                else $proficiencyLevel = 1; // Minimum level 1 for existing profiles

                $competencyProfile->proficiency_level = $proficiencyLevel;
                $competencyProfile->assessment_date = now();
                $competencyProfile->save();

                \App\Models\ActivityLog::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                    'action' => 'sync',
                    'module' => 'Training-Competency Sync',
                    'description' => "Synced competency proficiency to level {$proficiencyLevel} ({$actualProgress}% actual progress, exam: {$examProgress}%) from training dashboard for {$course->course_title}",
                ]);
            } else {
                // Log when no matching competency profile is found for debugging
                \Illuminate\Support\Facades\Log::info("No matching competency profile found for course: {$course->course_title}, employee: {$trainingRecord->employee_id}");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error syncing with competency profile: ' . $e->getMessage());
        }
    }

    /**
     * Debug method to check training records data
     */
    public function debugTrainingRecords()
    {
        try {
            $totalRecords = EmployeeTrainingDashboard::count();
            $uniqueEmployees = EmployeeTrainingDashboard::distinct('employee_id')->count('employee_id');

            $recordsByEmployee = EmployeeTrainingDashboard::select('employee_id', DB::raw('COUNT(*) as record_count'))
                ->groupBy('employee_id')
                ->get();

            $sampleRecords = EmployeeTrainingDashboard::with(['employee', 'course'])
                ->take(10)
                ->get()
                ->map(function($record) {
                    return [
                        'id' => $record->id,
                        'employee_id' => $record->employee_id,
                        'employee_name' => $record->employee ? ($record->employee->first_name . ' ' . $record->employee->last_name) : 'No Employee',
                        'course_id' => $record->course_id,
                        'course_title' => $record->course ? $record->course->course_title : 'No Course',
                        'training_title' => $record->training_title,
                        'status' => $record->status,
                        'progress' => $record->progress,
                        'created_at' => $record->created_at
                    ];
                });

            return response()->json([
                'total_records' => $totalRecords,
                'unique_employees' => $uniqueEmployees,
                'records_by_employee' => $recordsByEmployee,
                'sample_records' => $sampleRecords,
                'message' => 'Debug data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create missing training entries - FIXED to prevent all duplicates
     */
    public function createMissingEntries(Request $request)
    {
        try {
            // Password verification for security - require password for enhanced UI
            if ($request->has('password')) {
                $user = Auth::guard('admin')->user();
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password. Please enter your correct admin password.'
                    ], 401);
                }
            } else {
                // For backward compatibility, allow without password but log it
                Log::info('Create missing entries called without password verification');
            }
            $created = 0;
            $skipped = 0;
            $existingRecords = EmployeeTrainingDashboard::count();

            // Get ALL existing training records to prevent duplicates
            $existingTrainings = EmployeeTrainingDashboard::select('employee_id', 'course_id', 'training_title')
                ->get()
                ->groupBy('employee_id');

            // Get employees who have competency gaps (these are the ones who need training)
            $employeesWithGaps = \App\Models\CompetencyGap::with(['employee', 'competency'])
                ->where('current_level', '<', DB::raw('required_level'))
                ->get()
                ->groupBy('employee_id');

            // Only process employees with actual gaps who are NOT already in the dashboard
            foreach ($employeesWithGaps as $employeeId => $gaps) {
                $employee = \App\Models\Employee::where('employee_id', $employeeId)->first();
                if (!$employee) continue;

                // SKIP if employee already has training records in dashboard
                if ($existingTrainings->has($employeeId)) {
                    $skipped += count($gaps);
                    continue;
                }

                // Get existing trainings for this employee (should be empty at this point)
                $employeeExistingTrainings = collect();

                foreach ($gaps as $gap) {
                    $competencyName = $gap->competency->competency_name ?? '';
                    if (empty($competencyName)) continue;

                    // Search for relevant courses with stricter matching
                    $matchingCourses = \App\Models\CourseManagement::where(function($query) use ($competencyName) {
                        $cleanName = str_replace([' Skills', ' Competency', ' Training'], '', $competencyName);
                        $query->where('course_title', $competencyName)
                              ->orWhere('course_title', 'LIKE', '%' . $cleanName . '%');
                    })
                    ->whereNotNull('course_title')
                    ->where('course_title', '!=', '')
                    ->where('course_title', 'NOT LIKE', '%Training Course%')
                    ->where('course_title', 'NOT LIKE', '%Unknown%')
                    ->get();

                    foreach ($matchingCourses as $course) {
                        // STRICT DUPLICATE CHECK: Check against ALL existing records
                        $isDuplicate = $employeeExistingTrainings->contains(function($existing) use ($course) {
                            return $existing->course_id == $course->course_id ||
                                   $existing->training_title == $course->course_title ||
                                   (stripos($existing->training_title, $course->course_title) !== false) ||
                                   (stripos($course->course_title, $existing->training_title) !== false);
                        });

                        if (!$isDuplicate && $course->course_id && strlen(trim($course->course_title)) > 3) {
                            // Create new training record
                            $newRecord = EmployeeTrainingDashboard::create([
                                'employee_id' => $employee->employee_id,
                                'course_id' => $course->course_id,
                                'training_title' => $course->course_title,
                                'training_date' => now(),
                                'expired_date' => $gap->expired_date ?? now()->addDays(90),
                                'status' => 'Assigned',
                                'progress' => 0,
                                'assigned_by' => Auth::id(),
                                'source' => 'competency_gap',
                                'remarks' => "Auto-created from competency gap: {$competencyName}"
                            ]);

                            // Add to existing trainings to prevent further duplicates in this session
                            $employeeExistingTrainings->push($newRecord);
                            $created++;
                        } else {
                            $skipped++;
                        }
                    }
                }
            }

            // REMOVED destination training auto-creation to prevent unwanted entries

            // Log the activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'selective_create',
                'module' => 'Employee Training Dashboard',
                'description' => "Created {$created} training entries for employees with competency gaps who were not already in dashboard, skipped {$skipped} existing entries.",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully created {$created} training entries for employees not already in dashboard. Skipped {$skipped} existing entries.",
                'debug_info' => [
                    'employees_with_gaps' => $employeesWithGaps->count(),
                    'employees_already_in_dashboard' => $existingTrainings->count(),
                    'existing_records_before' => $existingRecords,
                    'new_records_after' => EmployeeTrainingDashboard::count()
                ],
                'created' => $created,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create missing entries: ' . $e->getMessage(),
                'debug_info' => [
                    'existing_records' => EmployeeTrainingDashboard::count()
                ]
            ], 500);
        }
    }

    /**
     * Export training data to Excel or CSV with password verification
     */
    public function exportTrainingData(Request $request)
    {
        try {
            // Password verification for security
            $user = Auth::guard('admin')->user();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return redirect()->back()->with('error', 'Invalid password. Please enter your correct admin password.');
            }

            $format = $request->input('format', 'excel');

            // Get training records with relationships
            $query = EmployeeTrainingDashboard::with(['employee', 'course']);

            // Apply filters if provided
            if ($request->filled('employee_filter')) {
                $query->where('employee_id', $request->employee_filter);
            }

            if ($request->filled('course_filter')) {
                $query->where('course_id', $request->course_filter);
            }

            if ($request->filled('status_filter')) {
                $statusFilter = $request->status_filter;
                $query->where(function($q) use ($statusFilter) {
                    if ($statusFilter === 'completed') {
                        $q->where('progress', '>=', 100);
                    } elseif ($statusFilter === 'in-progress') {
                        $q->where('progress', '>', 0)->where('progress', '<', 100);
                    } elseif ($statusFilter === 'not-started') {
                        $q->where('progress', '<=', 0);
                    }
                });
            }

            $trainingRecords = $query->get();

            // Prepare data for export
            $exportData = [];
            $exportData[] = [
                'Employee ID',
                'Employee Name',
                'Course ID',
                'Course Title',
                'Training Title',
                'Progress (%)',
                'Status',
                'Training Date',
                'Expired Date',
                'Last Accessed',
                'Assigned By',
                'Source',
                'Remarks'
            ];

            foreach ($trainingRecords as $record) {
                $employeeName = $record->employee
                    ? $record->employee->first_name . ' ' . $record->employee->last_name
                    : 'Unknown Employee';

                $courseTitle = $record->course
                    ? $record->course->course_title
                    : 'Unknown Course';

                $status = $this->getStatusFromProgress($record->progress ?? 0);

                $assignedBy = $record->assigned_by
                    ? \App\Models\User::find($record->assigned_by)?->name ?? 'Unknown'
                    : 'System';

                $exportData[] = [
                    $record->employee_id ?? '',
                    $employeeName,
                    $record->course_id ?? '',
                    $courseTitle,
                    $record->training_title ?? '',
                    $record->progress ?? 0,
                    ucfirst($status),
                    $record->training_date ? \Carbon\Carbon::parse($record->training_date)->format('Y-m-d') : '',
                    $record->expired_date ? \Carbon\Carbon::parse($record->expired_date)->format('Y-m-d') : '',
                    $record->last_accessed ? \Carbon\Carbon::parse($record->last_accessed)->format('Y-m-d H:i') : '',
                    $assignedBy,
                    $record->source ?? 'manual',
                    $record->remarks ?? ''
                ];
            }

            // Generate filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "employee_training_dashboard_{$timestamp}";

            if ($format === 'csv') {
                return $this->exportToCsv($exportData, $filename);
            } else {
                return $this->exportToExcel($exportData, $filename);
            }

        } catch (\Exception $e) {
            Log::error('Export training data error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export training data: ' . $e->getMessage());
        }
    }

    /**
     * Export data to CSV format
     */
    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export data to Excel format
     */
    private function exportToExcel($data, $filename)
    {
        // Simple Excel export using HTML table format
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xls\"",
        ];

        $callback = function() use ($data) {
            echo '<html><body><table border="1">';

            foreach ($data as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . htmlspecialchars($cell) . '</td>';
                }
                echo '</tr>';
            }

            echo '</table></body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Sync training progress with Competency Gap
     */
    private function syncWithCompetencyGap($trainingRecord)
    {
        try {
            $course = $trainingRecord->course;
            if (!$course) return;

            // Extract competency name from course title with better matching
            $courseTitle = str_replace([' Training', ' Course', ' Program'], '', $course->course_title);

            // Find matching competency gap with multiple search strategies
            $competencyGap = \App\Models\CompetencyGap::whereHas('competency', function($q) use ($courseTitle, $course) {
                // Try exact match first
                $q->where('competency_name', $courseTitle)
                  // Then try partial matches
                  ->orWhere('competency_name', 'LIKE', '%' . $courseTitle . '%')
                  // Try original course title
                  ->orWhere('competency_name', 'LIKE', '%' . $course->course_title . '%')
                  // Try specific matches for known patterns
                  ->orWhere(function($subQ) use ($courseTitle) {
                      if (stripos($courseTitle, 'Communication') !== false) {
                          $subQ->where('competency_name', 'LIKE', '%Communication%');
                      }
                      if (stripos($courseTitle, 'BAESA') !== false || stripos($courseTitle, 'QUEZON') !== false) {
                          $subQ->orWhere('competency_name', 'LIKE', '%Destination Knowledge%')
                               ->orWhere('competency_name', 'LIKE', '%Baesa%')
                               ->orWhere('competency_name', 'LIKE', '%Quezon%');
                      }
                  });
            })
            ->where('employee_id', $trainingRecord->employee_id)
            ->first();

            if ($competencyGap) {
                // Convert progress percentage to current level (1-5 scale)
                $currentLevel = max(1, min(5, ceil(($trainingRecord->progress / 100) * 5)));
                $competencyGap->current_level = $currentLevel;
                $competencyGap->gap = max(0, $competencyGap->required_level - $currentLevel);

                // SYNC EXPIRED DATES: Competency Gap is the source of truth
                if ($competencyGap->expired_date) {
                    // Always use gap's expired date for training record
                    $trainingRecord->expired_date = $competencyGap->expired_date;
                    $trainingRecord->save();
                } elseif (!$competencyGap->expired_date && $trainingRecord->expired_date) {
                    // Only if gap has no date, use training date for gap
                    $competencyGap->expired_date = $trainingRecord->expired_date;
                }

                $competencyGap->save();

                \App\Models\ActivityLog::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                    'action' => 'sync',
                    'module' => 'Training-Gap Sync',
                    'description' => "Synced competency gap current level to {$currentLevel} ({$trainingRecord->progress}%) and training expired date from gap list for {$course->course_title}",
                ]);
            } else {
                // Log when no matching competency gap is found for debugging
                \Illuminate\Support\Facades\Log::info("No matching competency gap found for course: {$course->course_title}, employee: {$trainingRecord->employee_id}");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error syncing with competency gap: ' . $e->getMessage());
        }
    }


    /**
     * Sync progress between Employee Training Dashboard and Destination Knowledge Training
     * Also update Competency Gap and Employee Competency Profile when training reaches 100%
     */
    private function syncProgressWithDestinationKnowledge($trainingRecord)
    {
        try {
            // Find corresponding destination knowledge training record
            // Look for records that match employee_id and course title
            $course = $trainingRecord->course;
            if (!$course) return;

            $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $trainingRecord->employee_id)
                ->where('destination_name', 'LIKE', '%' . $course->course_title . '%')
                ->first();

            if ($destinationRecord) {
                // Update existing destination record
                $destinationRecord->progress = $trainingRecord->progress ?? 0;

                // Update status based on progress
                if ($trainingRecord->progress >= 100) {
                    $destinationRecord->status = 'completed';
                    $destinationRecord->date_completed = now();
                } elseif ($trainingRecord->progress > 0) {
                    $destinationRecord->status = 'in-progress';
                } else {
                    $destinationRecord->status = 'not-started';
                }

                $destinationRecord->save();

                // Log the sync activity
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'sync',
                    'module' => 'Training Progress Sync',
                    'description' => "Synced progress ({$trainingRecord->progress}%) from Employee Training Dashboard to Destination Knowledge Training for {$course->course_title}",
                ]);
            } else {
                // CREATE NEW destination knowledge training record if it doesn't exist and this is destination-related training
                if ($this->isDestinationRelatedCourse($course->course_title)) {
                    $newDestinationRecord = \App\Models\DestinationKnowledgeTraining::create([
                        'employee_id' => $trainingRecord->employee_id,
                        'destination_name' => $course->course_title,
                        'details' => 'Auto-created from Employee Training Dashboard completion - ' . ($course->description ?? 'Destination knowledge training'),
                        'progress' => $trainingRecord->progress ?? 0,
                        'status' => $this->getStatusFromProgress((int)($trainingRecord->progress ?? 0)),
                        'date_completed' => ($trainingRecord->progress >= 100) ? now() : null,
                        'is_active' => true,
                    ]);

                    // Log the creation activity
                    \App\Models\ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'create',
                        'module' => 'Destination Knowledge Auto-Create',
                        'description' => "Auto-created Destination Knowledge Training record for {$course->course_title} with {$trainingRecord->progress}% progress from Employee Training Dashboard",
                    ]);
                }
            }

            // NEW: Update Competency Gap and Employee Competency Profile when training reaches 100%
            if ($trainingRecord->progress >= 100) {
                $this->updateCompetencyDataOnTrainingCompletion($trainingRecord);

                // AUTO-GENERATE CERTIFICATE when training reaches 100%
                $certificateController = new CertificateGenerationController();
                $certificateController->generateCertificateOnCompletion(
                    $trainingRecord->employee_id,
                    $trainingRecord->course_id,
                    now()
                );
            }

        } catch (\Exception $e) {
            Log::error('Error syncing progress with destination knowledge: ' . $e->getMessage());
        }
    }

    /**
     * Check if a course is destination-related based on title keywords
     */
    private function isDestinationRelatedCourse($courseTitle)
    {
        $destinationKeywords = [
            'BAESA', 'QUEZON', 'CITY', 'DESTINATION', 'LOCATION', 'BRANCH', 'OFFICE',
            'SITE', 'FACILITY', 'AREA', 'REGION', 'ZONE', 'DISTRICT', 'STATION'
        ];

        $courseTitle = strtoupper($courseTitle);

        foreach ($destinationKeywords as $keyword) {
            if (strpos($courseTitle, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get status string based on progress percentage
     */
    private function getStatusFromProgress($progress)
    {
        if ($progress >= 100) {
            return 'completed';
        } elseif ($progress > 0) {
            return 'in-progress';
        } else {
            return 'not-started';
        }
    }

    /**
     * Update Competency Gap current level and Employee Competency Profile proficiency level
     * when training reaches 100% completion
     */
    private function updateCompetencyDataOnTrainingCompletion($trainingRecord)
    {
        try {
            $course = $trainingRecord->course;
            if (!$course) return;

            // Extract competency name from course title (remove "Training" suffix)
            $courseTitle = str_replace(' Training', '', $course->course_title);

            // 1. Update Competency Gap current level to 100%
            $competencyGap = \App\Models\CompetencyGap::whereHas('competency', function($q) use ($courseTitle) {
                $q->where('competency_name', 'LIKE', '%' . $courseTitle . '%');
            })
            ->where('employee_id', $trainingRecord->employee_id)
            ->first();

            if ($competencyGap) {
                // Set current level to required level (100%)
                $competencyGap->current_level = $competencyGap->required_level;
                $competencyGap->gap = 0; // No gap remaining
                $competencyGap->save();

                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'update',
                    'module' => 'Competency Gap Auto-Update',
                    'description' => "Auto-updated competency gap current level to 100% for {$competencyGap->competency->competency_name} after training completion",
                ]);
            }

            // 2. Update Employee Competency Profile proficiency level to 5 (100%)
            $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($courseTitle) {
                $q->where('competency_name', 'LIKE', '%' . $courseTitle . '%');
            })
            ->where('employee_id', $trainingRecord->employee_id)
            ->first();

            if ($competencyProfile) {
                $competencyProfile->proficiency_level = 5; // Maximum proficiency level
                $competencyProfile->assessment_date = now();
                $competencyProfile->save();

                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'update',
                    'module' => 'Employee Competency Profile Auto-Update',
                    'description' => "Auto-updated proficiency level to 5 (Expert) for {$competencyProfile->competency->competency_name} after training completion",
                ]);
            }

            // 3. If no existing competency profile, create one
            if (!$competencyProfile && $competencyGap) {
                \App\Models\EmployeeCompetencyProfile::create([
                    'employee_id' => $trainingRecord->employee_id,
                    'competency_id' => $competencyGap->competency_id,
                    'proficiency_level' => 5,
                    'assessment_date' => now(),
                ]);

                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'create',
                    'module' => 'Employee Competency Profile Auto-Create',
                    'description' => "Auto-created competency profile with proficiency level 5 for {$competencyGap->competency->competency_name} after training completion",
                ]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating competency data on training completion: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $record = EmployeeTrainingDashboard::findOrFail($id);
            $employeeName = $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'Unknown';
            $courseName = $record->course ? $record->course->course_title : 'Unknown';

            $record->delete();

            // Log the deletion
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'delete',
                'module' => 'Employee Training Dashboard',
                'description' => "Deleted training record for {$employeeName} - {$courseName}",
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Training record deleted successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Training record deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete record: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete record: ' . $e->getMessage());
        }
    }

    /**
     * Auto-create competency profile and competency gap entries when course is assigned
     */
    private function autoCreateCompetencyEntries($trainingRecord)
    {
        try {
            $course = $trainingRecord->course;
            if (!$course) return;

            // Extract competency name from course title
            $courseTitle = $course->course_title;
            $competencyName = $this->extractCompetencyName($courseTitle);

            // Enhanced duplicate detection for competency library
            $competency = \App\Models\CompetencyLibrary::where('competency_name', $competencyName)->first();

            // If no exact match, check for similar competencies to prevent duplicates
            if (!$competency) {
                $existingCompetencies = \App\Models\CompetencyLibrary::all();
                foreach ($existingCompetencies as $existing) {
                    $existingName = strtoupper(trim($existing->competency_name));
                    $newName = strtoupper(trim($competencyName));

                    // Check multiple similarity patterns
                    if ($existingName === $newName || // Exact match (case insensitive)
                        str_contains($existingName, $newName) || // Existing contains new
                        str_contains($newName, $existingName) || // New contains existing
                        // Check without "Destination Knowledge -" prefix
                        str_replace('DESTINATION KNOWLEDGE - ', '', $existingName) === str_replace('DESTINATION KNOWLEDGE - ', '', $newName) ||
                        // Check core location name similarity
                        $this->areLocationNamesSimilar($existingName, $newName)) {
                        $competency = $existing;
                        break;
                    }
                }
            }

            if (!$competency) {
                // Create new competency in library only if it truly doesn't exist
                $competency = \App\Models\CompetencyLibrary::create([
                    'competency_name' => $competencyName,
                    'description' => 'Auto-created from course assignment: ' . $courseTitle,
                    'category' => $this->determineCompetencyCategory($courseTitle),
                ]);

                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'create',
                    'module' => 'Competency Library Auto-Create',
                    'description' => "Auto-created competency '{$competencyName}' from course assignment - no duplicate found",
                ]);
            } else {
                // Log that existing competency was reused
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'reuse',
                    'module' => 'Competency Library Reuse',
                    'description' => "Reused existing competency '{$competency->competency_name}' for course assignment '{$courseTitle}'",
                ]);
            }

            // Check if competency profile already exists
            $existingProfile = \App\Models\EmployeeCompetencyProfile::where('employee_id', $trainingRecord->employee_id)
                ->where('competency_id', $competency->id)
                ->first();

            if (!$existingProfile) {
                // Get actual progress from exam results first, then destination training, then training record
                $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($trainingRecord->employee_id, $trainingRecord->course_id);

                $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $trainingRecord->employee_id)
                    ->where(function($query) use ($competencyName, $courseTitle) {
                        // Enhanced matching for destination knowledge training
                        $cleanCompetencyName = str_replace('Destination Knowledge - ', '', $competencyName);
                        $cleanCourseTitle = str_replace(['Training', 'Course', 'Program'], '', $courseTitle);

                        $query->where('destination_name', $competencyName)
                              ->orWhere('destination_name', $courseTitle)
                              ->orWhere('destination_name', 'LIKE', '%' . $cleanCompetencyName . '%')
                              ->orWhere('destination_name', 'LIKE', '%' . $cleanCourseTitle . '%')
                              ->orWhere('destination_name', 'LIKE', '%' . strtoupper($cleanCompetencyName) . '%')
                              ->orWhere('destination_name', 'LIKE', '%' . strtoupper($cleanCourseTitle) . '%');
                    })
                    ->orderBy('progress', 'desc') // Get highest progress first
                    ->first();

                // Priority: Exam progress > Destination training progress > Training record progress
                $actualProgress = $examProgress > 0 ? $examProgress :
                                 ($destinationTraining ? $destinationTraining->progress :
                                 ($trainingRecord->progress ?? 0));

                // Use actual progress for all course types (no special destination course handling)
                $proficiencyLevel = 0; // Start with 0 for 0% progress

                if ($actualProgress >= 90) $proficiencyLevel = 5;
                elseif ($actualProgress >= 70) $proficiencyLevel = 4;
                elseif ($actualProgress >= 50) $proficiencyLevel = 3;
                elseif ($actualProgress >= 30) $proficiencyLevel = 2;
                elseif ($actualProgress > 0) $proficiencyLevel = 1;
                else $proficiencyLevel = 0; // 0% progress = 0 proficiency level

                // Create competency profile with actual proficiency level based on destination training progress
                \App\Models\EmployeeCompetencyProfile::create([
                    'employee_id' => $trainingRecord->employee_id,
                    'competency_id' => $competency->id,
                    'proficiency_level' => $proficiencyLevel,
                    'assessment_date' => now(),
                ]);

                $proficiencyDescription = "Based on {$actualProgress}% progress (exam: {$examProgress}%)";

                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'create',
                    'module' => 'Employee Competency Profile Auto-Create',
                    'description' => "Auto-created competency profile for '{$competencyName}' with proficiency level {$proficiencyLevel} ({$proficiencyDescription}) when course '{$courseTitle}' was assigned",
                ]);
            }

            // Check if competency gap already exists
            $existingGap = \App\Models\CompetencyGap::where('employee_id', $trainingRecord->employee_id)
                ->where('competency_id', $competency->id)
                ->first();

            if (!$existingGap) {
                // Get actual progress from exam results first, then destination training, then training record
                $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($trainingRecord->employee_id, $trainingRecord->course_id);

                $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $trainingRecord->employee_id)
                    ->where(function($query) use ($competencyName, $courseTitle) {
                        // Enhanced matching for destination knowledge training
                        $cleanCompetencyName = str_replace('Destination Knowledge - ', '', $competencyName);
                        $cleanCourseTitle = str_replace(['Training', 'Course', 'Program'], '', $courseTitle);

                        $query->where('destination_name', $competencyName)
                              ->orWhere('destination_name', $courseTitle)
                              ->orWhere('destination_name', 'LIKE', '%' . $cleanCompetencyName . '%')
                              ->orWhere('destination_name', 'LIKE', '%' . $cleanCourseTitle . '%')
                              ->orWhere('destination_name', 'LIKE', '%' . strtoupper($cleanCompetencyName) . '%')
                              ->orWhere('destination_name', 'LIKE', '%' . strtoupper($cleanCourseTitle) . '%');
                    })
                    ->orderBy('progress', 'desc') // Get highest progress first
                    ->first();

                // Priority: Exam progress > Destination training progress > Training record progress
                $actualProgress = $examProgress > 0 ? $examProgress :
                                 ($destinationTraining ? $destinationTraining->progress :
                                 ($trainingRecord->progress ?? 0));

                // Use actual progress for all course types (no special destination course handling)
                $currentLevel = 0;
                if ($actualProgress >= 90) $currentLevel = 5;
                elseif ($actualProgress >= 70) $currentLevel = 4;
                elseif ($actualProgress >= 50) $currentLevel = 3;
                elseif ($actualProgress >= 30) $currentLevel = 2;
                elseif ($actualProgress > 0) $currentLevel = 1;
                else $currentLevel = 0;

                $requiredLevel = 5; // Standard 1-5 scale, 5 = Expert level
                $gap = $requiredLevel - $currentLevel;

                \App\Models\CompetencyGap::create([
                    'employee_id' => $trainingRecord->employee_id,
                    'competency_id' => $competency->id,
                    'required_level' => $requiredLevel,
                    'current_level' => $currentLevel,
                    'gap' => $gap,
                ]);

                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'create',
                    'module' => 'Competency Gap Auto-Create',
                    'description' => "Auto-created competency gap for '{$competencyName}' when course '{$courseTitle}' was assigned (Gap: {$gap}%)",
                ]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error auto-creating competency entries: ' . $e->getMessage());
        }
    }

    /**
     * Extract competency name from course title
     */
    private function extractCompetencyName($courseTitle)
    {
        // Remove common course suffixes
        $competencyName = str_replace([' Training', ' Course', ' Program'], '', $courseTitle);

        // Check if this is a destination-related course
        if ($this->isDestinationCourse($courseTitle)) {
            // Format as "Destination Knowledge - [Location Name]"
            return 'Destination Knowledge - ' . $competencyName;
        }

        return $competencyName;
    }

    /**
     * Check if course is destination-related
     */
    private function isDestinationCourse($courseTitle)
    {
        $title = strtoupper($courseTitle);

        // Primary destination indicators
        $primaryKeywords = [
            'DESTINATION', 'LOCATION', 'PLACE', 'GEOGRAPHY', 'ROUTE', 'TRAVEL',
            'AREA', 'TERMINAL', 'STATION', 'AIRPORT', 'PORT', 'HARBOR'
        ];

        // Check for primary keywords first
        foreach ($primaryKeywords as $keyword) {
            if (str_contains($title, $keyword)) {
                return true;
            }
        }

        // Check for city/location patterns
        $locationPatterns = [
            'CITY', 'TOWN', 'PROVINCE', 'REGION', 'DISTRICT', 'MUNICIPALITY',
            'ISLAND', 'BEACH', 'MOUNTAIN', 'VALLEY', 'RIVER', 'LAKE'
        ];

        foreach ($locationPatterns as $pattern) {
            if (str_contains($title, $pattern)) {
                return true;
            }
        }

        // Check for known Philippine locations (expandable list)
        $philippineLocations = [
            'BAESA', 'QUEZON', 'BAGUIO', 'CUBAO', 'BORACAY', 'CEBU', 'DAVAO',
            'MANILA', 'MAKATI', 'TAGUIG', 'PASIG', 'ANTIPOLO', 'CALOOCAN',
            'MARIKINA', 'MUNTINLUPA', 'PARANAQUE', 'PASAY', 'PATEROS',
            'SAN JUAN', 'VALENZUELA', 'MALABON', 'NAVOTAS', 'LAS PINAS',
            'ILOILO', 'ZAMBOANGA', 'CAGAYAN', 'BATAAN', 'LAGUNA', 'CAVITE',
            'RIZAL', 'BULACAN', 'PAMPANGA', 'BATANGAS', 'PALAWAN', 'BOHOL',
            'LEYTE', 'SAMAR', 'NEGROS', 'PANAY', 'MINDANAO', 'LUZON', 'VISAYAS'
        ];

        foreach ($philippineLocations as $location) {
            if (str_contains($title, $location)) {
                return true;
            }
        }

        // Check if it looks like a proper noun (potential place name)
        // This catches custom location names that aren't in our predefined list
        $words = explode(' ', $title);
        foreach ($words as $word) {
            // Skip common words
            $commonWords = ['THE', 'AND', 'OR', 'OF', 'TO', 'IN', 'FOR', 'WITH', 'ON', 'AT', 'BY', 'FROM', 'AS', 'IS', 'WAS', 'ARE', 'WERE', 'BEEN', 'HAVE', 'HAS', 'HAD', 'DO', 'DOES', 'DID', 'WILL', 'WOULD', 'COULD', 'SHOULD', 'MAY', 'MIGHT', 'CAN', 'MUST', 'SHALL', 'TRAINING', 'COURSE', 'PROGRAM', 'KNOWLEDGE', 'SKILLS', 'DEVELOPMENT'];

            if (!in_array($word, $commonWords) && strlen($word) > 2) {
                // Check if word appears to be a proper noun (place name)
                // This is a heuristic - if it's not a common training word, it might be a location
                $trainingWords = ['COMMUNICATION', 'LEADERSHIP', 'MANAGEMENT', 'TECHNICAL', 'CUSTOMER', 'SERVICE', 'BEHAVIORAL', 'FUNCTIONAL', 'ANALYTICAL', 'CREATIVE', 'STRATEGIC', 'PROFESSIONAL', 'PERSONAL', 'BUSINESS', 'CORPORATE', 'ORGANIZATIONAL', 'INTERPERSONAL', 'PROBLEM', 'SOLVING', 'DECISION', 'MAKING', 'TIME', 'STRESS', 'CONFLICT', 'RESOLUTION', 'TEAM', 'BUILDING', 'PUBLIC', 'SPEAKING', 'PRESENTATION', 'WRITING', 'READING', 'LISTENING', 'CRITICAL', 'THINKING', 'INNOVATION', 'CREATIVITY', 'PLANNING', 'ORGANIZING', 'COORDINATING', 'SUPERVISING', 'MONITORING', 'EVALUATING', 'COACHING', 'MENTORING', 'TRAINING', 'TEACHING', 'LEARNING', 'EDUCATION', 'INSTRUCTION', 'WORKSHOP', 'SEMINAR', 'CONFERENCE', 'MEETING', 'SESSION', 'CLASS', 'LESSON', 'MODULE', 'UNIT', 'CHAPTER', 'SECTION', 'PART', 'LEVEL', 'BASIC', 'INTERMEDIATE', 'ADVANCED', 'BEGINNER', 'EXPERT', 'MASTER', 'PROFESSIONAL', 'CERTIFICATION', 'CERTIFICATE', 'DIPLOMA', 'DEGREE', 'QUALIFICATION', 'COMPETENCY', 'SKILL', 'ABILITY', 'CAPABILITY', 'PROFICIENCY', 'EXPERTISE', 'MASTERY', 'EXCELLENCE', 'QUALITY', 'STANDARD', 'BEST', 'PRACTICE', 'METHOD', 'TECHNIQUE', 'STRATEGY', 'APPROACH', 'PROCESS', 'PROCEDURE', 'SYSTEM', 'FRAMEWORK', 'MODEL', 'THEORY', 'CONCEPT', 'PRINCIPLE', 'RULE', 'GUIDELINE', 'POLICY', 'PROTOCOL', 'STANDARD', 'REQUIREMENT', 'SPECIFICATION', 'CRITERIA', 'MEASURE', 'METRIC', 'INDICATOR', 'BENCHMARK', 'TARGET', 'GOAL', 'OBJECTIVE', 'OUTCOME', 'RESULT', 'OUTPUT', 'DELIVERABLE', 'PRODUCT', 'SERVICE', 'SOLUTION', 'ANSWER', 'RESPONSE', 'FEEDBACK', 'EVALUATION', 'ASSESSMENT', 'REVIEW', 'ANALYSIS', 'REPORT', 'DOCUMENTATION', 'RECORD', 'LOG', 'TRACKING', 'MONITORING', 'CONTROL', 'MANAGEMENT', 'ADMINISTRATION', 'OPERATION', 'MAINTENANCE', 'SUPPORT', 'ASSISTANCE', 'HELP', 'AID', 'GUIDANCE', 'DIRECTION', 'INSTRUCTION', 'ADVICE', 'RECOMMENDATION', 'SUGGESTION', 'TIP', 'HINT', 'CLUE', 'INFORMATION', 'DATA', 'KNOWLEDGE', 'UNDERSTANDING', 'COMPREHENSION', 'AWARENESS', 'RECOGNITION', 'IDENTIFICATION', 'DISCOVERY', 'EXPLORATION', 'INVESTIGATION', 'RESEARCH', 'STUDY', 'EXAMINATION', 'INSPECTION', 'OBSERVATION', 'MONITORING', 'SURVEILLANCE', 'TRACKING', 'FOLLOWING', 'PURSUING', 'CHASING', 'HUNTING', 'SEARCHING', 'SEEKING', 'LOOKING', 'FINDING', 'LOCATING', 'POSITIONING', 'PLACING', 'SETTING', 'ESTABLISHING', 'CREATING', 'BUILDING', 'CONSTRUCTING', 'DEVELOPING', 'DESIGNING', 'PLANNING', 'PREPARING', 'ORGANIZING', 'ARRANGING', 'COORDINATING', 'MANAGING', 'CONTROLLING', 'DIRECTING', 'LEADING', 'GUIDING', 'SUPERVISING', 'OVERSEEING', 'MONITORING', 'CHECKING', 'VERIFYING', 'VALIDATING', 'CONFIRMING', 'ENSURING', 'GUARANTEEING', 'SECURING', 'PROTECTING', 'SAFEGUARDING', 'DEFENDING', 'SUPPORTING', 'MAINTAINING', 'SUSTAINING', 'CONTINUING', 'PERSISTING', 'ENDURING', 'LASTING', 'REMAINING', 'STAYING', 'KEEPING', 'HOLDING', 'RETAINING', 'PRESERVING', 'CONSERVING', 'SAVING', 'STORING', 'KEEPING', 'MAINTAINING', 'UPDATING', 'UPGRADING', 'IMPROVING', 'ENHANCING', 'OPTIMIZING', 'MAXIMIZING', 'MINIMIZING', 'REDUCING', 'DECREASING', 'INCREASING', 'EXPANDING', 'EXTENDING', 'ENLARGING', 'GROWING', 'DEVELOPING', 'EVOLVING', 'PROGRESSING', 'ADVANCING', 'MOVING', 'CHANGING', 'TRANSFORMING', 'CONVERTING', 'ADAPTING', 'ADJUSTING', 'MODIFYING', 'ALTERING', 'REVISING', 'UPDATING', 'REFRESHING', 'RENEWING', 'RESTORING', 'RECOVERING', 'RETRIEVING', 'REGAINING', 'RECLAIMING', 'RETURNING', 'COMING', 'GOING', 'MOVING', 'TRAVELING', 'JOURNEYING', 'TOURING', 'VISITING', 'EXPLORING', 'DISCOVERING', 'EXPERIENCING', 'ENJOYING', 'APPRECIATING', 'VALUING', 'RESPECTING', 'HONORING', 'RECOGNIZING', 'ACKNOWLEDGING', 'ACCEPTING', 'EMBRACING', 'WELCOMING', 'GREETING', 'MEETING', 'ENCOUNTERING', 'FACING', 'CONFRONTING', 'DEALING', 'HANDLING', 'MANAGING', 'COPING', 'SURVIVING', 'THRIVING', 'SUCCEEDING', 'ACHIEVING', 'ACCOMPLISHING', 'COMPLETING', 'FINISHING', 'ENDING', 'CONCLUDING', 'CLOSING', 'STOPPING', 'CEASING', 'TERMINATING', 'DISCONTINUING', 'ABANDONING', 'QUITTING', 'LEAVING', 'DEPARTING', 'EXITING', 'ESCAPING', 'FLEEING', 'RUNNING', 'WALKING', 'MOVING', 'GOING', 'COMING', 'ARRIVING', 'REACHING', 'GETTING', 'OBTAINING', 'ACQUIRING', 'GAINING', 'EARNING', 'WINNING', 'LOSING', 'FAILING', 'MISSING', 'LACKING', 'NEEDING', 'WANTING', 'DESIRING', 'WISHING', 'HOPING', 'EXPECTING', 'ANTICIPATING', 'WAITING', 'LOOKING', 'WATCHING', 'SEEING', 'VIEWING', 'OBSERVING', 'NOTICING', 'SPOTTING', 'DETECTING', 'DISCOVERING', 'FINDING', 'LOCATING', 'IDENTIFYING', 'RECOGNIZING', 'KNOWING', 'UNDERSTANDING', 'COMPREHENDING', 'GRASPING', 'REALIZING', 'LEARNING', 'STUDYING', 'READING', 'WRITING', 'SPEAKING', 'TALKING', 'COMMUNICATING', 'EXPRESSING', 'SHARING', 'TELLING', 'SAYING', 'STATING', 'DECLARING', 'ANNOUNCING', 'PROCLAIMING', 'REVEALING', 'DISCLOSING', 'EXPOSING', 'SHOWING', 'DISPLAYING', 'DEMONSTRATING', 'PRESENTING', 'EXHIBITING', 'PERFORMING', 'ACTING', 'PLAYING', 'WORKING', 'OPERATING', 'FUNCTIONING', 'RUNNING', 'EXECUTING', 'IMPLEMENTING', 'APPLYING', 'USING', 'UTILIZING', 'EMPLOYING', 'ENGAGING', 'INVOLVING', 'PARTICIPATING', 'CONTRIBUTING', 'HELPING', 'ASSISTING', 'SUPPORTING', 'AIDING', 'SERVING', 'PROVIDING', 'OFFERING', 'GIVING', 'DELIVERING', 'SUPPLYING', 'FURNISHING', 'EQUIPPING', 'PREPARING', 'READY', 'SET', 'GO'];

                if (!in_array($word, $trainingWords)) {
                    // This might be a location name - treat as destination
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine competency category based on course title
     */
    private function determineCompetencyCategory($courseTitle)
    {
        // Check if it's a destination course first
        if ($this->isDestinationCourse($courseTitle)) {
            return 'Destination Knowledge';
        }

        $title = strtoupper($courseTitle);

        if (str_contains($title, 'CUSTOMER SERVICE') || str_contains($title, 'SERVICE')) {
            return 'Customer Service';
        } elseif (str_contains($title, 'LEADERSHIP') || str_contains($title, 'MANAGEMENT')) {
            return 'Leadership';
        } elseif (str_contains($title, 'COMMUNICATION') || str_contains($title, 'SPEAKING')) {
            return 'Communication';
        } elseif (str_contains($title, 'TECHNICAL') || str_contains($title, 'SYSTEM')) {
            return 'Technical';
        } else {
            return 'General';
        }
    }

    /**
     * Check if two location names are similar (for duplicate detection)
     */
    private function areLocationNamesSimilar($name1, $name2)
    {
        // Remove common prefixes and suffixes
        $cleanName1 = str_replace(['DESTINATION KNOWLEDGE - ', 'TRAINING', 'COURSE', 'PROGRAM'], '', $name1);
        $cleanName2 = str_replace(['DESTINATION KNOWLEDGE - ', 'TRAINING', 'COURSE', 'PROGRAM'], '', $name2);

        $cleanName1 = trim($cleanName1);
        $cleanName2 = trim($cleanName2);

        // Check if core names are the same
        if ($cleanName1 === $cleanName2) {
            return true;
        }

        // Check if one contains the other (for variations like "BAESA" vs "BAESA QUEZON CITY")
        if (str_contains($cleanName1, $cleanName2) || str_contains($cleanName2, $cleanName1)) {
            return true;
        }

        // Check for common location variations
        $variations = [
            ['BAESA', 'BAESA QUEZON CITY', 'BAESA QC'],
            ['QUEZON', 'QUEZON CITY', 'QC'],
            ['BAGUIO', 'BAGUIO CITY'],
            ['CEBU', 'CEBU CITY'],
            ['DAVAO', 'DAVAO CITY']
        ];

        foreach ($variations as $group) {
            if (in_array($cleanName1, $group) && in_array($cleanName2, $group)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get employees from API with error handling and normalization
     */
    private function getEmployeesFromAPI()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get('http://hr4.jetlougetravels-ph.com/api/employees');
            $apiEmployees = $response->successful() ? $response->json() : [];

            if (isset($apiEmployees['data']) && is_array($apiEmployees['data'])) {
                $apiEmployees = $apiEmployees['data'];
            }

            if (is_array($apiEmployees) && !empty($apiEmployees)) {
                return collect($apiEmployees)->map(function($emp) {
                    // Normalize profile picture URL
                    $profilePic = $emp['profile_picture'] ?? null;
                    if ($profilePic && !\Illuminate\Support\Str::startsWith($profilePic, 'http')) {
                         $profilePic = 'http://hr4.jetlougetravels-ph.com/storage/' . ltrim($profilePic, '/');
                    }

                    // Create a pseudo-model object that behaves like Employee model
                    $empObj = new \App\Models\Employee();
                    $empObj->forceFill([
                        'id' => $emp['id'] ?? null,
                        'employee_id' => $emp['employee_id'] ?? $emp['id'] ?? $emp['external_employee_id'] ?? 'N/A',
                        'first_name' => $emp['first_name'] ?? 'Unknown',
                        'last_name' => $emp['last_name'] ?? 'Employee',
                        'profile_picture' => $profilePic,
                        'email' => $emp['email'] ?? null,
                        'department' => $emp['department'] ?? null,
                        'position' => $emp['position'] ?? $emp['role'] ?? null,
                    ]);

                    return $empObj;
                });
            }
        } catch (\Exception $e) {
            Log::error('Error fetching API employees: ' . $e->getMessage());
        }
        return collect();
    }

    /**
     * Determine the source of a training assignment
     */
    private function determineTrainingSource($trainingRecord)
    {
        // Check if remarks contain competency gap assignment indicators
        if ($trainingRecord->remarks &&
            (str_contains($trainingRecord->remarks, 'Auto-assigned from competency gap') ||
             str_contains($trainingRecord->remarks, 'competency gap analysis'))) {
            return 'competency_assigned';
        }

        // Check if assigned by admin
        if ($trainingRecord->assigned_by) {
            return 'admin_assigned';
        }

        return 'manual';
    }

    /**
     * Sync existing training records - create missing competency entries AND sync progress
     */
    public function syncExistingTrainingRecords()
    {
        try {
            $createdCount = 0;
            $syncedCount = 0;
            $errors = [];

            // Test database connection first
            try {
                $recordCount = EmployeeTrainingDashboard::count();
            } catch (\Exception $dbError) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database connection error: ' . $dbError->getMessage()
                ]);
            }

            // Get all training records with courses
            $trainingRecords = EmployeeTrainingDashboard::with('course')->get();

            if ($trainingRecords->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No training records found to sync. Database has ' . $recordCount . ' total records.'
                ]);
            }

            foreach ($trainingRecords as $record) {
                try {
                    if (!$record->course) {
                        $errors[] = "Record {$record->id} has no associated course";
                        continue;
                    }

                    $courseTitle = $record->course->course_title;

                    // Extract competency name (remove common suffixes)
                    $competencyName = str_replace([' Training', ' Course', ' Program'], '', $courseTitle);

                    // Skip if competency name is empty
                    if (empty(trim($competencyName))) {
                        $errors[] = "Empty competency name for course: {$courseTitle}";
                        continue;
                    }

                    // Check if competency exists with comprehensive duplicate checking
                    $competency = null;
                    $existingCompetencies = CompetencyLibrary::all();

                    foreach ($existingCompetencies as $existing) {
                        $existingName = strtoupper($existing->competency_name);
                        $newName = strtoupper($competencyName);

                        // Enhanced duplicate checking for destination knowledge
                        if ($existingName === $newName ||
                            str_contains($existingName, $newName) || // Existing contains new
                            str_contains($newName, $existingName) || // New contains existing
                            // Check without "Destination Knowledge -" prefix
                            str_replace('DESTINATION KNOWLEDGE - ', '', $existingName) === str_replace('DESTINATION KNOWLEDGE - ', '', $newName) ||
                            // Check core location name similarity
                            $this->areLocationNamesSimilar($existingName, $newName)) {
                            $competency = $existing;
                            break;
                        }

                        // SPECIAL CASE: If we're trying to create "BAESA" but "Destination Knowledge - BAESA" exists, use the destination version
                        if ($newName === 'BAESA' && str_contains($existingName, 'DESTINATION KNOWLEDGE - BAESA')) {
                            $competency = $existing;
                            break;
                        }

                        // SPECIAL CASE: If we're trying to create any destination name that already has "Destination Knowledge - [NAME]" version
                        $cleanExistingName = str_replace('DESTINATION KNOWLEDGE - ', '', $existingName);
                        if ($cleanExistingName === $newName && str_contains($existingName, 'DESTINATION KNOWLEDGE - ')) {
                            $competency = $existing;
                            break;
                        }
                    }

                    if (!$competency) {
                        // Determine category
                        $category = 'General';
                        $courseUpper = strtoupper($courseTitle);
                        if (strpos($courseUpper, 'BAESA') !== false || strpos($courseUpper, 'QUEZON') !== false || strpos($courseUpper, 'DESTINATION') !== false) {
                            $category = 'Destination Knowledge';
                        } elseif (strpos($courseUpper, 'CUSTOMER') !== false || strpos($courseUpper, 'SERVICE') !== false) {
                            $category = 'Customer Service';
                        } elseif (strpos($courseUpper, 'COMMUNICATION') !== false) {
                            $category = 'Communication';
                        } elseif (strpos($courseUpper, 'LEADERSHIP') !== false) {
                            $category = 'Leadership';
                        }

                        try {
                            $competency = CompetencyLibrary::create([
                                'competency_name' => $competencyName,
                                'description' => 'Auto-created from existing course: ' . $courseTitle,
                                'category' => $category,
                            ]);
                            $createdCount++;

                            // Log creation
                            \App\Models\ActivityLog::create([
                                'user_id' => Auth::id() ?? 1,
                                'action' => 'create',
                                'module' => 'Competency Library Auto-Create',
                                'description' => "Auto-created competency '{$competencyName}' from existing course - no duplicate found",
                            ]);
                        } catch (\Exception $compError) {
                            $errors[] = "Failed to create competency '{$competencyName}': " . $compError->getMessage();
                            continue;
                        }
                    } else {
                        // Log that existing competency was reused
                        \App\Models\ActivityLog::create([
                            'user_id' => Auth::id() ?? 1,
                            'action' => 'reuse',
                            'module' => 'Competency Library Reuse',
                            'description' => "Reused existing competency '{$competency->competency_name}' for existing course '{$courseTitle}' - prevented duplicate",
                        ]);
                    }

                    // Check if competency profile exists
                    $existingProfile = EmployeeCompetencyProfile::where('employee_id', $record->employee_id)
                        ->where('competency_id', $competency->id)
                        ->first();

                    if (!$existingProfile) {
                        // Get actual progress using same priority as display logic: Exam > Training record
                        $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $record->course_id);
                        $actualProgress = $examProgress > 0 ? $examProgress : ($record->progress ?? 0);

                        // Convert progress to proficiency level (1-5 scale)
                        $proficiencyLevel = 1; // Default minimum
                        if ($actualProgress >= 90) $proficiencyLevel = 5;
                        elseif ($actualProgress >= 70) $proficiencyLevel = 4;
                        elseif ($actualProgress >= 50) $proficiencyLevel = 3;
                        elseif ($actualProgress >= 30) $proficiencyLevel = 2;

                        try {
                            EmployeeCompetencyProfile::create([
                                'employee_id' => $record->employee_id,
                                'competency_id' => $competency->id,
                                'proficiency_level' => $proficiencyLevel,
                                'assessment_date' => now(),
                            ]);
                            $createdCount++;
                        } catch (\Exception $profileError) {
                            $errors[] = "Failed to create profile for employee {$record->employee_id}: " . $profileError->getMessage();
                        }
                    } else {
                        // SYNC EXISTING PROFILE: Update proficiency level based on current training progress
                        $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $record->course_id);
                        $actualProgress = $examProgress > 0 ? $examProgress : ($record->progress ?? 0);

                        // Convert actual progress to proficiency level (1-5 scale)
                        $newProficiencyLevel = 1; // Default minimum
                        if ($actualProgress >= 90) $newProficiencyLevel = 5;
                        elseif ($actualProgress >= 70) $newProficiencyLevel = 4;
                        elseif ($actualProgress >= 50) $newProficiencyLevel = 3;
                        elseif ($actualProgress >= 30) $newProficiencyLevel = 2;
                        elseif ($actualProgress > 0) $newProficiencyLevel = 1;

                        // Only update if the new proficiency level is different
                        if ($existingProfile->proficiency_level != $newProficiencyLevel) {
                            $oldLevel = $existingProfile->proficiency_level;
                            $existingProfile->proficiency_level = $newProficiencyLevel;
                            $existingProfile->assessment_date = now();
                            $existingProfile->save();
                            $syncedCount++;

                            // Log the sync
                            \App\Models\ActivityLog::create([
                                'user_id' => Auth::id() ?? 1,
                                'action' => 'sync',
                                'module' => 'Training-Competency Sync',
                                'description' => "Synced competency proficiency from level {$oldLevel} to {$newProficiencyLevel} ({$actualProgress}% progress, exam: {$examProgress}%) for {$courseTitle}",
                            ]);
                        }
                    }

                    // Check if competency gap exists
                    $existingGap = CompetencyGap::where('employee_id', $record->employee_id)
                        ->where('competency_id', $competency->id)
                        ->first();

                    if (!$existingGap) {
                        $currentLevel = $record->progress ?? 0;
                        $requiredLevel = 100;
                        $gap = max(0, $requiredLevel - $currentLevel);

                        try {
                            CompetencyGap::create([
                                'employee_id' => $record->employee_id,
                                'competency_id' => $competency->id,
                                'required_level' => $requiredLevel,
                                'current_level' => $currentLevel,
                                'gap' => $gap,
                            ]);
                            $createdCount++;
                        } catch (\Exception $gapError) {
                            $errors[] = "Failed to create gap for employee {$record->employee_id}: " . $gapError->getMessage();
                        }
                    } else {
                        // SYNC EXISTING GAP: Update current level based on training progress
                        $currentLevel = max(1, min(5, ceil(($record->progress / 100) * 5)));
                        if ($existingGap->current_level != $currentLevel) {
                            $existingGap->current_level = $currentLevel;
                            $existingGap->gap = max(0, $existingGap->required_level - $currentLevel);
                            $existingGap->save();
                            $syncedCount++;
                        }
                    }

                } catch (\Exception $recordError) {
                    $errors[] = "Error processing record {$record->id}: " . $recordError->getMessage();
                    continue;
                }
            }

            // Log activity
            if ($createdCount > 0 || $syncedCount > 0) {
                ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'sync',
                    'module' => 'Competency Sync',
                    'description' => "Created {$createdCount} missing entries and synced {$syncedCount} existing competency profiles from training records",
                ]);
            }

            $message = "Successfully created {$createdCount} missing entries and synced {$syncedCount} existing competency profiles with training progress.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', array_slice($errors, 0, 3));
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created_count' => $createdCount,
                'synced_count' => $syncedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Sync existing training records error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error syncing training records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix missing training_title values in employee_training_dashboard table
     */
    public function fixMissingTrainingTitles()
    {
        try {
            $updated = 0;
            $fixedUnknown = 0;

            // Find records where training_title is null but course_id exists
            $recordsWithMissingTitles = EmployeeTrainingDashboard::with('course')
                ->whereNull('training_title')
                ->whereNotNull('course_id')
                ->get();

            foreach ($recordsWithMissingTitles as $record) {
                if ($record->course && $record->course->course_title) {
                    $record->training_title = $record->course->course_title;
                    $record->save();
                    $updated++;
                }
            }

            // Also fix records that have "Unknown Course" or similar generic titles
            $unknownRecords = EmployeeTrainingDashboard::with('course')
                ->where(function($query) {
                    $query->where('training_title', 'Unknown Course')
                          ->orWhere('training_title', 'LIKE', '%Unknown%')
                          ->orWhere('training_title', 'LIKE', '%Training Course%')
                          ->orWhere('training_title', 'Course')
                          ->orWhere('training_title', 'N/A');
                })
                ->whereNotNull('course_id')
                ->get();

            foreach ($unknownRecords as $record) {
                if ($record->course && $record->course->course_title) {
                    $record->training_title = $record->course->course_title;
                    $record->save();
                    $fixedUnknown++;
                }
            }

            // Get summary of current training titles
            $titleSummary = EmployeeTrainingDashboard::select('training_title', DB::raw('count(*) as count'))
                ->groupBy('training_title')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'training_title')
                ->toArray();

            Log::info("Fixed training titles", [
                'null_titles_updated' => $updated,
                'unknown_titles_fixed' => $fixedUnknown,
                'title_summary' => $titleSummary
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updated} records with missing titles and fixed {$fixedUnknown} records with 'Unknown Course' titles",
                'records_updated' => $updated,
                'unknown_fixed' => $fixedUnknown,
                'title_summary' => $titleSummary
            ]);

        } catch (\Exception $e) {
            Log::error('Error fixing training titles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fixing training titles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove all "Training Course" generic entries and duplicates
     */
    public function removeTrainingCourseEntries()
    {
        try {
            $deletedCount = 0;

            // STEP 1: Remove all generic "Training Course" entries
            $genericEntries = EmployeeTrainingDashboard::where(function($query) {
                $query->where('training_title', 'LIKE', '%Training Course%')
                      ->orWhere('training_title', 'Unknown Course')
                      ->orWhere('training_title', 'Course')
                      ->orWhere('training_title', 'N/A')
                      ->orWhere('training_title', '')
                      ->orWhereNull('training_title');
            })->get();

            foreach ($genericEntries as $entry) {
                Log::info('Deleting generic Training Course entry:', [
                    'id' => $entry->id,
                    'employee_id' => $entry->employee_id,
                    'training_title' => $entry->training_title,
                    'course_id' => $entry->course_id
                ]);
                $entry->delete();
                $deletedCount++;
            }

            // STEP 2: Remove duplicate entries (same employee + same course/title)
            $allRecords = EmployeeTrainingDashboard::orderBy('created_at', 'asc')->get();
            $seen = [];

            foreach ($allRecords as $record) {
                $key = $record->employee_id . '|' . ($record->course_id ?? 'null') . '|' . ($record->training_title ?? 'null');

                if (isset($seen[$key])) {
                    // This is a duplicate - delete it
                    Log::info('Deleting duplicate entry:', [
                        'id' => $record->id,
                        'employee_id' => $record->employee_id,
                        'training_title' => $record->training_title,
                        'course_id' => $record->course_id,
                        'duplicate_key' => $key
                    ]);
                    $record->delete();
                    $deletedCount++;
                } else {
                    // Mark this combination as seen
                    $seen[$key] = $record->id;
                }
            }

            // STEP 3: Clean up orphaned entries with course_id but no actual course
            $orphanedEntries = EmployeeTrainingDashboard::whereNotNull('course_id')
                ->whereDoesntHave('course')
                ->get();

            foreach ($orphanedEntries as $entry) {
                Log::info('Deleting orphaned entry:', [
                    'id' => $entry->id,
                    'employee_id' => $entry->employee_id,
                    'training_title' => $entry->training_title,
                    'course_id' => $entry->course_id
                ]);
                $entry->delete();
                $deletedCount++;
            }

            // Log the cleanup activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'cleanup',
                'module' => 'Employee Training Dashboard',
                'description' => "Cleaned up {$deletedCount} generic and duplicate training entries.",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$deletedCount} generic and duplicate training entries",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing Training Course entries: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error removing Training Course entries: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fixMissingDates()
    {
        try {
            $updated = 0;
            $debugInfo = [];

            // First check if the table and columns exist
            $tableExists = Schema::hasTable('employee_training_dashboard');
            $debugInfo['table_exists'] = $tableExists;

            if (!$tableExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table employee_training_dashboard does not exist',
                    'debug' => $debugInfo
                ], 500);
            }

            // Check if columns exist
            $columnsExist = [
                'expired_date' => Schema::hasColumn('employee_training_dashboard', 'expired_date'),
                'training_date' => Schema::hasColumn('employee_training_dashboard', 'training_date'),
                'last_accessed' => Schema::hasColumn('employee_training_dashboard', 'last_accessed')
            ];
            $debugInfo['columns_exist'] = $columnsExist;

            // Get all records
            $records = EmployeeTrainingDashboard::with(['employee', 'course'])->get();
            $debugInfo['total_records'] = $records->count();

            foreach ($records as $record) {
                $needsUpdate = false;
                $recordDebug = ['id' => $record->id, 'updates' => []];

                // Fix missing expired_date - set to 90 days from now
                if ($columnsExist['expired_date'] && !$record->expired_date) {
                    $record->expired_date = \Carbon\Carbon::now()->addDays(90);
                    $needsUpdate = true;
                    $recordDebug['updates'][] = 'expired_date';
                }

                // Fix missing training_date - set to now if not set
                if ($columnsExist['training_date'] && !$record->training_date) {
                    $record->training_date = \Carbon\Carbon::now();
                    $needsUpdate = true;
                    $recordDebug['updates'][] = 'training_date';
                }

                // Fix missing last_accessed - set to now
                if ($columnsExist['last_accessed'] && !$record->last_accessed) {
                    $record->last_accessed = \Carbon\Carbon::now();
                    $needsUpdate = true;
                    $recordDebug['updates'][] = 'last_accessed';
                }

                if ($needsUpdate) {
                    $record->save();
                    $updated++;
                    $debugInfo['sample_updates'][] = $recordDebug;
                }
            }

            Log::info("Fixed missing dates in training records", [
                'records_updated' => $updated,
                'total_checked' => $records->count(),
                'debug_info' => $debugInfo
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updated} training records with missing dates",
                'records_updated' => $updated,
                'total_checked' => $records->count(),
                'debug_info' => $debugInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Error fixing missing dates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fixing missing dates: ' . $e->getMessage(),
                'error_details' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function debugTrainingData()
    {
        try {
            $debugData = [];

            // 1. Check total employees
            $totalEmployees = Employee::count();
            $debugData['total_employees'] = $totalEmployees;

            // 2. Check employee training dashboard records
            $dashboardRecords = EmployeeTrainingDashboard::with(['employee', 'course'])->get();
            $debugData['dashboard_records_count'] = $dashboardRecords->count();

            // 3. Get unique employees with training records
            $employeesWithTraining = $dashboardRecords->pluck('employee_id')->unique()->values();
            $debugData['employees_with_training'] = $employeesWithTraining->toArray();
            $debugData['employees_with_training_count'] = $employeesWithTraining->count();

            // 4. Check training requests
            $trainingRequests = \App\Models\TrainingRequest::with(['employee', 'course'])->get();
            $debugData['training_requests_count'] = $trainingRequests->count();
            $debugData['approved_requests_count'] = $trainingRequests->where('status', 'Approved')->count();

            // 5. Get employees with approved requests
            $employeesWithRequests = $trainingRequests->where('status', 'Approved')->pluck('employee_id')->unique()->values();
            $debugData['employees_with_approved_requests'] = $employeesWithRequests->toArray();

            // 6. Sample employee data
            $sampleEmployees = Employee::limit(5)->get(['employee_id', 'first_name', 'last_name']);
            $debugData['sample_employees'] = $sampleEmployees->toArray();

            // 7. Check what the index method actually returns
            $employees = Employee::all();
            $courses = CourseManagement::all();

            // Get regular training records
            $dashboardRecordsForIndex = EmployeeTrainingDashboard::with(['employee', 'course'])
                ->leftJoin('users', 'employee_training_dashboards.assigned_by', '=', 'users.id')
                ->select('employee_training_dashboards.*', 'users.name as assigned_by_name')
                ->orderBy('employee_training_dashboards.created_at', 'desc')
                ->get();

            $debugData['index_dashboard_records_count'] = $dashboardRecordsForIndex->count();
            $debugData['index_employees_count'] = $employees->count();
            $debugData['index_courses_count'] = $courses->count();

            // 8. Check for data issues
            $recordsWithoutEmployee = $dashboardRecordsForIndex->whereNull('employee')->count();
            $recordsWithoutCourse = $dashboardRecordsForIndex->whereNull('course')->count();

            $debugData['records_without_employee'] = $recordsWithoutEmployee;
            $debugData['records_without_course'] = $recordsWithoutCourse;

            // 9. Sample training records with details
            $sampleRecords = $dashboardRecordsForIndex->take(3)->map(function($record) {
                return [
                    'id' => $record->id,
                    'employee_id' => $record->employee_id,
                    'course_id' => $record->course_id,
                    'progress' => $record->progress,
                    'employee_name' => $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'NO EMPLOYEE',
                    'course_title' => $record->course ? $record->course->course_title : 'NO COURSE',
                    'training_title' => $record->training_title ?? 'NULL'
                ];
            });

            $debugData['sample_records'] = $sampleRecords->toArray();

            return response()->json($debugData, 200, [], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Fix employee_training_dashboards table - Create the correct plural table name
     * This method handles the SQLSTATE[42S02] error by ensuring the table exists with proper structure
     */
    public function fixEmployeeTrainingDashboardsTable()
    {
        try {
            $pdo = \DB::connection()->getPdo();
            $results = [];

            // Check if singular table exists
            $checkSingular = $pdo->query("SELECT COUNT(*) FROM information_schema.tables
                                         WHERE table_schema = DATABASE()
                                         AND table_name = 'employee_training_dashboard'");
            $singularExists = $checkSingular->fetchColumn() > 0;

            if ($singularExists) {
                $results[] = "Found singular table 'employee_training_dashboard'. Renaming to plural...";
                $pdo->exec("RENAME TABLE employee_training_dashboard TO employee_training_dashboards");
                $results[] = "✓ Renamed table to employee_training_dashboards";
            } else {
                $results[] = "✓ employee_training_dashboards table already exists";
            }

            // Verify table structure
            $describe = $pdo->query("DESCRIBE employee_training_dashboards");
            $columns = [];
            while ($row = $describe->fetch(\PDO::FETCH_ASSOC)) {
                $columns[] = "{$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']}";
            }

            // Count records
            $count = $pdo->query("SELECT COUNT(*) FROM employee_training_dashboards")->fetchColumn();

            return response()->json([
                'success' => true,
                'message' => 'employee_training_dashboards table is ready!',
                'results' => $results,
                'table_structure' => $columns,
                'record_count' => $count,
                'note' => 'The SQLSTATE[42S02] error should now be resolved.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fixing table: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Consolidate duplicate employee_training_dashboard tables
     */
    public function consolidateDuplicateTables()
    {
        try {
            $result = \App\Models\EmployeeTrainingDashboard::consolidateDuplicateTables();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error consolidating tables: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove training records with "Unknown Course" or invalid course associations
     */
    public function removeUnknownCourseRecords(Request $request)
    {
        try {
            // Password verification for security
            if ($request->has('password')) {
                $user = Auth::guard('admin')->user();
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password. Please enter your correct admin password.'
                    ], 401);
                }
            } else {
                // For backward compatibility, allow without password but log it
                Log::info('Remove unknown course records called without password verification');
            }

            $deletedCount = 0;

            // Find and delete records with "Unknown Course" or similar invalid titles
            $unknownRecords = EmployeeTrainingDashboard::where(function($query) {
                $query->where('training_title', 'LIKE', '%Unknown Course%')
                      ->orWhere('training_title', 'LIKE', '%Unknown%')
                      ->orWhere('training_title', 'Training Course')
                      ->orWhere('training_title', 'Course')
                      ->orWhere('training_title', '')
                      ->orWhereNull('training_title');
            })->get();

            // Also find records where course_id doesn't exist in course_management table
            $invalidCourseRecords = EmployeeTrainingDashboard::whereNotIn('course_id', function($query) {
                $query->select('course_id')->from('course_management');
            })->get();

            // Merge the collections
            $allRecordsToDelete = $unknownRecords->merge($invalidCourseRecords)->unique('id');

            foreach ($allRecordsToDelete as $record) {
                $employeeName = $record->employee
                    ? $record->employee->first_name . ' ' . $record->employee->last_name
                    : 'Unknown Employee';

                Log::info('Deleting unknown course record:', [
                    'id' => $record->id,
                    'employee_id' => $record->employee_id,
                    'employee_name' => $employeeName,
                    'course_id' => $record->course_id,
                    'training_title' => $record->training_title,
                    'progress' => $record->progress
                ]);

                $record->delete();
                $deletedCount++;
            }

            // Log the activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'cleanup',
                'module' => 'Employee Training Dashboard',
                'description' => "Removed {$deletedCount} training records with unknown or invalid course associations",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$deletedCount} training records with unknown or invalid courses.",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing unknown course records: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error removing unknown course records: ' . $e->getMessage()
            ], 500);
        }
    }
}
