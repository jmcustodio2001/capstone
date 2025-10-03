<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Database connection
    $pdo = new PDO(
        "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_DATABASE'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'training_notifications'");
    if ($checkTable->rowCount() > 0) {
        echo "Table 'training_notifications' already exists.\n";
        exit(0);
    }

    // Create training_notifications table
    $sql = "
    CREATE TABLE `training_notifications` (
      `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `employee_id` varchar(255) NOT NULL,
      `message` text NOT NULL,
      `sent_at` datetime NOT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`notification_id`),
      KEY `idx_employee_id` (`employee_id`),
      KEY `idx_sent_at` (`sent_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "Successfully created 'training_notifications' table.\n";

    // Insert sample notification for ADMIN to test
    $insertSql = "
    INSERT INTO `training_notifications` (`employee_id`, `message`, `sent_at`, `created_at`, `updated_at`) 
    VALUES ('ADMIN', 'Training notifications system is now active.', NOW(), NOW(), NOW())
    ";
    
    $pdo->exec($insertSql);
    echo "Added sample notification for ADMIN.\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
