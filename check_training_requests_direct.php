<?php

// Direct database connection to check training_requests table
$host = 'localhost';
$database = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully!\n";
    
    // Check if training_requests table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'training_requests'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "❌ training_requests table does not exist!\n";
        echo "Creating training_requests table...\n";
        
        $createTableSQL = "
        CREATE TABLE `training_requests` (
            `request_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `employee_id` varchar(20) NOT NULL,
            `course_id` bigint(20) unsigned DEFAULT NULL,
            `training_title` varchar(255) NOT NULL,
            `reason` text NOT NULL,
            `status` varchar(255) NOT NULL DEFAULT 'Pending',
            `requested_date` date NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`request_id`),
            KEY `training_requests_course_id_foreign` (`course_id`),
            CONSTRAINT `training_requests_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableSQL);
        echo "✅ training_requests table created successfully!\n";
    } else {
        echo "✅ training_requests table exists.\n";
    }
    
    // Check table structure
    echo "\nChecking table structure:\n";
    $stmt = $pdo->query("DESCRIBE training_requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Default']}\n";
    }
    
    // Check for any training requests
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM training_requests");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\nTotal training requests: $count\n";
    
    if ($count > 0) {
        echo "\nSample training requests:\n";
        $stmt = $pdo->query("SELECT request_id, employee_id, training_title, status, requested_date FROM training_requests LIMIT 5");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($requests as $request) {
            echo "- ID: {$request['request_id']}, Employee: {$request['employee_id']}, Title: {$request['training_title']}, Status: {$request['status']}\n";
        }
    }
    
    // Test if we can find a specific training request (if any exist)
    if ($count > 0) {
        echo "\nTesting TrainingRequest model functionality:\n";
        $stmt = $pdo->query("SELECT * FROM training_requests WHERE status = 'Pending' LIMIT 1");
        $pendingRequest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pendingRequest) {
            echo "✅ Found pending request: ID {$pendingRequest['request_id']}\n";
            echo "   Employee: {$pendingRequest['employee_id']}\n";
            echo "   Title: {$pendingRequest['training_title']}\n";
            echo "   Status: {$pendingRequest['status']}\n";
        } else {
            echo "ℹ️ No pending requests found.\n";
        }
    }
    
    echo "\n✅ Database check completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
