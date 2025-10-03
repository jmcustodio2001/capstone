<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "=== Testing training_record_certificate_tracking table ===\n\n";
    
    // Test 1: Check if table exists
    echo "1. Checking if table exists...\n";
    if (Schema::hasTable('training_record_certificate_tracking')) {
        echo "✓ Table exists!\n\n";
    } else {
        echo "✗ Table does not exist!\n";
        exit(1);
    }
    
    // Test 2: Test the original failing query
    echo "2. Testing the original failing query...\n";
    $count = DB::table('training_record_certificate_tracking')
        ->where('status', 'Completed')
        ->whereNotNull('certificate_number')
        ->count();
    echo "✓ Query executed successfully! Found {$count} records.\n\n";
    
    // Test 3: Show table structure
    echo "3. Table structure:\n";
    $columns = DB::select("DESCRIBE training_record_certificate_tracking");
    foreach ($columns as $column) {
        echo "   - {$column->Field} ({$column->Type})\n";
    }
    echo "\n";
    
    // Test 4: Test model access
    echo "4. Testing model access...\n";
    $modelCount = \App\Models\TrainingRecordCertificateTracking::count();
    echo "✓ Model access works! Total records: {$modelCount}\n\n";
    
    echo "=== ALL TESTS PASSED ===\n";
    echo "The training_record_certificate_tracking table is working correctly!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
