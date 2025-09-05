<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExamSubmissionController extends Controller
{
    public function submit(Request $request, $attemptId)
    {
        // No CSRF protection - direct submission
        $attempt = ExamAttempt::findOrFail($attemptId);
        $employeeId = Auth::user()->employee_id;
        
        // Verify ownership
        if ($attempt->employee_id != $employeeId) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }
        
        $answers = $request->input('answers', []);
        $questions = ExamQuestion::getQuestionsForCourse($attempt->course_id, $attempt->type);
        
        $correctAnswers = 0;
        $totalQuestions = $questions->count();
        
        // Validate that answers were submitted
        if (empty($answers)) {
            return redirect()->back()->with('error', 'No answers were submitted. Please select answers for all questions before submitting.');
        }
        
        $debugInfo = [];
        
        foreach ($questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            
            if ($userAnswer !== null) {
                $isCorrect = false;
                $selectedAnswerText = null;
                
                // Get the text of the selected answer
                if (isset($question->options[$userAnswer])) {
                    $selectedAnswerText = $question->options[$userAnswer];
                }
                
                // Method 1: Direct key comparison (a, b, c, d)
                if ($userAnswer === $question->correct_answer) {
                    $isCorrect = true;
                }
                
                // Method 2: Text comparison - selected answer text vs correct answer text
                if (!$isCorrect && $selectedAnswerText) {
                    if (strcasecmp(trim($selectedAnswerText), trim($question->correct_answer)) === 0) {
                        $isCorrect = true;
                    }
                }
                
                // Method 3: Check if correct answer is a key and matches user selection
                if (!$isCorrect) {
                    foreach ($question->options as $optionKey => $optionText) {
                        if ($optionKey === $question->correct_answer && $userAnswer === $optionKey) {
                            $isCorrect = true;
                            break;
                        }
                    }
                }
                
                // Method 4: Check if correct answer is text and find matching option
                if (!$isCorrect && $selectedAnswerText) {
                    foreach ($question->options as $optionKey => $optionText) {
                        if (strcasecmp(trim($optionText), trim($question->correct_answer)) === 0 && $userAnswer === $optionKey) {
                            $isCorrect = true;
                            break;
                        }
                    }
                }
                
                // Method 5: Partial text matching for similar answers
                if (!$isCorrect && $selectedAnswerText) {
                    $selectedClean = strtolower(trim(preg_replace('/[^\w\s]/', '', $selectedAnswerText)));
                    $correctClean = strtolower(trim(preg_replace('/[^\w\s]/', '', $question->correct_answer)));
                    
                    if ($selectedClean === $correctClean) {
                        $isCorrect = true;
                    }
                }
                
                // Method 6: Additional fallback - check if any option matches the correct answer exactly
                if (!$isCorrect && $selectedAnswerText) {
                    foreach ($question->options as $optKey => $optText) {
                        if ($optKey === $userAnswer && strcasecmp(trim($optText), trim($question->correct_answer)) === 0) {
                            $isCorrect = true;
                            break;
                        }
                    }
                }
                
                if ($isCorrect) {
                    $correctAnswers++;
                }
                
                // Debug logging for troubleshooting
                $debugInfo[] = [
                    'question_id' => $question->id,
                    'user_selected_key' => $userAnswer,
                    'user_selected_text' => $selectedAnswerText,
                    'correct_answer' => $question->correct_answer,
                    'is_correct' => $isCorrect,
                    'options' => $question->options
                ];
            } else {
                // No answer provided for this question
                $debugInfo[] = [
                    'question_id' => $question->id,
                    'user_selected_key' => 'NO_ANSWER',
                    'user_selected_text' => 'NO_ANSWER',
                    'correct_answer' => $question->correct_answer,
                    'is_correct' => false,
                    'options' => $question->options
                ];
            }
        }
        
        Log::info('Exam Answer Validation Debug', [
            'employee_id' => $employeeId,
            'course_id' => $attempt->course_id,
            'type' => $attempt->type,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'raw_answers_input' => $answers,
            'debug_details' => $debugInfo
        ]);
        
        $score = ($correctAnswers / $totalQuestions) * 100;
        $status = $score >= 80 ? 'completed' : 'failed'; // 80% passing grade
        
        // Update attempt
        $attempt->update([
            'answers' => $answers,
            'correct_answers' => $correctAnswers,
            'score' => $score,
            'status' => $status,
            'completed_at' => now(),
            'per_question_correct' => isset($perQuestionCorrect) ? $perQuestionCorrect : [],
        ]);

        // Skip training progress update for now - just process exam results
        
        return redirect()->route('employee.exam.result', $attempt->id);
    }
}
