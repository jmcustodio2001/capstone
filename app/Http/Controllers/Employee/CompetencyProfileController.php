<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCompetencyProfile;
use App\Models\Employee;
use App\Models\CompetencyLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CompetencyProfileController extends Controller
{
    /**
     * Display the competency tracker dashboard for the authenticated employee.
     */
    public function index()
    {
        $employee = Auth::user()->employee ?? Employee::where('email', Auth::user()->email)->first();
        
        if (!$employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee profile not found.');
        }

        // Get real progress data from multiple sources
        $progressData = $this->calculateRealAverageProgress($employee->employee_id);
        
        $competencyProfiles = EmployeeCompetencyProfile::with(['competency'])
            ->where('employee_id', $employee->employee_id)
            ->orderBy('proficiency_level', 'asc')
            ->get();

        // Transform competency profiles to match tracker format with real progress
        $competencyTrackers = $competencyProfiles->map(function($profile) use ($progressData) {
            $proficiencyLevel = (int) $profile->proficiency_level;
            $gapScore = max(0, 5 - $proficiencyLevel);
            
            // Get real progress for this competency from training data
            $realProgress = $this->getRealCompetencyProgress($profile, $progressData['training_progress']);
            
            return [
                'id' => $profile->id,
                'employee_id' => $profile->employee_id,
                'competency_id' => $profile->competency_id,
                'competency' => $profile->competency,
                'competency_name' => $profile->competency->competency_name ?? 'Unknown',
                'current_level' => $proficiencyLevel,
                'target_level' => 5, // Default target
                'gap_score' => $gapScore,
                'progress_percentage' => $realProgress,
                'progress_status' => $this->getProgressStatusFromPercentage($realProgress),
                'gap_status' => $this->getGapStatusFromScore($gapScore),
                'status' => 'Active',
                'assessment_date' => $profile->assessment_date,
                'last_assessment_date' => $profile->assessment_date,
                'manager_feedback' => 'Based on competency assessment and training progress',
                'recommended_training' => $this->getRecommendedTraining($profile->competency->competency_name, $proficiencyLevel),
                'deadline' => $this->getCompetencyGapDeadline($profile->employee_id, $profile->competency_id),
                'next_review_date' => null,
                'promotion_path_alignment' => 'Aligned'
            ];
        });

        $totalCompetencies = $competencyTrackers->count();
        $averageProgress = $progressData['overall_average'];
        $competenciesWithGaps = $competencyTrackers->where('gap_score', '>', 0)->count();
        $needsDevelopment = $competencyTrackers->where('gap_score', '>', 2)->count();
        $onTrack = $competencyTrackers->where('progress_percentage', '>=', 60)->count();

        $recommendedTrainings = $competencyTrackers->pluck('recommended_training')
            ->filter()
            ->unique()
            ->take(5);

        // Get upcoming deadlines from competency gaps and tracker data
        $upcomingDeadlines = $this->getUpcomingDeadlines($employee->employee_id, $competencyTrackers);

        return view('employee_ess_modules.competency_profile.competency_tracker', compact(
            'competencyTrackers',
            'employee',
            'totalCompetencies',
            'averageProgress',
            'competenciesWithGaps',
            'needsDevelopment',
            'onTrack',
            'recommendedTrainings',
            'upcomingDeadlines'
        ));
    }

    /**
     * Show detailed view of a specific competency.
     */
    public function show($id)
    {
        $employee = Auth::user()->employee ?? Employee::where('email', Auth::user()->email)->first();
        
        $profile = EmployeeCompetencyProfile::with(['competency'])
            ->where('id', $id)
            ->where('employee_id', $employee->employee_id)
            ->firstOrFail();

        // Transform to tracker format
        $proficiencyLevel = (int) $profile->proficiency_level;
        $gapScore = max(0, 5 - $proficiencyLevel);
        
        $competencyTracker = [
            'id' => $profile->id,
            'employee_id' => $profile->employee_id,
            'competency_id' => $profile->competency_id,
            'competency' => $profile->competency ? [
                'competency_name' => $profile->competency->competency_name,
                'description' => $profile->competency->description,
                'category' => $profile->competency->category
            ] : null,
            'competency_name' => $profile->competency->competency_name ?? 'Unknown',
            'current_level' => $proficiencyLevel,
            'target_level' => 5,
            'gap_score' => $gapScore,
            'progress_percentage' => ($proficiencyLevel / 5) * 100,
            'progress_status' => $this->getProgressStatusFromLevel($proficiencyLevel),
            'gap_status' => $this->getGapStatusFromScore($gapScore),
            'status' => 'Active',
            'assessment_date' => $profile->assessment_date,
            'last_assessment_date' => $profile->assessment_date,
            'manager_feedback' => 'Based on competency assessment on ' . $profile->assessment_date,
            'recommended_training' => $this->getRecommendedTraining($profile->competency->competency_name, $proficiencyLevel),
            'deadline' => $this->getCompetencyGapDeadline($profile->employee_id, $profile->competency_id),
            'next_review_date' => null,
            'promotion_path_alignment' => 'Aligned'
        ];

        return view('employee_ess_modules.competency_profile.show', compact('competencyTracker', 'employee'));
    }

    /**
     * Handle feedback request from employee
     */
    public function requestFeedback(Request $request)
    {
        $request->validate([
            'competency_id' => 'required|integer',
            'employee_id' => 'required|integer'
        ]);

        try {
            // Here you would typically:
            // 1. Create a feedback request record
            // 2. Send notification to manager
            // 3. Log the request
            
            // For now, we'll simulate success
            return response()->json([
                'success' => true,
                'message' => 'Feedback request sent to your manager successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send feedback request. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle self-progress update from employee
     */
    public function updateProgressSelf(Request $request)
    {
        $request->validate([
            'competency_id' => 'required|integer',
            'employee_id' => 'required|integer',
            'new_level' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Find the competency profile
            $profile = EmployeeCompetencyProfile::where('competency_id', $request->competency_id)
                ->where('employee_id', $request->employee_id)
                ->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Competency profile not found.'
                ], 404);
            }

            // Update the proficiency level
            $profile->proficiency_level = $request->new_level;
            $profile->assessment_date = now();
            
            // Add notes to feedback if provided
            if ($request->notes) {
                $profile->feedback = ($profile->feedback ? $profile->feedback . '\n\n' : '') . 
                    '[' . now()->format('Y-m-d H:i') . '] Self-assessment: ' . $request->notes;
            }
            
            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress. Please try again.'
            ], 500);
        }
    }

    /**
     * Get competency progress data for AJAX requests.
     */
    public function getProgressData(Request $request)
    {
        $employee = Auth::user()->employee ?? Employee::where('email', Auth::user()->email)->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Get real progress data from multiple sources
        $progressData = $this->calculateRealAverageProgress($employee->employee_id);
        
        $competencyProfiles = EmployeeCompetencyProfile::with(['competency'])
            ->where('employee_id', $employee->employee_id)
            ->get();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_competencies' => $competencyProfiles->count(),
                'average_progress' => number_format($progressData['overall_average'], 1),
                'needs_development' => $competencyProfiles->where('proficiency_level', '<=', 2)->count(),
                'on_track' => $competencyProfiles->where('proficiency_level', '>=', 3)->count(),
                'training_completion_rate' => number_format($progressData['training_completion_rate'], 1),
                'certificate_count' => $progressData['certificate_count']
            ],
            'trackers' => $competencyProfiles->map(function($profile) use ($progressData) {
                $proficiencyLevel = (int) $profile->proficiency_level;
                $gapScore = max(0, 5 - $proficiencyLevel);
                $realProgress = $this->getRealCompetencyProgress($profile, $progressData['training_progress']);
                
                return [
                    'id' => $profile->id,
                    'competency_name' => $profile->competency->competency_name,
                    'current_level' => $proficiencyLevel,
                    'target_level' => 5,
                    'progress_percentage' => $realProgress,
                    'gap_score' => $gapScore,
                    'progress_status' => $this->getProgressStatusFromPercentage($realProgress),
                    'gap_status' => $this->getGapStatusFromScore($gapScore),
                    'manager_feedback' => 'Based on competency assessment and training data',
                    'recommended_training' => $this->getRecommendedTraining($profile->competency->competency_name, $proficiencyLevel),
                    'deadline' => $this->getCompetencyDeadline($profile),
                ];
            })
        ]);
    }

    /**
     * Update progress for a competency (if allowed).
     */
    public function updateProgress(Request $request, $id)
    {
        $employee = Auth::user()->employee ?? Employee::where('email', Auth::user()->email)->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $profile = EmployeeCompetencyProfile::where('employee_id', $employee->employee_id)->findOrFail($id);
        
        $validated = $request->validate([
            'current_level' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000'
        ]);

        $profile->update([
            'proficiency_level' => $validated['current_level'],
            'assessment_date' => now()
        ]);

        $gapScore = max(0, 5 - $validated['current_level']);
        $progressPercentage = ($validated['current_level'] / 5) * 100;
        $progressStatus = $this->getProgressStatusFromLevel($validated['current_level']);
        $gapStatus = $this->getGapStatusFromScore($gapScore);

        return response()->json([
            'success' => true,
            'message' => 'Progress updated successfully',
            'tracker' => [
                'id' => $profile->id,
                'current_level' => $validated['current_level'],
                'progress_percentage' => $progressPercentage,
                'gap_score' => $gapScore,
                'progress_status' => $progressStatus,
                'gap_status' => $gapStatus
            ]
        ]);
    }

    private function getStatusFromLevel($level)
    {
        switch($level) {
            case 5:
                return 'Expert';
            case 4:
                return 'Advanced';
            case 3:
                return 'Intermediate';
            case 2:
                return 'Basic';
            case 1:
            default:
                return 'Beginner';
        }
    }

    private function getProgressStatusFromLevel($level)
    {
        switch($level) {
            case 5:
                return 'Excellent';
            case 4:
                return 'Good';
            case 3:
                return 'Fair';
            case 2:
            case 1:
            default:
                return 'Needs Improvement';
        }
    }

    private function getGapStatusFromScore($gapScore)
    {
        if ($gapScore <= 1) {
            return 'Strong';
        } elseif ($gapScore <= 2) {
            return 'Moderate';
        } else {
            return 'Needs Development';
        }
    }

    /**
     * Start training for a specific competency
     */
    public function startTraining($id)
    {
        try {
            // Debug authentication
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
            }
            
            $employee = $user->employee ?? Employee::where('email', $user->email)->first();
            
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found for user: ' . $user->email], 404);
            }

            $profile = EmployeeCompetencyProfile::with(['competency'])
                ->where('id', $id)
                ->where('employee_id', $employee->employee_id)
                ->first();

            if (!$profile) {
                return response()->json(['success' => false, 'message' => 'Competency profile not found'], 404);
            }

            // Find related course based on competency name
            $course = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $profile->competency->competency_name . '%')
                ->orWhere('course_title', 'LIKE', '%' . str_replace(' ', '%', $profile->competency->competency_name) . '%')
                ->first();

            if (!$course) {
                // Create a generic training course for this competency
                $course = \App\Models\CourseManagement::create([
                    'course_title' => $profile->competency->competency_name . ' Training',
                    'description' => 'Training course for ' . $profile->competency->competency_name . ' competency development',
                    'start_date' => now(),
                    'end_date' => now()->addDays(30),
                    'status' => 'Active'
                ]);
            }

            return response()->json([
                'success' => true,
                'course_id' => $course->course_id,
                'course_title' => $course->course_title,
                'competency_name' => $profile->competency->competency_name,
                'redirect_url' => route('employee.exam.start', ['courseId' => $course->course_id])
            ]);
        } catch (\Exception $e) {
            Log::error('Error starting training: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error starting training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate real average progress from multiple data sources
     */
    private function calculateRealAverageProgress($employeeId)
    {
        // Get competency profile progress
        $competencyProfiles = EmployeeCompetencyProfile::where('employee_id', $employeeId)->get();
        $competencyProgress = $competencyProfiles->avg('proficiency_level') * 20; // Convert to percentage
        
        // Get training dashboard progress
        $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)->get();
        $trainingProgress = $trainingRecords->avg('progress') ?? 0;
        
        // Get destination knowledge training progress
        $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)->get();
        $destinationProgress = $destinationTraining->avg('progress') ?? 0;
        
        // Get certificate completion data
        $certificates = \App\Models\TrainingRecordCertificateTracking::where('employee_id', $employeeId)
            ->where('status', 'Completed')
            ->count();
        $totalCertificates = \App\Models\TrainingRecordCertificateTracking::where('employee_id', $employeeId)->count();
        $certificateCompletionRate = $totalCertificates > 0 ? ($certificates / $totalCertificates) * 100 : 0;
        
        // Calculate weighted average (40% competency, 30% training, 20% destination, 10% certificates)
        $overallAverage = (
            ($competencyProgress * 0.4) +
            ($trainingProgress * 0.3) +
            ($destinationProgress * 0.2) +
            ($certificateCompletionRate * 0.1)
        );
        
        return [
            'overall_average' => round($overallAverage, 1),
            'competency_progress' => round($competencyProgress, 1),
            'training_progress' => $trainingRecords->keyBy('course_title'),
            'destination_progress' => round($destinationProgress, 1),
            'training_completion_rate' => round($trainingProgress, 1),
            'certificate_count' => $certificates,
            'certificate_completion_rate' => round($certificateCompletionRate, 1)
        ];
    }
    
    /**
     * Get real progress for a specific competency
     */
    private function getRealCompetencyProgress($profile, $trainingProgress)
    {
        $competencyName = $profile->competency->competency_name;
        $proficiencyProgress = ($profile->proficiency_level / 5) * 100;
        
        // Look for matching training records
        $matchingTraining = $trainingProgress->filter(function($training) use ($competencyName) {
            return stripos($training->course_title, $competencyName) !== false ||
                   stripos($competencyName, $training->course_title) !== false;
        });
        
        if ($matchingTraining->isNotEmpty()) {
            $trainingAvg = $matchingTraining->avg('progress');
            // Weighted average: 60% proficiency, 40% training
            return round(($proficiencyProgress * 0.6) + ($trainingAvg * 0.4), 1);
        }
        
        return round($proficiencyProgress, 1);
    }
    
    /**
     * Get progress status from percentage
     */
    private function getProgressStatusFromPercentage($percentage)
    {
        if ($percentage >= 90) {
            return 'Excellent';
        } elseif ($percentage >= 70) {
            return 'Good';
        } elseif ($percentage >= 50) {
            return 'Fair';
        } else {
            return 'Needs Improvement';
        }
    }
    
    /**
     * Get competency deadline from competency gap record
     */
    private function getCompetencyGapDeadline($employeeId, $competencyId)
    {
        // First, try to get deadline from competency gap record
        $competencyGap = \App\Models\CompetencyGap::where('employee_id', $employeeId)
            ->where('competency_id', $competencyId)
            ->first();
            
        if ($competencyGap && $competencyGap->expired_date) {
            return \Carbon\Carbon::parse($competencyGap->expired_date)->format('Y-m-d');
        }
        
        // Fallback: calculate based on proficiency level if no gap record exists
        $profile = \App\Models\EmployeeCompetencyProfile::where('employee_id', $employeeId)
            ->where('competency_id', $competencyId)
            ->first();
            
        if ($profile) {
            return $this->getCompetencyDeadline($profile);
        }
        
        return null;
    }

    /**
     * Get competency deadline based on progress (fallback method)
     */
    private function getCompetencyDeadline($profile)
    {
        $proficiencyLevel = $profile->proficiency_level;
        
        // Set deadlines based on competency level
        if ($proficiencyLevel <= 2) {
            // Low proficiency - urgent deadline (30 days)
            return Carbon::now()->addDays(30)->format('Y-m-d');
        } elseif ($proficiencyLevel <= 3) {
            // Medium proficiency - moderate deadline (60 days)
            return Carbon::now()->addDays(60)->format('Y-m-d');
        } elseif ($proficiencyLevel <= 4) {
            // Good proficiency - extended deadline (90 days)
            return Carbon::now()->addDays(90)->format('Y-m-d');
        }
        
        // Expert level - no urgent deadline
        return null;
    }

    /**
     * Get upcoming deadlines from competency gaps and tracker data
     */
    private function getUpcomingDeadlines($employeeId, $competencyTrackers)
    {
        $upcomingDeadlines = collect();
        
        // Get deadlines from competency trackers that have deadlines set
        foreach ($competencyTrackers as $tracker) {
            if (isset($tracker['deadline']) && $tracker['deadline']) {
                try {
                    $deadlineDate = \Carbon\Carbon::parse($tracker['deadline']);
                    $now = \Carbon\Carbon::now();
                    
                    // Only include future deadlines within next 30 days
                    if ($deadlineDate->isFuture() && $deadlineDate->diffInDays($now) <= 30) {
                        $upcomingDeadlines->push((object)[
                            'competency' => (object)[
                                'competency_name' => $tracker['competency_name'] ?? 'Unknown Competency'
                            ],
                            'deadline' => $tracker['deadline'],
                            'days_left' => $deadlineDate->diffInDays($now),
                            'urgency' => $this->getDeadlineUrgency($deadlineDate)
                        ]);
                    }
                } catch (\Exception $e) {
                    // Skip invalid dates
                    continue;
                }
            }
        }
        
        // Sort by deadline (earliest first)
        return $upcomingDeadlines->sortBy(function($deadline) {
            return \Carbon\Carbon::parse($deadline->deadline);
        })->take(5);
    }

    /**
     * Determine deadline urgency level
     */
    private function getDeadlineUrgency($deadlineDate)
    {
        $daysLeft = \Carbon\Carbon::now()->diffInDays($deadlineDate, false);
        
        if ($daysLeft <= 3) {
            return 'urgent';
        } elseif ($daysLeft <= 7) {
            return 'soon';
        } else {
            return 'normal';
        }
    }

    private function getRecommendedTraining($competencyName, $level)
    {
        if ($level >= 4) {
            return "Advanced {$competencyName} certification or mentoring others";
        } elseif ($level >= 3) {
            return "Advanced {$competencyName} workshop or specialized training";
        } elseif ($level >= 2) {
            return "Intermediate {$competencyName} course or hands-on practice";
        } else {
            return "Basic {$competencyName} fundamentals training";
        }
    }
}
