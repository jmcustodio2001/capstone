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
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\CertificateGenerationController;

class EmployeeTrainingDashboardController extends Controller
{
    public function index()
    {
        $employees = \App\Models\Employee::all();
        $courses = \App\Models\CourseManagement::all();
        
        // Get regular training records
        $dashboardRecords = \App\Models\EmployeeTrainingDashboard::with(['employee', 'course'])
            ->leftJoin('users', 'employee_training_dashboards.assigned_by', '=', 'users.id')
            ->select('employee_training_dashboards.*', 'users.name as assigned_by_name')
            ->orderBy('employee_training_dashboards.created_at', 'desc')
            ->get();
            
        // Get approved training requests and convert them to training records format
        $approvedRequests = \App\Models\TrainingRequest::with(['employee', 'course'])
            ->where('status', 'Approved')
            ->get()
            ->map(function($request) {
                // Calculate actual progress from multiple sources
                $actualProgress = 0;
                $employeeId = $request->employee_id;
                $courseId = $request->course_id;
                
                // 1. Check exam progress first (highest priority)
                if ($courseId) {
                    $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employeeId, $courseId);
                    if ($examProgress > 0) {
                        $actualProgress = $examProgress;
                    }
                }
                
                // 2. Check employee training dashboard progress
                if ($actualProgress == 0 && $courseId) {
                    $trainingRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                        ->where('course_id', $courseId)
                        ->first();
                    if ($trainingRecord && $trainingRecord->progress > 0) {
                        $actualProgress = $trainingRecord->progress;
                    }
                }
                
                // 3. Check destination knowledge training progress
                if ($actualProgress == 0 && $request->course) {
                    $courseTitle = $request->course->course_title;
                    $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                        ->where('destination_name', 'LIKE', '%' . $courseTitle . '%')
                        ->first();
                    if ($destinationRecord && $destinationRecord->progress > 0) {
                        $actualProgress = $destinationRecord->progress;
                    }
                }
                
                // 4. Check competency profile progress
                if ($actualProgress == 0 && $request->course) {
                    $courseTitle = str_replace([' Training', ' Course', ' Program'], '', $request->course->course_title);
                    $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($courseTitle) {
                        $q->where('competency_name', 'LIKE', '%' . $courseTitle . '%');
                    })->where('employee_id', $employeeId)->first();
                    
                    if ($competencyProfile && $competencyProfile->proficiency_level > 1) {
                        $actualProgress = ($competencyProfile->proficiency_level / 5) * 100;
                    }
                }
                
                // Default to 0% if no progress found (approved doesn't mean completed)
                $finalProgress = max(0, min(100, round($actualProgress)));
                
                // Handle missing course relationship
                $courseData = $request->course;
                if (!$courseData && $request->course_id) {
                    // Try to find course by ID if relationship failed
                    $courseData = \App\Models\CourseManagement::find($request->course_id);
                }
                
                // If still no course found but we have training_title, create a pseudo course
                if (!$courseData && $request->training_title) {
                    $pseudoCourse = new \stdClass();
                    $pseudoCourse->course_id = $request->course_id ?? 'unknown';
                    $pseudoCourse->course_title = $request->training_title;
                    $pseudoCourse->description = 'Training requested by employee';
                    $pseudoCourse->expired_date = null; // Add expired_date property
                    $courseData = $pseudoCourse;
                }
                
                // Calculate expired date for approved requests
                $expiredDate = null;
                if ($courseData && isset($courseData->expired_date)) {
                    $expiredDate = $courseData->expired_date;
                } else {
                    // Set default expiration (90 days from request date)
                    $expiredDate = \Carbon\Carbon::parse($request->requested_date)->addDays(90)->format('Y-m-d H:i:s');
                }
                
                // Create a pseudo training record from approved request
                $pseudoRecord = new \stdClass();
                $pseudoRecord->id = 'request_' . $request->request_id;
                $pseudoRecord->employee_id = $request->employee_id;
                $pseudoRecord->course_id = $request->course_id ?? 'unknown';
                $pseudoRecord->progress = $finalProgress; // Use calculated actual progress
                $pseudoRecord->training_date = $request->requested_date;
                $pseudoRecord->last_accessed = $request->updated_at;
                $pseudoRecord->expired_date = $expiredDate; // Use calculated expired date
                $pseudoRecord->assigned_by = null;
                $pseudoRecord->assigned_by_name = 'Training Request';
                $pseudoRecord->created_at = $request->created_at;
                $pseudoRecord->updated_at = $request->updated_at;
                $pseudoRecord->employee = $request->employee;
                $pseudoRecord->course = $courseData; // Use resolved course data
                $pseudoRecord->source = 'Training Request (Approved)';
                $pseudoRecord->competency_gap = null;
                
                return $pseudoRecord;
            });
            
        // Combine both collections
        $allRecords = $dashboardRecords->concat($approvedRequests);
        
        // Sort by created_at descending
        $allRecords = $allRecords->sortByDesc('created_at');
        
        // Paginate manually
        $currentPage = request()->get('page', 1);
        $perPage = 10;
        $total = $allRecords->count();
        $items = $allRecords->forPage($currentPage, $perPage)->values();
        
        $trainingRecords = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        
        // Add competency gap context to regular training records
        foreach ($trainingRecords as $record) {
            if (!isset($record->source)) {
                // Check if this training was assigned from competency gap
                $competencyGap = \App\Models\CompetencyGap::with('competency')
                    ->where('employee_id', $record->employee_id)
                    ->whereHas('competency', function($q) use ($record) {
                        if ($record->course) {
                            $courseTitle = str_replace(' Training', '', $record->course->course_title);
                            $q->where('competency_name', 'LIKE', '%' . $courseTitle . '%');
                        }
                    })
                    ->first();
                    
                $record->competency_gap = $competencyGap;
                $record->source = $this->determineTrainingSource($record);
            }
        }
        
        return view('learning_management.employee_training_dashboard', compact('employees', 'courses', 'trainingRecords'));
    }

    /**
     * Fix existing training records that don't have expiration dates by syncing with Destination Knowledge Training
     */
    public function fixExpiredDates()
    {
        try {
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
                        $newExpiredDate = $destinationTraining->expired_date;
                        $synced++;
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
                        $newExpiredDate = $competencyGap->expired_date;
                    }
                }
                
                // If still no date, set default expiration (90 days from now)
                if (!$newExpiredDate) {
                    $newExpiredDate = now()->addDays(90)->format('Y-m-d H:i:s');
                }
                
                // Update if different from current
                if ($originalExpiredDate != $newExpiredDate) {
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

    public function create()
    {
        $employees = \App\Models\Employee::all();
        $courses = \App\Models\CourseManagement::all();
        $trainingRecords = \App\Models\EmployeeTrainingDashboard::with(['employee', 'course'])->paginate(10);
        return view('learning_management.employee_training_dashboard', compact('employees', 'courses', 'trainingRecords'));
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
        
        // Set default expired date if not provided
        if (!isset($data['expired_date'])) {
            $data['expired_date'] = now()->addDays(90);
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
        
        // AUTO-CREATE competency profile and gap when course is assigned
        $this->autoCreateCompetencyEntries($record);
        
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
        
        // AUTO-CREATE competency profile and gap if they don't exist
        $this->autoCreateCompetencyEntries($record);
        
        // Sync progress with Destination Knowledge Training
        $this->syncProgressWithDestinationKnowledge($record);
        
        // ALWAYS sync with Competency Profile and Gap regardless of progress level
        $this->syncWithCompetencyProfile($record);
        $this->syncWithCompetencyGap($record);
        
        return redirect()->back()->with('success', 'Training record updated successfully!');
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
                // Auto-create competency entries if they don't exist
                $this->autoCreateCompetencyEntries($record);
                
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
                        'status' => $this->getStatusFromProgress($trainingRecord->progress ?? 0),
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
                $certificateController = new CertificateGenerationController(new \App\Services\AICertificateGeneratorService());
                $certificateController->generateCertificateOnCompletion(
                    $trainingRecord->employee_id,
                    $trainingRecord->course_id,
                    now()->format('Y-m-d')
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
}
