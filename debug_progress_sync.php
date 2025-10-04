<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ExamAttempt;
use App\Models\EmployeeTrainingDashboard;
use App\Models\CourseManagement;

echo "=== EXAM PROGRESS SYNC DEBUG ===\n\n";

// Test employee ID (replace with actual employee ID)
$employeeId = 'EMP001';

echo "1. CHECKING RECENT EXAM ATTEMPTS FOR EMPLOYEE: $employeeId\n";
echo str_repeat("-", 60) . "\n";

$recentExams = ExamAttempt::with('course')
    ->where('employee_id', $employeeId)
    ->orderBy('completed_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentExams as $exam) {
    echo "Exam ID: {$exam->id}\n";
    echo "Course: {$exam->course->course_title ?? 'Unknown'} (ID: {$exam->course_id})\n";
    echo "Score: {$exam->score}%\n";
    echo "Status: {$exam->status}\n";
    echo "Completed: {$exam->completed_at}\n";
    echo "Pass Status: " . ($exam->score >= 80 ? 'PASSED' : 'FAILED') . "\n";
    echo "\n";
}

echo "\n2. CHECKING EMPLOYEE TRAINING DASHBOARD RECORDS\n";
echo str_repeat("-", 60) . "\n";

$dashboardRecords = EmployeeTrainingDashboard::with('course')
    ->where('employee_id', $employeeId)
    ->get();

foreach ($dashboardRecords as $record) {
    echo "Dashboard ID: {$record->id}\n";
    echo "Course: {$record->course->course_title ?? 'Unknown'} (ID: {$record->course_id})\n";
    echo "Progress: {$record->progress}%\n";
    echo "Status: {$record->status}\n";
    echo "Last Updated: {$record->updated_at}\n";
    
    // Check if there's a corresponding exam attempt
    $examAttempt = ExamAttempt::where('employee_id', $employeeId)
        ->where('course_id', $record->course_id)
        ->where('status', 'completed')
        ->orderBy('score', 'desc')
        ->first();
    
    if ($examAttempt) {
        echo "Best Exam Score: {$examAttempt->score}%\n";
        echo "Exam Status: " . ($examAttempt->score >= 80 ? 'PASSED' : 'FAILED') . "\n";
        echo "Progress Sync Status: " . ($record->progress == ($examAttempt->score >= 80 ? 100 : $examAttempt->score) ? 'SYNCED' : 'OUT OF SYNC') . "\n";
    } else {
        echo "No exam attempts found for this course\n";
    }
    echo "\n";
}

echo "\n3. CHECKING FOR SYNC ISSUES\n";
echo str_repeat("-", 60) . "\n";

// Find courses where exam is passed but dashboard progress is not 100%
$syncIssues = DB::select("
    SELECT 
        ea.employee_id,
        ea.course_id,
        c.course_title,
        MAX(ea.score) as best_exam_score,
        etd.progress as dashboard_progress,
        etd.status as dashboard_status,
        etd.updated_at as dashboard_updated
    FROM exam_attempts ea
    JOIN courses c ON ea.course_id = c.id
    LEFT JOIN employee_training_dashboard etd ON ea.employee_id = etd.employee_id AND ea.course_id = etd.course_id
    WHERE ea.employee_id = ? 
    AND ea.status = 'completed'
    AND ea.score >= 80
    GROUP BY ea.employee_id, ea.course_id, c.course_title, etd.progress, etd.status, etd.updated_at
    HAVING (etd.progress IS NULL OR etd.progress < 100)
", [$employeeId]);

if (count($syncIssues) > 0) {
    echo "FOUND SYNC ISSUES:\n";
    foreach ($syncIssues as $issue) {
        echo "Course: {$issue->course_title} (ID: {$issue->course_id})\n";
        echo "Best Exam Score: {$issue->best_exam_score}% (PASSED)\n";
        echo "Dashboard Progress: " . ($issue->dashboard_progress ?? 'NULL') . "%\n";
        echo "Dashboard Status: " . ($issue->dashboard_status ?? 'NULL') . "\n";
        echo "Dashboard Last Updated: " . ($issue->dashboard_updated ?? 'NULL') . "\n";
        echo "ACTION NEEDED: Update dashboard progress to 100%\n";
        echo "\n";
    }
} else {
    echo "No sync issues found - all passed exams have correct dashboard progress.\n";
}

echo "\n4. CHECKING MISSING DASHBOARD RECORDS\n";
echo str_repeat("-", 60) . "\n";

// Find exam attempts that don't have corresponding dashboard records
$missingDashboard = DB::select("
    SELECT 
        ea.employee_id,
        ea.course_id,
        c.course_title,
        MAX(ea.score) as best_exam_score,
        ea.status as exam_status
    FROM exam_attempts ea
    JOIN courses c ON ea.course_id = c.id
    LEFT JOIN employee_training_dashboard etd ON ea.employee_id = etd.employee_id AND ea.course_id = etd.course_id
    WHERE ea.employee_id = ? 
    AND ea.status = 'completed'
    AND etd.id IS NULL
    GROUP BY ea.employee_id, ea.course_id, c.course_title, ea.status
", [$employeeId]);

if (count($missingDashboard) > 0) {
    echo "FOUND MISSING DASHBOARD RECORDS:\n";
    foreach ($missingDashboard as $missing) {
        echo "Course: {$missing->course_title} (ID: {$missing->course_id})\n";
        echo "Best Exam Score: {$missing->best_exam_score}%\n";
        echo "Exam Status: {$missing->exam_status}\n";
        echo "ACTION NEEDED: Create dashboard record\n";
        echo "\n";
    }
} else {
    echo "All exam attempts have corresponding dashboard records.\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
