<?php

// Direct database connection approach
$host = 'localhost';
$dbname = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'training_requests'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Creating training_requests table...\n";
        
        $createTableSQL = "
        CREATE TABLE `training_requests` (
          `request_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `employee_id` varchar(20) NOT NULL,
          `course_id` bigint(20) UNSIGNED DEFAULT NULL,
          `training_title` varchar(255) NOT NULL,
          `reason` text NOT NULL,
          `status` varchar(255) NOT NULL DEFAULT 'Pending',
          `requested_date` date NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`request_id`),
          KEY `training_requests_employee_id_index` (`employee_id`),
          KEY `training_requests_course_id_index` (`course_id`),
          KEY `training_requests_status_index` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableSQL);
        echo "✓ training_requests table created successfully!\n";
    } else {
        echo "✓ training_requests table already exists.\n";
    }
    
    // Check if table has data
    $stmt = $pdo->query("SELECT COUNT(*) FROM training_requests");
    $count = $stmt->fetchColumn();
    
    echo "Current training requests count: $count\n";
    
    if ($count == 0) {
        echo "Adding sample training requests...\n";
        
        // Get sample data
        $employeeStmt = $pdo->query("SELECT employee_id, CONCAT(first_name, ' ', last_name) as full_name FROM employees LIMIT 3");
        $employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $courseStmt = $pdo->query("SELECT course_id, course_title FROM course_management WHERE status = 'Active' LIMIT 3");
        $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($employees) > 0 && count($courses) > 0) {
            $insertSQL = "
            INSERT INTO training_requests (employee_id, course_id, training_title, reason, status, requested_date, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";
            
            $stmt = $pdo->prepare($insertSQL);
            
            // Create sample requests
            $sampleRequests = [
                [
                    'employee_id' => $employees[0]['employee_id'],
                    'course_id' => $courses[0]['course_id'],
                    'training_title' => $courses[0]['course_title'],
                    'reason' => 'Professional development and skill enhancement required for current role',
                    'status' => 'Pending'
                ]
            ];
            
            if (count($employees) > 1 && count($courses) > 1) {
                $sampleRequests[] = [
                    'employee_id' => $employees[1]['employee_id'],
                    'course_id' => $courses[1]['course_id'],
                    'training_title' => $courses[1]['course_title'],
                    'reason' => 'Required for career advancement and competency development',
                    'status' => 'Pending'
                ];
            }
            
            foreach ($sampleRequests as $request) {
                $stmt->execute([
                    $request['employee_id'],
                    $request['course_id'],
                    $request['training_title'],
                    $request['reason'],
                    $request['status'],
                    date('Y-m-d')
                ]);
            }
            
            echo "✓ Sample training requests added successfully!\n";
        } else {
            echo "⚠ No employees or courses found to create sample requests.\n";
        }
    }
    
    // Display current training requests
    $stmt = $pdo->query("
        SELECT tr.*, e.first_name, e.last_name, cm.course_title 
        FROM training_requests tr 
        LEFT JOIN employees e ON tr.employee_id = e.employee_id 
        LEFT JOIN course_management cm ON tr.course_id = cm.course_id 
        ORDER BY tr.created_at DESC
    ");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent training requests:\n";
    echo "========================\n";
    foreach ($requests as $request) {
        echo "ID: {$request['request_id']} | Employee: {$request['first_name']} {$request['last_name']} | Course: {$request['training_title']} | Status: {$request['status']}\n";
    }
    
    echo "\n✓ Training requests table is now properly set up and populated!\n";
    echo "You can now test the approve/reject functionality in the Course Management module.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
