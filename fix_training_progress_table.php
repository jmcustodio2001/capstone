<?php
/**
 * Fix missing training_progress table
 * This script creates the training_progress table to resolve SQLSTATE[42S02] error
 */

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $database = $_ENV['DB_DATABASE'] ?? 'hr2system';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
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
    
    // Add sample data for testing (optional)
    echo "\nAdding sample data for testing...\n";
    $sampleData = "
    INSERT INTO `training_progress` (`employee_id`, `training_title`, `progress_percentage`, `last_updated`, `created_at`, `updated_at`) 
    VALUES 
    (1, 'Sample Training Progress', 25, NOW(), NOW(), NOW()),
    (2, 'Leadership Development', 50, NOW(), NOW(), NOW()),
    (3, 'Technical Skills Training', 75, NOW(), NOW(), NOW())
    ";
    
    $pdo->exec($sampleData);
    echo "✓ Sample data added successfully.\n";
    
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
