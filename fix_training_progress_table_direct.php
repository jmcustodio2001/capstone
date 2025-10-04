<?php
/**
 * Direct fix for missing training_progress table
 * This script creates the training_progress table to resolve SQLSTATE[42S02] error
 */

// Simple database connection without Laravel dependencies
$host = 'localhost';
$database = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if table exists
    $checkQuery = "SHOW TABLES LIKE 'training_progress'";
    $result = $pdo->query($checkQuery);
    
    if ($result->rowCount() > 0) {
        echo "training_progress table already exists. Dropping and recreating...\n";
        $pdo->exec("DROP TABLE training_progress");
    }
    
    // Create training_progress table
    $createTableSQL = "
    CREATE TABLE `training_progress` (
      `progress_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `employee_id` bigint(20) unsigned NOT NULL,
      `training_title` varchar(255) NOT NULL,
      `progress_percentage` int(11) NOT NULL DEFAULT 0,
      `last_updated` datetime NOT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`progress_id`),
      KEY `idx_employee_id` (`employee_id`),
      KEY `idx_training_title` (`training_title`),
      KEY `idx_last_updated` (`last_updated`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createTableSQL);
    echo "✓ training_progress table created successfully.\n";
    
    // Verify table creation
    $verifyQuery = "DESCRIBE training_progress";
    $columns = $pdo->query($verifyQuery)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nTable structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
    // Test query to ensure table works
    $testQuery = "SELECT COUNT(*) as count FROM training_progress";
    $count = $pdo->query($testQuery)->fetch(PDO::FETCH_ASSOC);
    echo "✓ Test query successful. Records in table: {$count['count']}\n";
    
    echo "\n=== SUCCESS ===\n";
    echo "training_progress table has been created and is ready to use.\n";
    echo "The SQLSTATE[42S02] error should now be resolved.\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
