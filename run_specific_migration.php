<?php

// Force run the specific training_record_certificate_tracking migration
require_once 'vendor/autoload.php';

try {
    // Load environment variables if .env exists
    if (file_exists('.env')) {
        $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
    
    // Database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $database = $_ENV['DB_DATABASE'] ?? 'hr2system';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "Connected to database: $database\n";
    
    // Remove migration record to allow re-running
    $pdo->exec("DELETE FROM migrations WHERE migration = '2025_08_16_140000_create_training_record_certificate_tracking_table'");
    echo "Removed migration record.\n";
    
    // Drop table if exists
    $pdo->exec("DROP TABLE IF EXISTS training_record_certificate_tracking");
    echo "Dropped existing table if it existed.\n";
    
    // Create the table with updated structure
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
        KEY `training_record_certificate_tracking_employee_id_index` (`employee_id`),
        KEY `training_record_certificate_tracking_course_id_index` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Created training_record_certificate_tracking table successfully!\n";
    
    // Add migration record
    $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('2025_08_16_140000_create_training_record_certificate_tracking_table', 1)");
    echo "Added migration record.\n";
    
    // Verify table structure
    $stmt = $pdo->query("DESCRIBE training_record_certificate_tracking");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nTable structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
