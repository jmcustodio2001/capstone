<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DestinationKnowledgeTraining;
use App\Models\UpcomingTraining;

echo "Fixing BOHOL training assignment...\n";

// Find the BOHOL training record
$record = DestinationKnowledgeTraining::where('destination_name', 'BOHOL')->first();

if ($record) {
    echo "Found BOHOL record - ID: {$record->id}, Employee: {$record->employee_id}\n";
    echo "Current admin_approved_for_upcoming: " . ($record->admin_approved_for_upcoming ? 'TRUE' : 'FALSE') . "\n";
    echo "Current is_active: " . ($record->is_active ? 'TRUE' : 'FALSE') . "\n";
    echo "Current status: {$record->status}\n";
    
    // Set the admin_approved_for_upcoming flag
    if (!$record->admin_approved_for_upcoming) {
        $record->admin_approved_for_upcoming = true;
        $record->save();
        echo "✓ Updated admin_approved_for_upcoming to TRUE\n";
    } else {
        echo "✓ admin_approved_for_upcoming already set to TRUE\n";
    }
    
    // Check if upcoming training record exists
    $upcoming = UpcomingTraining::where('employee_id', $record->employee_id)
        ->where('training_title', 'BOHOL')
        ->first();
    
    echo "Upcoming training record exists: " . ($upcoming ? 'YES' : 'NO') . "\n";
    
    // Create upcoming training record if it doesn't exist
    if (!$upcoming) {
        $upcomingRecord = UpcomingTraining::create([
            'employee_id' => $record->employee_id,
            'training_title' => 'BOHOL',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'status' => 'Scheduled'
        ]);
        echo "✓ Created upcoming training record with ID: {$upcomingRecord->upcoming_id}\n";
    } else {
        echo "✓ Upcoming training record already exists\n";
    }
    
    echo "\n=== FINAL STATUS ===\n";
    $record->refresh();
    echo "admin_approved_for_upcoming: " . ($record->admin_approved_for_upcoming ? 'TRUE' : 'FALSE') . "\n";
    
    $upcomingCheck = UpcomingTraining::where('employee_id', $record->employee_id)
        ->where('training_title', 'BOHOL')
        ->first();
    echo "Upcoming training exists: " . ($upcomingCheck ? 'YES' : 'NO') . "\n";
    
    echo "\n✓ BOHOL training should now appear in employee's upcoming trainings!\n";
    
} else {
    echo "❌ No BOHOL training record found!\n";
}
