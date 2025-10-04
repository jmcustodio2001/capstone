<?php

// Direct database connection to debug Communication Skills progress
$host = 'localhost';
$dbname = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== COMMUNICATION SKILLS PROGRESS DEBUG ===\n\n";
    
    $employeeId = 'EMP001';
    
    // Find Communication Skills course
    $stmt = $pdo->prepare("SELECT course_id, course_title FROM course_management WHERE course_title LIKE '%Communication Skills%'");
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        echo "❌ Course not found\n";
        exit;
    }
    
    $courseId = $course['course_id'];
    echo "✅ Course Found: {$course['course_title']} (ID: $courseId)\n\n";
    
    // Check exam attempts
    echo "--- EXAM ATTEMPTS ---\n";
    $stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE employee_id = ? AND course_id = ? ORDER BY completed_at DESC");
    $stmt->execute([$employeeId, $courseId]);
    $examAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($examAttempts)) {
        foreach ($examAttempts as $attempt) {
            echo "Attempt ID: {$attempt['id']}\n";
            echo "Score: {$attempt['score']}%\n";
            echo "Status: {$attempt['status']}\n";
            echo "Completed: {$attempt['completed_at']}\n";
            echo "Pass Status: " . ($attempt['score'] >= 80 ? 'PASSED' : 'FAILED') . "\n";
            echo "---\n";
        }
        
        $latestAttempt = $examAttempts[0];
        echo "Latest Attempt Score: {$latestAttempt['score']}%\n";
        echo "Should Progress Be: " . ($latestAttempt['score'] >= 80 ? '100%' : $latestAttempt['score'] . '%') . "\n\n";
    } else {
        echo "❌ No exam attempts found\n\n";
    }
    
    // Check employee training dashboard
    echo "--- EMPLOYEE TRAINING DASHBOARD ---\n";
    $stmt = $pdo->prepare("SELECT * FROM employee_training_dashboard WHERE employee_id = ? AND course_id = ?");
    $stmt->execute([$employeeId, $courseId]);
    $dashboard = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dashboard) {
        echo "✅ Dashboard Record Found:\n";
        echo "Progress: {$dashboard['progress']}%\n";
        echo "Status: {$dashboard['status']}\n";
        echo "Updated At: {$dashboard['updated_at']}\n";
        echo "Created At: {$dashboard['created_at']}\n\n";
        
        // Check if dashboard needs updating
        if (!empty($examAttempts)) {
            $latestScore = $examAttempts[0]['score'];
            $expectedProgress = $latestScore >= 80 ? 100 : $latestScore;
            
            if ($dashboard['progress'] != $expectedProgress) {
                echo "❌ MISMATCH DETECTED!\n";
                echo "Dashboard Progress: {$dashboard['progress']}%\n";
                echo "Expected Progress: {$expectedProgress}%\n";
                echo "Latest Exam Score: {$latestScore}%\n\n";
                
                echo "FIXING DASHBOARD RECORD...\n";
                $newStatus = $latestScore >= 80 ? 'Completed' : 'Failed';
                $updateStmt = $pdo->prepare("UPDATE employee_training_dashboard SET progress = ?, status = ?, updated_at = NOW() WHERE employee_id = ? AND course_id = ?");
                $updateStmt->execute([$expectedProgress, $newStatus, $employeeId, $courseId]);
                
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
        if (!empty($examAttempts)) {
            $latestScore = $examAttempts[0]['score'];
            $progress = $latestScore >= 80 ? 100 : $latestScore;
            $status = $latestScore >= 80 ? 'Completed' : 'Failed';
            
            echo "Creating dashboard record...\n";
            $insertStmt = $pdo->prepare("INSERT INTO employee_training_dashboard (employee_id, course_id, training_date, progress, status, remarks, assigned_by, created_at, updated_at) VALUES (?, ?, CURDATE(), ?, ?, 'Auto-created from exam completion', 1, NOW(), NOW())");
            $insertStmt->execute([$employeeId, $courseId, $progress, $status]);
            
            echo "✅ Dashboard record created!\n";
            echo "Progress: {$progress}%\n";
            echo "Status: {$status}\n";
        }
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
