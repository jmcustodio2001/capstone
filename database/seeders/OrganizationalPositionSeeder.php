<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrganizationalPosition;

class OrganizationalPositionSeeder extends Seeder
{
    public function run()
    {
        $positions = [
            [
                'position_name' => 'Chief Executive Officer',
                'position_code' => 'CEO',
                'description' => 'Chief Executive Officer responsible for overall company strategy and operations',
                'department' => 'Executive',
                'level' => 'executive',
                'hierarchy_level' => 1,
                'reports_to' => null,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 5, 'weight' => 3], // Leadership
                    ['competency_id' => 2, 'required_level' => 5, 'weight' => 3], // Strategic Thinking
                    ['competency_id' => 3, 'required_level' => 5, 'weight' => 2], // Communication
                ],
                'min_experience_years' => 10,
                'min_readiness_score' => 90.00,
                'is_critical_position' => true,
                'is_active' => true
            ],
            [
                'position_name' => 'Chief Technology Officer',
                'position_code' => 'CTO',
                'description' => 'Chief Technology Officer responsible for technology strategy and innovation',
                'department' => 'Technology',
                'level' => 'executive',
                'hierarchy_level' => 2,
                'reports_to' => 1,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 4, 'weight' => 2], // Leadership
                    ['competency_id' => 4, 'required_level' => 5, 'weight' => 3], // Technical Skills
                    ['competency_id' => 2, 'required_level' => 4, 'weight' => 2], // Strategic Thinking
                ],
                'min_experience_years' => 8,
                'min_readiness_score' => 85.00,
                'is_critical_position' => true,
                'is_active' => true
            ],
            [
                'position_name' => 'Chief Financial Officer',
                'position_code' => 'CFO',
                'description' => 'Chief Financial Officer responsible for financial strategy and operations',
                'department' => 'Finance',
                'level' => 'executive',
                'hierarchy_level' => 2,
                'reports_to' => 1,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 4, 'weight' => 2], // Leadership
                    ['competency_id' => 5, 'required_level' => 5, 'weight' => 3], // Financial Analysis
                    ['competency_id' => 2, 'required_level' => 4, 'weight' => 2], // Strategic Thinking
                ],
                'min_experience_years' => 8,
                'min_readiness_score' => 85.00,
                'is_critical_position' => true,
                'is_active' => true
            ],
            [
                'position_name' => 'Chief Marketing Officer',
                'position_code' => 'CMO',
                'description' => 'Chief Marketing Officer responsible for marketing strategy and brand management',
                'department' => 'Marketing',
                'level' => 'executive',
                'hierarchy_level' => 2,
                'reports_to' => 1,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 4, 'weight' => 2], // Leadership
                    ['competency_id' => 3, 'required_level' => 5, 'weight' => 3], // Communication
                    ['competency_id' => 6, 'required_level' => 4, 'weight' => 2], // Marketing Skills
                ],
                'min_experience_years' => 7,
                'min_readiness_score' => 80.00,
                'is_critical_position' => true,
                'is_active' => true
            ],
            [
                'position_name' => 'Development Manager',
                'position_code' => 'DEV_MGR',
                'description' => 'Development Manager responsible for software development teams',
                'department' => 'Technology',
                'level' => 'manager',
                'hierarchy_level' => 3,
                'reports_to' => 2,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 3, 'weight' => 2], // Leadership
                    ['competency_id' => 4, 'required_level' => 4, 'weight' => 3], // Technical Skills
                    ['competency_id' => 7, 'required_level' => 3, 'weight' => 2], // Team Management
                ],
                'min_experience_years' => 5,
                'min_readiness_score' => 75.00,
                'is_critical_position' => false,
                'is_active' => true
            ],
            [
                'position_name' => 'Finance Manager',
                'position_code' => 'FIN_MGR',
                'description' => 'Finance Manager responsible for financial operations and reporting',
                'department' => 'Finance',
                'level' => 'manager',
                'hierarchy_level' => 3,
                'reports_to' => 3,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 3, 'weight' => 2], // Leadership
                    ['competency_id' => 5, 'required_level' => 4, 'weight' => 3], // Financial Analysis
                    ['competency_id' => 8, 'required_level' => 3, 'weight' => 2], // Analytical Skills
                ],
                'min_experience_years' => 5,
                'min_readiness_score' => 75.00,
                'is_critical_position' => false,
                'is_active' => true
            ],
            [
                'position_name' => 'Sales Manager',
                'position_code' => 'SALES_MGR',
                'description' => 'Sales Manager responsible for sales team and revenue generation',
                'department' => 'Sales',
                'level' => 'manager',
                'hierarchy_level' => 3,
                'reports_to' => 4,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 3, 'weight' => 2], // Leadership
                    ['competency_id' => 3, 'required_level' => 4, 'weight' => 3], // Communication
                    ['competency_id' => 9, 'required_level' => 4, 'weight' => 2], // Sales Skills
                ],
                'min_experience_years' => 4,
                'min_readiness_score' => 70.00,
                'is_critical_position' => false,
                'is_active' => true
            ],
            [
                'position_name' => 'HR Manager',
                'position_code' => 'HR_MGR',
                'description' => 'HR Manager responsible for human resources operations and employee relations',
                'department' => 'Human Resources',
                'level' => 'manager',
                'hierarchy_level' => 3,
                'reports_to' => 1,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 3, 'weight' => 2], // Leadership
                    ['competency_id' => 3, 'required_level' => 4, 'weight' => 2], // Communication
                    ['competency_id' => 10, 'required_level' => 4, 'weight' => 3], // HR Skills
                ],
                'min_experience_years' => 4,
                'min_readiness_score' => 70.00,
                'is_critical_position' => false,
                'is_active' => true
            ]
        ];

        foreach ($positions as $position) {
            OrganizationalPosition::create($position);
        }
    }
}
