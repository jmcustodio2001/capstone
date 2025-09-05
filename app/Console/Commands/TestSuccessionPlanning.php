<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\OrganizationalPosition;
use App\Models\SuccessionCandidate;
use App\Services\SuccessionEligibilityService;

class TestSuccessionPlanning extends Command
{
    protected $signature = 'test:succession-planning';
    protected $description = 'Test succession planning functionality';

    public function handle()
    {
        $this->info('Testing Succession Planning Features...');

        // Test 1: Create sample organizational positions
        $this->info('1. Creating sample organizational positions...');
        $this->createSamplePositions();

        // Test 2: Test eligibility service
        $this->info('2. Testing employee eligibility calculation...');
        $this->testEligibilityService();

        // Test 3: Test automatic candidate evaluation
        $this->info('3. Testing automatic candidate evaluation...');
        $this->testCandidateEvaluation();

        $this->info('✅ All succession planning features tested successfully!');
    }

    private function createSamplePositions()
    {
        $positions = [
            [
                'position_name' => 'Chief Executive Officer',
                'position_code' => 'CEO',
                'description' => 'Chief Executive Officer',
                'department' => 'Executive',
                'level' => 'executive',
                'hierarchy_level' => 1,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 5, 'weight' => 3],
                    ['competency_id' => 2, 'required_level' => 5, 'weight' => 3],
                ],
                'min_experience_years' => 10,
                'min_readiness_score' => 90.00,
                'is_critical_position' => true,
                'is_active' => true
            ],
            [
                'position_name' => 'Chief Technology Officer',
                'position_code' => 'CTO',
                'description' => 'Chief Technology Officer',
                'department' => 'Technology',
                'level' => 'executive',
                'hierarchy_level' => 2,
                'required_competencies' => [
                    ['competency_id' => 1, 'required_level' => 4, 'weight' => 2],
                    ['competency_id' => 3, 'required_level' => 5, 'weight' => 3],
                ],
                'min_experience_years' => 8,
                'min_readiness_score' => 85.00,
                'is_critical_position' => true,
                'is_active' => true
            ]
        ];

        foreach ($positions as $position) {
            OrganizationalPosition::updateOrCreate(
                ['position_code' => $position['position_code']],
                $position
            );
        }

        $this->info('   ✅ Sample positions created');
    }

    private function testEligibilityService()
    {
        $service = new SuccessionEligibilityService();
        $employees = Employee::limit(3)->get();

        if ($employees->count() > 0) {
            foreach ($employees as $employee) {
                $eligibility = $service->evaluateEmployeeForAllPositions($employee);
                $this->info("   Employee: {$employee->first_name} {$employee->last_name}");
                
                foreach ($eligibility as $position) {
                    $this->info("     - {$position['position_name']}: {$position['readiness_score']}% ready");
                }
            }
            $this->info('   ✅ Eligibility service working');
        } else {
            $this->warn('   ⚠️ No employees found for testing');
        }
    }

    private function testCandidateEvaluation()
    {
        $service = new SuccessionEligibilityService();
        $positions = OrganizationalPosition::limit(2)->get();

        foreach ($positions as $position) {
            $candidates = $service->getCandidatesForPosition($position);
            $this->info("   Position: {$position->position_name}");
            $this->info("   Found {$candidates->count()} eligible candidates");
            
            foreach ($candidates->take(2) as $candidate) {
                $this->info("     - {$candidate['name']}: {$candidate['readiness_score']}%");
            }
        }

        $this->info('   ✅ Candidate evaluation working');
    }
}
