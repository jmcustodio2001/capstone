<?php

// Direct database connection to fix training_requests table
$host = 'localhost';
$database = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to hr2system database successfully!\n";
    
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
            KEY `training_requests_course_id_foreign` (`course_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableSQL);
        echo "✅ training_requests table created successfully!\n";
        
        // Add foreign key constraint if course_management table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'course_management'");
        if ($stmt->rowCount() > 0) {
            try {
                $pdo->exec("ALTER TABLE `training_requests` ADD CONSTRAINT `training_requests_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE SET NULL");
                echo "✅ Foreign key constraint added successfully!\n";
            } catch (Exception $e) {
                echo "⚠️ Warning: Could not add foreign key constraint: " . $e->getMessage() . "\n";
            }
        }
        
        // Insert sample data for testing
        echo "Adding sample training request for testing...\n";
        $insertSQL = "
        INSERT INTO `training_requests` 
        (`employee_id`, `training_title`, `reason`, `status`, `requested_date`, `created_at`, `updated_at`) 
        VALUES 
        ('EMP001', 'Customer Service Excellence', 'Need to improve customer interaction skills', 'Pending', CURDATE(), NOW(), NOW())
        ";
        
        $pdo->exec($insertSQL);
        echo "✅ Sample training request added!\n";
        
    } else {
        echo "✅ training_requests table already exists.\n";
    }
    
    // Verify table structure
    echo "\n📋 Current table structure:\n";
    $stmt = $pdo->query("DESCRIBE training_requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) - Null: {$column['Null']} - Default: {$column['Default']}\n";
    }
    
    // Check current data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM training_requests");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\n📊 Total training requests: $count\n";
    
    if ($count > 0) {
        echo "\n📋 Sample training requests:\n";
        $stmt = $pdo->query("SELECT request_id, employee_id, training_title, status, requested_date FROM training_requests ORDER BY request_id DESC LIMIT 5");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($requests as $request) {
            echo "- ID: {$request['request_id']}, Employee: {$request['employee_id']}, Title: {$request['training_title']}, Status: {$request['status']}\n";
        }
    }
    
    echo "\n✅ Training requests table is now ready!\n";
    echo "🔧 You can now test the approval functionality in the Course Management module.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
