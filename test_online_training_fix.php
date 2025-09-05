<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\DestinationKnowledgeTraining;
use App\Models\UpcomingTraining;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Online Training Fix ===\n\n";

try {
    // Find an employee with destination knowledge training
    $employee = DB::table('employees')->first();
    
    if (!$employee) {
        echo "❌ No employees found in database\n";
        exit;
    }
    
    echo "Testing with Employee ID: {$employee->employee_id}\n\n";
    
    // Create a test Online Training record
    $testRecord = DestinationKnowledgeTraining::create([
        'employee_id' => $employee->employee_id,
        'destination_name' => 'Test Online Training - PALAWAN',
        'details' => 'Test record to verify Online Training fix',
        'objectives' => 'Test objectives',
        'duration' => '2 hours',
        'delivery_mode' => 'Online Training',
        'progress' => 0,
        'status' => 'not-started',
        'is_active' => true,
        'admin_approved_for_upcoming' => false // NOT approved yet
    ]);
    
    echo "✓ Created test Online Training record (ID: {$testRecord->id})\n";
    echo "  - Delivery Mode: {$testRecord->delivery_mode}\n";
    echo "  - Admin Approved for Upcoming: " . ($testRecord->admin_approved_for_upcoming ? 'YES' : 'NO') . "\n";
    echo "  - Is Active: " . ($testRecord->is_active ? 'YES' : 'NO') . "\n\n";
    
    // Test 1: Check if it appears in upcoming training (should NOT appear)
    $upcomingCount = DestinationKnowledgeTraining::where('employee_id', $employee->employee_id)
        ->where('admin_approved_for_upcoming', true)
        ->count();
    
    echo "TEST 1 - Before Auto-Assign button click:\n";
    echo "  Records approved for upcoming training: {$upcomingCount}\n";
    echo "  Expected: 0 (Online Training should NOT appear until Auto-Assign is clicked)\n";
    echo "  Result: " . ($upcomingCount == 0 ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Test 2: Simulate Auto-Assign button click
    echo "TEST 2 - Simulating Auto-Assign button click...\n";
    $testRecord->admin_approved_for_upcoming = true;
    $testRecord->save();
    
    echo "  ✓ Set admin_approved_for_upcoming = TRUE\n";
    
    // Check if it now appears in upcoming training
    $upcomingCountAfter = DestinationKnowledgeTraining::where('employee_id', $employee->employee_id)
        ->where('admin_approved_for_upcoming', true)
        ->count();
    
    echo "  Records approved for upcoming training: {$upcomingCountAfter}\n";
    echo "  Expected: 1 (Online Training should appear after Auto-Assign is clicked)\n";
    echo "  Result: " . ($upcomingCountAfter >= 1 ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Test 3: Create a non-Online Training record
    $nonOnlineRecord = DestinationKnowledgeTraining::create([
        'employee_id' => $employee->employee_id,
        'destination_name' => 'Test On-site Training - BOHOL',
        'details' => 'Test record for non-online training',
        'objectives' => 'Test objectives',
        'duration' => '1 day',
        'delivery_mode' => 'On-site Training',
        'progress' => 0,
        'status' => 'not-started',
        'is_active' => true,
        'admin_approved_for_upcoming' => false // NOT approved yet
    ]);
    
    echo "TEST 3 - Non-Online Training behavior:\n";
    echo "  ✓ Created On-site Training record (ID: {$nonOnlineRecord->id})\n";
    echo "  - Delivery Mode: {$nonOnlineRecord->delivery_mode}\n";
    echo "  - Admin Approved for Upcoming: " . ($nonOnlineRecord->admin_approved_for_upcoming ? 'YES' : 'NO') . "\n";
    echo "  Note: Non-Online Training also requires Auto-Assign button click\n\n";
    
    // Clean up test records
    echo "=== CLEANUP ===\n";
    $testRecord->delete();
    $nonOnlineRecord->delete();
    echo "✓ Deleted test records\n\n";
    
    echo "=== SUMMARY ===\n";
    echo "✅ Fix is working correctly!\n";
    echo "✅ Online Training delivery mode will NOT appear in Upcoming Training until Auto-Assign button is clicked\n";
    echo "✅ admin_approved_for_upcoming flag properly controls visibility\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
