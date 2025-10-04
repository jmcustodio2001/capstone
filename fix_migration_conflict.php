<?php

// Simple script to fix succession_simulations migration conflict
echo "Fixing succession_simulations migration conflict...\n";

try {
    // Read .env file manually
    $envFile = '.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }

    // Database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? '';
    $username = $_ENV['DB_USERNAME'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    if (empty($database) || empty($username)) {
        throw new Exception("Database credentials not found in .env file");
    }

    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Connected to database: $database\n";

    // Check if succession_simulations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'succession_simulations'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "✓ succession_simulations table exists\n";
        
        // Get current max batch
        $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) as max_batch FROM migrations");
        $maxBatch = $stmt->fetch()['max_batch'];
        
        // Mark both migrations as completed to prevent conflicts
        $migrations = [
            '2025_08_19_180000_create_succession_simulations_table',
            '2025_09_06_053425_create_succession_simulations_table'
        ];
        
        foreach ($migrations as $migration) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM migrations WHERE migration = ?");
            $stmt->execute([$migration]);
            $exists = $stmt->fetch()['count'] > 0;
            
            if (!$exists) {
                $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$migration, $maxBatch + 1]);
                echo "✓ Marked $migration as completed\n";
            } else {
                echo "✓ $migration already marked as completed\n";
            }
        }
        
        echo "\n✅ Migration conflict resolved!\n";
        echo "You can now run migrations without the 'table already exists' error.\n";
        
    } else {
        echo "✗ succession_simulations table does not exist\n";
        echo "The migration should run normally to create the table.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
