<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DestinationKnowledgeTraining;
use App\Models\UpcomingTraining;

echo "Fixing ALL destination training assignments...\n\n";

// Find all destination training records that should be assigned to upcoming
$records = DestinationKnowledgeTraining::all();

$fixedCount = 0;
$createdUpcomingCount = 0;

foreach ($records as $record) {
    echo "Processing: {$record->destination_name} for Employee {$record->employee_id}\n";
    
    $needsUpdate = false;
    
    // Check if admin_approved_for_upcoming should be set
    // If the record shows "Already Assigned" in admin panel, it should have this flag
    if (!$record->admin_approved_for_upcoming && $record->is_active) {
        $record->admin_approved_for_upcoming = true;
        $needsUpdate = true;
        echo "  ✓ Setting admin_approved_for_upcoming to TRUE\n";
    }
    
    // Save if needed
    if ($needsUpdate) {
        $record->save();
        $fixedCount++;
    }
    
    // Check if upcoming training record exists
    $upcoming = UpcomingTraining::where('employee_id', $record->employee_id)
        ->where('training_title', $record->destination_name)
        ->first();
    
    // Create upcoming training record if approved but doesn't exist
    if ($record->admin_approved_for_upcoming && !$upcoming) {
        UpcomingTraining::create([
            'employee_id' => $record->employee_id,
            'training_title' => $record->destination_name,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'status' => 'Scheduled'
        ]);
        echo "  ✓ Created upcoming training record\n";
        $createdUpcomingCount++;
    }
    
    echo "  Status: admin_approved=" . ($record->admin_approved_for_upcoming ? 'TRUE' : 'FALSE') . 
         ", upcoming_exists=" . ($upcoming ? 'YES' : 'NO') . "\n\n";
}

echo "=== SUMMARY ===\n";
echo "Total records processed: " . $records->count() . "\n";
echo "Records with admin_approved_for_upcoming flag fixed: {$fixedCount}\n";
echo "Upcoming training records created: {$createdUpcomingCount}\n";
echo "\n✓ All destination trainings should now appear in employee upcoming views!\n";
