<?php

// Force run the training_record_certificate_tracking migration
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Initialize database connection
$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'hr2system',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $pdo = $capsule->getConnection()->getPdo();
    
    // Check if migrations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
    if ($stmt->rowCount() > 0) {
        // Remove the migration record so it can be run again
        $pdo->exec("DELETE FROM migrations WHERE migration = '2025_08_16_140000_create_training_record_certificate_tracking_table'");
        echo "Removed migration record from migrations table.\n";
    }
    
    // Check if table exists and drop it
    $stmt = $pdo->query("SHOW TABLES LIKE 'training_record_certificate_tracking'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("DROP TABLE training_record_certificate_tracking");
        echo "Dropped existing training_record_certificate_tracking table.\n";
    }
    
    // Now create the table using the migration structure
    $sql = "CREATE TABLE `training_record_certificate_tracking` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `employee_id` varchar(50) NOT NULL,
        `course_id` bigint(20) unsigned NOT NULL,
        `training_date` date NOT NULL,
        `certificate_number` varchar(255) DEFAULT NULL,
        `certificate_expiry` date DEFAULT NULL,
        `status` varchar(255) NOT NULL DEFAULT 'Active',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `training_record_certificate_tracking_employee_id_index` (`employee_id`),
        KEY `training_record_certificate_tracking_course_id_index` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Created training_record_certificate_tracking table successfully.\n";
    
    // Add the migration record back
    if ($stmt = $pdo->query("SHOW TABLES LIKE 'migrations'")) {
        if ($stmt->rowCount() > 0) {
            $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('2025_08_16_140000_create_training_record_certificate_tracking_table', 1)");
            echo "Added migration record back to migrations table.\n";
        }
    }
    
    // Verify table structure
    $stmt = $pdo->query("DESCRIBE training_record_certificate_tracking");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nTable structure verified:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
