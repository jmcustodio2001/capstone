<?php

// Direct fix for succession_simulations migration conflict
$host = 'localhost';
$database = 'hr2ess';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'succession_simulations'");
    if ($stmt->rowCount() > 0) {
        echo "succession_simulations table exists\n";
        
        // Get next batch number
        $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
        $nextBatch = $stmt->fetch()['next_batch'];
        
        // Mark migration as completed
        $migrationName = '2025_09_06_053425_create_succession_simulations_table';
        $stmt = $pdo->prepare("INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migrationName, $nextBatch]);
        
        echo "Migration marked as completed: $migrationName\n";
        echo "You can now run 'php artisan migrate' without the table exists error.\n";
    } else {
        echo "Table doesn't exist - migration should run normally\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
