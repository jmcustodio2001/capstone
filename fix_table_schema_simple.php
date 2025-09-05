<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hr2system';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database.\n";
    
    // Fix employee_id column type
    $pdo->exec("ALTER TABLE upcoming_trainings MODIFY COLUMN employee_id VARCHAR(20) NOT NULL");
    echo "Fixed employee_id column to VARCHAR(20).\n";
    
    // Fix date columns
    $pdo->exec("ALTER TABLE upcoming_trainings MODIFY COLUMN start_date DATE");
    $pdo->exec("ALTER TABLE upcoming_trainings MODIFY COLUMN end_date DATE");
    echo "Fixed date columns.\n";
    
    // Show structure
    $stmt = $pdo->query("DESCRIBE upcoming_trainings");
    echo "\nTable structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']}: {$row['Type']}\n";
    }
    
    echo "\nTable fixed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
