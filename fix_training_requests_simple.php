<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hr2system';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database.\n";
    
    // Fix course_id column to VARCHAR
    $pdo->exec("ALTER TABLE training_requests MODIFY COLUMN course_id VARCHAR(255) NULL");
    echo "Fixed course_id column to VARCHAR(255).\n";
    
    // Fix employee_id column to VARCHAR
    $pdo->exec("ALTER TABLE training_requests MODIFY COLUMN employee_id VARCHAR(20) NOT NULL");
    echo "Fixed employee_id column to VARCHAR(20).\n";
    
    // Show structure
    $stmt = $pdo->query("DESCRIBE training_requests");
    echo "\nTable structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']}: {$row['Type']}\n";
    }
    
    echo "\nTable fixed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
