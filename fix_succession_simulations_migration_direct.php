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

    echo "=== Fixing succession_simulations migration conflict ===\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'succession_simulations'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✓ succession_simulations table exists in database\n";
        
        // Mark the newer migration as already run to prevent re-execution
        $migrationName = '2025_09_06_053425_create_succession_simulations_table';
        
        // Get the current max batch number
        $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) as max_batch FROM migrations");
        $maxBatch = $stmt->fetch(PDO::FETCH_ASSOC)['max_batch'];
        
        // Check if this migration is already recorded
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM migrations WHERE migration = ?");
        $stmt->execute([$migrationName]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if (!$exists) {
            // Insert the migration record to mark it as run
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migrationName, $maxBatch + 1]);
            echo "✓ Marked migration '$migrationName' as completed\n";
        } else {
            echo "✓ Migration '$migrationName' already marked as completed\n";
        }
        
        // Also check for the older migration
        $oldMigrationName = '2025_08_19_180000_create_succession_simulations_table';
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM migrations WHERE migration = ?");
        $stmt->execute([$oldMigrationName]);
        $oldExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if (!$oldExists) {
            // Mark the old migration as run too to prevent conflicts
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$oldMigrationName, $maxBatch]);
            echo "✓ Marked old migration '$oldMigrationName' as completed\n";
        } else {
            echo "✓ Old migration '$oldMigrationName' already marked as completed\n";
        }
        
    } else {
        echo "✗ succession_simulations table does not exist - migration needs to run normally\n";
    }
    
    // Show final migration status
    echo "\n=== Final migration status ===\n";
    $stmt = $pdo->prepare("SELECT migration, batch FROM migrations WHERE migration LIKE '%succession_simulations%' ORDER BY batch");
    $stmt->execute();
    $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($migrations as $migration) {
        echo "✓ {$migration['migration']} (batch: {$migration['batch']})\n";
    }
    
    echo "\n=== Migration conflict resolved! ===\n";
    echo "You can now run 'php artisan migrate' without errors.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
