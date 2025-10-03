<?php

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
    
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Connected to database successfully.\n";
    
    // Check if table already exists
    $checkTable = $pdo->prepare("SHOW TABLES LIKE 'training_record_certificate_tracking'");
    $checkTable->execute();
    
    if ($checkTable->rowCount() > 0) {
        echo "Table 'training_record_certificate_tracking' already exists.\n";
        exit(0);
    }
    
    // Create the table
    $createTableSQL = "
    CREATE TABLE `training_record_certificate_tracking` (
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
        KEY `training_record_certificate_tracking_employee_id_foreign` (`employee_id`),
        KEY `training_record_certificate_tracking_course_id_foreign` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTableSQL);
    echo "Table 'training_record_certificate_tracking' created successfully.\n";
    
    // Check if employees table exists before adding foreign key
    $checkEmployees = $pdo->prepare("SHOW TABLES LIKE 'employees'");
    $checkEmployees->execute();
    
    if ($checkEmployees->rowCount() > 0) {
        try {
            $pdo->exec("
                ALTER TABLE `training_record_certificate_tracking` 
                ADD CONSTRAINT `training_record_certificate_tracking_employee_id_foreign` 
                FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
            ");
            echo "Foreign key constraint for employee_id added successfully.\n";
        } catch (PDOException $e) {
            echo "Warning: Could not add employee_id foreign key constraint: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Warning: employees table not found, skipping employee_id foreign key constraint.\n";
    }
    
    // Check if course_management table exists before adding foreign key
    $checkCourses = $pdo->prepare("SHOW TABLES LIKE 'course_management'");
    $checkCourses->execute();
    
    if ($checkCourses->rowCount() > 0) {
        try {
            $pdo->exec("
                ALTER TABLE `training_record_certificate_tracking` 
                ADD CONSTRAINT `training_record_certificate_tracking_course_id_foreign` 
                FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE
            ");
            echo "Foreign key constraint for course_id added successfully.\n";
        } catch (PDOException $e) {
            echo "Warning: Could not add course_id foreign key constraint: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Warning: course_management table not found, skipping course_id foreign key constraint.\n";
    }
    
    echo "Database table creation completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
