<?php
// Create the correct employee_training_dashboard table (singular)
$host = 'localhost';
$dbname = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database: $dbname\n";
    
    // Create the table with the exact name expected by the SQL query
    $sql = "CREATE TABLE IF NOT EXISTS `employee_training_dashboard` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✓ Table 'employee_training_dashboard' created successfully!\n";
    
    // Verify table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'employee_training_dashboard'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table verification: employee_training_dashboard exists\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE employee_training_dashboard");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nTable structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    }
    
    echo "\n✓ Database fix completed! You can now reload your my_trainings page.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
