<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyLibrary;
use Illuminate\Support\Facades\Log;

class EmployeeSkillObserver
{
    /**
     * Handle the Employee "created" event.
     * Automatically create competency profiles based on employee skills
     */
    public function created(Employee $employee): void
    {
        $this->syncEmployeeSkills($employee);
    }

    /**
     * Handle the Employee "updated" event.
     * Update competency profiles when employee skills change
     */
    public function updated(Employee $employee): void
    {
        $this->syncEmployeeSkills($employee);
    }

    /**
     * Sync employee skills to competency profiles
     */
    private function syncEmployeeSkills(Employee $employee): void
    {
        try {
            // Get skills from employee record (assuming it's stored in a 'skills' field)
            $skills = $employee->skills ?? null;
            
            if (empty($skills)) {
                return;
            }

            // Parse skills - could be comma-separated, newline-separated, or JSON
            $skillsList = $this->parseSkills($skills);

            foreach ($skillsList as $skillName) {
                $skillName = trim($skillName);
                
                if (empty($skillName)) {
                    continue;
                }

                // Find or create competency in library
                $competency = CompetencyLibrary::firstOrCreate(
                    ['competency_name' => $skillName],
                    [
                        'description' => 'Auto-created from employee skills',
                        'category' => 'Technical Skills'
                    ]
                );

                // Create or update employee competency profile
                EmployeeCompetencyProfile::updateOrCreate(
                    [
                        'employee_id' => $employee->employee_id,
                        'competency_id' => $competency->id
                    ],
                    [
                        'proficiency_level' => 5, // Max proficiency for listed skills
                        'assessment_date' => now()
                    ]
                );
            }

            Log::info("Synced skills for employee: {$employee->employee_id}");
        } catch (\Exception $e) {
            Log::error("Error syncing employee skills: " . $e->getMessage());
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
        // Single skill
        else {
            $skillsList = [$skills];
        }

        // Clean up the skills
        return array_filter(array_map('trim', $skillsList));
    }
}
