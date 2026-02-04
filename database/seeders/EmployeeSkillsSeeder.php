<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyLibrary;

class EmployeeSkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder fetches employees from the API and creates competency profiles
     * based on their skills, ensuring data persists after migrate:fresh
     */
    public function run(): void
    {
        $this->command->info('Fetching employees from API...');

        try {
            // Fetch employees from API
            $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            
            if (!$response->successful()) {
                $this->command->error('Failed to fetch employees from API');
                return;
            }

            $employees = $response->json();

            // Handle if response is wrapped in a data key
            if (isset($employees['data']) && is_array($employees['data'])) {
                $employees = $employees['data'];
            } elseif (!is_array($employees)) {
                $employees = [];
            }

            $this->command->info('Found ' . count($employees) . ' employees');

            $syncedCount = 0;
            $skillsCount = 0;

            foreach ($employees as $employee) {
                // Get employee ID
                $employeeId = $employee['employee_id'] ?? $employee['id'] ?? $employee['external_employee_id'] ?? null;
                
                if (!$employeeId) {
                    continue;
                }

                // Get employee skills
                $skills = $employee['skills'] ?? null;
                
                if (empty($skills) || $skills === 'N/A') {
                    continue;
                }

                // Parse skills
                $skillsList = $this->parseSkills($skills);

                foreach ($skillsList as $skillName) {
                    $skillName = trim($skillName);
                    
                    if (empty($skillName) || strlen($skillName) < 2) {
                        continue;
                    }

                    // Find competency in library (strict match to ensure count stays at 30)
                    $competency = CompetencyLibrary::where('competency_name', $skillName)->first();

                    // Try fuzzy match if not found
                    if (!$competency) {
                        $competency = CompetencyLibrary::where('competency_name', 'LIKE', '%' . $skillName . '%')->first();
                    }

                    // If still not found, skip to maintain the strict 30 count
                    if (!$competency) {
                        continue;
                    }

                    // Create or update employee competency profile
                    EmployeeCompetencyProfile::updateOrCreate(
                        [
                            'employee_id' => $employeeId,
                            'competency_id' => $competency->id
                        ],
                        [
                            'proficiency_level' => 5, // Max proficiency for listed skills
                            'assessment_date' => now()
                        ]
                    );

                    $skillsCount++;
                }

                $syncedCount++;
            }

            $this->command->info("Successfully synced skills for {$syncedCount} employees");
            $this->command->info("Total competency profiles created/updated: {$skillsCount}");

        } catch (\Exception $e) {
            $this->command->error('Error syncing employee skills: ' . $e->getMessage());
            Log::error('EmployeeSkillsSeeder error: ' . $e->getMessage());
        }
    }

    /**
     * Parse skills from various formats
     */
    private function parseSkills($skills): array
    {
        if (is_array($skills)) {
            return $skills;
        }

        // Try JSON decode first
        if (is_string($skills) && (str_starts_with($skills, '[') || str_starts_with($skills, '{'))) {
            $decoded = json_decode($skills, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Split by common delimiters
        $skillsList = [];
        
        // Try newline separation first
        if (strpos($skills, "\n") !== false) {
            $skillsList = explode("\n", $skills);
        } 
        // Try comma separation
        elseif (strpos($skills, ',') !== false) {
            $skillsList = explode(',', $skills);
        }
        // Try semicolon separation
        elseif (strpos($skills, ';') !== false) {
            $skillsList = explode(';', $skills);
        }
        // Try pipe separation
        elseif (strpos($skills, '|') !== false) {
            $skillsList = explode('|', $skills);
        }
        // Single skill
        else {
            $skillsList = [$skills];
        }

        // Clean up the skills
        return array_filter(array_map('trim', $skillsList));
    }
}
