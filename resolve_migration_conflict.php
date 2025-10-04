<?php

// Direct database connection to fix succession_simulations migration conflict
$host = 'localhost';
$port = '3306';
$database = '';
$username = '';
$password = '';

// Read database credentials from .env file
if (file_exists('.env')) {
    $env = file_get_contents('.env');
    if (preg_match('/DB_DATABASE=(.*)/', $env, $matches)) {
        $database = trim($matches[1]);
    }
    if (preg_match('/DB_USERNAME=(.*)/', $env, $matches)) {
        $username = trim($matches[1]);
    }
    if (preg_match('/DB_PASSWORD=(.*)/', $env, $matches)) {
        $password = trim($matches[1]);
    }
    if (preg_match('/DB_HOST=(.*)/', $env, $matches)) {
        $host = trim($matches[1]);
    }
    if (preg_match('/DB_PORT=(.*)/', $env, $matches)) {
        $port = trim($matches[1]);
    }
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if succession_simulations table exists
    $result = $pdo->query("SHOW TABLES LIKE 'succession_simulations'");
    if ($result->rowCount() > 0) {
        echo "succession_simulations table exists in database.\n";
        
        // Get current max batch number
        $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) as max_batch FROM migrations");
        $maxBatch = $stmt->fetch(PDO::FETCH_ASSOC)['max_batch'];
        
        // Check if migration is already recorded
        $migrationName = '2025_09_06_053425_create_succession_simulations_table';
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM migrations WHERE migration = ?");
        $stmt->execute([$migrationName]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if (!$exists) {
            // Insert migration record to mark it as completed
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migrationName, $maxBatch + 1]);
            echo "Successfully marked migration as completed: $migrationName\n";
        } else {
            echo "Migration already marked as completed: $migrationName\n";
        }
        
        echo "Migration conflict resolved! You can now run 'php artisan migrate' without errors.\n";
    } else {
        echo "succession_simulations table does not exist. Migration should run normally.\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
