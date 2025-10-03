<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Checking succession_simulations table status ===\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'succession_simulations'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✓ succession_simulations table EXISTS in database\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE succession_simulations");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nTable structure:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}\n";
        }
        
        // Check row count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM succession_simulations");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "\nTable has $count rows\n";
    } else {
        echo "✗ succession_simulations table does NOT exist in database\n";
    }
    
    echo "\n=== Checking migrations table ===\n";
    
    // Check which succession_simulations migrations have been run
    $stmt = $pdo->prepare("SELECT migration, batch FROM migrations WHERE migration LIKE '%succession_simulations%' ORDER BY batch");
    $stmt->execute();
    $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($migrations)) {
        echo "✗ No succession_simulations migrations found in migrations table\n";
    } else {
        echo "Found succession_simulations migrations:\n";
        foreach ($migrations as $migration) {
            echo "- {$migration['migration']} (batch: {$migration['batch']})\n";
        }
    }
    
    echo "\n=== Migration files on disk ===\n";
    $migrationFiles = glob('database/migrations/*succession_simulations*.php');
    foreach ($migrationFiles as $file) {
        echo "- " . basename($file) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
