<?php

// Simple script to create the missing employee_training_dashboards table
echo "Starting table creation script...\n";

try {
    // Database connection parameters (adjust as needed)
    $host = '127.0.0.1';
    $dbname = 'hr2system';
    $username = 'root';
    $password = '';  // Update this if you have a password
    
    echo "Attempting to connect to database: $dbname\n";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'employee_training_dashboards'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'employee_training_dashboards' already exists.\n";
    } else {
        echo "Creating table 'employee_training_dashboards'...\n";
        
        $sql = "CREATE TABLE `employee_training_dashboards` (
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
            KEY `employee_training_dashboards_employee_id_foreign` (`employee_id`),
            KEY `employee_training_dashboards_course_id_foreign` (`course_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "Table created successfully!\n";
        
        // Try to add foreign key constraints (may fail if referenced tables don't exist)
        try {
            $pdo->exec("ALTER TABLE `employee_training_dashboards` 
                       ADD CONSTRAINT `employee_training_dashboards_employee_id_foreign` 
                       FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE");
            echo "Added employee_id foreign key constraint.\n";
        } catch (Exception $e) {
            echo "Warning: Could not add employee_id foreign key constraint: " . $e->getMessage() . "\n";
        }
        
        try {
            $pdo->exec("ALTER TABLE `employee_training_dashboards` 
                       ADD CONSTRAINT `employee_training_dashboards_course_id_foreign` 
                       FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE");
            echo "Added course_id foreign key constraint.\n";
        } catch (Exception $e) {
            echo "Warning: Could not add course_id foreign key constraint: " . $e->getMessage() . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Please check your database connection settings.\n";
}
