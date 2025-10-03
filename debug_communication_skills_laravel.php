<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ExamAttempt;
use App\Models\EmployeeTrainingDashboard;
use App\Models\CourseManagement;
use App\Models\TrainingRequest;

echo "=== COMMUNICATION SKILLS PROGRESS DEBUG (Laravel Models) ===\n\n";

$employeeId = 'EMP001';
$courseTitle = 'Communication Skills';

try {
    // Find Communication Skills course
    $course = CourseManagement::where('course_title', 'LIKE', '%' . $courseTitle . '%')->first();
    
    if (!$course) {
        echo "❌ Course not found for title: $courseTitle\n";
        exit;
    }
    
    $courseId = $course->course_id;
    echo "✅ Course Found: {$course->course_title} (ID: $courseId)\n\n";
    
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
        
        $latestAttempt = $examAttempts->first();
        echo "Latest Attempt Score: {$latestAttempt->score}%\n";
        echo "Should Progress Be: " . ($latestAttempt->score >= 80 ? '100%' : $latestAttempt->score . '%') . "\n\n";
    } else {
        echo "❌ No exam attempts found\n\n";
    }
    
    // Check employee training dashboard
    echo "--- EMPLOYEE TRAINING DASHBOARD ---\n";
    $dashboard = EmployeeTrainingDashboard::where('employee_id', $employeeId)
        ->where('course_id', $courseId)
        ->first();
    
    if ($dashboard) {
        echo "✅ Dashboard Record Found:\n";
        echo "Progress: {$dashboard->progress}%\n";
        echo "Status: {$dashboard->status}\n";
        echo "Updated At: {$dashboard->updated_at}\n";
        echo "Created At: {$dashboard->created_at}\n\n";
        
        // Check if dashboard needs updating
        if ($examAttempts->count() > 0) {
            $latestScore = $examAttempts->first()->score;
            $expectedProgress = $latestScore >= 80 ? 100 : $latestScore;
            
            if ($dashboard->progress != $expectedProgress) {
                echo "❌ MISMATCH DETECTED!\n";
                echo "Dashboard Progress: {$dashboard->progress}%\n";
                echo "Expected Progress: {$expectedProgress}%\n";
                echo "Latest Exam Score: {$latestScore}%\n\n";
                
                echo "FIXING DASHBOARD RECORD...\n";
                $newStatus = $latestScore >= 80 ? 'Completed' : 'Failed';
                
                $dashboard->update([
                    'progress' => $expectedProgress,
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);
                
                echo "✅ Dashboard updated successfully!\n";
                echo "New Progress: {$expectedProgress}%\n";
                echo "New Status: {$newStatus}\n";
            } else {
                echo "✅ Dashboard is up to date\n";
            }
        }
    } else {
        echo "❌ No dashboard record found\n";
        
        // Create dashboard record if exam exists
        if ($examAttempts->count() > 0) {
            $latestScore = $examAttempts->first()->score;
            $progress = $latestScore >= 80 ? 100 : $latestScore;
            $status = $latestScore >= 80 ? 'Completed' : 'Failed';
            
            echo "Creating dashboard record...\n";
            EmployeeTrainingDashboard::create([
                'employee_id' => $employeeId,
                'course_id' => $courseId,
                'training_date' => now()->format('Y-m-d'),
                'progress' => $progress,
                'status' => $status,
                'remarks' => 'Auto-created from exam completion',
                'assigned_by' => 1, // System/Admin
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "✅ Dashboard record created!\n";
            echo "Progress: {$progress}%\n";
            echo "Status: {$status}\n";
        }
    }
    
    // Check training requests
    echo "\n--- TRAINING REQUESTS ---\n";
    $trainingRequest = TrainingRequest::where('employee_id', $employeeId)
        ->where(function($query) use ($courseTitle, $courseId) {
            $query->where('training_title', 'LIKE', '%' . $courseTitle . '%')
                  ->orWhere('course_id', $courseId);
        })
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
    
    echo "\n=== PROGRESS CALCULATION SIMULATION ===\n";
    $progressValue = 0;
    $progressSource = 'none';
    
    // Priority 1: Check exam progress (same logic as _progress.blade.php)
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
    if ($progressValue == 0 && $dashboard) {
        $progressValue = max(0, min(100, (float)$dashboard->progress));
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
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
