<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\CourseManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function take($attemptId)
    {
        $attempt = ExamAttempt::with('course')->findOrFail($attemptId);
        
        // Verify this attempt belongs to the authenticated user
        $user = Auth::user();
        if (!$user || $attempt->employee_id !== $user->employee_id) {
            abort(403, 'Unauthorized access to exam attempt.');
        }
        
        // Check if attempt is already completed
        if ($attempt->status === 'completed') {
            return redirect()->route('employee.exam.result', $attempt->id);
        }
        
        $course = $attempt->course;
        $questions = ExamQuestion::getQuestionsForCourse($attempt->course_id, $attempt->type);
        
        // Auto-generate questions if none exist for this course
        if ($questions->isEmpty()) {
            $this->autoGenerateQuestionsForCourse($attempt->course_id, $course->course_title);
            $questions = ExamQuestion::getQuestionsForCourse($attempt->course_id, $attempt->type);
        }
        
        // Calculate remaining attempts
        $totalAttempts = ExamAttempt::where('employee_id', $attempt->employee_id)
            ->where('course_id', $attempt->course_id)
            ->where('type', $attempt->type)
            ->count();
        $remainingAttempts = 3 - $totalAttempts;
        
        return view('employee_ess_modules.exam.take_exam', compact('attempt', 'course', 'questions', 'remainingAttempts'));
    }

    /**
     * Move completed exam to Completed Trainings section
     */
    private function moveExamToCompletedTrainings($attempt, $progress)
    {
        try {
            $course = CourseManagement::find($attempt->course_id);
            if (!$course) {
                Log::warning("Course not found for exam attempt", ['attempt_id' => $attempt->id]);
                return;
            }

            // Check if completed training record already exists
            $existingRecord = \App\Models\CompletedTraining::where('employee_id', $attempt->employee_id)
                ->where(function($query) use ($course) {
                    $query->where('training_title', $course->course_title)
                          ->orWhere('training_title', 'LIKE', '%' . $course->course_title . '%');
                })
                ->first();

            if (!$existingRecord) {
                // Create completed training record
                \App\Models\CompletedTraining::create([
                    'employee_id' => $attempt->employee_id,
                    'training_title' => $course->course_title,
                    'completion_date' => now(),
                    'remarks' => "Completed via exam with score: {$attempt->score}%",
                    'status' => 'Verified',
                    'certificate_path' => null // Will be updated after certificate generation
                ]);

                Log::info("Automatically moved exam to completed trainings", [
                    'employee_id' => $attempt->employee_id,
                    'course_title' => $course->course_title,
                    'exam_score' => $attempt->score
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error moving exam to completed trainings: ' . $e->getMessage());
        }
    }

    /**
     * Auto-generate exam questions for any training topic
     */
    private function autoGenerateQuestionsForCourse($courseId, $courseTitle)
    {
        $questions = $this->generateTopicSpecificQuestions($courseTitle);
        
        foreach($questions as $q) {
            ExamQuestion::create([
                'course_id' => $courseId,
                'question' => $q['question'],
                'options' => $q['options'],
                'correct_answer' => $q['correct_answer'],
                'explanation' => $q['explanation'] ?? "Knowledge related to $courseTitle",
                'type' => 'exam',
                'points' => 1,
                'is_active' => true
            ]);
        }
        
        Log::info("Auto-generated exam questions for course", [
            'course_id' => $courseId,
            'course_title' => $courseTitle,
            'questions_created' => count($questions)
        ]);
    }

    /**
     * Generate topic-specific questions for ANY training course
     */
    private function generateTopicSpecificQuestions($courseTitle)
    {
        $topic = strtolower($courseTitle);
        
        // DESTINATION KNOWLEDGE - Any location/place
        if (preg_match('/\b(destination|location|place|city|terminal|station|baesa|quezon|cubao|baguio|boracay|cebu|davao|manila|palawan|geography|route|travel|area)\b/i', $courseTitle)) {
            return $this->generateDestinationQuestions($courseTitle);
        }
        // CUSTOMER SERVICE - Any customer-related training
        elseif (preg_match('/\b(customer|service|excellence|client|support|satisfaction)\b/i', $courseTitle)) {
            return $this->generateCustomerServiceQuestions();
        }
        // LEADERSHIP - Any leadership/management training
        elseif (preg_match('/\b(leadership|management|supervisor|team|leader|coaching)\b/i', $courseTitle)) {
            return $this->generateLeadershipQuestions();
        }
        // COMMUNICATION - Any communication training
        elseif (preg_match('/\b(communication|speaking|presentation|writing|verbal|listening)\b/i', $courseTitle)) {
            return $this->generateCommunicationQuestions();
        }
        // TECHNICAL SKILLS - Any technical/software training
        elseif (preg_match('/\b(technical|software|computer|system|technology|programming|digital|IT)\b/i', $courseTitle)) {
            return $this->generateTechnicalQuestions();
        }
        // SAFETY - Any safety/health training
        elseif (preg_match('/\b(safety|health|security|emergency|first aid|workplace safety)\b/i', $courseTitle)) {
            return $this->generateSafetyQuestions();
        }
        // SALES - Any sales/marketing training
        elseif (preg_match('/\b(sales|marketing|selling|negotiation|business development)\b/i', $courseTitle)) {
            return $this->generateSalesQuestions();
        }
        // DEFAULT - Generate topic-specific questions for any other subject
        else {
            return $this->generateGenericTopicQuestions($courseTitle);
        }
    }

    public function submitAjax(Request $request, $attemptId)
    {
        try {
            $attempt = ExamAttempt::findOrFail($attemptId);
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
            }
            $employeeId = $user->employee_id;
            
            // Verify this attempt belongs to the authenticated user
            if ($attempt->employee_id !== $employeeId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to exam attempt.'], 403);
            }
            
            $answers = $request->input('answers', []);
            
            Log::info("EXAM SUBMIT DEBUG", [
                'attempt_id' => $attemptId,
                'employee_id' => $employeeId,
                'raw_request' => $request->all(),
                'answers_received' => $answers,
                'answers_count' => count($answers)
            ]);
            
            // Get questions for this attempt
            $questions = ExamQuestion::getQuestionsForCourse($attempt->course_id, $attempt->type);
            $totalQuestions = $questions->count();
            
            // Check if questions exist
            if ($totalQuestions === 0) {
                Log::warning("No questions found for exam submission", [
                    'course_id' => $attempt->course_id,
                    'type' => $attempt->type
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No questions available for this exam.'
                ], 400);
            }
            
            $correctAnswers = 0;
            
            // Enhanced answer validation - accurate and robust
            foreach ($questions as $question) {
                $userAnswer = $answers[$question->id] ?? null;
                $isCorrect = false;
                
                if ($userAnswer !== null && $userAnswer !== '') {
                    // Safely get the selected answer text from options
                    $selectedAnswerText = null;
                    if (is_array($question->options) && isset($question->options[$userAnswer])) {
                        $selectedAnswerText = $question->options[$userAnswer];
                    }
                    
                    // Enhanced validation with multiple comparison methods
                    if ($selectedAnswerText) {
                        $selectedClean = trim(strtolower($selectedAnswerText));
                        $correctClean = trim(strtolower($question->correct_answer));
                        
                        // Method 1: Exact match (case-insensitive)
                        if ($selectedClean === $correctClean) {
                            $isCorrect = true;
                        }
                        // Method 2: Remove extra spaces and punctuation
                        elseif (preg_replace('/[^a-z0-9\s]/i', '', $selectedClean) === preg_replace('/[^a-z0-9\s]/i', '', $correctClean)) {
                            $isCorrect = true;
                        }
                        // Method 3: Check if selected answer contains the correct answer or vice versa
                        elseif (strpos($selectedClean, $correctClean) !== false || strpos($correctClean, $selectedClean) !== false) {
                            $isCorrect = true;
                        }
                        
                        Log::info($isCorrect ? "Answer CORRECT" : "Answer INCORRECT", [
                            'question_id' => $question->id,
                            'user_selected' => $userAnswer,
                            'selected_text' => $selectedAnswerText,
                            'correct_answer' => $question->correct_answer,
                            'selected_clean' => $selectedClean,
                            'correct_clean' => $correctClean,
                            'is_correct' => $isCorrect
                        ]);
                    } else {
                        Log::warning("Answer option not found", [
                            'question_id' => $question->id,
                            'user_selected' => $userAnswer,
                            'available_options' => is_array($question->options) ? array_keys($question->options) : 'none'
                        ]);
                    }
                } else {
                    Log::info("Answer SKIPPED", [
                        'question_id' => $question->id,
                        'user_answer' => $userAnswer
                    ]);
                }
                
                if ($isCorrect) {
                    $correctAnswers++;
                }
            }
            
            // Calculate score
            $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
            
            Log::info("Exam scoring complete", [
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'calculated_score' => $score
            ]);
            
            // Update exam attempt with correct pass/fail status
            $isPassed = $score >= 80;
            $examStatus = $isPassed ? 'completed' : 'failed';
            
            $attempt->update([
                'answers' => $answers,
                'score' => $score,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'status' => $examStatus,
                'completed_at' => now()
            ]);
            
            // Update training progress based on accurate pass/fail logic
            // 80-100% = PASSED (100% progress), Below 80% = FAILED (progress = actual score)
            $progress = $isPassed ? 100 : $score;
            $status = $isPassed ? 'Completed' : 'Failed';
            
            Log::info("Pass/Fail determination", [
                'score' => $score,
                'is_passed' => $isPassed,
                'progress' => $progress,
                'status' => $status
            ]);
            
            // Safely update or create training progress records
            try {
                // Force update - ensure we always update the record
                $updated = DB::table('employee_training_dashboard')
                    ->where('employee_id', $employeeId)
                    ->where('course_id', $attempt->course_id)
                    ->update([
                        'progress' => $progress,
                        'status' => $status,
                        'updated_at' => now(),
                        'remarks' => 'Updated from exam completion - Score: ' . $score . '%'
                    ]);
                
                Log::info("Dashboard update attempt", [
                    'employee_id' => $employeeId,
                    'course_id' => $attempt->course_id,
                    'progress' => $progress,
                    'status' => $status,
                    'rows_affected' => $updated
                ]);
                
                // If no record was updated, create a new one
                if ($updated === 0) {
                    // Check if record exists but wasn't updated due to constraints
                    $existingRecord = DB::table('employee_training_dashboard')
                        ->where('employee_id', $employeeId)
                        ->where('course_id', $attempt->course_id)
                        ->first();
                    
                    if ($existingRecord) {
                        // Force update using raw SQL
                        DB::statement(
                            "UPDATE employee_training_dashboard SET progress = ?, status = ?, updated_at = NOW(), remarks = ? WHERE employee_id = ? AND course_id = ?",
                            [$progress, $status, 'Force updated from exam completion - Score: ' . $score . '%', $employeeId, $attempt->course_id]
                        );
                        Log::info("Force updated existing dashboard record", [
                            'employee_id' => $employeeId,
                            'course_id' => $attempt->course_id,
                            'progress' => $progress,
                            'status' => $status
                        ]);
                    } else {
                        // Create new record
                        DB::table('employee_training_dashboard')->insert([
                            'employee_id' => $employeeId,
                            'course_id' => $attempt->course_id,
                            'training_date' => now()->format('Y-m-d'),
                            'progress' => $progress,
                            'status' => $status,
                            'remarks' => 'Auto-created from exam completion - Score: ' . $score . '%',
                            'assigned_by' => 1, // System/Admin
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        Log::info("Created new dashboard record", [
                            'employee_id' => $employeeId,
                            'course_id' => $attempt->course_id,
                            'progress' => $progress,
                            'status' => $status
                        ]);
                    }
                } else {
                    Log::info("Successfully updated dashboard record", [
                        'employee_id' => $employeeId,
                        'course_id' => $attempt->course_id,
                        'progress' => $progress,
                        'status' => $status,
                        'rows_updated' => $updated
                    ]);
                }
                
                // Verify the update worked
                $verifyRecord = DB::table('employee_training_dashboard')
                    ->where('employee_id', $employeeId)
                    ->where('course_id', $attempt->course_id)
                    ->first();
                
                if ($verifyRecord) {
                    Log::info("Dashboard record verification", [
                        'employee_id' => $employeeId,
                        'course_id' => $attempt->course_id,
                        'current_progress' => $verifyRecord->progress,
                        'current_status' => $verifyRecord->status,
                        'expected_progress' => $progress,
                        'expected_status' => $status,
                        'match' => ($verifyRecord->progress == $progress && $verifyRecord->status == $status)
                    ]);
                } else {
                    Log::error("Dashboard record not found after update attempt", [
                        'employee_id' => $employeeId,
                        'course_id' => $attempt->course_id
                    ]);
                }
                    
                // If exam is passed (100% progress), automatically move to Completed Trainings
                if ($isPassed) {
                    $this->moveExamToCompletedTrainings($attempt, $progress);
                    
                    // Update competency profile when exam is passed
                    $this->updateCompetencyProfileFromExam($attempt, $progress);
                }
                
                // Also update any training requests for this course
                DB::table('training_requests')
                    ->where('employee_id', $employeeId)
                    ->where('course_id', $attempt->course_id)
                    ->where('status', 'Approved')
                    ->update([
                        'updated_at' => now()
                    ]);
                    
            } catch (\Exception $e) {
                Log::warning("Failed to update employee_training_dashboard: " . $e->getMessage());
            }
            
            // Update destination knowledge training records with enhanced matching
            try {
                $course = CourseManagement::find($attempt->course_id);
                if ($course) {
                    // Convert status to match destination training format
                    $destinationStatus = $status === 'Completed' ? 'completed' : 
                                       ($status === 'In Progress' ? 'in-progress' : 'not-started');
                    
                    // Enhanced matching logic - try multiple patterns
                    $courseTitle = $course->course_title;
                    $updated = 0;
                    
                    // Pattern 1: Exact match
                    $updated = DB::table('destination_knowledge_trainings')
                        ->where('employee_id', $employeeId)
                        ->where('destination_name', $courseTitle)
                        ->update([
                            'progress' => $progress,
                            'status' => $destinationStatus,
                            'date_completed' => $progress >= 100 ? now()->format('Y-m-d') : null,
                            'updated_at' => now()
                        ]);
                    
                    // Pattern 2: If no exact match, try LIKE patterns
                    if ($updated === 0) {
                        $updated = DB::table('destination_knowledge_trainings')
                            ->where('employee_id', $employeeId)
                            ->where(function($query) use ($courseTitle) {
                                $query->where('destination_name', 'LIKE', '%' . $courseTitle . '%')
                                      ->orWhere('destination_name', 'LIKE', $courseTitle . '%')
                                      ->orWhereRaw('UPPER(destination_name) = UPPER(?)', [$courseTitle]);
                            })
                            ->update([
                                'progress' => $progress,
                                'status' => $destinationStatus,
                                'date_completed' => $progress >= 100 ? now()->format('Y-m-d') : null,
                                'updated_at' => now()
                            ]);
                    }
                    
                    // Pattern 3: If still no match, try word-based matching
                    if ($updated === 0) {
                        $words = explode(' ', $courseTitle);
                        foreach ($words as $word) {
                            if (strlen($word) > 3) { // Only use significant words
                                $updated = DB::table('destination_knowledge_trainings')
                                    ->where('employee_id', $employeeId)
                                    ->where('destination_name', 'LIKE', '%' . $word . '%')
                                    ->update([
                                        'progress' => $progress,
                                        'status' => $destinationStatus,
                                        'date_completed' => $progress >= 100 ? now()->format('Y-m-d') : null,
                                        'updated_at' => now()
                                    ]);
                                if ($updated > 0) break;
                            }
                        }
                    }
                    
                    Log::info("Updated destination knowledge training", [
                        'employee_id' => $employeeId,
                        'course_title' => $courseTitle,
                        'records_updated' => $updated,
                        'progress' => $progress,
                        'status' => $destinationStatus
                    ]);
                    
                    // If still no records updated, log available records for debugging
                    if ($updated === 0) {
                        $availableRecords = DB::table('destination_knowledge_trainings')
                            ->where('employee_id', $employeeId)
                            ->pluck('destination_name')
                            ->toArray();
                        
                        Log::warning("No destination training records updated", [
                            'employee_id' => $employeeId,
                            'course_title' => $courseTitle,
                            'available_destinations' => $availableRecords
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Failed to update destination_knowledge_training: " . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Exam submitted successfully',
                'score' => $score,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'status' => 'completed'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Exam submission error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Submission failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display exam result
     */
    public function result($attemptId)
    {
        $attempt = ExamAttempt::with(['course', 'employee'])->findOrFail($attemptId);
        
        // Verify this attempt belongs to the authenticated user
        $user = Auth::user();
        if (!$user || $attempt->employee_id !== $user->employee_id) {
            abort(403, 'Unauthorized access to exam result.');
        }
        
        // Calculate remaining attempts
        $totalAttempts = ExamAttempt::where('employee_id', $attempt->employee_id)
            ->where('course_id', $attempt->course_id)
            ->where('type', $attempt->type)
            ->count();
        $remainingAttempts = max(0, 3 - $totalAttempts);
        
        // Calculate scores and progress
        $scores = [
            'exam_score' => $attempt->score,
            'quiz_score' => 0
        ];
        
        // Apply same pass/fail logic: 80-100% = passed, 75% and below = failed
        $combinedProgress = $attempt->score >= 80 ? 100 : $attempt->score;
        
        return view('employee_ess_modules.exam.simple_result', compact('attempt', 'scores', 'combinedProgress', 'remainingAttempts'));
    }

    /**
     * Display simple exam result page
     */
    public function simpleResult($attemptId)
    {
        $attempt = ExamAttempt::with(['course', 'employee'])->findOrFail($attemptId);
        
        // Verify this attempt belongs to the authenticated user
        $user = Auth::user();
        if (!$user || $attempt->employee_id !== $user->employee_id) {
            abort(403, 'Unauthorized access to exam result.');
        }
        
        // Calculate remaining attempts
        $totalAttempts = ExamAttempt::where('employee_id', $attempt->employee_id)
            ->where('course_id', $attempt->course_id)
            ->where('type', $attempt->type)
            ->count();
        $remainingAttempts = max(0, 3 - $totalAttempts);
        
        // Calculate scores and progress
        $scores = [
            'exam_score' => $attempt->score,
            'quiz_score' => 0
        ];
        
        // Apply same pass/fail logic: 80-100% = passed, 75% and below = failed
        $combinedProgress = $attempt->score >= 80 ? 100 : $attempt->score;
        
        return view('employee_ess_modules.exam.simple_result', compact('attempt', 'scores', 'combinedProgress', 'remainingAttempts'));
    }

    /**
     * Start a new exam for the given course
     */
    public function startExam($courseId)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Authentication required.');
        }
        $employeeId = $user->employee_id;
        
        // Find the course
        $course = CourseManagement::findOrFail($courseId);
        
        // Check if employee has already completed this exam
        $completedAttempt = ExamAttempt::where('employee_id', $employeeId)
            ->where('course_id', $courseId)
            ->where('status', 'completed')
            ->where('score', '>=', 80)
            ->first();
            
        if ($completedAttempt) {
            return redirect()->to('/employee/exam/result/' . $completedAttempt->id)
                ->with('info', 'You have already passed this exam.');
        }
        
        // Check remaining attempts (max 3)
        $totalAttempts = ExamAttempt::where('employee_id', $employeeId)
            ->where('course_id', $courseId)
            ->count();
            
        if ($totalAttempts >= 3) {
            return redirect()->route('employee.my_trainings.index')
                ->with('error', 'You have reached the maximum number of exam attempts (3) for this course.');
        }
        
        // Create new exam attempt
        $attempt = ExamAttempt::create([
            'employee_id' => $employeeId,
            'course_id' => $courseId,
            'type' => 'exam',
            'status' => 'in_progress',
            'started_at' => now(),
            'answers' => [],
            'score' => 0,
            'correct_answers' => 0,
            'total_questions' => 0
        ]);
        
        Log::info("New exam attempt created", [
            'attempt_id' => $attempt->id,
            'employee_id' => $employeeId,
            'course_id' => $courseId,
            'remaining_attempts' => 3 - $totalAttempts - 1
        ]);
        
        return redirect()->to('/employee/exam/take/' . $attempt->id);
    }

    /**
     * Show exam result
     */
    public function showResult($attemptId)
    {
        return $this->result($attemptId);
    }

    /**
     * Show simple exam result
     */
    public function showSimpleResult($attemptId)
    {
        return $this->simpleResult($attemptId);
    }

    /**
     * Generate destination knowledge questions
     */
    private function generateDestinationQuestions($courseTitle)
    {
        $location = preg_replace('/\b(destination|knowledge|training|course)\b/i', '', $courseTitle);
        $location = trim($location);
        
        return [
            [
                'question' => "What is the primary purpose of learning about $location?",
                'options' => [
                    'a' => 'To provide accurate travel information to clients',
                    'b' => 'For personal vacation planning',
                    'c' => 'To memorize facts',
                    'd' => 'For academic purposes only'
                ],
                'correct_answer' => 'To provide accurate travel information to clients'
            ],
            [
                'question' => "When advising clients about $location, what should be your priority?",
                'options' => [
                    'a' => 'Selling the most expensive package',
                    'b' => 'Understanding client needs and preferences',
                    'c' => 'Promoting only popular attractions',
                    'd' => 'Following standard scripts'
                ],
                'correct_answer' => 'Understanding client needs and preferences'
            ],
            [
                'question' => "What type of information should you gather about $location?",
                'options' => [
                    'a' => 'Only tourist attractions',
                    'b' => 'Transportation, accommodation, activities, and local culture',
                    'c' => 'Just hotel prices',
                    'd' => 'Weather information only'
                ],
                'correct_answer' => 'Transportation, accommodation, activities, and local culture'
            ],
            [
                'question' => "How should you present $location information to clients?",
                'options' => [
                    'a' => 'Use technical travel industry terms',
                    'b' => 'Provide clear, relevant, and personalized information',
                    'c' => 'Give all available information at once',
                    'd' => 'Focus only on positive aspects'
                ],
                'correct_answer' => 'Provide clear, relevant, and personalized information'
            ],
            [
                'question' => "What should you do if a client asks about $location and you don't know the answer?",
                'options' => [
                    'a' => 'Make up an answer',
                    'b' => 'Admit you don\'t know and offer to find out',
                    'c' => 'Change the subject',
                    'd' => 'Refer them to someone else immediately'
                ],
                'correct_answer' => 'Admit you don\'t know and offer to find out'
            ]
        ];
    }

    /**
     * Generate customer service questions
     */
    private function generateCustomerServiceQuestions()
    {
        return [
            [
                'question' => 'What is the foundation of excellent customer service?',
                'options' => [
                    'a' => 'Following company policies strictly',
                    'b' => 'Understanding and meeting customer needs',
                    'c' => 'Selling more products',
                    'd' => 'Processing requests quickly'
                ],
                'correct_answer' => 'Understanding and meeting customer needs'
            ],
            [
                'question' => 'How should you handle a frustrated customer?',
                'options' => [
                    'a' => 'Defend company policies',
                    'b' => 'Listen actively and show empathy',
                    'c' => 'Transfer them immediately',
                    'd' => 'Explain why they are wrong'
                ],
                'correct_answer' => 'Listen actively and show empathy'
            ],
            [
                'question' => 'What demonstrates service excellence?',
                'options' => [
                    'a' => 'Meeting minimum requirements',
                    'b' => 'Going above and beyond expectations',
                    'c' => 'Following scripts exactly',
                    'd' => 'Processing requests fast'
                ],
                'correct_answer' => 'Going above and beyond expectations'
            ],
            [
                'question' => 'How should you communicate service limitations?',
                'options' => [
                    'a' => 'Avoid mentioning them',
                    'b' => 'Be honest and offer alternatives',
                    'c' => 'Blame company policies',
                    'd' => 'Use technical explanations'
                ],
                'correct_answer' => 'Be honest and offer alternatives'
            ],
            [
                'question' => 'What builds long-term customer relationships?',
                'options' => [
                    'a' => 'Lowest prices only',
                    'b' => 'Consistent quality service',
                    'c' => 'Frequent promotions',
                    'd' => 'Automated responses'
                ],
                'correct_answer' => 'Consistent quality service'
            ]
        ];
    }

    /**
     * Generate leadership questions
     */
    private function generateLeadershipQuestions()
    {
        return [
            [
                'question' => 'What is the key to effective leadership?',
                'options' => [
                    'a' => 'Having authority over others',
                    'b' => 'Inspiring and guiding team members',
                    'c' => 'Making all decisions alone',
                    'd' => 'Enforcing strict rules'
                ],
                'correct_answer' => 'Inspiring and guiding team members'
            ],
            [
                'question' => 'How should a leader handle team conflicts?',
                'options' => [
                    'a' => 'Ignore the conflict',
                    'b' => 'Facilitate open discussion and resolution',
                    'c' => 'Take sides immediately',
                    'd' => 'Punish all involved parties'
                ],
                'correct_answer' => 'Facilitate open discussion and resolution'
            ],
            [
                'question' => 'What is effective delegation?',
                'options' => [
                    'a' => 'Giving tasks to anyone available',
                    'b' => 'Matching tasks to skills and providing clear guidance',
                    'c' => 'Avoiding delegation entirely',
                    'd' => 'Delegating only easy tasks'
                ],
                'correct_answer' => 'Matching tasks to skills and providing clear guidance'
            ],
            [
                'question' => 'How should leaders motivate their team?',
                'options' => [
                    'a' => 'Money incentives only',
                    'b' => 'Recognition and development opportunities',
                    'c' => 'Strict supervision',
                    'd' => 'Competition among members'
                ],
                'correct_answer' => 'Recognition and development opportunities'
            ],
            [
                'question' => 'What makes leadership communication effective?',
                'options' => [
                    'a' => 'Speaking loudly and clearly',
                    'b' => 'Being transparent and providing clear direction',
                    'c' => 'Using complex business terms',
                    'd' => 'Communicating only when necessary'
                ],
                'correct_answer' => 'Being transparent and providing clear direction'
            ]
        ];
    }

    /**
     * Generate communication questions
     */
    private function generateCommunicationQuestions()
    {
        return [
            [
                'question' => 'What is the most important aspect of effective communication?',
                'options' => [
                    'a' => 'Speaking loudly and clearly',
                    'b' => 'Active listening and understanding',
                    'c' => 'Using complex vocabulary',
                    'd' => 'Talking without interruption'
                ],
                'correct_answer' => 'Active listening and understanding'
            ],
            [
                'question' => 'How should you handle communication barriers?',
                'options' => [
                    'a' => 'Ignore them and continue',
                    'b' => 'Identify and address them directly',
                    'c' => 'Speak louder',
                    'd' => 'Use more technical terms'
                ],
                'correct_answer' => 'Identify and address them directly'
            ],
            [
                'question' => 'What makes written communication effective?',
                'options' => [
                    'a' => 'Using complex sentences',
                    'b' => 'Being clear, concise, and purposeful',
                    'c' => 'Including lots of details',
                    'd' => 'Using formal language only'
                ],
                'correct_answer' => 'Being clear, concise, and purposeful'
            ],
            [
                'question' => 'How should you provide feedback in communication?',
                'options' => [
                    'a' => 'Focus on personality traits',
                    'b' => 'Be specific about behaviors and impact',
                    'c' => 'Give feedback publicly',
                    'd' => 'Wait for formal reviews'
                ],
                'correct_answer' => 'Be specific about behaviors and impact'
            ],
            [
                'question' => 'What percentage of communication is typically nonverbal?',
                'options' => [
                    'a' => '10%',
                    'b' => '35%',
                    'c' => '55%',
                    'd' => '80%'
                ],
                'correct_answer' => '55%'
            ]
        ];
    }

    /**
     * Generate technical questions
     */
    private function generateTechnicalQuestions()
    {
        return [
            [
                'question' => 'What is the most important practice in technical work?',
                'options' => [
                    'a' => 'Working as fast as possible',
                    'b' => 'Testing and documenting your work',
                    'c' => 'Using the latest technology',
                    'd' => 'Working independently'
                ],
                'correct_answer' => 'Testing and documenting your work'
            ],
            [
                'question' => 'How should you approach learning new technical skills?',
                'options' => [
                    'a' => 'Theory study only',
                    'b' => 'Hands-on practice with theoretical understanding',
                    'c' => 'Watching tutorials only',
                    'd' => 'Trial and error approach'
                ],
                'correct_answer' => 'Hands-on practice with theoretical understanding'
            ],
            [
                'question' => 'What is the first step in technical problem solving?',
                'options' => [
                    'a' => 'Start implementing solutions immediately',
                    'b' => 'Clearly understand the problem',
                    'c' => 'Look for existing solutions online',
                    'd' => 'Ask colleagues for help'
                ],
                'correct_answer' => 'Clearly understand the problem'
            ],
            [
                'question' => 'How should technical professionals stay current?',
                'options' => [
                    'a' => 'Stick to familiar tools only',
                    'b' => 'Continuously learn and adapt to new technologies',
                    'c' => 'Wait for company training',
                    'd' => 'Follow technology trends blindly'
                ],
                'correct_answer' => 'Continuously learn and adapt to new technologies'
            ],
            [
                'question' => 'What should be prioritized in technical solutions?',
                'options' => [
                    'a' => 'Speed of delivery',
                    'b' => 'Security and reliability',
                    'c' => 'Latest features only',
                    'd' => 'Minimal testing'
                ],
                'correct_answer' => 'Security and reliability'
            ]
        ];
    }

    /**
     * Generate safety questions
     */
    private function generateSafetyQuestions()
    {
        return [
            [
                'question' => 'What is the primary goal of workplace safety?',
                'options' => [
                    'a' => 'Following regulations only',
                    'b' => 'Preventing accidents and protecting employees',
                    'c' => 'Reducing insurance costs',
                    'd' => 'Meeting minimum requirements'
                ],
                'correct_answer' => 'Preventing accidents and protecting employees'
            ],
            [
                'question' => 'When should safety procedures be followed?',
                'options' => [
                    'a' => 'Only when supervisors are watching',
                    'b' => 'Always, without exception',
                    'c' => 'Only for dangerous tasks',
                    'd' => 'When convenient'
                ],
                'correct_answer' => 'Always, without exception'
            ],
            [
                'question' => 'What should you do if you notice a safety hazard?',
                'options' => [
                    'a' => 'Ignore it if it doesn\'t affect you',
                    'b' => 'Report it immediately to appropriate personnel',
                    'c' => 'Fix it yourself without telling anyone',
                    'd' => 'Wait for someone else to notice'
                ],
                'correct_answer' => 'Report it immediately to appropriate personnel'
            ],
            [
                'question' => 'Why is safety training important for all employees?',
                'options' => [
                    'a' => 'It\'s required by law only',
                    'b' => 'It protects everyone and creates a safe work environment',
                    'c' => 'It reduces company liability',
                    'd' => 'It\'s just a formality'
                ],
                'correct_answer' => 'It protects everyone and creates a safe work environment'
            ],
            [
                'question' => 'What is the best approach to emergency preparedness?',
                'options' => [
                    'a' => 'Hope emergencies never happen',
                    'b' => 'Know procedures and practice regularly',
                    'c' => 'Rely on others to handle emergencies',
                    'd' => 'Read procedures only when needed'
                ],
                'correct_answer' => 'Know procedures and practice regularly'
            ]
        ];
    }

    /**
     * Generate sales questions
     */
    private function generateSalesQuestions()
    {
        return [
            [
                'question' => 'What is the foundation of successful selling?',
                'options' => [
                    'a' => 'Aggressive persuasion techniques',
                    'b' => 'Understanding customer needs and building relationships',
                    'c' => 'Offering lowest prices',
                    'd' => 'Using high-pressure tactics'
                ],
                'correct_answer' => 'Understanding customer needs and building relationships'
            ],
            [
                'question' => 'How should you handle customer objections?',
                'options' => [
                    'a' => 'Argue against their concerns',
                    'b' => 'Listen, acknowledge, and address concerns',
                    'c' => 'Ignore objections and continue selling',
                    'd' => 'Offer immediate discounts'
                ],
                'correct_answer' => 'Listen, acknowledge, and address concerns'
            ],
            [
                'question' => 'What makes a sales presentation effective?',
                'options' => [
                    'a' => 'Focusing only on product features',
                    'b' => 'Connecting benefits to customer needs',
                    'c' => 'Using complex technical details',
                    'd' => 'Talking without interruption'
                ],
                'correct_answer' => 'Connecting benefits to customer needs'
            ],
            [
                'question' => 'When is the best time to close a sale?',
                'options' => [
                    'a' => 'At the beginning of the conversation',
                    'b' => 'When customer shows buying signals',
                    'c' => 'After presenting all features',
                    'd' => 'At the end of every meeting'
                ],
                'correct_answer' => 'When customer shows buying signals'
            ],
            [
                'question' => 'How should you follow up after a sale?',
                'options' => [
                    'a' => 'Move on to next customer immediately',
                    'b' => 'Ensure customer satisfaction and build ongoing relationship',
                    'c' => 'Only contact if problems arise',
                    'd' => 'Wait for customer to contact you'
                ],
                'correct_answer' => 'Ensure customer satisfaction and build ongoing relationship'
            ]
        ];
    }

    /**
     * Generate generic topic questions for any training subject
     */
    private function generateGenericTopicQuestions($courseTitle)
    {
        $topicName = preg_replace('/\b(training|course|program|module|workshop|seminar)\b/i', '', $courseTitle);
        $topicName = trim($topicName);
        
        return [
            [
                'question' => "What is the main objective of $topicName training?",
                'options' => [
                    'a' => 'To complete required hours',
                    'b' => 'To develop relevant skills and knowledge',
                    'c' => 'To get a certificate',
                    'd' => 'To satisfy company requirements'
                ],
                'correct_answer' => 'To develop relevant skills and knowledge'
            ],
            [
                'question' => "How should you apply $topicName knowledge in your work?",
                'options' => [
                    'a' => 'Only when specifically asked',
                    'b' => 'Integrate it into daily work practices',
                    'c' => 'Use it occasionally',
                    'd' => 'Keep it separate from work'
                ],
                'correct_answer' => 'Integrate it into daily work practices'
            ],
            [
                'question' => "What demonstrates mastery of $topicName concepts?",
                'options' => [
                    'a' => 'Memorizing all information',
                    'b' => 'Applying knowledge effectively in real situations',
                    'c' => 'Passing the test only',
                    'd' => 'Explaining concepts to others'
                ],
                'correct_answer' => 'Applying knowledge effectively in real situations'
            ],
            [
                'question' => "How should you continue developing $topicName skills?",
                'options' => [
                    'a' => 'Stop learning after training',
                    'b' => 'Practice regularly and seek feedback',
                    'c' => 'Wait for advanced training',
                    'd' => 'Focus on other skills instead'
                ],
                'correct_answer' => 'Practice regularly and seek feedback'
            ],
            [
                'question' => "What is the best way to share $topicName knowledge with colleagues?",
                'options' => [
                    'a' => 'Keep knowledge to yourself',
                    'b' => 'Share insights and collaborate on improvements',
                    'c' => 'Only share when asked directly',
                    'd' => 'Demonstrate superiority over others'
                ],
                'correct_answer' => 'Share insights and collaborate on improvements'
            ]
        ];
    }

/**
 * Update competency profile when exam is passed
 */
private function updateCompetencyProfileFromExam($attempt, $progress)
{
    try {
        $course = CourseManagement::find($attempt->course_id);
        if (!$course) {
            Log::warning("Course not found for competency update", ['attempt_id' => $attempt->id]);
            return;
        }

        // Extract competency name from course title
        $courseTitle = str_replace([' Training', ' Course', ' Program'], '', $course->course_title);
        
        // Find matching competency profile
        $competencyProfile = \App\Models\EmployeeCompetencyProfile::whereHas('competency', function($q) use ($courseTitle, $course) {
            $q->where('competency_name', 'LIKE', '%' . $courseTitle . '%')
              ->orWhere('competency_name', 'LIKE', '%' . $course->course_title . '%')
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
        ->where('employee_id', $attempt->employee_id)
        ->first();

        if ($competencyProfile) {
            // Convert exam score to proficiency level (1-5 scale)
            $proficiencyLevel = 5; // Passed exam = maximum proficiency
            if ($attempt->score < 90) $proficiencyLevel = 4;
            if ($attempt->score < 85) $proficiencyLevel = 3;
            
            $competencyProfile->proficiency_level = $proficiencyLevel;
            $competencyProfile->assessment_date = now();
            $competencyProfile->save();
            
            Log::info("Updated competency profile from exam", [
                'employee_id' => $attempt->employee_id,
                'course_title' => $course->course_title,
                'exam_score' => $attempt->score,
                'proficiency_level' => $proficiencyLevel
            ]);
            
            // Also update competency gap if exists
            $competencyGap = \App\Models\CompetencyGap::whereHas('competency', function($q) use ($courseTitle, $course) {
                $q->where('competency_name', 'LIKE', '%' . $courseTitle . '%')
                  ->orWhere('competency_name', 'LIKE', '%' . $course->course_title . '%');
            })
            ->where('employee_id', $attempt->employee_id)
            ->first();
            
            if ($competencyGap) {
                $competencyGap->current_level = $proficiencyLevel;
                $competencyGap->gap = max(0, $competencyGap->required_level - $proficiencyLevel);
                $competencyGap->save();
                
                Log::info("Updated competency gap from exam", [
                    'employee_id' => $attempt->employee_id,
                    'course_title' => $course->course_title,
                    'current_level' => $proficiencyLevel,
                    'gap' => $competencyGap->gap
                ]);
            }
        } else {
            // Create new competency profile if none exists
            $competency = \App\Models\CompetencyLibrary::where('competency_name', 'LIKE', '%' . $courseTitle . '%')
                ->orWhere('competency_name', 'LIKE', '%' . $course->course_title . '%')
                ->first();
            
            if ($competency) {
                $proficiencyLevel = 5; // Passed exam = maximum proficiency
                if ($attempt->score < 90) $proficiencyLevel = 4;
                if ($attempt->score < 85) $proficiencyLevel = 3;
                
                \App\Models\EmployeeCompetencyProfile::create([
                    'employee_id' => $attempt->employee_id,
                    'competency_id' => $competency->competency_id,
                    'proficiency_level' => $proficiencyLevel,
                    'assessment_date' => now(),
                    'assessment_method' => 'Exam Result',
                    'notes' => "Auto-created from exam completion with {$attempt->score}% score"
                ]);
                
                Log::info("Created new competency profile from exam", [
                    'employee_id' => $attempt->employee_id,
                    'competency_name' => $competency->competency_name,
                    'proficiency_level' => $proficiencyLevel
                ]);
            }
        }
    } catch (\Exception $e) {
        Log::error('Error updating competency profile from exam: ' . $e->getMessage());
    }
}
}
