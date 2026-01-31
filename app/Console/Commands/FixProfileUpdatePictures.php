<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProfileUpdate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FixProfileUpdatePictures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:profile-update-pictures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing employee profile pictures in profile_updates table using external API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching employees from API...');

        try {
            $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            
            if (!$response->successful()) {
                $this->error('Failed to fetch employees from API: ' . $response->status());
                return;
            }

            $data = $response->json();
            // Handle different API response structures
            $employees = $data['data'] ?? $data;

            if (!is_array($employees)) {
                $this->error('Invalid API response format');
                return;
            }

            // Create a lookup map by ID
            $employeeMap = [];
            foreach ($employees as $emp) {
                $id = $emp['employee_id'] ?? $emp['id'] ?? $emp['external_employee_id'] ?? null;
                if ($id) {
                    $employeeMap[(string)$id] = $emp;
                }
            }

            $this->info('Found ' . count($employeeMap) . ' employees from API.');

            // Get updates with missing profile pictures
            $updates = ProfileUpdate::whereNull('employee_profile_picture')
                ->orWhere('employee_profile_picture', '')
                ->get();

            $this->info('Found ' . $updates->count() . ' profile updates to check.');

            $updatedCount = 0;

            foreach ($updates as $update) {
                $empId = (string)$update->employee_id;
                
                if (isset($employeeMap[$empId])) {
                    $empData = $employeeMap[$empId];
                    
                    $profilePicture = $empData['profile_picture'] ?? null;

                    if ($profilePicture) {
                        $update->update([
                            'employee_profile_picture' => $profilePicture
                        ]);

                        $this->info("Updated request #{$update->id} for employee ID {$empId} -> Picture found");
                        $updatedCount++;
                    } else {
                         $this->warn("No profile picture found for employee ID {$empId}");
                    }
                } else {
                    $this->warn("Could not find employee details for request #{$update->id} (Employee ID: {$empId})");
                }
            }

            $this->info("Completed. Updated {$updatedCount} records.");

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('FixProfileUpdatePictures error: ' . $e->getMessage());
        }
    }
}
