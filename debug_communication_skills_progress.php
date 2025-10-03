<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ExamAttempt;
use App\Models\EmployeeTrainingDashboard;
use App\Models\TrainingRequest;
use App\Models\CourseManagement;

echo "=== DEBUGGING COMMUNICATION SKILLS PROGRESS ===\n\n";

$employeeId = 'EMP001';
$courseTitle = 'Communication Skills';

// Find course by title
$course = CourseManagement::where('course_title', 'LIKE', '%' . $courseTitle . '%')->first();
if (!$course) {
    echo "❌ Course not found for title: $courseTitle\n";
    exit;
}

$courseId = $course->course_id;
echo "✅ Found Course: {$course->course_title} (ID: $courseId)\n\n";

// Check exam attempts
echo "--- EXAM ATTEMPTS ---\n";
$examAttempts = ExamAttempt::where('employee_id', $employeeId)
    ->where('course_id', $courseId)
    ->orderBy('completed_at', 'desc')
    ->get();

if ($examAttempts->count() > 0) {
    foreach ($examAttempts as $attempt) {
        echo "Attempt ID: {$attempt->id}\n";
        echo "Score: {$attempt->score}%\n";
        echo "Status: {$attempt->status}\n";
        echo "Completed: {$attempt->completed_at}\n";
        echo "Pass Status: " . ($attempt->score >= 80 ? 'PASSED' : 'FAILED') . "\n";
        echo "---\n";
    }
} else {
    echo "❌ No exam attempts found\n";
}

// Check employee training dashboard
echo "\n--- EMPLOYEE TRAINING DASHBOARD ---\n";
$dashboardRecord = EmployeeTrainingDashboard::where('employee_id', $employeeId)
    ->where('course_id', $courseId)
    ->first();

if ($dashboardRecord) {
    echo "✅ Dashboard Record Found:\n";
    echo "Progress: {$dashboardRecord->progress}%\n";
    echo "Status: {$dashboardRecord->status}\n";
    echo "Updated At: {$dashboardRecord->updated_at}\n";
    echo "Created At: {$dashboardRecord->created_at}\n";
} else {
    echo "❌ No dashboard record found\n";
}

// Check training requests
echo "\n--- TRAINING REQUESTS ---\n";
$trainingRequest = TrainingRequest::where('employee_id', $employeeId)
    ->where('training_title', 'LIKE', '%' . $courseTitle . '%')
    ->orWhere('course_id', $courseId)
    ->first();

if ($trainingRequest) {
    echo "✅ Training Request Found:\n";
    echo "Request ID: {$trainingRequest->request_id}\n";
    echo "Training Title: {$trainingRequest->training_title}\n";
    echo "Status: {$trainingRequest->status}\n";
    echo "Course ID: {$trainingRequest->course_id}\n";
} else {
    echo "❌ No training request found\n";
}

// Simulate progress calculation logic from _progress.blade.php
echo "\n--- PROGRESS CALCULATION SIMULATION ---\n";
$progressValue = 0;
$progressSource = 'none';

// Priority 1: Check exam progress
if ($examAttempts->count() > 0) {
    $latestAttempt = $examAttempts->first();
    $actualScore = round($latestAttempt->score);
    $progressValue = $actualScore >= 80 ? 100 : $actualScore;
    $progressSource = 'exam';
    echo "✅ Using Exam Progress: {$progressValue}% (Source: {$progressSource})\n";
    echo "Latest Exam Score: {$actualScore}%\n";
    echo "Pass Status: " . ($actualScore >= 80 ? 'PASSED' : 'FAILED') . "\n";
}

// Priority 2: Check dashboard if no exam progress
if ($progressValue == 0 && $dashboardRecord) {
    $progressValue = max(0, min(100, (float)$dashboardRecord->progress));
    $progressSource = 'dashboard';
    echo "✅ Using Dashboard Progress: {$progressValue}% (Source: {$progressSource})\n";
}

echo "\n--- FINAL RESULT ---\n";
echo "Final Progress Value: {$progressValue}%\n";
echo "Progress Source: {$progressSource}\n";

if ($progressValue == 40) {
    echo "\n❌ ISSUE IDENTIFIED: Progress is stuck at 40%\n";
    echo "This suggests the dashboard record has not been updated with the exam result.\n";
    
    if ($examAttempts->count() > 0) {
        $latestAttempt = $examAttempts->first();
        echo "\nRecommended Fix:\n";
        echo "1. Update dashboard record with exam score: {$latestAttempt->score}%\n";
        echo "2. Set progress to: " . ($latestAttempt->score >= 80 ? 100 : $latestAttempt->score) . "%\n";
        echo "3. Update status to: " . ($latestAttempt->score >= 80 ? 'Completed' : 'Failed') . "\n";
    }
}

echo "\n=== DEBUG COMPLETE ===\n";
