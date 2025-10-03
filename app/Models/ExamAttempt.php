<?php

namespace App\Models;

use Exception;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'course_id',
        'type',
        'attempt_number',
        'score',
        'total_questions',
        'correct_answers',
        'status',
        'started_at',
        'completed_at',
        'answers'
    ];

    protected $casts = [
        'answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(CourseManagement::class, 'course_id', 'course_id');
    }

    // Check if employee can take another attempt
    public static function canTakeAttempt($employeeId, $courseId, $type)
    {
        $attemptCount = self::where('employee_id', $employeeId)
            ->where('course_id', $courseId)
            ->where('type', $type)
            ->count();
        
        return $attemptCount < 3;
    }

    // Get remaining attempts
    public static function getRemainingAttempts($employeeId, $courseId, $type)
    {
        $attemptCount = self::where('employee_id', $employeeId)
            ->where('course_id', $courseId)
            ->where('type', $type)
            ->count();
        
        return max(0, 3 - $attemptCount);
    }

    // Get next attempt number
    public static function getNextAttemptNumber($employeeId, $courseId, $type)
    {
        $lastAttempt = self::where('employee_id', $employeeId)
            ->where('course_id', $courseId)
            ->where('type', $type)
            ->max('attempt_number');
        
        return ($lastAttempt ?? 0) + 1;
    }

    // Get best scores for exam only
    public static function getBestScores($employeeId, $courseId)
    {
        $bestExamScore = self::where('employee_id', $employeeId)
            ->where('course_id', $courseId)
            ->where('type', 'exam')
            ->whereIn('status', ['completed', 'failed']) // Include both completed and failed attempts
            ->max('score') ?? 0;
            
        return [
            'exam_score' => $bestExamScore
        ];
    }

    // Calculate progress from exam score with passing grade logic
    public static function calculateCombinedProgress($employeeId, $courseId)
    {
        try {
            // Get the best exam score directly
            $bestExamScore = self::where('employee_id', $employeeId)
                ->where('course_id', $courseId)
                ->where('type', 'exam')
                ->where('status', 'completed')
                ->max('score');
            
            if ($bestExamScore === null) {
                return 0; // No completed exam attempts
            }
            
            $examScore = max(0, min(100, (float)$bestExamScore));
            
            // Log for debugging
            \Illuminate\Support\Facades\Log::info('Calculating progress', [
                'employee_id' => $employeeId,
                'course_id' => $courseId,
                'best_exam_score' => $bestExamScore,
                'calculated_progress' => $examScore >= 80 ? 100 : $examScore
            ]);

            // Apply passing grade logic: 80%+ exam scores result in 100% progress
            if ($examScore >= 80) {
                return 100; // Training complete when exam is passed
            } else {
                return round($examScore, 1); // Return actual score if below passing
            }
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error calculating exam progress: ' . $e->getMessage());
            return 0;
        }
    }
    
    // Get exam score breakdown for display with accurate progress calculation
    public static function getScoreBreakdown($employeeId, $courseId)
    {
        try {
            $scores = self::getBestScores($employeeId, $courseId);
            $examScore = max(0, min(100, $scores['exam_score']));
            
            // Calculate accurate progress based on passing grade logic
            $actualProgress = self::calculateCombinedProgress($employeeId, $courseId);
            
            return [
                'exam_score' => $examScore,
                'combined_progress' => round($actualProgress, 2),
                'exam_weight' => 100,
                'has_exam' => $examScore > 0,
                'is_complete' => $examScore >= 80,
                'passing_grade' => 80,
                'progress_explanation' => $examScore >= 80 ? 'Training completed - exam passed' : ($examScore > 0 ? 'Progress capped at 79% until exam is passed (80%+)' : 'No exam attempt yet')
            ];
            
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting score breakdown: ' . $e->getMessage());
            return [
                'exam_score' => 0,
                'combined_progress' => 0,
                'exam_weight' => 100,
                'has_exam' => false,
                'is_complete' => false,
                'passing_grade' => 80,
                'progress_explanation' => 'No exam data available'
            ];
        }
    }

    // Reset exam attempts when course is reassigned
    public static function resetAttemptsForCourse($employeeId, $courseId)
    {
        try {
            $deletedCount = self::where('employee_id', $employeeId)
                ->where('course_id', $courseId)
                ->delete();
            
            \Illuminate\Support\Facades\Log::info("Reset exam attempts for employee {$employeeId}, course {$courseId}. Deleted {$deletedCount} attempts.");
            
            return $deletedCount;
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error resetting exam attempts: ' . $e->getMessage());
            return 0;
        }
    }
}
