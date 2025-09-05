<?php

// Direct MySQL connection to create the missing table
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hr2system';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if table exists
    $checkTableQuery = "SHOW TABLES LIKE 'employee_training_dashboard'";
    $stmt = $pdo->query($checkTableQuery);
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Creating employee_training_dashboard table...\n";
        
        $createTableSQL = "
        CREATE TABLE `employee_training_dashboard` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `employee_id` varchar(20) NOT NULL,
            `course_id` bigint(20) UNSIGNED NOT NULL,
            `training_date` date DEFAULT NULL,
            `progress` int(11) NOT NULL DEFAULT 0,
            `status` varchar(255) NOT NULL DEFAULT 'Not Started',
            `remarks` text DEFAULT NULL,
            `last_accessed` timestamp NULL DEFAULT NULL,
            `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
            `expired_date` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `employee_training_dashboard_employee_id_index` (`employee_id`),
            KEY `employee_training_dashboard_course_id_index` (`course_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableSQL);
        echo "✅ employee_training_dashboard table created successfully!\n";
    } else {
        echo "ℹ️  employee_training_dashboard table already exists.\n";
    }
    
    // Verify table structure
    $columnsQuery = "SHOW COLUMNS FROM employee_training_dashboard";
    $stmt = $pdo->query($columnsQuery);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Table columns: " . implode(', ', $columns) . "\n";
    echo "✅ Script completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
