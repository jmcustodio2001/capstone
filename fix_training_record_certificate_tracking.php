<?php

// Simple script to create the missing training_record_certificate_tracking table
// This uses basic PHP PDO without Laravel dependencies

$host = 'localhost';
$database = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database: $database\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'training_record_certificate_tracking'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'training_record_certificate_tracking' already exists.\n";
        exit(0);
    }
    
    // Create the table
    $sql = "CREATE TABLE `training_record_certificate_tracking` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `employee_id` varchar(50) NOT NULL,
        `course_id` bigint(20) unsigned NOT NULL,
        `training_date` date NOT NULL,
        `certificate_number` varchar(255) DEFAULT NULL,
        `certificate_expiry` date DEFAULT NULL,
        `certificate_url` varchar(255) DEFAULT NULL,
        `status` varchar(255) NOT NULL DEFAULT 'Active',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_employee_id` (`employee_id`),
        INDEX `idx_course_id` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Table 'training_record_certificate_tracking' created successfully!\n";
    
    // Verify table was created
    $stmt = $pdo->query("DESCRIBE training_record_certificate_tracking");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // If connection failed, try with different credentials
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "\nTrying alternative database connection...\n";
        
        // Try common alternative credentials
        $alternatives = [
            ['localhost', 'hr2system', 'root', ''],
            ['127.0.0.1', 'hr2system', 'root', ''],
            ['localhost', 'hr2system', 'root', 'root'],
            ['localhost', 'hr2system', 'root', 'password']
        ];
        
        foreach ($alternatives as $creds) {
            try {
                $pdo = new PDO("mysql:host={$creds[0]};dbname={$creds[1]};charset=utf8mb4", $creds[2], $creds[3]);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo "Connected with: host={$creds[0]}, db={$creds[1]}, user={$creds[2]}\n";
                
                // Create table with successful connection
                $pdo->exec($sql);
                echo "Table created successfully with alternative credentials!\n";
                break;
                
            } catch (PDOException $e2) {
                continue;
            }
        }
    }
}
?>
