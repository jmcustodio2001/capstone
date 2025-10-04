<?php

// Direct fix for Communication Skills progress issue
$host = 'localhost';
$dbname = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING COMMUNICATION SKILLS PROGRESS ===\n\n";
    
    $employeeId = 'EMP001';
    
    // Find Communication Skills course
    $stmt = $pdo->prepare("SELECT course_id, course_title FROM course_management WHERE course_title LIKE '%Communication Skills%'");
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        echo "Course not found\n";
        exit;
    }
    
    $courseId = $course['course_id'];
    echo "Course: {$course['course_title']} (ID: $courseId)\n";
    
    // Get latest exam attempt
    $stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE employee_id = ? AND course_id = ? ORDER BY completed_at DESC LIMIT 1");
    $stmt->execute([$employeeId, $courseId]);
    $examAttempt = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$examAttempt) {
        echo "No exam attempt found\n";
        exit;
    }
    
    echo "Latest Exam Score: {$examAttempt['score']}%\n";
    echo "Exam Status: {$examAttempt['status']}\n";
    
    // Calculate correct progress
    $correctProgress = $examAttempt['score'] >= 80 ? 100 : $examAttempt['score'];
    $correctStatus = $examAttempt['score'] >= 80 ? 'Completed' : 'Failed';
    
    echo "Correct Progress Should Be: {$correctProgress}%\n";
    echo "Correct Status Should Be: {$correctStatus}\n\n";
    
    // Check current dashboard record
    $stmt = $pdo->prepare("SELECT * FROM employee_training_dashboard WHERE employee_id = ? AND course_id = ?");
    $stmt->execute([$employeeId, $courseId]);
    $dashboard = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dashboard) {
        echo "Current Dashboard Progress: {$dashboard['progress']}%\n";
        echo "Current Dashboard Status: {$dashboard['status']}\n";
        
        if ($dashboard['progress'] != $correctProgress || $dashboard['status'] != $correctStatus) {
            echo "\nUpdating dashboard record...\n";
            $updateStmt = $pdo->prepare("UPDATE employee_training_dashboard SET progress = ?, status = ?, updated_at = NOW() WHERE employee_id = ? AND course_id = ?");
            $updateStmt->execute([$correctProgress, $correctStatus, $employeeId, $courseId]);
            echo "✅ Dashboard updated successfully!\n";
        } else {
            echo "✅ Dashboard is already correct\n";
        }
    } else {
        echo "No dashboard record found. Creating new record...\n";
        $insertStmt = $pdo->prepare("INSERT INTO employee_training_dashboard (employee_id, course_id, training_date, progress, status, remarks, assigned_by, created_at, updated_at) VALUES (?, ?, CURDATE(), ?, ?, 'Auto-created from exam completion', 1, NOW(), NOW())");
        $insertStmt->execute([$employeeId, $courseId, $correctProgress, $correctStatus]);
        echo "✅ Dashboard record created!\n";
    }
    
    // Verify the fix
    $stmt = $pdo->prepare("SELECT * FROM employee_training_dashboard WHERE employee_id = ? AND course_id = ?");
    $stmt->execute([$employeeId, $courseId]);
    $updatedDashboard = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n=== VERIFICATION ===\n";
    echo "Final Dashboard Progress: {$updatedDashboard['progress']}%\n";
    echo "Final Dashboard Status: {$updatedDashboard['status']}\n";
    echo "Last Updated: {$updatedDashboard['updated_at']}\n";
    
    echo "\n✅ FIX COMPLETE - Progress should now show correctly in the UI\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
