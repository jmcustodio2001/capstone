<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing Communication Skills training request course_id...\n";
echo "======================================================\n\n";

// First, check if Communication Skills course exists
$course = DB::table('courses')
    ->where('course_title', 'Communication Skills')
    ->first();

if (!$course) {
    // Create the Communication Skills course
    echo "Creating Communication Skills course...\n";
    
    $courseId = DB::table('courses')->insertGetId([
        'course_title' => 'Communication Skills',
        'description' => 'Develop effective communication skills for professional success',
        'duration_hours' => 8,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "Created course with ID: {$courseId}\n";
} else {
    $courseId = $course->course_id;
    echo "Found existing course with ID: {$courseId}\n";
}

// Update training requests that have Communication Skills but no course_id
$updated = DB::table('training_requests')
    ->where('training_title', 'Communication Skills')
    ->whereNull('course_id')
    ->update(['course_id' => $courseId]);

echo "Updated {$updated} training request(s) with course_id: {$courseId}\n";

// Verify the update
$requests = DB::table('training_requests')
    ->where('training_title', 'Communication Skills')
    ->get(['request_id', 'training_title', 'course_id', 'status']);

echo "\nVerification - Training requests for Communication Skills:\n";
foreach ($requests as $request) {
    echo "Request ID: {$request->request_id}, Course ID: {$request->course_id}, Status: {$request->status}\n";
}

echo "\nDone! The Take Exam button should now appear for approved Communication Skills requests.\n";
