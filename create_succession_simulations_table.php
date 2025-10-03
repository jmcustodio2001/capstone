<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'database' => env('DB_DATABASE', 'hr2system'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

try {
    echo "Creating succession_simulations table...\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/create_succession_simulations_table.sql');
    
    // Split SQL statements by semicolon and execute each one
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            Capsule::statement($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "✅ succession_simulations table created successfully!\n";
    
    // Verify the table was created
    $tableExists = Capsule::select("SHOW TABLES LIKE 'succession_simulations'");
    if (!empty($tableExists)) {
        echo "✅ Table verification: succession_simulations table exists\n";
        
        // Check table structure
        $columns = Capsule::select("DESCRIBE succession_simulations");
        echo "📋 Table structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field} ({$column->Type})\n";
        }
        
        // Check if sample data was inserted
        $count = Capsule::select("SELECT COUNT(*) as count FROM succession_simulations")[0]->count;
        echo "📊 Sample records inserted: {$count}\n";
    } else {
        echo "❌ Table verification failed: succession_simulations table not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating succession_simulations table: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🎉 Database fix completed successfully!\n";
echo "The succession_simulations table is now available for use.\n";
