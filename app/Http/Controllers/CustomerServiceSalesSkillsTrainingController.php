<?php

namespace App\Http\Controllers;

use App\Models\CustomerServiceSalesSkillsTraining;
use App\Models\Employee;
use App\Models\EmployeeTrainingDashboard; // or CourseManagement if you want courses
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyGap;
use App\Models\CompetencyLibrary;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CustomerServiceSalesSkillsTrainingController extends Controller
{
    public function index()
    {
        // Fetch all local employees to map emails to local profile pictures (consistent with Destination controller)
        $localEmployees = Employee::all();
        $emailToLocalMap = [];
        foreach ($localEmployees as $localEmp) {
            if ($localEmp->email) {
                $emailToLocalMap[strtolower($localEmp->email)] = $localEmp;
            }
        }

        // Fetch employees from API endpoint
        $employeeMap = [];
        try {
            $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            $apiData = $response->successful() ? $response->json() : [];
            $apiEmployees = (isset($apiData['data']) && is_array($apiData['data'])) ? $apiData['data'] : $apiData;

            if (is_array($apiEmployees)) {
                foreach ($apiEmployees as $emp) {
                    $empId = is_array($emp) 
                        ? ($emp['employee_id'] ?? $emp['id'] ?? null)
                        : ($emp->employee_id ?? $emp->id ?? null);
                    
                    if (empty($empId) && is_array($emp) && isset($emp['external_employee_id'])) {
                        $empId = $emp['external_employee_id'];
                    } elseif (empty($empId) && is_object($emp) && isset($emp->external_employee_id)) {
                        $empId = $emp->external_employee_id;
                    }

                    if ($empId) {
                        $empEmail = strtolower(is_array($emp) ? ($emp['email'] ?? '') : ($emp->email ?? ''));
                        $localRef = $emailToLocalMap[$empEmail] ?? null;
                        
                        $profilePic = is_array($emp) ? ($emp['profile_picture'] ?? null) : ($emp->profile_picture ?? null);
                        
                        // Prioritize local photo if it exists
                        if ($localRef && $localRef->profile_picture) {
                            $profilePic = $localRef->profile_picture;
                        } elseif ($profilePic && strpos($profilePic, 'http') !== 0) {
                            $profilePic = 'https://hr4.jetlougetravels-ph.com/storage/' . ltrim($profilePic, '/');
                        }
                        
                        if (is_array($emp)) {
                            $emp['profile_picture'] = $profilePic;
                        } elseif (is_object($emp)) {
                            $emp->profile_picture = $profilePic;
                        }
                        
                        $employeeMap[$empId] = $emp;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch employees from API in CustomerServiceSalesSkillsTraining: ' . $e->getMessage());
        }

        // Removed redundant assignment to $records
        $employees = Employee::all();

        // Filter out destination training courses from the dropdown
        $trainings = EmployeeTrainingDashboard::with('course')
            ->whereHas('course', function($query) {
                $query->where('course_title', 'NOT LIKE', '%ITALY%')
                      ->where('course_title', 'NOT LIKE', '%BESTLINK%')
                      ->where('course_title', 'NOT LIKE', '%BORACAY%')
                      ->where('course_title', 'NOT LIKE', '%destination%')
                      ->where('course_title', 'NOT LIKE', '%Destination%');
            })
            ->orWhere(function($query) {
                $query->whereNull('course_id')
                      ->where('training_title', 'NOT LIKE', '%ITALY%')
                      ->where('training_title', 'NOT LIKE', '%BESTLINK%')
                      ->where('training_title', 'NOT LIKE', '%BORACAY%')
                      ->where('training_title', 'NOT LIKE', '%destination%')
                      ->where('training_title', 'NOT LIKE', '%Destination%');
            })
            ->get();

        // Fetch all gaps and recommend trainings
        $gapsRecords = CompetencyGap::with(['employee', 'competency'])->get();

        // Map API data to gaps for real name display
        foreach ($gapsRecords as $gap) {
            if (isset($employeeMap[$gap->employee_id])) {
                $apiEmp = $employeeMap[$gap->employee_id];
                $employeeData = new \stdClass();
                $employeeData->employee_id = $gap->employee_id;
                $employeeData->first_name = is_array($apiEmp) ? ($apiEmp['first_name'] ?? 'Unknown') : ($apiEmp->first_name ?? 'Unknown');
                $employeeData->last_name = is_array($apiEmp) ? ($apiEmp['last_name'] ?? 'Employee') : ($apiEmp->last_name ?? 'Employee');
                $employeeData->profile_picture = is_array($apiEmp) ? ($apiEmp['profile_picture'] ?? null) : ($apiEmp->profile_picture ?? null);
                $gap->setRelation('employee', $employeeData);
            }
        }

        $gaps = $gapsRecords->map(function($gap) {
                $recommendedTraining = EmployeeTrainingDashboard::whereHas('course', function($q) use ($gap) {
                    $q->where('course_title', 'LIKE', '%' . $gap->competency->competency_name . '%');
                })->first();
                return (object) [
                    'employee' => $gap->employee,
                    'employee_id' => $gap->employee_id,
                    'competency' => $gap->competency,
                    'required_level' => $gap->required_level,
                    'current_level' => $gap->current_level,
                    'gap' => $gap->gap,
                    'recommended_training' => $recommendedTraining ? $recommendedTraining->course : null,
                ];
            });

        // Content Reference: Get ALL competency records to show complete overview
        // Show ALL competencies for complete skills overview (30 records total)
        $skills = CompetencyLibrary::orderBy('category')
            ->orderBy('competency_name')
            ->get();

        Log::info('Total skills count in controller: ' . $skills->count());

        // SYNC WITH MAIN EMPLOYEE TRAINING DASHBOARD DATA
        // Instead of using separate CustomerServiceSalesSkillsTraining table,
        // directly fetch filtered data from the main EmployeeTrainingDashboard system

        // ENHANCED DEDUPLICATION: Use same logic as main EmployeeTrainingDashboardController
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

        // Get filtered training records with deduplication
        $dashboardRecords = EmployeeTrainingDashboard::with(['employee', 'course'])
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
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($record) {
                // Remove duplicates at the database level first
                return $record->employee_id . '_' . $record->course_id . '_' . ($record->training_title ?? '');
            });

        foreach ($dashboardRecords as $record) {
            // Populate employee relation from API if missing in local DB
            if (!$record->employee && isset($employeeMap[$record->employee_id])) {
                $apiEmp = $employeeMap[$record->employee_id];
                $employeeData = new \stdClass();
                $employeeData->employee_id = $record->employee_id;
                $employeeData->first_name = is_array($apiEmp) ? ($apiEmp['first_name'] ?? 'Unknown') : ($apiEmp->first_name ?? 'Unknown');
                $employeeData->last_name = is_array($apiEmp) ? ($apiEmp['last_name'] ?? 'Employee') : ($apiEmp->last_name ?? 'Employee');
                $employeeData->profile_picture = is_array($apiEmp) ? ($apiEmp['profile_picture'] ?? null) : ($apiEmp->profile_picture ?? null);
                $record->setRelation('employee', $employeeData);
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
                continue; // Skip if already seen
            }

            // Enhanced progress sync with multiple strategies
            $examProgress = 0;
            $courseIdToUse = $record->course_id;

            // Strategy 1: Try with current course_id
            if ($courseIdToUse) {
                $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseIdToUse);
            }

            // Strategy 2: If no progress and we have a course title, try to find the correct course
            if ($examProgress == 0 && $record->course && $record->course->course_title) {
                $courseByTitle = \App\Models\CourseManagement::where('course_title', $record->course->course_title)->first();
                if ($courseByTitle && $courseByTitle->course_id != $courseIdToUse) {
                    $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($record->employee_id, $courseByTitle->course_id);
                    if ($examProgress > 0) {
                        $courseIdToUse = $courseByTitle->course_id;
                        // Update the record with the correct course_id
                        $record->course_id = $courseIdToUse;
                    }
                }
            }

            // Strategy 3: Check if there's progress in the main employee training dashboard for this employee-course combination
            if ($examProgress == 0) {
                $mainDashboardRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $record->employee_id)
                    ->where(function($query) use ($record) {
                        if ($record->course && $record->course->course_title) {
                            $query->whereHas('course', function($courseQuery) use ($record) {
                                $courseQuery->where('course_title', $record->course->course_title);
                            });
                        }
                    })
                    ->where('progress', '>', 0)
                    ->first();

                if ($mainDashboardRecord && $mainDashboardRecord->progress > 0) {
                    $examProgress = $mainDashboardRecord->progress;
                    // Sync the course_id if different
                    if ($mainDashboardRecord->course_id != $record->course_id) {
                        $record->course_id = $mainDashboardRecord->course_id;
                        $courseIdToUse = $mainDashboardRecord->course_id;
                    }
                }
            }

            if ($examProgress > 0 && $examProgress != $record->progress) {
                // Update the dashboard record with exam progress
                $record->progress = $examProgress;
                $record->status = $examProgress >= 100 ? 'Completed' : ($examProgress >= 80 ? 'Completed' : 'In Progress');
                $record->save();

                // Also trigger competency profile sync for 100% completion
                if ($examProgress >= 100) {
                    try {
                        $controller = new \App\Http\Controllers\EmployeeTrainingDashboardController();
                        // Use reflection to call the private method or create a public wrapper
                        $method = new \ReflectionMethod($controller, 'syncWithCompetencyProfile');
                        $method->setAccessible(true);
                        $method->invoke($controller, $record);

                        $gapMethod = new \ReflectionMethod($controller, 'syncWithCompetencyGap');
                        $gapMethod->setAccessible(true);
                        $gapMethod->invoke($controller, $record);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Error syncing competency profile: ' . $e->getMessage());
                    }
                }
            }

            // ENHANCED: Fix missing training_title from course relationship
            if ($record->course && !$record->training_title) {
                $record->update(['training_title' => $record->course->course_title]);
            }

            // Add to unique records
            $uniqueRecords->push($record);

            // Track all unique keys for this record to prevent future duplicates
            foreach ($uniqueKeys as $key) {
                $seenCombinations->put($key, 'dashboard');
            }
        }

        // PRIORITY 2: Get employee competency profiles (if EmployeeTrainingDashboard is empty)
        // This bridges the gap when data is stored in EmployeeCompetencyProfile instead
        if ($uniqueRecords->isEmpty()) {
            $competencyProfiles = \App\Models\EmployeeCompetencyProfile::with(['employee', 'competency'])->get();

            foreach ($competencyProfiles as $profile) {
                // Populate employee relation from API if missing in local DB
                if (!$profile->employee && isset($employeeMap[$profile->employee_id])) {
                    $apiEmp = $employeeMap[$profile->employee_id];
                    $employeeData = new \stdClass();
                    $employeeData->employee_id = $profile->employee_id;
                    $employeeData->first_name = is_array($apiEmp) ? ($apiEmp['first_name'] ?? 'Unknown') : ($apiEmp->first_name ?? 'Unknown');
                    $employeeData->last_name = is_array($apiEmp) ? ($apiEmp['last_name'] ?? 'Employee') : ($apiEmp->last_name ?? 'Employee');
                    $employeeData->profile_picture = is_array($apiEmp) ? ($apiEmp['profile_picture'] ?? null) : ($apiEmp->profile_picture ?? null);
                    $profile->setRelation('employee', $employeeData);
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
                $pseudoRecord->training_date = $profile->updated_at;

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
                }
            }
        }

        // Convert to the expected format
        $records = $uniqueRecords->map(function($item) {
            return (object)[
                'employee' => $item->employee,
                'employee_id' => $item->employee_id,
                'training' => (object)[
                    'course_title' => $item->course ? $item->course->course_title : ($item->training_title ?? 'Training'),
                    'title' => $item->training_title ?? ($item->course ? $item->course->course_title : 'Training'),
                    'course' => $item->course,
                ],
                'date_completed' => $item->training_date,
                'id' => $item->id,
                'employee_id' => $item->employee_id,
                'training_id' => $item->course_id,
                'course_id' => $item->course_id, // Add this for proper progress calculation
                'progress' => $item->progress,
                'status' => $item->status,
                'readiness_score' => null, // Will be calculated in the view
            ];
        });

        return view('training_management.customer_service_sales_skills_training', compact('records', 'employees', 'trainings', 'gaps', 'skills'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'training_id' => 'required|integer',
            'date_completed' => 'required|date',
        ]);

        // Create or update record directly in EmployeeTrainingDashboard
        $dashboard = EmployeeTrainingDashboard::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'course_id' => $request->training_id
            ],
            [
                'progress' => 100,
                'status' => 'Completed',
                'training_date' => $request->date_completed,
                'assigned_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Get course info for training_title
        $course = \App\Models\CourseManagement::find($request->training_id);
        if ($course && !$dashboard->training_title) {
            $dashboard->update(['training_title' => $course->course_title]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Customer Service Sales Skills Training',
            'description' => 'Added customer service sales skills training record (ID: ' . $dashboard->id . ') - synced with main dashboard',
        ]);

        return redirect()->route('customer_service_sales_skills_training.index')
            ->with('success', 'Training added successfully and synced with main dashboard.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'training_id' => 'required|integer',
            'date_completed' => 'required|date',
        ]);

        // Update record directly in EmployeeTrainingDashboard
        $dashboard = EmployeeTrainingDashboard::findOrFail($id);
        $dashboard->update([
            'employee_id' => $request->employee_id,
            'course_id' => $request->training_id,
            'training_date' => $request->date_completed,
            'progress' => 100,
            'status' => 'Completed',
            'updated_at' => now()
        ]);

        // Get course info for training_title
        $course = \App\Models\CourseManagement::find($request->training_id);
        if ($course && !$dashboard->training_title) {
            $dashboard->update(['training_title' => $course->course_title]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'module' => 'Customer Service Sales Skills Training',
            'description' => 'Updated customer service sales skills training record (ID: ' . $dashboard->id . ') - synced with main dashboard',
        ]);

        return redirect()->route('customer_service_sales_skills_training.index')
            ->with('success', 'Training updated successfully and synced with main dashboard.');
    }

    public function destroy($id)
    {
        // Delete record directly from EmployeeTrainingDashboard
        $dashboard = EmployeeTrainingDashboard::findOrFail($id);
        $dashboard->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Customer Service Sales Skills Training',
            'description' => 'Deleted customer service sales skills training record (ID: ' . $dashboard->id . ') - synced with main dashboard',
        ]);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Training deleted successfully and synced with main dashboard.']);
        }
        return redirect()->route('customer_service_sales_skills_training.index')
            ->with('success', 'Training deleted successfully and synced with main dashboard.');
    }
}
