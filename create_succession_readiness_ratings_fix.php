<?php

// Direct database connection to create succession_readiness_ratings table
// This fixes the SQLSTATE[42S02] error: Base table or view not found

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Create database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'hr2system';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'succession_readiness_ratings'");
    if ($checkTable->rowCount() > 0) {
        echo "Table 'succession_readiness_ratings' already exists.\n";
        exit(0);
    }
    
    // Create the table
    $createTableSQL = "
    CREATE TABLE `succession_readiness_ratings` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `employee_id` varchar(20) NOT NULL,
        `readiness_score` int(11) NOT NULL DEFAULT 0,
        `readiness_level` varchar(255) NULL,
        `assessment_notes` text NULL,
        `assessment_date` date NULL,
        `assessed_by` varchar(255) NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `succession_readiness_ratings_employee_id_index` (`employee_id`),
        KEY `succession_readiness_ratings_readiness_score_index` (`readiness_score`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTableSQL);
    echo "Table 'succession_readiness_ratings' created successfully.\n";
    
    // Add some sample data
    $insertSQL = "
    INSERT INTO `succession_readiness_ratings` 
    (`employee_id`, `readiness_score`, `readiness_level`, `assessment_date`, `created_at`, `updated_at`) 
    VALUES
    ('EMP001', 85, 'High', '2024-01-15', NOW(), NOW()),
    ('EMP002', 92, 'Very High', '2024-01-15', NOW(), NOW()),
    ('EMP003', 78, 'Medium', '2024-01-15', NOW(), NOW())
    ON DUPLICATE KEY UPDATE `updated_at` = NOW();
    ";
    
    $pdo->exec($insertSQL);
    echo "Sample data inserted successfully.\n";
    
    // Verify the table was created
    $verifySQL = "SELECT COUNT(*) as count FROM succession_readiness_ratings";
    $result = $pdo->query($verifySQL);
    $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "Table verification: Found $count records in succession_readiness_ratings table.\n";
    echo "SUCCESS: succession_readiness_ratings table created and populated.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
