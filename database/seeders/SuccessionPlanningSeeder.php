<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuccessionCandidate;
use App\Models\SuccessionScenario;
use App\Models\Employee;
use App\Models\OrganizationalPosition;

class SuccessionPlanningSeeder extends Seeder
{
    public function run()
    {
        // Create some succession scenarios
        $scenarios = [
            [
                'scenario_name' => 'CEO Succession Plan',
                'scenario_type' => 'resignation',
                'description' => 'Emergency succession plan if CEO resigns unexpectedly',
                'affected_positions' => [1], // CEO position
                'impact_level' => 'high',
                'estimated_timeline_days' => 90,
                'status' => 'active',
                'created_by' => 'system'
            ],
            [
                'scenario_name' => 'Technology Department Restructure',
                'scenario_type' => 'restructuring',
                'description' => 'Restructuring of technology department with new roles',
                'affected_positions' => [2, 5], // CTO and Dev Manager
                'impact_level' => 'medium',
                'estimated_timeline_days' => 180,
                'status' => 'draft',
                'created_by' => 'system'
            ],
            [
                'scenario_name' => 'Rapid Growth Expansion',
                'scenario_type' => 'growth',
                'description' => 'Company expansion requiring new leadership positions',
                'affected_positions' => [1, 2, 3, 4], // All C-level positions
                'impact_level' => 'medium',
                'estimated_timeline_days' => 365,
                'status' => 'active',
                'created_by' => 'system'
            ]
        ];

        foreach ($scenarios as $scenario) {
            SuccessionScenario::create($scenario);
        }

        // Get some employees to create succession candidates
        $employees = Employee::limit(10)->get();
        $positions = OrganizationalPosition::all();

        if ($employees->count() > 0 && $positions->count() > 0) {
            // Create some sample succession candidates
            $candidateData = [
                // CEO candidates
                ['employee_id' => $employees->first()->employee_id, 'target_position_id' => 1, 'readiness_score' => 85.5, 'readiness_level' => 'developing'],
                // CTO candidates  
                ['employee_id' => $employees->skip(1)->first()->employee_id ?? $employees->first()->employee_id, 'target_position_id' => 2, 'readiness_score' => 92.0, 'readiness_level' => 'ready'],
                // CFO candidates
                ['employee_id' => $employees->skip(2)->first()->employee_id ?? $employees->first()->employee_id, 'target_position_id' => 3, 'readiness_score' => 78.5, 'readiness_level' => 'developing'],
            ];

            foreach ($candidateData as $candidate) {
                SuccessionCandidate::create(array_merge($candidate, [
                    'development_plan' => 'Structured development plan with mentoring and training',
                    'competency_gaps' => [
                        ['competency' => 'Leadership', 'gap' => 1, 'current' => 4, 'required' => 5],
                        ['competency' => 'Strategic Thinking', 'gap' => 0, 'current' => 5, 'required' => 5]
                    ],
                    'strengths' => ['Communication', 'Technical Skills'],
                    'development_areas' => ['Leadership', 'Strategic Planning'],
                    'status' => 'active',
                    'created_by' => 'system'
                ]));
            }
        }
    }
}
