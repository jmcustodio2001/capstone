<?php

// Bootstrap Laravel application
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Checking succession_simulations table status...\n";
    
    // Check if table exists
    if (Schema::hasTable('succession_simulations')) {
        echo "✓ succession_simulations table exists in database\n";
        
        // Check if migration is already recorded
        $migrationName = '2025_09_06_053425_create_succession_simulations_table';
        $exists = DB::table('migrations')
            ->where('migration', $migrationName)
            ->exists();
            
        if (!$exists) {
            // Get next batch number
            $nextBatch = DB::table('migrations')->max('batch') + 1;
            
            // Insert migration record to mark it as completed
            DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $nextBatch
            ]);
            
            echo "✓ Marked migration as completed: $migrationName\n";
        } else {
            echo "✓ Migration already marked as completed\n";
        }
        
        echo "\n✅ Migration conflict resolved!\n";
        echo "You can now run 'php artisan migrate' without the 'table already exists' error.\n";
        
    } else {
        echo "✗ succession_simulations table does not exist\n";
        echo "The migration should run normally to create the table.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
