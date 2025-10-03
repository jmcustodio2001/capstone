<?php

// Simple fix using Laravel's DB facade
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing Communication Skills Take Exam button...\n";

try {
    // Check if Communication Skills course exists
    $course = DB::table('courses')
        ->where('course_title', 'Communication Skills')
        ->first();
    
    if (!$course) {
        // Create the course
        $courseId = DB::table('courses')->insertGetId([
            'course_title' => 'Communication Skills',
            'description' => 'Develop effective communication skills for professional success',
            'duration_hours' => 8,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Created Communication Skills course with ID: $courseId\n";
    } else {
        $courseId = $course->course_id;
        echo "Found existing Communication Skills course with ID: $courseId\n";
    }
    
    // Update training requests
    $updated = DB::table('training_requests')
        ->where('training_title', 'Communication Skills')
        ->whereNull('course_id')
        ->update(['course_id' => $courseId]);
    
    echo "Updated $updated training request(s)\n";
    
    // Create exam questions if none exist
    $questionCount = DB::table('exam_questions')
        ->where('course_id', $courseId)
        ->count();
    
    if ($questionCount == 0) {
        $questions = [
            [
                'course_id' => $courseId,
                'question' => 'What is the most important aspect of effective communication?',
                'option_a' => 'Speaking loudly',
                'option_b' => 'Active listening',
                'option_c' => 'Using complex vocabulary',
                'option_d' => 'Talking fast',
                'correct_answer' => 'B',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'course_id' => $courseId,
                'question' => 'Which of the following is a barrier to effective communication?',
                'option_a' => 'Clear language',
                'option_b' => 'Active listening',
                'option_c' => 'Noise and distractions',
                'option_d' => 'Eye contact',
                'correct_answer' => 'C',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'course_id' => $courseId,
                'question' => 'What does non-verbal communication include?',
                'option_a' => 'Body language and facial expressions',
                'option_b' => 'Only spoken words',
                'option_c' => 'Written text',
                'option_d' => 'Email messages',
                'correct_answer' => 'A',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        DB::table('exam_questions')->insert($questions);
        echo "Created " . count($questions) . " exam questions\n";
    }
    
    // Verify the fix
    $requests = DB::table('training_requests')
        ->where('training_title', 'Communication Skills')
        ->get(['request_id', 'course_id', 'status']);
    
    echo "\nVerification:\n";
    foreach ($requests as $request) {
        echo "Request ID: {$request->request_id}, Course ID: {$request->course_id}, Status: {$request->status}\n";
    }
    
    echo "\n✅ Fix completed! Refresh the page to see the Take Exam button.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
