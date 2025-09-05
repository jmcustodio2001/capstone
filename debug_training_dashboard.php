<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hr2system';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== JM CUSTODIO DEBUG ===\n\n";
    
    // 1. Find employee
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE first_name = 'JM' AND last_name = 'CUSTODIO'");
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo "Employee JM CUSTODIO not found\n";
        exit;
    }
    
    echo "Employee found:\n";
    echo "- ID: " . $employee['employee_id'] . "\n";
    echo "- Name: " . $employee['first_name'] . " " . $employee['last_name'] . "\n\n";
    
    // 2. Check training requests
    $stmt = $pdo->prepare("SELECT * FROM training_requests WHERE employee_id = ?");
    $stmt->execute([$employee['employee_id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Training Requests (" . count($requests) . "):\n";
    foreach ($requests as $request) {
        echo "- Request ID: " . $request['request_id'] . "\n";
        echo "  Status: " . $request['status'] . "\n";
        echo "  Course ID: " . ($request['course_id'] ?? 'NULL') . "\n";
        echo "  Training Title: " . ($request['training_title'] ?? 'NULL') . "\n";
        
        if ($request['course_id']) {
            $courseStmt = $pdo->prepare("SELECT * FROM course_management WHERE course_id = ?");
            $courseStmt->execute([$request['course_id']]);
            $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($course) {
                echo "  Course Found: " . $course['course_title'] . "\n";
            } else {
                echo "  Course NOT FOUND for ID: " . $request['course_id'] . "\n";
            }
        }
        echo "\n";
    }
    
    // 3. Check employee training dashboard
    $stmt = $pdo->prepare("SELECT * FROM employee_training_dashboards WHERE employee_id = ?");
    $stmt->execute([$employee['employee_id']]);
    $dashboardRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Employee Training Dashboard (" . count($dashboardRecords) . "):\n";
    foreach ($dashboardRecords as $record) {
        echo "- ID: " . $record['id'] . "\n";
        echo "  Course ID: " . ($record['course_id'] ?? 'NULL') . "\n";
        echo "  Progress: " . ($record['progress'] ?? 'NULL') . "%\n";
        
        if ($record['course_id']) {
            $courseStmt = $pdo->prepare("SELECT * FROM course_management WHERE course_id = ?");
            $courseStmt->execute([$record['course_id']]);
            $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($course) {
                echo "  Course: " . $course['course_title'] . "\n";
            } else {
                echo "  Course NOT FOUND for ID: " . $record['course_id'] . "\n";
            }
        }
        echo "\n";
    }
    
    // 4. Check all courses to see what's available
    $stmt = $pdo->prepare("SELECT course_id, course_title FROM course_management LIMIT 5");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sample Courses Available:\n";
    foreach ($courses as $course) {
        echo "- ID: " . $course['course_id'] . " - " . $course['course_title'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
