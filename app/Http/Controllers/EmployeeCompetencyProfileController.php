<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCompetencyProfile;
use App\Models\Employee;
use App\Models\CompetencyLibrary;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\CourseManagementNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EmployeeCompetencyProfileController extends Controller
{
    public function index()
    {
        // 1. Fetch employees from API endpoint FIRST
        $employees = [];
        try {
            $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            $employees = $response->successful() ? $response->json() : [];

            // Handle if response is wrapped in a data key
            if (isset($employees['data']) && is_array($employees['data'])) {
                $employees = $employees['data'];
            } elseif (!is_array($employees)) {
                $employees = [];
            }

            // 2. Sync skills for each employee found in API
            if (is_array($employees)) {
                foreach ($employees as $emp) {
                     // Determine the ID to use - prioritize IDs that match our local format if possible, 
                     // but broadly support the structure returned by the API
                     $empId = $emp['employee_id'] ?? $emp['id'] ?? null;
                     
                     // Also check for external_employee_id if strictly using that
                     if (empty($empId) && isset($emp['external_employee_id'])) {
                         $empId = $emp['external_employee_id'];
                     }
                     
                     $skills = $emp['skills'] ?? null;
                     
                     if ($empId && !empty($skills)) {
                         $this->syncExternalSkillsString($empId, $skills);
                     }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch/sync employees from API: ' . $e->getMessage());
            // Fallback to local database if API fails
            $employees = Employee::all()->toArray();
        }

        // 3. NOW fetch profiles (including newly created ones from the sync)
        $profiles = EmployeeCompetencyProfile::with(['employee', 'competency'])->orderBy('id')->get();

        // Create a map of employee_id => employee data for quick lookup
        $employeeMap = [];
        foreach ($employees as $employee) {
            $empId = is_array($employee) 
                ? ($employee['external_employee_id'] ?? $employee['employee_id'] ?? $employee['id'] ?? null)
                : ($employee->external_employee_id ?? $employee->employee_id ?? $employee->id ?? null);
            
            if ($empId) {
                $employeeMap[$empId] = $employee;
            }
        }

        // Attach employee data from API to each profile
        foreach ($profiles as $profile) {
            if (isset($employeeMap[$profile->employee_id])) {
                $apiEmployee = $employeeMap[$profile->employee_id];
                
                // Create a temporary object to hold employee data
                $employeeData = new \stdClass();
                $employeeData->employee_id = $profile->employee_id;
                $employeeData->first_name = is_array($apiEmployee) 
                    ? ($apiEmployee['first_name'] ?? 'Unknown')
                    : ($apiEmployee->first_name ?? 'Unknown');
                $employeeData->last_name = is_array($apiEmployee) 
                    ? ($apiEmployee['last_name'] ?? 'Employee')
                    : ($apiEmployee->last_name ?? 'Employee');
                $employeeData->profile_picture = is_array($apiEmployee) 
                    ? ($apiEmployee['profile_picture'] ?? null)
                    : ($apiEmployee->profile_picture ?? null);
                
                // Override the employee relationship with API data
                $profile->setRelation('employee', $employeeData);
            }
        }

        // Exclude all destination training related competencies from the main competency dropdown
        $competencylibrary = CompetencyLibrary::where('category', '!=', 'Destination Knowledge')
            ->where('category', '!=', 'General')
            ->where('competency_name', 'NOT LIKE', '%BESTLINK%')
            ->where('competency_name', 'NOT LIKE', '%ITALY%')
            ->where('competency_name', 'NOT LIKE', '%destination%')
            ->where('description', 'NOT LIKE', '%Auto-created from destination knowledge training%')
            ->get();

        // Get unique destination names from DestinationKnowledgeTraining
        $destinationTrainings = \App\Models\DestinationKnowledgeTraining::select('destination_name')
            ->distinct()
            ->whereNotNull('destination_name')
            ->where('destination_name', '!=', '')
            ->orderBy('destination_name')
            ->get()
            ->pluck('destination_name')
            ->unique()
            ->values();

        // Sort employees by name
        usort($employees, function($a, $b) {
            $aName = (is_array($a) ? ($a['first_name'] ?? '') : ($a->first_name ?? '')) . ' ' . (is_array($a) ? ($a['last_name'] ?? '') : ($a->last_name ?? ''));
            $bName = (is_array($b) ? ($b['first_name'] ?? '') : ($b->first_name ?? '')) . ' ' . (is_array($b) ? ($b['last_name'] ?? '') : ($b->last_name ?? ''));
            return strcasecmp($aName, $bName);
        });

        // Keep a copy of all employees for the dropdown
        $allEmployees = $employees;

        // Manually paginate the employees array
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 9; // Show 9 employees per page
        $employeesCollection = collect($employees);
        
        $employees = new \Illuminate\Pagination\LengthAwarePaginator(
            $employeesCollection->forPage($page, $perPage),
            $employeesCollection->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('competency_management.employee_competency_profiles', compact('profiles', 'employees', 'allEmployees', 'competencylibrary', 'destinationTrainings'));
    }

    /**
     * Get current competency level for employee-competency combination (API endpoint)
     */
    public function getCurrentLevel($employeeId, $competencyId)
    {
        try {
            // Get competency details
            $competency = CompetencyLibrary::find($competencyId);
            if (!$competency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Competency not found'
                ], 404);
            }

            // Check if employee competency profile exists
            $competencyProfile = EmployeeCompetencyProfile::where('employee_id', $employeeId)
                ->where('competency_id', $competencyId)
                ->first();

            $currentLevel = 0;
            $currentPercentage = 0;
            $progressSource = 'none';

            if ($competencyProfile) {
                $competencyName = $competency->competency_name;
                $storedProficiency = ($competencyProfile->proficiency_level / 5) * 100;
                $actualProgress = 0;

                // Check if this is truly manually set (not from destination knowledge sync)
                $isDestinationCompetency = stripos($competencyName, 'Destination Knowledge') !== false;

                if ($isDestinationCompetency) {
                    // For destination competencies, always use training data
                    $isManuallySet = false;
                } else {
                    // For non-destination competencies, use broader manual detection
                    $isManuallySet = $competencyProfile->proficiency_level > 1 ||
                                     ($competencyProfile->proficiency_level == 1 && $competencyProfile->assessment_date &&
                                      \Carbon\Carbon::parse($competencyProfile->assessment_date)->diffInDays(now()) < 30);
                }

                if (stripos($competencyName, 'Destination Knowledge') !== false) {
                    // Extract location name from competency
                    $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                    $locationName = trim($locationName);

                    if (!empty($locationName)) {
                        // Find matching destination knowledge training record
                        $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                            ->where('destination_name', 'LIKE', '%' . $locationName . '%')
                            ->first();

                        if ($destinationRecord) {
                            $destinationNameClean = str_replace([' Training', 'Training'], '', $destinationRecord->destination_name);

                            // Find matching course ID for this destination
                            $matchingCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $destinationNameClean . '%')->first();
                            $courseId = $matchingCourse ? $matchingCourse->course_id : null;

                            // Get exam progress
                            $combinedProgress = 0;
                            if ($courseId) {
                                $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employeeId, $courseId);
                            }

                            // Fall back to training dashboard progress if no exam data
                            if ($combinedProgress == 0) {
                                $trainingProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                                    ->where('course_id', $courseId)
                                    ->value('progress');
                                $combinedProgress = $trainingProgress ?? $destinationRecord->progress ?? 0;
                            }

                            $actualProgress = min(100, round($combinedProgress));
                            $progressSource = 'destination';
                        }
                    }
                } else {
                    // For non-destination competencies, use employee training dashboard
                    $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)->get();

                    foreach ($trainingRecords as $record) {
                        $courseTitle = $record->training_title ?? '';

                        // General competency matching
                        $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                        $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                        if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                            // Get progress from this training record
                            $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employeeId, $record->course_id);
                            $trainingProgress = $record->progress ?? 0;

                            // Priority: Exam progress > Training record progress
                            $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                            $progressSource = 'training';
                            break;
                        }
                    }
                }

                // Use manual proficiency level if manually set, otherwise use training data
                if ($isManuallySet) {
                    $currentPercentage = $storedProficiency;
                    $progressSource = 'manual';
                } else {
                    $currentPercentage = $actualProgress > 0 ? $actualProgress : $storedProficiency;
                }

                // Convert percentage to level (1-5)
                if ($currentPercentage >= 90) $currentLevel = 5;
                elseif ($currentPercentage >= 70) $currentLevel = 4;
                elseif ($currentPercentage >= 50) $currentLevel = 3;
                elseif ($currentPercentage >= 30) $currentLevel = 2;
                elseif ($currentPercentage > 0) $currentLevel = 1;
                else $currentLevel = 0;

                if ($actualProgress == 0 && $progressSource !== 'manual') {
                    $progressSource = 'profile';
                }
            }

            return response()->json([
                'success' => true,
                'current_level' => $currentLevel,
                'percentage' => round($currentPercentage),
                'source' => $progressSource,
                'has_profile' => $competencyProfile !== null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching competency level: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get training progress for employee-competency combination
     */
    public function getTrainingProgress(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $competencyId = $request->input('competency_id');

        if (!$employeeId || !$competencyId) {
            return response()->json(['progress' => null, 'proficiency_level' => null]);
        }

        // Get competency name
        $competency = CompetencyLibrary::find($competencyId);
        if (!$competency) {
            return response()->json(['progress' => null, 'proficiency_level' => null]);
        }

        // Find matching training record by competency name
        $competencyName = $competency->competency_name;

        // Look for training records that match the competency name
        $trainingRecord = \App\Models\EmployeeTrainingDashboard::with('course')
            ->where('employee_id', $employeeId)
            ->whereHas('course', function($query) use ($competencyName) {
                // Remove common suffixes and match
                $searchTerms = [
                    $competencyName,
                    $competencyName . ' Training',
                    str_replace(' Training', '', $competencyName),
                    str_replace(' Course', '', $competencyName),
                    str_replace(' Program', '', $competencyName)
                ];

                $query->where(function($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $q->orWhere('course_title', 'LIKE', '%' . $term . '%');
                    }
                });
            })
            ->orderBy('updated_at', 'desc')
            ->first();

        $progress = $trainingRecord ? $trainingRecord->progress : null;

        // Convert progress to proficiency level (0-100% -> 0-5 scale) - start from 0%
        $proficiencyLevel = null;
        if ($progress !== null) {
            if ($progress >= 90) $proficiencyLevel = 5;
            elseif ($progress >= 70) $proficiencyLevel = 4;
            elseif ($progress >= 50) $proficiencyLevel = 3;
            elseif ($progress >= 30) $proficiencyLevel = 2;
            elseif ($progress > 0) $proficiencyLevel = 1;
            else $proficiencyLevel = 0; // 0% progress = 0 proficiency level
        }

        return response()->json([
            'progress' => $progress,
            'proficiency_level' => $proficiencyLevel,
            'course_title' => $trainingRecord ? $trainingRecord->course->course_title : null,
            'last_accessed' => $trainingRecord ? $trainingRecord->last_accessed : null
        ]);
    }

    public function store(Request $request)
    {
        // Handle destination training selections
        $competencyId = $request->input('competency_id');

        if (substr($competencyId, 0, 12) === 'destination_') {
            // Extract destination name from the destinations array
            $destinationIndex = (int) str_replace('destination_', '', $competencyId);
            $destinationTrainings = \App\Models\DestinationKnowledgeTraining::select('destination_name')
                ->distinct()
                ->whereNotNull('destination_name')
                ->where('destination_name', '!=', '')
                ->orderBy('destination_name')
                ->get()
                ->pluck('destination_name')
                ->unique()
                ->values();

            if (!isset($destinationTrainings[$destinationIndex])) {
                return redirect()->route('employee_competency_profiles.index')
                    ->with('error', 'Invalid destination selection.');
            }

            $destinationName = $destinationTrainings[$destinationIndex];

            // Create or find competency for this destination
            $competencyName = 'Destination Knowledge - ' . $destinationName;
            $competency = CompetencyLibrary::firstOrCreate(
                ['competency_name' => $competencyName],
                [
                    'description' => 'Knowledge and expertise about ' . $destinationName . ' destination',
                    'category' => 'Destination Knowledge'
                ]
            );

            $competencyId = $competency->id;
        }

        $validated = $request->validate([
            'employee_id' => 'required|string',
            'proficiency_level' => 'required|integer|between:1,5',
            'assessment_date' => 'required|date',
        ]);

        $validated['competency_id'] = $competencyId;

        // Check for existing competency profile to prevent duplicates
        $existingProfile = EmployeeCompetencyProfile::where('employee_id', $validated['employee_id'])
            ->where('competency_id', $validated['competency_id'])
            ->first();

        if ($existingProfile) {
            return redirect()->route('employee_competency_profiles.index')
                ->with('error', 'This employee already has a competency profile for this skill. Please edit the existing one instead.');
        }

        $profile = EmployeeCompetencyProfile::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'Competency Management',
            'action' => 'create',
            'description' => 'Added employee competency profile for employee ID: ' . $profile->employee_id,
            'model_type' => EmployeeCompetencyProfile::class,
            'model_id' => $profile->id,
        ]);
        return redirect()->route('employee_competency_profiles.index')->with('success', 'Profile created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Handle destination training selections
        $competencyId = $request->input('competency_id');

        if (substr($competencyId, 0, 12) === 'destination_') {
            // Extract destination name from the destinations array
            $destinationIndex = (int) str_replace('destination_', '', $competencyId);
            $destinationTrainings = \App\Models\DestinationKnowledgeTraining::select('destination_name')
                ->distinct()
                ->whereNotNull('destination_name')
                ->where('destination_name', '!=', '')
                ->orderBy('destination_name')
                ->get()
                ->pluck('destination_name')
                ->unique()
                ->values();

            if (!isset($destinationTrainings[$destinationIndex])) {
                return redirect()->route('employee_competency_profiles.index')
                    ->with('error', 'Invalid destination selection.');
            }

            $destinationName = $destinationTrainings[$destinationIndex];

            // Create or find competency for this destination
            $competencyName = 'Destination Knowledge - ' . $destinationName;
            $competency = CompetencyLibrary::firstOrCreate(
                ['competency_name' => $competencyName],
                [
                    'description' => 'Knowledge and expertise about ' . $destinationName . ' destination',
                    'category' => 'Destination Knowledge'
                ]
            );

            $competencyId = $competency->id;
        }

        $validated = $request->validate([
            'employee_id' => 'required|string',
            'proficiency_level' => 'required|integer|between:1,5',
            'assessment_date' => 'required|date',
        ]);

        $validated['competency_id'] = $competencyId;

        $profile = EmployeeCompetencyProfile::findOrFail($id);
        $profile->update($validated);
        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'Competency Management',
            'action' => 'update',
            'description' => 'Updated employee competency profile for employee ID: ' . $profile->employee_id,
            'model_type' => EmployeeCompetencyProfile::class,
            'model_id' => $profile->id,
        ]);
        return redirect()->route('employee_competency_profiles.index')->with('success', 'Profile updated successfully!');
    }

    public function destroy($id)
    {
        $profile = EmployeeCompetencyProfile::findOrFail($id);
        $employeeId = $profile->employee_id;
        $profile->delete();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'Competency Management',
            'action' => 'delete',
            'description' => 'Deleted employee competency profile for employee ID: ' . $employeeId,
            'model_type' => EmployeeCompetencyProfile::class,
            'model_id' => $id,
        ]);
        return redirect()->route('employee_competency_profiles.index')->with('success', 'Profile deleted successfully!');
    }

    /**
     * Sync competency profiles with training progress - individual progress tracking per employee
     */
    public function syncWithTrainingProgress()
    {
        try {
            $updatedCount = 0;
            $profiles = EmployeeCompetencyProfile::with(['employee', 'competency'])->get();

            foreach ($profiles as $profile) {
                $competencyName = $profile->competency->competency_name;
                $actualProgress = 0;
                $examProgress = 0;

                // Check destination knowledge training first for this specific employee
                if (stripos($competencyName, 'Destination Knowledge') !== false) {
                    $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                    $locationName = trim($locationName);

                    if (!empty($locationName)) {
                        $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $profile->employee_id)
                            ->where(function($query) use ($locationName, $competencyName) {
                                $query->where('destination_name', 'LIKE', '%' . $locationName . '%')
                                      ->orWhere('destination_name', 'LIKE', '%' . strtoupper($locationName) . '%')
                                      ->orWhere('destination_name', 'LIKE', '%' . strtolower($locationName) . '%')
                                      ->orWhere('destination_name', $competencyName)
                                      ->orWhere('destination_name', $locationName);
                            })
                            ->orderBy('progress', 'desc') // Get highest progress first
                            ->first();

                        if ($destinationRecord) {
                            $actualProgress = $destinationRecord->progress ?? 0;
                        }
                    }
                }

                // Check employee training dashboard for this specific employee
                if ($actualProgress == 0) {
                    $trainingRecord = \App\Models\EmployeeTrainingDashboard::with('course')
                        ->where('employee_id', $profile->employee_id)
                        ->whereHas('course', function($query) use ($competencyName) {
                            $searchTerms = [
                                $competencyName,
                                $competencyName . ' Training',
                                str_replace(' Training', '', $competencyName),
                                str_replace(' Course', '', $competencyName),
                                str_replace(' Program', '', $competencyName)
                            ];

                            $query->where(function($q) use ($searchTerms) {
                                foreach ($searchTerms as $term) {
                                    $q->orWhere('course_title', 'LIKE', '%' . $term . '%');
                                }
                            });
                        })
                        ->orderBy('updated_at', 'desc')
                        ->first();

                    if ($trainingRecord) {
                        $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($profile->employee_id, $trainingRecord->course_id);
                        $trainingProgress = $trainingRecord->progress ?? 0;
                        $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                    }
                }

                // Convert individual progress to proficiency level for THIS employee only
                // Do not downgrade to 0. Keep at least current level or minimum 1 for existing profiles.
                $computedLevel = null;
                if ($actualProgress >= 90) $computedLevel = 5;
                elseif ($actualProgress >= 70) $computedLevel = 4;
                elseif ($actualProgress >= 50) $computedLevel = 3;
                elseif ($actualProgress >= 30) $computedLevel = 2;
                elseif ($actualProgress > 0) $computedLevel = 1;

                // Enhanced logic to prevent resetting proficiency level to 0%
                // ALWAYS preserve manually set values - they should be FIXED and never reset
                $existingLevel = (int) $profile->proficiency_level;

                // Check if this is a manually set proficiency level that should be preserved
                $isManuallySet = false;

                // For destination competencies, check if manually set (level > 1 or recently assessed)
                if (stripos($competencyName, 'Destination Knowledge') !== false) {
                    $isManuallySet = $existingLevel > 1 ||
                                   ($existingLevel >= 1 && $profile->assessment_date &&
                                    \Carbon\Carbon::parse($profile->assessment_date)->diffInDays(now()) < 30);
                } else {
                    // For non-destination competencies, be more conservative about manual detection
                    $isManuallySet = $existingLevel > 1 ||
                                   ($existingLevel >= 1 && $profile->assessment_date &&
                                    \Carbon\Carbon::parse($profile->assessment_date)->diffInDays(now()) < 7);
                }

                if ($isManuallySet) {
                    // PRESERVE manually set proficiency level - NEVER change it
                    $newProficiencyLevel = $existingLevel;
                } else {
                    // Only update if not manually set
                    if (is_null($computedLevel)) {
                        // No training progress found, keep existing level but ensure minimum 1
                        $newProficiencyLevel = max(1, $existingLevel);
                    } else {
                        // Training progress found, use computed level but ensure minimum 1
                        $newProficiencyLevel = max(1, (int) $computedLevel);
                    }
                }

                // Only update if proficiency level changed for this individual employee
                if ($newProficiencyLevel != $profile->proficiency_level) {
                    $oldProficiency = $profile->proficiency_level;
                    $profile->proficiency_level = $newProficiencyLevel;
                    $profile->assessment_date = now();
                    $profile->save();

                    // Also update competency gap if exists
                    $competencyGap = \App\Models\CompetencyGap::where('employee_id', $profile->employee_id)
                        ->where('competency_id', $profile->competency_id)
                        ->first();

                    if ($competencyGap) {
                        $competencyGap->current_level = $newProficiencyLevel;
                        $competencyGap->gap = max(0, $competencyGap->required_level - $newProficiencyLevel);
                        $competencyGap->save();
                    }

                    $updatedCount++;

                    // Log the activity
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'module' => 'Competency Management',
                        'action' => 'sync_individual',
                        'description' => "Updated {$competencyName} proficiency from {$oldProficiency} to {$newProficiencyLevel} based on individual progress {$actualProgress}% (exam: {$examProgress}%) for employee ID: {$profile->employee_id}",
                        'model_type' => EmployeeCompetencyProfile::class,
                        'model_id' => $profile->id,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} competency profiles based on individual employee progress.",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing competency profiles: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Convert proficiency level (1-5) to progress percentage (0-100%)
     */
    private function convertProficiencyToProgress($proficiencyLevel)
    {
        $levelMap = [
            0 => 0,   // No proficiency
            1 => 20,  // Beginner
            2 => 40,  // Developing
            3 => 60,  // Proficient
            4 => 80,  // Advanced
            5 => 100  // Expert
        ];

        return $levelMap[$proficiencyLevel] ?? 0; // Default to 0% instead of 20%
    }

    private function extractCompetencyName($trainingTitle)
    {
        // Remove common training suffixes
        $competencyName = preg_replace('/\s*(Training|Course|Program|Certification)$/i', '', $trainingTitle);

        // Special handling for destination knowledge - check for Baesa, Quezon, or other destination names
        if (stripos($trainingTitle, 'BAESA') !== false ||
            stripos($trainingTitle, 'QUEZON') !== false ||
            stripos($trainingTitle, 'Baesa') !== false ||
            stripos($trainingTitle, 'Quezon') !== false ||
            stripos($trainingTitle, 'destination') !== false) {

            // Extract the specific destination name
            if (stripos($trainingTitle, 'Baesa') !== false && stripos($trainingTitle, 'Quezon') !== false) {
                return "Destination Knowledge - Baesa Quezon City";
            } elseif (stripos($trainingTitle, 'Baesa') !== false) {
                return "Destination Knowledge - Baesa";
            } elseif (stripos($trainingTitle, 'Quezon') !== false) {
                return "Destination Knowledge - Quezon City";
            } else {
                return "Destination Knowledge - " . ucwords(strtolower($competencyName));
            }
        }

        return ucwords(strtolower($competencyName));
    }

    private function categorizeCompetency($competencyName)
    {
        $name = strtolower($competencyName);

        if (strpos($name, 'destination') !== false || strpos($name, 'baesa') !== false || strpos($name, 'quezon') !== false) {
            return 'Destination Knowledge';
        } elseif (strpos($name, 'service') !== false || strpos($name, 'customer') !== false) {
            return 'Customer Service';
        } elseif (strpos($name, 'leadership') !== false || strpos($name, 'management') !== false) {
            return 'Leadership';
        } elseif (strpos($name, 'communication') !== false || strpos($name, 'speaking') !== false) {
            return 'Communication';
        } elseif (strpos($name, 'technical') !== false || strpos($name, 'system') !== false) {
            return 'Technical';
        }

        return 'General';
    }

    /**
            'Communication Skills',
            'Problem-Solving',
            'Time Management',
            'Teamwork',
            'Leadership'
        ];

        $mediumPrioritySkills = [
            'Sales Skills',
            'Product Knowledge',
            'Cultural Awareness',
            'Technology Proficiency'
        ];

        $name = strtolower($competencyName);

        foreach ($highPrioritySkills as $skill) {
            if (stripos($name, strtolower($skill)) !== false) {
                return 'High';
            }
        }

        foreach ($mediumPrioritySkills as $skill) {
            if (stripos($name, strtolower($skill)) !== false) {
                return 'Medium';
            }
        }

        return 'Low';
    }

    /**
     * Initialize basic skills for employee with no competency profiles
     */
    public function initializeBasicSkills($employeeId)
    {
        try {
            $employee = Employee::where('employee_id', $employeeId)->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }

            // Check if employee already has competency profiles
            $existingCount = EmployeeCompetencyProfile::where('employee_id', $employeeId)->count();

            if ($existingCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already has competency profiles. Use skill gap detection instead.'
                ], 400);
            }

            // Get basic competencies to initialize (common skills for all employees)
            $basicCompetencies = CompetencyLibrary::whereIn('competency_name', [
                'Communication Skills',
                'Customer Service Excellence',
                'Problem-Solving',
                'Time Management',
                'Teamwork',
                'Professional Ethics',
                'Adaptability',
                'Basic Computer Skills'
            ])->get();

            // If no basic competencies found, get first 8 competencies from library
            if ($basicCompetencies->count() === 0) {
                $basicCompetencies = CompetencyLibrary::where('category', '!=', 'Destination Knowledge')
                    ->limit(8)
                    ->get();
            }

            $createdProfiles = [];
            $currentDate = now();

            foreach ($basicCompetencies as $competency) {
                $profile = EmployeeCompetencyProfile::create([
                    'employee_id' => $employeeId,
                    'competency_id' => $competency->id,
                    'proficiency_level' => 1, // Start with beginner level
                    'assessment_date' => $currentDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $createdProfiles[] = [
                    'id' => $profile->id,
                    'competency_name' => $competency->competency_name,
                    'category' => $competency->category,
                    'proficiency_level' => 1
                ];

                // Log the activity
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::guard('admin')->id(),
                    'action' => 'CREATE',
                    'module' => 'Employee Competency Profiles',
                    'description' => "Auto-initialized basic skill: {$competency->competency_name} for employee {$employee->first_name} {$employee->last_name}",
                    'model' => 'EmployeeCompetencyProfile',
                    'model_id' => $profile->id,
                    'changes' => json_encode([
                        'employee_id' => $employeeId,
                        'competency_id' => $competency->id,
                        'proficiency_level' => 1,
                        'note' => 'Auto-initialized basic skill'
                    ]),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Basic skills initialized successfully.',
                'created_profiles' => $createdProfiles,
                'total_created' => count($createdProfiles),
                'employee' => [
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->first_name . ' ' . $employee->last_name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error initializing basic skills: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee's existing skills/competency profiles
     */
    public function getEmployeeSkills($employeeId)
    {
        try {
            $employee = Employee::where('employee_id', $employeeId)->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }

            // Sync skills from external API
            try {
                $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
                if ($response->successful()) {
                    $apiData = $response->json();
                    $employeesList = isset($apiData['data']) ? $apiData['data'] : $apiData;
                    
                    if (is_array($employeesList)) {
                        foreach ($employeesList as $apiEmp) {
                            $apiId = $apiEmp['employee_id'] ?? $apiEmp['id'] ?? null;
                            // Match loosely to ensure we find the employee
                            if ((string)$apiId === (string)$employeeId || 
                                ($apiEmp['external_employee_id'] ?? '') === (string)$employeeId) {
                                
                                if (!empty($apiEmp['skills'])) {
                                    $this->syncExternalSkillsString($employeeId, $apiEmp['skills']);
                                }
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to sync external skills: ' . $e->getMessage());
            }

            $skills = EmployeeCompetencyProfile::where('employee_id', $employeeId)
                ->with(['competency:id,competency_name,category,description'])
                ->orderBy('assessment_date', 'desc')
                ->get()
                ->map(function ($profile) {
                    return [
                        'id' => $profile->id,
                        'competency_id' => $profile->competency_id,
                        'competency_name' => $profile->competency->competency_name ?? 'Unknown Competency',
                        'category' => $profile->competency->category ?? null,
                        'description' => $profile->competency->description ?? null,
                        'proficiency_level' => $profile->proficiency_level,
                        'assessment_date' => $profile->assessment_date,
                        'created_at' => $profile->created_at,
                        'updated_at' => $profile->updated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'skills' => $skills,
                'total_skills' => $skills->count(),
                'employee' => [
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'position' => $employee->position
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employee skills: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify course management about employee competency profile status
     */
    public function notifyCourseManagement(Request $request, $id)
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ], 403);
        }

        try {
            $profile = EmployeeCompetencyProfile::with(['employee', 'competency'])->findOrFail($id);

            // Check if competency is already approved and active (proficiency level 5)
            if ($profile->proficiency_level >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'This competency is already approved and active. Notification not needed.'
                ], 400);
            }

            $competency = $profile->competency;
            $employee = $profile->employee;

            // Find active courses that use this competency
            $activeCourses = \App\Models\CourseManagement::where('status', 'Active')
                ->where(function($query) use ($competency) {
                    $query->where('course_title', 'LIKE', '%' . $competency->competency_name . '%')
                          ->orWhere('description', 'LIKE', '%' . $competency->competency_name . '%');
                })
                ->get();

            // Create notification for course management
            $notification = CourseManagementNotification::create([
                'competency_id' => $competency->id,
                'competency_name' => $competency->competency_name,
                'message' => 'Employee competency profile update: ' . $employee->first_name . ' ' . $employee->last_name .
                           ' has proficiency level ' . $profile->proficiency_level . '/5 in "' . $competency->competency_name . '". ' .
                           ($activeCourses->count() > 0 ?
                           'Found ' . $activeCourses->count() . ' active courses that may be affected.' :
                           'No active courses found using this competency.'),
                'notification_type' => 'employee_competency_update',
                'created_by' => Auth::guard('admin')->id(),
                'employee_id' => $employee->employee_id,
                'proficiency_level' => $profile->proficiency_level,
            ]);

            // Log the notification action
            ActivityLog::createLog([
                'module' => 'Employee Competency Profile',
                'action' => 'notification',
                'description' => 'Sent notification to course management about employee competency: ' .
                               $employee->first_name . ' ' . $employee->last_name . ' - ' . $competency->competency_name .
                               ' (Level ' . $profile->proficiency_level . '/5, ' . $activeCourses->count() . ' active courses affected)',
                'model_type' => EmployeeCompetencyProfile::class,
                'model_id' => $profile->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent to course management successfully. ' .
                           ($activeCourses->count() > 0 ?
                           $activeCourses->count() . ' active courses may be affected.' :
                           'No active courses found using this competency.'),
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'competency' => $competency->competency_name,
                'proficiency_level' => $profile->proficiency_level,
                'active_courses_count' => $activeCourses->count(),
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Parse and sync skills string from external source
     */
    private function syncExternalSkillsString($employeeId, $skillsString)
    {
        if (empty($skillsString)) return;

        // Split by newlines, commas, or semicolons
        $skills = preg_split('/[\r\n,;]+/', $skillsString, -1, PREG_SPLIT_NO_EMPTY);
        $currentDate = now();

        foreach ($skills as $skillName) {
            $skillName = trim($skillName);
            if (empty($skillName)) continue;

            // Clean up skill name
            $skillName = ucwords(strtolower($skillName));

            // Find or create competency in library
            $competency = CompetencyLibrary::firstOrCreate(
                ['competency_name' => $skillName],
                [
                    'description' => 'Auto-imported skill from employee profile',
                    'category' => 'General'
                ]
            );

            // Check if profile exists, if not create it
            $profile = EmployeeCompetencyProfile::where('employee_id', $employeeId)
                ->where('competency_id', $competency->id)
                ->first();

            if (!$profile) {
                EmployeeCompetencyProfile::create([
                    'employee_id' => $employeeId,
                    'competency_id' => $competency->id,
                    'proficiency_level' => 5, // Set to Expert/100% as this is an acquired skill
                    'assessment_date' => $currentDate
                ]);

                // Log the creation
                ActivityLog::createLog([
                    'module' => 'Competency Management',
                    'action' => 'import',
                    'description' => "Auto-imported skill '{$skillName}' at 100% proficiency for employee ID: {$employeeId}",
                    'model_type' => EmployeeCompetencyProfile::class,
                    'model_id' => 0, // Placeholder
                ]);
            } else {
                // If profile exists, ensure it is updated to Level 5 (100%) since the employee possesses this skill
                if ($profile->proficiency_level < 5) {
                    $profile->update([
                        'proficiency_level' => 5,
                        'assessment_date' => $currentDate
                    ]);
                }
            }
        }
    }
}
