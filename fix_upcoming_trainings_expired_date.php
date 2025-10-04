<?php

// Direct database connection to add expired_date column to upcoming_trainings table
// This ensures the column exists before the controller tries to use it

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'hr2ess';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.\n";

    // Check if expired_date column exists
    $checkColumnQuery = "SHOW COLUMNS FROM upcoming_trainings LIKE 'expired_date'";
    $stmt = $pdo->prepare($checkColumnQuery);
    $stmt->execute();
    $columnExists = $stmt->fetch();

    if (!$columnExists) {
        echo "Adding expired_date column to upcoming_trainings table...\n";
        
        // Add expired_date column
        $addColumnQuery = "ALTER TABLE upcoming_trainings ADD COLUMN expired_date DATE NULL AFTER end_date";
        $pdo->exec($addColumnQuery);
        
        echo "✓ expired_date column added successfully.\n";
    } else {
        echo "✓ expired_date column already exists.\n";
    }

    // Update existing records to sync expired_date with end_date where expired_date is null
    echo "Syncing existing records...\n";
    $syncQuery = "UPDATE upcoming_trainings SET expired_date = end_date WHERE expired_date IS NULL AND end_date IS NOT NULL";
    $result = $pdo->exec($syncQuery);
    echo "✓ Synced $result existing records.\n";

    echo "\nDatabase update completed successfully!\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
