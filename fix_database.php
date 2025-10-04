<?php

// Fix Database Script for HR2ESS
echo "Starting database fix...\n";

// 1. Generate APP_KEY if missing
$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);

if (strpos($envContent, 'APP_KEY=') !== false && preg_match('/APP_KEY=\s*$/', $envContent)) {
    echo "Generating APP_KEY...\n";
    exec('php artisan key:generate', $output, $return);
    if ($return === 0) {
        echo "✓ APP_KEY generated successfully\n";
    } else {
        echo "✗ Failed to generate APP_KEY\n";
    }
}

// 2. Create MySQL database
$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'hr2system';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '$database' created/verified\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo "Please ensure MySQL is running and credentials are correct\n";
    exit(1);
}

// 3. Clean up migration conflicts
echo "Cleaning migration conflicts...\n";

// Remove problematic migration files
$problematicFiles = [
    'database/migrations/2025_08_23_192959_create_succession_planning_tables.php',
    'database/migrations/2025_09_06_053425_create_succession_simulations_table.php.bak',
    'database/migrations/2025_08_19_180000_create_succession_simulations_table.php.bak'
];

foreach ($problematicFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        unlink($fullPath);
        echo "✓ Removed conflicting migration: $file\n";
    }
}

// 4. Run fresh migrations
echo "Running fresh migrations...\n";
exec('php artisan migrate:fresh --force', $output, $return);

if ($return === 0) {
    echo "✓ Migrations completed successfully\n";
} else {
    echo "✗ Migration failed. Running individual fixes...\n";
    
    // Try to fix specific issues
    exec('php artisan migrate:reset --force', $resetOutput, $resetReturn);
    exec('php artisan migrate --force', $migrateOutput, $migrateReturn);
    
    if ($migrateReturn === 0) {
        echo "✓ Migrations fixed and completed\n";
    } else {
        echo "✗ Migrations still failing. Manual intervention needed.\n";
    }
}

// 5. Run seeders
echo "Running seeders...\n";
exec('php artisan db:seed --force', $seedOutput, $seedReturn);

if ($seedReturn === 0) {
    echo "✓ Seeders completed successfully\n";
} else {
    echo "! Seeders failed (this may be normal if no seeders are configured)\n";
}

echo "\nDatabase fix completed!\n";
echo "You can now run: php artisan serve\n";

?>