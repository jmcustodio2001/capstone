<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    // Check if table exists
    if (!Schema::hasTable('employee_training_dashboard')) {
        echo "Starting database fix...\n";

        $host = 'localhost';
        $username = 'root';
        $password = '';
        $database = 'hr2system';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "Connected to database: $database\n";
            
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
            echo "Table 'employee_training_dashboard' created successfully!\n";
            
            // Verify table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'employee_training_dashboard'");
            if ($stmt->rowCount() > 0) {
                echo "Table verification: SUCCESS\n";
                
                // Show table structure
                $stmt = $pdo->query("DESCRIBE employee_training_dashboard");
                echo "\nTable structure:\n";
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "  - {$row['Field']} ({$row['Type']})\n";
                }
                
                echo "\nDatabase fix completed! Your my_trainings page should now work.\n";
            } else {
                echo "Table verification: FAILED\n";
            }
            
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage() . "\n";
            echo "Please check your MySQL connection and try again.\n";
        }
    } else {
        echo "Table already exists.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
