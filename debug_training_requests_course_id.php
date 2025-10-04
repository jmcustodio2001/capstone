<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking training_requests table for Communication Skills...\n";
echo "========================================================\n\n";

// Check the specific training request
$requests = DB::table('training_requests')
    ->where('training_title', 'Communication Skills')
    ->get(['id', 'request_id', 'training_title', 'course_id', 'status', 'employee_id']);

echo "Found " . count($requests) . " records:\n\n";

foreach ($requests as $request) {
    echo "ID: {$request->id}\n";
    echo "Request ID: {$request->request_id}\n";
    echo "Training Title: {$request->training_title}\n";
    echo "Course ID: " . ($request->course_id ?? 'NULL') . "\n";
    echo "Status: {$request->status}\n";
    echo "Employee ID: {$request->employee_id}\n";
    echo "---\n";
}

// Check if there are any courses with Communication Skills
echo "\nChecking courses table for Communication Skills...\n";
$courses = DB::table('courses')
    ->where('course_title', 'LIKE', '%Communication%')
    ->orWhere('course_title', 'LIKE', '%Skills%')
    ->get(['course_id', 'course_title']);

echo "Found " . count($courses) . " matching courses:\n\n";

foreach ($courses as $course) {
    echo "Course ID: {$course->course_id}\n";
    echo "Course Title: {$course->course_title}\n";
    echo "---\n";
}

// Check upcoming_trainings for Communication Skills
echo "\nChecking upcoming_trainings table for Communication Skills...\n";
$upcoming = DB::table('upcoming_trainings')
    ->where('training_title', 'LIKE', '%Communication%')
    ->get(['upcoming_id', 'training_title', 'course_id', 'employee_id']);

echo "Found " . count($upcoming) . " matching upcoming trainings:\n\n";

foreach ($upcoming as $training) {
    echo "Upcoming ID: {$training->upcoming_id}\n";
    echo "Training Title: {$training->training_title}\n";
    echo "Course ID: " . ($training->course_id ?? 'NULL') . "\n";
    echo "Employee ID: {$training->employee_id}\n";
    echo "---\n";
}
