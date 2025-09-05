<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProfileUpdate;
use App\Models\Employee;

class FixProfileUpdateOldValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile-updates:fix-old-values';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix old_value fields in profile_updates table with actual employee data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix profile update old_values...');

        // Get all profile updates with N/A or empty old_value
        $updates = ProfileUpdate::whereIn('old_value', ['N/A', '', null])
            ->with('employee')
            ->get();

        $this->info("Found {$updates->count()} profile updates to fix.");

        $fixed = 0;
        $errors = 0;

        foreach ($updates as $update) {
            try {
                if (!$update->employee) {
                    $this->warn("Skipping update #{$update->id} - employee not found");
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

                $this->line("Fixed update #{$update->id} - {$update->field_name}: '{$currentValue}'");
                $fixed++;

            } catch (\Exception $e) {
                $this->error("Error fixing update #{$update->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("\nCompleted!");
        $this->info("Fixed: {$fixed} records");
        $this->info("Errors: {$errors} records");
        $this->info("Total processed: " . ($fixed + $errors) . " records");

        return Command::SUCCESS;
    }
}
