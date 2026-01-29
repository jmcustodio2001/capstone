<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OrganizationalPosition;
use App\Models\SuccessionCandidate;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyLibrary;

class SuccessionEligibilityService
{
    public function evaluateAllCandidates()
    {
        $apiEmployees = $this->getEmployeesFromAPI();
        $employees = is_array($apiEmployees) ? collect($apiEmployees) : $apiEmployees;
        $positions = OrganizationalPosition::where('is_active', true)->get();
        
        $evaluations = [];
        
        foreach ($employees as $employee) {
            $empId = is_object($employee) ? $employee->employee_id : ($employee['employee_id'] ?? $employee['id'] ?? null);
            if (!$empId) continue;

            // Bridge API employee with local competency data
            if (!isset($employee->competencyProfiles)) {
                $employee->competencyProfiles = \App\Models\EmployeeCompetencyProfile::with('competency')
                    ->where('employee_id', $empId)
                    ->get();
            }

            $evaluations[$empId] = $this->evaluateEmployeeForAllPositions($employee);
        }
        
        return $evaluations;
    }

    public function evaluateEmployeeForAllPositions($employee)
    {
        $positions = OrganizationalPosition::where('is_active', true)->get();
        $eligibility = [];
        
        foreach ($positions as $position) {
            $readinessData = $this->calculatePositionReadiness($employee, $position);
            
            $eligibility[] = [
                'position_id' => $position->id,
                'position_name' => $position->position_name,
                'department' => $position->department,
                'level' => $position->level,
                'readiness_score' => $readinessData['score'],
                'readiness_level' => $readinessData['level'],
                'competency_gaps' => $readinessData['gaps'],
                'strengths' => $readinessData['strengths'],
                'is_eligible' => $readinessData['score'] >= $position->min_readiness_score,
                'missing_requirements' => $readinessData['missing_requirements']
            ];
        }
        
        // Sort by readiness score descending
        usort($eligibility, function($a, $b) {
            return $b['readiness_score'] <=> $a['readiness_score'];
        });
        
        return $eligibility;
    }

    public function getCandidatesForPosition($position)
    {
        $apiEmployees = $this->getEmployeesFromAPI();
        $employees = is_array($apiEmployees) ? collect($apiEmployees) : $apiEmployees;
        $candidates = [];
        
        foreach ($employees as $employee) {
            $empId = is_object($employee) ? $employee->employee_id : ($employee['employee_id'] ?? $employee['id'] ?? null);
            if (!$empId) continue;

            // Bridge API employee with local competency data
            if (!isset($employee->competencyProfiles)) {
                $employee->competencyProfiles = \App\Models\EmployeeCompetencyProfile::with('competency')
                    ->where('employee_id', $empId)
                    ->get();
            }

            $readinessData = $this->calculatePositionReadiness($employee, $position);
            
            // Include all candidates with any readiness (>= 0%)
        if ($readinessData['score'] >= 0) {
                $fname = is_object($employee) ? $employee->first_name : ($employee['first_name'] ?? 'Unknown');
                $lname = is_object($employee) ? $employee->last_name : ($employee['last_name'] ?? 'Employee');
                $pos = is_object($employee) ? ($employee->position ?? null) : ($employee['position'] ?? $employee['role'] ?? null);
                $dept = is_object($employee) ? ($employee->department ?? null) : ($employee['department'] ?? null);

                $candidates[] = [
                    'employee_id' => $empId,
                    'name' => $fname . ' ' . $lname,
                    'current_position' => $pos ?? 'Not specified',
                    'department' => $dept ?? 'Not specified',
                    'readiness_score' => $readinessData['score'],
                    'readiness_level' => $readinessData['level'],
                    'competency_breakdown' => $readinessData['competency_breakdown'],
                    'strengths' => $readinessData['strengths'],
                    'gaps' => $readinessData['gaps'],
                    'experience_years' => $this->calculateExperience($employee),
                    'is_ready' => $readinessData['score'] >= $position->min_readiness_score
                ];
            }
        }
        
        // Sort by readiness score descending
        usort($candidates, function($a, $b) {
            return $b['readiness_score'] <=> $a['readiness_score'];
        });
        
        return $candidates;
    }

    public function calculatePositionReadiness($employee, $position)
    {
        $requiredCompetencies = $position->required_competencies ?? [];
        
        if (empty($requiredCompetencies)) {
            return [
                'score' => 0,
                'level' => 'not_assessed',
                'gaps' => [],
                'strengths' => [],
                'competency_breakdown' => [],
                'missing_requirements' => ['No competency requirements defined for this position']
            ];
        }

        $employeeCompetencies = $employee->competencyProfiles->keyBy('competency_id');
        $totalScore = 0;
        $totalWeight = 0;
        $competencyBreakdown = [];
        $gaps = [];
        $strengths = [];
        $missingRequirements = [];

        foreach ($requiredCompetencies as $requirement) {
            $competencyId = $requirement['competency_id'] ?? null;
            $requiredLevel = $requirement['required_level'] ?? 3;
            $weight = $requirement['weight'] ?? 1;

            if (!$competencyId) continue;

            $competency = CompetencyLibrary::find($competencyId);
            if (!$competency) continue;

            $employeeProfile = $employeeCompetencies->get($competencyId);
            $currentLevel = $employeeProfile ? $employeeProfile->proficiency_level : 0;

            // Calculate score for this competency (0-100%)
            $competencyScore = $currentLevel > 0 ? min(($currentLevel / $requiredLevel) * 100, 100) : 0;
            
            $competencyBreakdown[] = [
                'competency_name' => $competency->competency_name,
                'required_level' => $requiredLevel,
                'current_level' => $currentLevel,
                'score' => $competencyScore,
                'gap' => max(0, $requiredLevel - $currentLevel)
            ];

            if ($currentLevel >= $requiredLevel) {
                $strengths[] = $competency->competency_name;
            } elseif ($currentLevel > 0) {
                $gaps[] = [
                    'competency' => $competency->competency_name,
                    'gap' => $requiredLevel - $currentLevel,
                    'current' => $currentLevel,
                    'required' => $requiredLevel
                ];
            } else {
                $missingRequirements[] = $competency->competency_name;
            }

            $totalScore += $competencyScore * $weight;
            $totalWeight += $weight;
        }

        $overallScore = $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
        
        // Determine readiness level
        $readinessLevel = 'potential';
        if ($overallScore >= 90) {
            $readinessLevel = 'ready';
        } elseif ($overallScore >= 70) {
            $readinessLevel = 'developing';
        }

        return [
            'score' => $overallScore,
            'level' => $readinessLevel,
            'competency_breakdown' => $competencyBreakdown,
            'gaps' => $gaps,
            'strengths' => $strengths,
            'missing_requirements' => $missingRequirements
        ];
    }

    private function calculateExperience($employee)
    {
        // Calculate years of experience based on hire date or other factors
        if (isset($employee->hire_date)) {
            return now()->diffInYears($employee->hire_date);
        }
        
        // Fallback: estimate based on competency levels
        $avgCompetencyLevel = $employee->competencyProfiles->avg('proficiency_level') ?? 1;
        return max(1, floor($avgCompetencyLevel)); // Rough estimate
    }

    public function autoUpdateCandidates()
    {
        $apiEmployees = $this->getEmployeesFromAPI();
        $employees = is_array($apiEmployees) ? collect($apiEmployees) : $apiEmployees;
        $positions = OrganizationalPosition::where('is_active', true)->get();
        
        foreach ($employees as $employee) {
            $empId = is_object($employee) ? $employee->employee_id : ($employee['employee_id'] ?? $employee['id'] ?? null);
            if (!$empId) continue;

            // Bridge API employee with local competency data
            if (!isset($employee->competencyProfiles)) {
                $employee->competencyProfiles = \App\Models\EmployeeCompetencyProfile::with('competency')
                    ->where('employee_id', $empId)
                    ->get();
            }

            foreach ($positions as $position) {
                $readinessData = $this->calculatePositionReadiness($employee, $position);
                
                // Only create/update if employee meets minimum threshold
                if ($readinessData['score'] >= 60) {
                    SuccessionCandidate::updateOrCreate(
                        [
                            'employee_id' => $empId,
                            'target_position_id' => $position->id
                        ],
                        [
                            'readiness_score' => $readinessData['score'],
                            'readiness_level' => $readinessData['level'],
                            'competency_gaps' => $readinessData['gaps'],
                            'strengths' => $readinessData['strengths'],
                            'development_areas' => $readinessData['missing_requirements'],
                            'updated_by' => 'system_auto_update'
                        ]
                    );
                }
            }
        }
    }

    private function getEmployeesFromAPI()
    {
        // Map local emails to local profile pictures
        $emailToLocalMap = [];
        try {
            $localEmployees = \App\Models\Employee::all();
            foreach ($localEmployees as $localEmp) {
                if ($localEmp->email) {
                    $emailToLocalMap[strtolower($localEmp->email)] = $localEmp;
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to fetch local employees for mapping in SuccessionEligibilityService: ' . $e->getMessage());
        }

        try {
            $response = \Illuminate\Support\Facades\Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            $apiEmployees = $response->successful() ? $response->json() : [];
            
            if (isset($apiEmployees['data']) && is_array($apiEmployees['data'])) {
                $apiEmployees = $apiEmployees['data'];
            }

            if (is_array($apiEmployees) && !empty($apiEmployees)) {
                return collect($apiEmployees)->map(function($emp) use ($emailToLocalMap) {
                    $profilePic = $emp['profile_picture'] ?? null;
                    $empEmail = strtolower($emp['email'] ?? '');
                    
                    // Prioritize local photo if it exists
                    $localRef = $emailToLocalMap[$empEmail] ?? null;
                    if ($localRef && $localRef->profile_picture) {
                        $profilePic = $localRef->profile_picture;
                    } elseif ($profilePic && strpos($profilePic, 'http') !== 0) {
                        $profilePic = 'https://hr4.jetlougetravels-ph.com/storage/' . ltrim($profilePic, '/');
                    }
                    
                    return (object) [
                        'employee_id' => $emp['employee_id'] ?? $emp['id'] ?? $emp['external_employee_id'] ?? 'N/A',
                        'first_name' => $emp['first_name'] ?? 'Unknown',
                        'last_name' => $emp['last_name'] ?? 'Employee',
                        'position' => $emp['role'] ?? $emp['position'] ?? 'N/A',
                        'profile_picture' => $profilePic,
                        'hire_date' => $emp['date_hired'] ?? $emp['hire_date'] ?? null
                    ];
                });
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to fetch employees from API in SuccessionEligibilityService: ' . $e->getMessage());
        }
        return Employee::all();
    }
}
