<?php

// Simple fix for succession_simulations migration conflict
echo "Fixing succession_simulations migration conflict...\n";

// Read .env manually
$env = [];
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? '';
$username = $env['DB_USERNAME'] ?? '';
$password = $env['DB_PASSWORD'] ?? '';

if (empty($database)) {
    die("Database name not found in .env\n");
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'succession_simulations'");
    if ($stmt->rowCount() == 0) {
        echo "Table doesn't exist - migration should run normally\n";
        exit(0);
    }
    
    echo "Table exists, marking migration as complete...\n";
    
    // Get max batch
    $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
    $nextBatch = $stmt->fetch()['next_batch'];
    
    // Insert migration record
    $migrationName = '2025_09_06_053425_create_succession_simulations_table';
    $stmt = $pdo->prepare("INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)");
    $result = $stmt->execute([$migrationName, $nextBatch]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Migration marked as complete: $migrationName\n";
    } else {
        echo "✓ Migration was already marked as complete\n";
    }
    
    echo "✅ Fixed! You can now run 'php artisan migrate' without errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
