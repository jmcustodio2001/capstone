<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ExamAttempt;
use App\Models\EmployeeTrainingDashboard;
use App\Models\CourseManagement;

echo "=== FIXING EXAM PROGRESS SYNC ISSUES ===\n\n";

// Get all completed exam attempts that passed (score >= 80)
$passedExams = DB::select("
    SELECT 
        ea.employee_id,
        ea.course_id,
        c.course_title,
        MAX(ea.score) as best_score,
        MAX(ea.completed_at) as latest_completion
    FROM exam_attempts ea
    JOIN courses c ON ea.course_id = c.id
    WHERE ea.status = 'completed' AND ea.score >= 80
    GROUP BY ea.employee_id, ea.course_id, c.course_title
");

echo "Found " . count($passedExams) . " passed exam records to check.\n\n";

$fixedCount = 0;
$createdCount = 0;

foreach ($passedExams as $exam) {
    echo "Processing: {$exam->course_title} for Employee {$exam->employee_id}\n";
    echo "Best Score: {$exam->best_score}% (PASSED)\n";
    
    // Check if dashboard record exists
    $dashboardRecord = EmployeeTrainingDashboard::where('employee_id', $exam->employee_id)
        ->where('course_id', $exam->course_id)
        ->first();
    
    if ($dashboardRecord) {
        // Update existing record if progress is not 100%
        if ($dashboardRecord->progress < 100) {
            $dashboardRecord->update([
                'progress' => 100,
                'status' => 'Completed',
                'updated_at' => now()
            ]);
            echo "✓ Updated dashboard progress from {$dashboardRecord->progress}% to 100%\n";
            $fixedCount++;
        } else {
            echo "✓ Dashboard already shows 100% progress\n";
        }
    } else {
        // Create missing dashboard record
        EmployeeTrainingDashboard::create([
            'employee_id' => $exam->employee_id,
            'course_id' => $exam->course_id,
            'training_date' => $exam->latest_completion,
            'progress' => 100,
            'status' => 'Completed',
            'remarks' => 'Auto-created from passed exam result',
            'assigned_by' => 1, // System/Admin
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✓ Created missing dashboard record with 100% progress\n";
        $createdCount++;
    }
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "Records updated: $fixedCount\n";
echo "Records created: $createdCount\n";
echo "Total processed: " . count($passedExams) . "\n\n";

// Now check for any failed exams that need progress updates
echo "=== CHECKING FAILED EXAMS ===\n";

$failedExams = DB::select("
    SELECT 
        ea.employee_id,
        ea.course_id,
        c.course_title,
        MAX(ea.score) as best_score,
        MAX(ea.completed_at) as latest_completion
    FROM exam_attempts ea
    JOIN courses c ON ea.course_id = c.id
    WHERE ea.status IN ('completed', 'failed') AND ea.score < 80
    GROUP BY ea.employee_id, ea.course_id, c.course_title
    HAVING MAX(ea.score) < 80
");

echo "Found " . count($failedExams) . " failed exam records to check.\n\n";

$failedFixedCount = 0;

foreach ($failedExams as $exam) {
    echo "Processing Failed: {$exam->course_title} for Employee {$exam->employee_id}\n";
    echo "Best Score: {$exam->best_score}% (FAILED)\n";
    
    // Check if dashboard record exists and update with actual score
    $dashboardRecord = EmployeeTrainingDashboard::where('employee_id', $exam->employee_id)
        ->where('course_id', $exam->course_id)
        ->first();
    
    if ($dashboardRecord) {
        // Update with actual score for failed exams
        if ($dashboardRecord->progress != $exam->best_score) {
            $dashboardRecord->update([
                'progress' => $exam->best_score,
                'status' => 'Failed',
                'updated_at' => now()
            ]);
            echo "✓ Updated dashboard progress to {$exam->best_score}% (Failed)\n";
            $failedFixedCount++;
        } else {
            echo "✓ Dashboard already shows correct progress\n";
        }
    } else {
        // Create dashboard record for failed exam
        EmployeeTrainingDashboard::create([
            'employee_id' => $exam->employee_id,
            'course_id' => $exam->course_id,
            'training_date' => $exam->latest_completion,
            'progress' => $exam->best_score,
            'status' => 'Failed',
            'remarks' => 'Auto-created from failed exam result',
            'assigned_by' => 1, // System/Admin
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✓ Created dashboard record with {$exam->best_score}% progress (Failed)\n";
        $failedFixedCount++;
    }
    echo "\n";
}

echo "=== FAILED EXAMS SUMMARY ===\n";
echo "Failed exam records processed: $failedFixedCount\n";

echo "\n=== PROGRESS SYNC FIX COMPLETE ===\n";
