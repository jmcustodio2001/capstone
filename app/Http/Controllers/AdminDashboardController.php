<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\CourseManagement;
use App\Models\CompetencyGap;
use App\Models\PotentialSuccessor;
use App\Models\EmployeeTrainingDashboard;
use App\Models\EmployeeCompetencyProfile;
use App\Models\TrainingRequest;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics - fetch from API same as employee list
        $totalEmployees = 0;
        try {
            $response = \Illuminate\Support\Facades\Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            if ($response->successful()) {
                $apiData = $response->json();
                // Handle { success: true, data: [...] } structure
                if (isset($apiData['data']) && is_array($apiData['data'])) {
                    $totalEmployees = count($apiData['data']);
                } elseif (is_array($apiData)) {
                    $totalEmployees = count($apiData);
                }
            } else {
                // Fallback to local database if API fails
                $totalEmployees = Employee::count();
            }
        } catch (\Exception $e) {
            // Fallback to local database if API call fails
            $totalEmployees = Employee::count();
        }

        $totalUsers = User::count();
        $activeCourses = CourseManagement::where('status', 'active')->count();
        $trainingSessions = EmployeeTrainingDashboard::count();
        $employeeUsers = User::where('role', 'employee')->count();
        $successionPlans = PotentialSuccessor::count();
        $competencies = \App\Models\CompetencyLibrary::count();
        $attendanceLogs = \App\Models\AttendanceTimeLog::count();
        // Count completed trainings from both sources
        $completedFromDashboard = EmployeeTrainingDashboard::where('status', 'Completed')->count();
        $completedFromEmployees = \App\Models\CompletedTraining::count();
        $completedTrainings = $completedFromDashboard + $completedFromEmployees;
        $activeTrainings = EmployeeTrainingDashboard::whereIn('status', ['Ongoing', 'In Progress', 'Scheduled'])->count();
        $pendingGapAnalyses = CompetencyGap::where('gap', '>', 0)->count();
        $trainingRequestsCount = TrainingRequest::count();


        // Get recent activities
        $recentEmployees = Employee::latest()->take(5)->get();
        $recentGapAnalyses = CompetencyGap::with('employee')
            ->latest()
            ->take(5)
            ->get();
        // Get recent trainings - SINGLE SOURCE with deduplication to prevent duplicates
        $recentTrainings = collect();

        // Use ONLY EmployeeTrainingDashboard as single source of truth with deduplication
        $dashboardTrainings = EmployeeTrainingDashboard::with(['employee', 'course'])
            ->whereNotNull('training_date')
            ->latest('training_date')
            ->take(20) // Get more records to allow for deduplication
            ->get();

        // Apply deduplication based on employee-course combination
        $uniqueTrainings = collect();
        $seenCombinations = [];

        foreach ($dashboardTrainings as $training) {
            // Create multiple unique keys to catch all possible duplicates
            $uniqueKeys = [];

            // Primary key: employee_id + course_id (if exists)
            if ($training->course_id) {
                $uniqueKeys[] = $training->employee_id . '_course_' . $training->course_id;
            }

            // Secondary key: employee_id + training_title (if exists)
            if ($training->training_title) {
                $uniqueKeys[] = $training->employee_id . '_training_' . md5(strtolower(trim($training->training_title)));
            }

            // Tertiary key: employee_id + course_title from relationship (if exists)
            if ($training->course && $training->course->course_title) {
                $uniqueKeys[] = $training->employee_id . '_coursetitle_' . md5(strtolower(trim($training->course->course_title)));
            }

            // Check if any of the unique keys have been seen before
            $isDuplicate = false;
            foreach ($uniqueKeys as $key) {
                if (in_array($key, $seenCombinations)) {
                    $isDuplicate = true;
                    break;
                }
            }

            // Only add if this is not a duplicate
            if (!$isDuplicate && !empty($uniqueKeys)) {
                // Add all unique keys to seen combinations
                foreach ($uniqueKeys as $key) {
                    $seenCombinations[] = $key;
                }
                $uniqueTrainings->push($training);

                // Stop when we have 5 unique trainings
                if ($uniqueTrainings->count() >= 5) {
                    break;
                }
            }
        }

        $recentTrainings = $uniqueTrainings->map(function($training) {
            // Get raw training_title from database to bypass accessor
            $rawTrainingTitle = $training->getAttributes()['training_title'] ?? null;

            // Set display title with priority system - bypass problematic accessor
            if ($rawTrainingTitle) {
                $training->display_title = $rawTrainingTitle;
            } elseif ($training->course && $training->course->course_title) {
                $training->display_title = $training->course->course_title;
                // Update database with actual course title for future use
                $training->update(['training_title' => $training->course->course_title]);
            } elseif ($training->course_id) {
                // Try to load course if not already loaded
                $course = \App\Models\CourseManagement::find($training->course_id);
                if ($course && $course->course_title) {
                    $training->display_title = $course->course_title;
                    $training->update(['training_title' => $course->course_title]);
                } else {
                    $training->display_title = 'Unknown Course';
                }
            } else {
                $training->display_title = 'Training Session';
            }

            // Calculate participant count for this specific training
            $participantCount = 1; // Default to 1 (the current participant)
            if ($training->course_id) {
                $participantCount = EmployeeTrainingDashboard::where('course_id', $training->course_id)
                    ->distinct('employee_id')
                    ->count('employee_id');
            } elseif ($training->training_title) {
                $participantCount = EmployeeTrainingDashboard::where('training_title', $training->training_title)
                    ->distinct('employee_id')
                    ->count('employee_id');
            }

            $training->participant_count = max($participantCount, 1);

            return $training;
        });

        // Recent Completed Trainings from both sources
        $recentCompletedTrainings = collect();

        try {
            // Get completed trainings from Employee Training Dashboard
            $dashboardCompleted = EmployeeTrainingDashboard::with(['employee', 'course'])
                ->where('status', 'Completed')
                ->latest('updated_at')
                ->take(10)
                ->get();

            // Get completed trainings from Employee Self-Reported
            $employeeCompleted = \App\Models\CompletedTraining::with('employee')
                ->latest('completion_date')
                ->take(10)
                ->get();

            // Convert to arrays and merge
            $allCompletions = collect();

            // Add dashboard completions
            foreach ($dashboardCompleted as $training) {
                $allCompletions->push([
                    'id' => $training->id,
                    'employee' => $training->employee,
                    'training_title' => $training->training_title ?? ($training->course ? $training->course->course_title : 'Training Course'),
                    'completion_date' => $training->updated_at,
                    'source' => 'Admin Assigned',
                    'status' => 'Verified',
                    'type' => 'dashboard'
                ]);
            }

            // Add employee completions
            foreach ($employeeCompleted as $training) {
                $allCompletions->push([
                    'id' => $training->completed_id,
                    'employee' => $training->employee,
                    'training_title' => $training->training_title,
                    'completion_date' => $training->completion_date,
                    'source' => 'Employee Reported',
                    'status' => $training->status ?? 'Pending',
                    'type' => 'employee'
                ]);
            }

            // Sort by completion date and take 5
            $recentCompletedTrainings = $allCompletions->sortByDesc(function($item) {
                return $item['completion_date'];
            })->take(5)->values();

        } catch (\Exception $e) {
            Log::error('Error fetching recent completed trainings: ' . $e->getMessage());
            $recentCompletedTrainings = collect();
        }

        // Recent Competency Updates (last 7 days)
        $recentCompetencyUpdates = EmployeeCompetencyProfile::with(['employee', 'competency'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($profile) {
                return (object) [
                    'id' => $profile->id,
                    'employee' => $profile->employee,
                    'competency' => $profile->competency,
                    'current_level' => (int) $profile->proficiency_level,
                    'progress_percentage' => ((int) $profile->proficiency_level / 5) * 100,
                    'gap_score' => max(0, 5 - (int) $profile->proficiency_level),
                    'last_updated' => $profile->updated_at
                ];
            });

        // Get top skills in demand based on competency gaps
        $topSkills = collect();

        try {
            // Try to get competency gaps with relationships
            $topSkillsData = CompetencyGap::with('competency')
                ->where('gap', '>', 0)
                ->get()
                ->groupBy('competency_id')
                ->map(function($gaps, $competencyId) {
                    $competency = $gaps->first()->competency;
                    return [
                        'competency_name' => $competency ? $competency->competency_name : 'Unknown Competency',
                        'gap_count' => $gaps->count(),
                        'avg_gap' => $gaps->avg('gap')
                    ];
                })
                ->sortByDesc('gap_count')
                ->take(5);

            if ($topSkillsData->isNotEmpty()) {
                $maxCount = $topSkillsData->max('gap_count');
                $topSkills = $topSkillsData->map(function($skill) use ($maxCount) {
                    $percent = $maxCount > 0 ? round(($skill['gap_count'] / $maxCount) * 100) : 0;
                    return [
                        'name' => $skill['competency_name'],
                        'percent' => max($percent, 10) // Minimum 10% for visibility
                    ];
                })->values();
            } else {
                // Fallback: Use most requested training courses
                $trainingRequestsData = TrainingRequest::selectRaw('training_title, COUNT(*) as request_count')
                    ->groupBy('training_title')
                    ->orderByDesc('request_count')
                    ->take(5)
                    ->get();

                if ($trainingRequestsData->isNotEmpty()) {
                    $maxRequests = $trainingRequestsData->max('request_count');
                    $topSkills = $trainingRequestsData->map(function($request) use ($maxRequests) {
                        $percent = $maxRequests > 0 ? round(($request->request_count / $maxRequests) * 100) : 0;
                        return [
                            'name' => $request->training_title,
                            'percent' => max($percent, 15)
                        ];
                    })->values();
                } else {
                    // Final fallback: Popular skills
                    $topSkills = collect([
                        ['name' => 'Communication Skills', 'percent' => 85],
                        ['name' => 'Leadership & Management', 'percent' => 70],
                        ['name' => 'Customer Service', 'percent' => 60],
                        ['name' => 'Technical Knowledge', 'percent' => 45],
                        ['name' => 'Problem Solving', 'percent' => 35]
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching top skills: ' . $e->getMessage());
            // Fallback data
            $topSkills = collect([
                ['name' => 'Communication Skills', 'percent' => 85],
                ['name' => 'Leadership & Management', 'percent' => 70],
                ['name' => 'Customer Service', 'percent' => 60],
                ['name' => 'Technical Knowledge', 'percent' => 45],
                ['name' => 'Problem Solving', 'percent' => 35]
            ]);
        }

        return view('admin_dashboard', compact(
            'totalEmployees',
            'totalUsers',
            'activeCourses',
            'trainingSessions',
            'employeeUsers',
            'successionPlans',
            'competencies',
            'attendanceLogs',
            'completedTrainings',
            'trainingRequestsCount',
            'recentEmployees',
            'recentGapAnalyses',
            'recentTrainings',
            'recentCompletedTrainings',
            'recentCompetencyUpdates',
            'topSkills'
        ));
    }

    public function debugTrainingData()
    {
        $trainings = EmployeeTrainingDashboard::with(['employee', 'course'])
            ->whereNotNull('training_date')
            ->latest('training_date')
            ->take(5)
            ->get();

        $debugData = [];
        foreach ($trainings as $training) {
            $debugData[] = [
                'id' => $training->id,
                'employee_id' => $training->employee_id,
                'employee_name' => $training->employee ? $training->employee->first_name . ' ' . $training->employee->last_name : 'No Employee',
                'course_id' => $training->course_id,
                'raw_training_title' => $training->getAttributes()['training_title'] ?? null,
                'accessor_training_title' => $training->training_title,
                'course_exists' => $training->course ? 'YES' : 'NO',
                'course_title' => $training->course ? $training->course->course_title : 'No Course',
                'training_date' => $training->training_date,
                'status' => $training->status,
                'raw_attributes' => $training->getAttributes()
            ];
        }

        return response()->json([
            'message' => 'Training Debug Data',
            'count' => count($debugData),
            'data' => $debugData
        ], 200);
    }

    public function fixMissingTrainingTitles()
    {
        $updated = 0;
        $trainings = EmployeeTrainingDashboard::with('course')
            ->whereNull('training_title')
            ->whereNotNull('course_id')
            ->get();

        foreach ($trainings as $training) {
            if ($training->course && $training->course->course_title) {
                $training->update(['training_title' => $training->course->course_title]);
                $updated++;
            }
        }

        return response()->json([
            'message' => 'Training titles updated successfully',
            'updated_count' => $updated,
            'total_checked' => $trainings->count()
        ], 200);
    }

    public function cleanupDuplicateTrainings()
    {
        $duplicatesRemoved = 0;
        $totalRecords = EmployeeTrainingDashboard::count();

        // Get all training records grouped by employee_id and course_id/training_title
        $allTrainings = EmployeeTrainingDashboard::with(['employee', 'course'])
            ->orderBy('created_at', 'desc') // Keep the most recent record
            ->get();

        $seenCombinations = [];
        $duplicateIds = [];
        $debugInfo = [];

        foreach ($allTrainings as $training) {
            // Create multiple unique keys to catch all possible duplicates
            $uniqueKeys = [];

            // Primary key: employee_id + course_id (if exists)
            if ($training->course_id) {
                $uniqueKeys[] = $training->employee_id . '_course_' . $training->course_id;
            }

            // Secondary key: employee_id + training_title (if exists)
            if ($training->training_title) {
                $uniqueKeys[] = $training->employee_id . '_training_' . md5(strtolower(trim($training->training_title)));
            }

            // Tertiary key: employee_id + course_title from relationship (if exists)
            if ($training->course && $training->course->course_title) {
                $uniqueKeys[] = $training->employee_id . '_coursetitle_' . md5(strtolower(trim($training->course->course_title)));
            }

            // Check if any of the unique keys have been seen before
            $isDuplicate = false;
            $matchingKey = null;
            foreach ($uniqueKeys as $key) {
                if (in_array($key, $seenCombinations)) {
                    $isDuplicate = true;
                    $matchingKey = $key;
                    break;
                }
            }

            if ($isDuplicate) {
                // This is a duplicate, mark for deletion
                $duplicateIds[] = $training->id;
                $duplicatesRemoved++;
                $debugInfo[] = [
                    'id' => $training->id,
                    'employee_id' => $training->employee_id,
                    'course_id' => $training->course_id,
                    'training_title' => $training->training_title,
                    'status' => $training->status,
                    'matching_key' => $matchingKey,
                    'action' => 'DELETED'
                ];
            } else {
                // Add all unique keys to seen combinations
                foreach ($uniqueKeys as $key) {
                    $seenCombinations[] = $key;
                }
                $debugInfo[] = [
                    'id' => $training->id,
                    'employee_id' => $training->employee_id,
                    'course_id' => $training->course_id,
                    'training_title' => $training->training_title,
                    'status' => $training->status,
                    'unique_keys' => $uniqueKeys,
                    'action' => 'KEPT'
                ];
            }
        }

        // Delete duplicate records
        if (!empty($duplicateIds)) {
            EmployeeTrainingDashboard::whereIn('id', $duplicateIds)->delete();
        }

        $finalRecords = EmployeeTrainingDashboard::count();

        return response()->json([
            'message' => 'Duplicate training records cleaned up successfully',
            'total_records_before' => $totalRecords,
            'duplicates_removed' => $duplicatesRemoved,
            'final_records' => $finalRecords,
            'unique_combinations' => count($seenCombinations),
            'debug_info' => $debugInfo
        ], 200);
    }

    public function debugCommunicationSkillsDuplicates()
    {
        // Find all Communication Skills records
        $communicationSkillsRecords = EmployeeTrainingDashboard::with(['employee', 'course'])
            ->where(function($query) {
                $query->where('training_title', 'LIKE', '%Communication Skills%')
                      ->orWhereHas('course', function($courseQuery) {
                          $courseQuery->where('course_title', 'LIKE', '%Communication Skills%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $debugData = [];
        foreach ($communicationSkillsRecords as $record) {
            $debugData[] = [
                'id' => $record->id,
                'employee_id' => $record->employee_id,
                'employee_name' => $record->employee ? $record->employee->first_name . ' ' . $record->employee->last_name : 'No Employee',
                'course_id' => $record->course_id,
                'training_title' => $record->training_title,
                'course_title' => $record->course ? $record->course->course_title : 'No Course',
                'status' => $record->status,
                'training_date' => $record->training_date,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at
            ];
        }

        return response()->json([
            'message' => 'Communication Skills Debug Data',
            'total_records' => count($debugData),
            'records' => $debugData
        ], 200);
    }
}
