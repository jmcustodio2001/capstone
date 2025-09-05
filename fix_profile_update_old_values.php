<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\ProfileUpdate;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting to fix profile update old_values...\n";

// Get all profile updates with N/A or empty old_value
$updates = ProfileUpdate::whereIn('old_value', ['N/A', '', null])
    ->with('employee')
    ->get();

echo "Found " . $updates->count() . " profile updates to fix.\n";

$fixed = 0;
$errors = 0;

foreach ($updates as $update) {
    try {
        if (!$update->employee) {
            echo "Skipping update #{$update->id} - employee not found\n";
            $errors++;
            continue;
        }

        // Field mapping for proper database column names
        $fieldMapping = [
            'phone' => 'phone_number',
            'phone_number' => 'phone_number',
            'emergency_contact_name' => 'emergency_contact_name',
            'emergency_contact_phone' => 'emergency_contact_phone', 
            'emergency_contact_relationship' => 'emergency_contact_relationship',
        ];

        $actualFieldName = $fieldMapping[$update->field_name] ?? $update->field_name;
        $currentValue = $update->employee->{$actualFieldName};

        // Handle special cases for better display
        if ($currentValue === null || $currentValue === '' || $currentValue === 'N/A') {
            $currentValue = 'Not set';
        }

        // Update the old_value
        $update->update(['old_value' => $currentValue]);

        echo "Fixed update #{$update->id} - {$update->field_name}: '{$currentValue}'\n";
        $fixed++;

    } catch (Exception $e) {
        echo "Error fixing update #{$update->id}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\nCompleted!\n";
echo "Fixed: {$fixed} records\n";
echo "Errors: {$errors} records\n";
echo "Total processed: " . ($fixed + $errors) . " records\n";
