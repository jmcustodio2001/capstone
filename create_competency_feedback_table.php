<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'hr2ess';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create competency_feedback_requests table
    $sql = "
    CREATE TABLE IF NOT EXISTS competency_feedback_requests (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        employee_id VARCHAR(255) NOT NULL,
        competency_id BIGINT UNSIGNED NOT NULL,
        request_message TEXT NULL,
        status ENUM('pending', 'responded', 'closed') DEFAULT 'pending',
        manager_response TEXT NULL,
        manager_id BIGINT UNSIGNED NULL,
        responded_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_employee_status (employee_id, status),
        INDEX idx_competency_id (competency_id),
        INDEX idx_manager_id (manager_id),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "✅ Successfully created competency_feedback_requests table!\n";

    // Check if table exists and show structure
    $result = $pdo->query("DESCRIBE competency_feedback_requests");
    echo "\n📋 Table structure:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}\n";
    }

    echo "\n🎉 Competency feedback system is ready to use!\n";
    echo "📍 Admin can access feedback requests at: /admin/competency-feedback\n";
    echo "📍 Employees can request feedback from competency details page\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
