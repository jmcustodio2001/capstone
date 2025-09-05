<?php
// Direct fix for missing employee_training_dashboards table
echo "Fixing missing employee_training_dashboards table...\n";

$host = 'localhost';
$dbname = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database: $dbname\n";
    
    // Check if table exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
    $stmt->execute([$dbname, 'employee_training_dashboards']);
    $tableExists = $stmt->fetchColumn() > 0;
    
    if ($tableExists) {
        echo "Table 'employee_training_dashboards' already exists.\n";
    } else {
        echo "Creating table 'employee_training_dashboards'...\n";
        
        $sql = "CREATE TABLE `employee_training_dashboards` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `employee_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
            `course_id` bigint(20) UNSIGNED NOT NULL,
            `training_date` date DEFAULT NULL,
            `progress` int(11) NOT NULL DEFAULT 0,
            `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Not Started',
            `remarks` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `last_accessed` timestamp NULL DEFAULT NULL,
            `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
            `expired_date` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `employee_training_dashboards_employee_id_index` (`employee_id`),
            KEY `employee_training_dashboards_course_id_index` (`course_id`),
            KEY `employee_training_dashboards_status_index` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✓ Table 'employee_training_dashboards' created successfully!\n";
    }
    
    // Verify table structure
    $stmt = $pdo->query("DESCRIBE employee_training_dashboards");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nTable structure verified:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\n✓ Database fix completed successfully!\n";
    echo "You can now reload your my_trainings page.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "Please check your database connection settings.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
