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
        // Fetch all local employees to map emails to local profile pictures
        $localEmployees = Employee::all();
        $emailToLocalMap = [];
        foreach ($localEmployees as $localEmp) {
            if ($localEmp->email) {
                $emailToLocalMap[strtolower($localEmp->email)] = $localEmp;
            }
        }

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
                     // Use the employee_id directly from API - no conversion
                     $empId = $emp['employee_id'] ?? $emp['id'] ?? null;
                     
                     $skills = $emp['skills'] ?? null;
                     
                     // Auto-sync employee skills to competency profiles
                     if ($empId && $skills && $skills !== 'N/A') {
                         $this->syncEmployeeSkillsToCompetencies($empId, $skills);
                     }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch/sync employees from API: ' . $e->getMessage());
            // Fallback to local database if API fails
            $employees = Employee::all()->toArray();
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
        
        $paginatedEmployeesData = $employeesCollection->forPage($page, $perPage);
        $paginatedEmployeeIds = $paginatedEmployeesData->map(function($emp) {
            return is_array($emp) 
                ? ($emp['employee_id'] ?? $emp['id'] ?? null)
                : ($emp->employee_id ?? $emp->id ?? null);
        })->filter()->toArray();

        // Sync for these specific employees BEFORE fetching profiles for display
        foreach ($paginatedEmployeesData as $key => $emp) {
            $empId = is_array($emp) 
                ? ($emp['employee_id'] ?? $emp['id'] ?? null)
                : ($emp->employee_id ?? $emp->id ?? null);
            
            $email = is_array($emp) ? ($emp['email'] ?? null) : ($emp->email ?? null);
            
            if ($empId) {
                $this->syncWithTrainingProgress($empId, $email);
            }

            // Resolve Profile Picture for Display
            $empEmail = strtolower($email ?? '');
            $localRef = $emailToLocalMap[$empEmail] ?? null;
            $profilePic = is_array($emp) ? ($emp['profile_picture'] ?? null) : ($emp->profile_picture ?? null);
            
            $finalProfilePic = $profilePic;

            if ($localRef && $localRef->profile_picture) {
                // Local override exists
                $finalProfilePic = $localRef->profile_picture;
            } elseif ($profilePic && strpos($profilePic, 'http') !== 0) {
                // It's a relative path from API, prepend external domain
                $finalProfilePic = 'https://hr4.jetlougetravels-ph.com/storage/' . ltrim($profilePic, '/');
            }

            // Update the item in the collection
            if (is_array($emp)) {
                $emp['profile_picture'] = $finalProfilePic;
                $paginatedEmployeesData->put($key, $emp);
            } else {
                $paginatedEmployeesData[$key]->profile_picture = $finalProfilePic;
            }
        }

        // Fetch profiles ONLY for the paginated employees for better performance
        $profiles = EmployeeCompetencyProfile::with(['employee', 'competency'])
            ->whereIn('employee_id', $paginatedEmployeeIds)
            ->orderBy('id')->get();

        // Create a map for employee data to attach to profiles
        $employeeMap = [];
        foreach ($allEmployees as $employee) {
            $empId = is_array($employee) 
                ? ($employee['employee_id'] ?? $employee['id'] ?? null)
                : ($employee->employee_id ?? $employee->id ?? null);
            if ($empId) $employeeMap[$empId] = $employee;
        }

        // Attach employee data from API to each profile
        foreach ($profiles as $profile) {
            if (isset($employeeMap[$profile->employee_id])) {
                $apiEmployee = $employeeMap[$profile->employee_id];
                $employeeData = new \stdClass();
                $employeeData->employee_id = $profile->employee_id;
                $employeeData->first_name = is_array($apiEmployee) ? ($apiEmployee['first_name'] ?? 'Unknown') : ($apiEmployee->first_name ?? 'Unknown');
                $employeeData->last_name = is_array($apiEmployee) ? ($apiEmployee['last_name'] ?? 'Employee') : ($apiEmployee->last_name ?? 'Employee');
                
                // Improved photolink logic: Check local first then API
                $empEmail = strtolower(is_array($apiEmployee) ? ($apiEmployee['email'] ?? '') : ($apiEmployee->email ?? ''));
                $localRef = $emailToLocalMap[$empEmail] ?? null;
                $profilePic = is_array($apiEmployee) ? ($apiEmployee['profile_picture'] ?? null) : ($apiEmployee->profile_picture ?? null);
                
                if ($localRef && $localRef->profile_picture) {
                    $employeeData->profile_picture = $localRef->profile_picture;
                } elseif ($profilePic && strpos($profilePic, 'http') !== 0) {
                    $employeeData->profile_picture = 'https://hr4.jetlougetravels-ph.com/storage/' . ltrim($profilePic, '/');
                } else {
                    $employeeData->profile_picture = $profilePic;
                }
                $profile->setRelation('employee', $employeeData);
            }
        }

        $employees = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedEmployeesData,
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
     * Robust version that checks multiple sources and handles ID mappings
     */
    public function syncWithTrainingProgress($employeeId = null, $email = null)
    {
        try {
            $updatedCount = 0;
            $query = EmployeeCompetencyProfile::with(['employee', 'competency']);
            
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }
            
            $profiles = $query->get();

            foreach ($profiles as $profile) {
                $competencyName = $profile->competency->competency_name;
                $empId = (string) $profile->employee_id;
                
                // Handle different employee ID formats (e.g., "2" vs "EMP002")
                $possibleEmpIds = [$empId];
                if (is_numeric($empId)) {
                    $possibleEmpIds[] = 'EMP' . str_pad($empId, 3, '0', STR_PAD_LEFT);
                } elseif (preg_match('/EMP(\d+)/i', $empId, $matches)) {
                    $possibleEmpIds[] = (string) (int) $matches[1];
                }

                // Add identity matching via email if available
                $empEmail = $email;
                if (!$empEmail) {
                    $localEmp = \App\Models\Employee::where('employee_id', $empId)->first();
                    $empEmail = $localEmp ? $localEmp->email : null;
                }

                if ($empEmail) {
                    $linkedLocalEmps = \App\Models\Employee::where('email', $empEmail)->pluck('employee_id')->toArray();
                    foreach ($linkedLocalEmps as $lid) {
                        $possibleEmpIds[] = (string) $lid;
                    }
                }

                $possibleEmpIds = array_unique(array_filter($possibleEmpIds));

                $actualProgress = 0;

                // 1. Identify search terms for course titles
                $searchTerms = [
                    $competencyName,
                    $competencyName . ' Training',
                    str_replace(' Training', '', $competencyName),
                    str_replace(' Course', '', $competencyName),
                    str_replace(' Program', '', $competencyName),
                    str_replace(' Skills', '', $competencyName)
                ];
                $searchTerms = array_unique(array_filter($searchTerms));

                // 2. SEARCH SOURCES
                
                // Source A: Destination Knowledge Training (highest priority for location skills)
                if (stripos($competencyName, 'Destination Knowledge') !== false) {
                    $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                    $locationName = trim($locationName);

                    if (!empty($locationName)) {
                        $destinationRecord = \App\Models\DestinationKnowledgeTraining::whereIn('employee_id', $possibleEmpIds)
                            ->where(function($q) use ($locationName, $competencyName) {
                                $q->where('destination_name', 'LIKE', '%' . $locationName . '%')
                                  ->orWhere('destination_name', 'LIKE', '%' . strtoupper($locationName) . '%')
                                  ->orWhere('destination_name', 'LIKE', '%' . strtolower($locationName) . '%')
                                  ->orWhere('destination_name', $competencyName)
                                  ->orWhere('destination_name', $locationName);
                            })
                            ->orderBy('progress', 'desc')
                            ->first();

                        if ($destinationRecord) {
                            $actualProgress = max($actualProgress, $destinationRecord->progress ?? 0);
                        }
                    }
                }

                if ($actualProgress < 100) {
                    // Source B: Check all Course Management matches
                    $matchingCourses = \App\Models\CourseManagement::where(function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->orWhere('course_title', 'LIKE', '%' . $term . '%');
                        }
                    })->get();

                    foreach ($matchingCourses as $course) {
                        // Check Exam Attempts for this course (80% threshold)
                        $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($empId, $course->course_id);
                        // Also try other ID formats for exam attempts
                        foreach ($possibleEmpIds as $pid) {
                            if ($pid !== $empId) {
                                $examProgress = max($examProgress, \App\Models\ExamAttempt::calculateCombinedProgress($pid, $course->course_id));
                            }
                        }
                        
                        // Check Training Dashboard progress
                        $dashboardProgress = \App\Models\EmployeeTrainingDashboard::whereIn('employee_id', $possibleEmpIds)
                            ->where('course_id', $course->course_id)
                            ->max('progress') ?? 0;
                            
                        $actualProgress = max($actualProgress, $examProgress, (float)$dashboardProgress);
                    }
                }

                if ($actualProgress < 100) {
                    // Source C: Check Completed Training table
                    $completedRec = \App\Models\CompletedTraining::whereIn('employee_id', $possibleEmpIds)
                        ->where(function($q) use ($searchTerms) {
                            foreach ($searchTerms as $term) {
                                $q->orWhere('training_title', 'LIKE', '%' . $term . '%');
                            }
                        })->first();
                    if ($completedRec) $actualProgress = 100;
                }

                if ($actualProgress < 100) {
                    // Source D: Check Training Requests
                    $requestRec = \App\Models\TrainingRequest::whereIn('employee_id', $possibleEmpIds)
                        ->where(function($q) use ($searchTerms) {
                            foreach ($searchTerms as $term) {
                                $q->orWhere('training_title', 'LIKE', '%' . $term . '%');
                            }
                        })
                        ->whereIn('status', ['Completed', 'Passed', 'Approved'])
                        ->first();
                    if ($requestRec) {
                        if (in_array($requestRec->status, ['Completed', 'Passed'])) {
                            $actualProgress = 100;
                        } elseif ($requestRec->course_id) {
                            // If approved but not marked completed, check exam/dashboard for THAT course
                            $exP = \App\Models\ExamAttempt::calculateCombinedProgress($empId, $requestRec->course_id);
                            foreach ($possibleEmpIds as $pid) {
                                $exP = max($exP, \App\Models\ExamAttempt::calculateCombinedProgress($pid, $requestRec->course_id));
                            }
                            $dashP = \App\Models\EmployeeTrainingDashboard::whereIn('employee_id', $possibleEmpIds)
                                ->where('course_id', $requestRec->course_id)
                                ->max('progress') ?? 0;
                            $actualProgress = max($actualProgress, $exP, $dashP);
                        }
                    }
                }

                // Final progress cap and level conversion
                $actualProgress = min(100, (float)$actualProgress);
                
                $computedLevel = 0;
                if ($actualProgress >= 100) $computedLevel = 5;
                elseif ($actualProgress >= 80) $computedLevel = 4;
                elseif ($actualProgress >= 60) $computedLevel = 3;
                elseif ($actualProgress >= 40) $computedLevel = 2;
                elseif ($actualProgress > 0) $computedLevel = 1;

                $existingLevel = (int) $profile->proficiency_level;

                // Preservation logic for manual assessments
                $isManuallySet = false;
                if ($existingLevel > 1 && $profile->assessment_date) {
                    if (\Carbon\Carbon::parse($profile->assessment_date)->diffInDays(now()) < 7) {
                        $isManuallySet = true;
                    }
                }

                // Determine final proficiency level
                if ($isManuallySet && $computedLevel < $existingLevel) {
                    $newProficiencyLevel = $existingLevel;
                } else {
                    $newProficiencyLevel = max($existingLevel, (int) $computedLevel);
                }

                // Update if progress improved
                if ($newProficiencyLevel > $profile->proficiency_level) {
                    $oldProficiency = $profile->proficiency_level;
                    $profile->proficiency_level = $newProficiencyLevel;
                    $profile->assessment_date = now();
                    $profile->save();

                    // Update competency gap
                    $competencyGap = \App\Models\CompetencyGap::whereIn('employee_id', $possibleEmpIds)
                        ->where('competency_id', $profile->competency_id)
                        ->first();

                    if ($competencyGap) {
                        $competencyGap->current_level = $newProficiencyLevel;
                        $competencyGap->gap = max(0, $competencyGap->required_level - $newProficiencyLevel);
                        $competencyGap->save();
                    }

                    $updatedCount++;

                    // Log activity
                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'module' => 'Competency Management',
                        'action' => 'sync_individual_robust',
                        'description' => "Updated {$competencyName} proficiency from {$oldProficiency} to {$newProficiencyLevel} based on detected progress {$actualProgress}% for employee ID: {$empId}",
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
                                    // Auto-import removed
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
     * Ensure destination knowledge trainings have corresponding competency profiles
     */
    private function ensureDestinationProfilesExist($employeeId, $email = null)
    {
        try {
            $empId = (string) $employeeId;
            $possibleEmpIds = [$empId];
            
            // Handle different employee ID formats
            if (is_numeric($empId)) {
                $possibleEmpIds[] = 'EMP' . str_pad($empId, 3, '0', STR_PAD_LEFT);
            } elseif (preg_match('/EMP(\d+)/i', $empId, $matches)) {
                $possibleEmpIds[] = (string) (int) $matches[1];
            }

            // Add identity matching via email if available
            $empEmail = $email;
            if (!$empEmail) {
                $localEmp = \App\Models\Employee::where('employee_id', $empId)->first();
                $empEmail = $localEmp ? $localEmp->email : null;
            }

            if ($empEmail) {
                $linkedLocalEmps = \App\Models\Employee::where('email', $empEmail)->pluck('employee_id')->toArray();
                foreach ($linkedLocalEmps as $lid) {
                    $possibleEmpIds[] = (string) $lid;
                }
            }

            $possibleEmpIds = array_unique(array_filter($possibleEmpIds));

            // Get all destination trainings for these IDs
            $destinationRecords = \App\Models\DestinationKnowledgeTraining::whereIn('employee_id', $possibleEmpIds)
                ->get();

            foreach ($destinationRecords as $record) {
                $destinationName = trim(str_replace([' Training', 'Training'], '', $record->destination_name));
                if (empty($destinationName)) continue;

                $competencyName = 'Destination Knowledge - ' . $destinationName;
                
                // Ensure Competency Exists
                $competency = CompetencyLibrary::firstOrCreate(
                    ['competency_name' => $competencyName],
                    [
                        'description' => 'Knowledge and expertise about ' . $destinationName . ' destination',
                        'category' => 'Destination Knowledge'
                    ]
                );

                // Ensure Profile Exists for the PRIMARY employee ID (the one passed to this function)
                $exists = EmployeeCompetencyProfile::where('employee_id', $employeeId)
                    ->where('competency_id', $competency->id)
                    ->exists();

                if (!$exists) {
                    // Calculate initial proficiency
                    $progress = $record->progress ?? 0;
                    $proficiencyLevel = 0;
                    
                    if ($progress >= 90) $proficiencyLevel = 5;
                    elseif ($progress >= 70) $proficiencyLevel = 4;
                    elseif ($progress >= 50) $proficiencyLevel = 3;
                    elseif ($progress >= 30) $proficiencyLevel = 2;
                    elseif ($progress > 0) $proficiencyLevel = 1;

                    EmployeeCompetencyProfile::create([
                        'employee_id' => $employeeId,
                        'competency_id' => $competency->id,
                        'proficiency_level' => $proficiencyLevel,
                        'assessment_date' => $record->updated_at ?? now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in ensureDestinationProfilesExist: ' . $e->getMessage());
        }
    }

    /**
     * Sync employee skills to competency profiles
     * This ensures skills from the employee list are automatically tracked in competencies
     * Uses API employee ID as the source of truth and only creates profiles for actual skills
     */
    private function syncEmployeeSkillsToCompetencies($employeeId, $skills)
    {
        try {
            if (empty($skills) || $skills === 'N/A') {
                return;
            }

            // Parse skills from various formats
            $skillsList = $this->parseSkills($skills);

            // If no valid skills found, delete any auto-created profiles for this employee
            if (empty($skillsList)) {
                // Remove only auto-created profiles (not manually added ones)
                EmployeeCompetencyProfile::where('employee_id', $employeeId)
                    ->where('created_from_api_skills', true)
                    ->delete();
                return;
            }

            // Get all current auto-created profiles for this employee
            $existingAutoProfiles = EmployeeCompetencyProfile::where('employee_id', $employeeId)
                ->where('created_from_api_skills', true)
                ->with('competency')
                ->get()
                ->keyBy(function($profile) {
                    return strtolower(trim($profile->competency->competency_name));
                });

            // Track which skills should have profiles
            $skillsToKeep = [];

            foreach ($skillsList as $skillName) {
                $skillName = trim($skillName);
                
                if (empty($skillName) || strlen($skillName) < 2) {
                    continue;
                }

                $skillKeyName = strtolower($skillName);
                $skillsToKeep[$skillKeyName] = $skillName;

                // Find or create competency in library
                $competency = CompetencyLibrary::firstOrCreate(
                    ['competency_name' => $skillName],
                    [
                        'description' => 'Auto-created from employee API skills',
                        'category' => 'Technical Skills'
                    ]
                );

                // Check if profile already exists
                $existingProfile = EmployeeCompetencyProfile::where('employee_id', $employeeId)
                    ->where('competency_id', $competency->id)
                    ->first();

                if (!$existingProfile) {
                    // Create new competency profile with max proficiency, marked as API-created
                    EmployeeCompetencyProfile::create([
                        'employee_id' => $employeeId,
                        'competency_id' => $competency->id,
                        'proficiency_level' => 5, // Max proficiency for listed skills
                        'assessment_date' => now(),
                        'created_from_api_skills' => true // Mark as auto-created from API
                    ]);

                    \Log::info("Created competency profile for API employee {$employeeId}: {$skillName}");
                }
            }

            // Remove auto-created profiles that are no longer in the API skills list
            foreach ($existingAutoProfiles as $keyName => $profile) {
                if (!isset($skillsToKeep[$keyName])) {
                    $profile->delete();
                    \Log::info("Deleted obsolete competency profile for API employee {$employeeId}: {$profile->competency->competency_name}");
                }
            }

        } catch (\Exception $e) {
            \Log::error("Error syncing skills for employee {$employeeId}: " . $e->getMessage());
        }
    }

    /**
     * Parse skills from various formats (comma-separated, newline-separated, etc.)
     */
    private function parseSkills($skills): array
    {
        if (is_array($skills)) {
            return $skills;
        }

        // Try JSON decode first
        if (is_string($skills) && (str_starts_with($skills, '[') || str_starts_with($skills, '{'))) {
            $decoded = json_decode($skills, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Split by common delimiters
        $skillsList = [];
        
        // Try newline separation first
        if (strpos($skills, "\n") !== false) {
            $skillsList = explode("\n", $skills);
        } 
        // Try comma separation
        elseif (strpos($skills, ',') !== false) {
            $skillsList = explode(',', $skills);
        }
        // Try semicolon separation
        elseif (strpos($skills, ';') !== false) {
            $skillsList = explode(';', $skills);
        }
        // Try pipe separation
        elseif (strpos($skills, '|') !== false) {
            $skillsList = explode('|', $skills);
        }
        // Single skill
        else {
            $skillsList = [$skills];
        }

        // Clean up the skills and remove empty values
        return array_filter(array_map('trim', $skillsList));
    }
}
