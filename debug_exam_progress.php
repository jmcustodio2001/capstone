<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG: Communication Skills Exam Progress Issue ===\n\n";

// 1. Check if Communication Skills course exists
$course = \App\Models\CourseManagement::where('course_title', 'Communication Skills')->first();
if (!$course) {
    echo "❌ Communication Skills course not found!\n";
    exit;
}

echo "✅ Course found: ID = {$course->course_id}, Title = {$course->course_title}\n\n";

// 2. Check exam attempts for EMP001
$attempts = \App\Models\ExamAttempt::where('employee_id', 'EMP001')
    ->where('course_id', $course->course_id)
    ->orderBy('completed_at', 'desc')
    ->get();

echo "📊 Exam Attempts for EMP001:\n";
echo "Total attempts: " . $attempts->count() . "\n";

foreach ($attempts as $attempt) {
    echo "- Attempt ID: {$attempt->id}\n";
    echo "  Score: {$attempt->score}%\n";
    echo "  Status: {$attempt->status}\n";
    echo "  Completed: " . ($attempt->completed_at ? $attempt->completed_at->format('Y-m-d H:i:s') : 'Not completed') . "\n";
    echo "  Course ID: {$attempt->course_id}\n\n";
}

// 3. Test progress calculation
$progress = \App\Models\ExamAttempt::calculateCombinedProgress('EMP001', $course->course_id);
echo "🧮 Calculated Progress: {$progress}%\n\n";

// 4. Check training dashboard records
$dashboard = \App\Models\EmployeeTrainingDashboard::where('employee_id', 'EMP001')
    ->where('course_id', $course->course_id)
    ->first();

echo "📋 Training Dashboard Record:\n";
if ($dashboard) {
    echo "- Progress: {$dashboard->progress}%\n";
    echo "- Status: {$dashboard->status}\n";
    echo "- Updated: {$dashboard->updated_at}\n\n";
} else {
    echo "❌ No training dashboard record found\n\n";
}

// 5. Check training requests
$requests = \App\Models\TrainingRequest::where('employee_id', 'EMP001')
    ->where('training_title', 'Communication Skills')
    ->get();

echo "📝 Training Requests:\n";
foreach ($requests as $request) {
    echo "- Request ID: {$request->request_id}\n";
    echo "  Status: {$request->status}\n";
    echo "  Course ID: {$request->course_id}\n";
    echo "  Created: {$request->created_at}\n\n";
}

// 6. Check upcoming trainings
$upcoming = \App\Models\UpcomingTraining::where('employee_id', 'EMP001')
    ->where('training_title', 'Communication Skills')
    ->get();

echo "⏰ Upcoming Trainings:\n";
foreach ($upcoming as $training) {
    echo "- Training ID: {$training->id}\n";
    echo "  Status: {$training->status}\n";
    echo "  Source: {$training->source}\n";
    echo "  Created: {$training->created_at}\n\n";
}

// 7. Manual progress calculation test
echo "🔧 Manual Progress Test:\n";
$latestAttempt = \App\Models\ExamAttempt::where('employee_id', 'EMP001')
    ->where('course_id', $course->course_id)
    ->where('status', 'completed')
    ->orderBy('completed_at', 'desc')
    ->first();

if ($latestAttempt) {
    echo "Latest completed attempt score: {$latestAttempt->score}%\n";
    echo "Should show as: " . ($latestAttempt->score >= 80 ? "PASSED (100% progress)" : "FAILED ({$latestAttempt->score}% progress)") . "\n";
} else {
    echo "❌ No completed exam attempts found\n";
}

echo "\n=== END DEBUG ===\n";
