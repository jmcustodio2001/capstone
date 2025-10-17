<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ExamAttempt;
use App\Models\EmployeeTrainingDashboard;
use App\Models\CompletedTraining;
use App\Models\TrainingRequest;
use App\Models\CourseManagement;

class TrainingProgressUpdateController extends Controller
{
    /**
     * Store a new training progress record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'training_title' => 'required|string',
                'employee_id' => 'required',
                'status' => 'required|string',
                'progress' => 'required|numeric|min:0|max:100',
            ]);

            $employeeId = $request->employee_id;
            $courseId = $request->course_id;

            // Update training dashboard
            $dashboardData = [
                'employee_id' => $employeeId,
                'training_title' => $request->training_title,
                'status' => $request->status,
                'progress' => $request->progress,
                'source' => $request->source ?? 'manual',
                'last_accessed' => now(),
                'remarks' => 'Updated via progress tracking'
            ];

            if ($courseId) {
                $dashboardData['course_id'] = $courseId;
            }

            // Update or create dashboard record
            $dashboard = EmployeeTrainingDashboard::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'training_title' => $request->training_title
                ],
                $dashboardData
            );

            // Move to completed trainings if progress is 100%
            if ($request->progress >= 100) {
                $this->moveToCompletedTrainings($employeeId, $courseId, 100);
            }

            return response()->json([
                'success' => true,
                'message' => 'Training progress updated successfully',
                'data' => $dashboard
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing training progress: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to store training progress: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Update training progress after exam completion
     */
    public function updateProgressAfterExam(Request $request)
    {
        try {
            $employeeId = Auth::user()->employee_id;
            $courseId = $request->input('course_id');
            $examScore = $request->input('exam_score');

            Log::info('Updating training progress after exam completion', [
                'employee_id' => $employeeId,
                'course_id' => $courseId,
                'exam_score' => $examScore
            ]);

            // Calculate progress based on exam score
            $progress = $examScore >= 80 ? 100 : $examScore;
            $status = $examScore >= 80 ? 'Completed' : ($examScore > 0 ? 'In Progress' : 'Not Started');

            // Update employee training dashboard
            $updated = DB::table('employee_training_dashboard')
                ->where('employee_id', $employeeId)
                ->where('course_id', $courseId)
                ->update([
                    'progress' => $progress,
                    'status' => $status,
                    'updated_at' => now()
                ]);

            // If exam passed (80%+), move to completed trainings
            if ($examScore >= 80) {
                $this->moveToCompletedTrainings($employeeId, $courseId, $examScore);
            }

            return response()->json([
                'success' => true,
                'message' => 'Training progress updated successfully',
                'progress' => $progress,
                'status' => $status,
                'updated_records' => $updated
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating training progress: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move completed training to completed trainings table
     */
    private function moveToCompletedTrainings($employeeId, $courseId, $examScore)
    {
        try {
            $course = CourseManagement::find($courseId);
            if (!$course) {
                Log::warning("Course not found for completed training", ['course_id' => $courseId]);
                return;
            }

            // Check if already exists in completed trainings
            $exists = CompletedTraining::where('employee_id', $employeeId)
                ->where(function($query) use ($course, $courseId) {
                    $query->where('course_id', $courseId)
                          ->orWhere('training_title', $course->course_title);
                })
                ->exists();

            if (!$exists) {
                CompletedTraining::create([
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'training_title' => $course->course_title,
                    'completion_date' => now()->format('Y-m-d'),
                    'remarks' => "Completed via exam with score: {$examScore}%",
                    'status' => 'Verified'
                ]);

                Log::info("Moved training to completed trainings", [
                    'employee_id' => $employeeId,
                    'course_title' => $course->course_title,
                    'exam_score' => $examScore
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error moving to completed trainings: ' . $e->getMessage());
        }
    }

    /**
     * Refresh training progress data
     */
    public function refreshProgress()
    {
        try {
            $employeeId = Auth::user()->employee_id;

            // Get all training records for this employee
            $trainingRecords = EmployeeTrainingDashboard::where('employee_id', $employeeId)->get();

            $updatedCount = 0;

            foreach ($trainingRecords as $training) {
                // Calculate real-time progress from exam attempts
                $examProgress = ExamAttempt::calculateCombinedProgress($employeeId, $training->course_id);

                if ($examProgress > 0 && $examProgress != $training->progress) {
                    $status = $examProgress >= 100 ? 'Completed' :
                             ($examProgress >= 80 ? 'Passed' :
                             ($examProgress > 0 ? 'In Progress' : 'Not Started'));

                    $training->update([
                        'progress' => $examProgress,
                        'status' => $status,
                        'updated_at' => now()
                    ]);

                    $updatedCount++;

                    // Move to completed if 100%
                    if ($examProgress >= 100) {
                        $this->moveToCompletedTrainings($employeeId, $training->course_id, $examProgress);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Refreshed progress for {$updatedCount} training records",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error refreshing progress: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh progress: ' . $e->getMessage()
            ], 500);
        }
    }
}
