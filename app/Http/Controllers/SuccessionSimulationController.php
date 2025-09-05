<?php

namespace App\Http\Controllers;

use App\Models\SuccessionSimulation;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;

class SuccessionSimulationController extends Controller
{
    public function index(): View
    {
        $simulations = $this->getSimulations();
        $employees = $this->getEmployeesList();
        $completedCertificates = $this->getCompletedCertificates();
        $certificateStatuses = $this->getCertificateStatuses($simulations);
        $positions = $this->getOrganizationalPositions();
        $topCandidates = $this->getTopCandidatesForPositions($positions);
        $dashboardMetrics = $this->calculateDashboardMetrics();
        $scenarioData = $this->generateScenarioData($positions, $topCandidates, $dashboardMetrics);

        return view('succession_planning.succession_planning_dashboard_simulation_tools', [
            'simulations' => $simulations,
            'employees' => $employees,
            'certificateStatuses' => $certificateStatuses,
            'positions' => $positions,
            'topCandidates' => $topCandidates,
            'totalCandidates' => $dashboardMetrics['totalCandidates'],
            'readyLeaders' => $dashboardMetrics['readyLeaders'],
            'inDevelopment' => $dashboardMetrics['inDevelopment'],
            'keyPositions' => $dashboardMetrics['keyPositions'],
            'scenarioData' => $scenarioData,
            'completedCertificates' => $completedCertificates
        ]);
    }

    private function getSimulations(): Collection
    {
        return SuccessionSimulation::with('employee')->orderByDesc('created_at')->get();
    }

    private function getEmployeesList(): Collection
    {
        return Employee::all()->map(function($emp) {
            return [
                'id' => $emp->employee_id,
                'name' => $emp->first_name . ' ' . $emp->last_name
            ];
        });
    }

    private function getCompletedCertificates(): Collection
    {
        return \App\Models\TrainingRecordCertificateTracking::with(['course', 'employee'])
            ->where('status', 'Completed')
            ->whereNotNull('certificate_number')
            ->get()
            ->map(function($cert) {
                $employeeName = $cert->employee->first_name . ' ' . $cert->employee->last_name;
                $courseTitle = $cert->course ? $cert->course->course_title : 'Unknown Course';
                
                return [
                    'employee_name' => $employeeName,
                    'course_title' => $courseTitle,
                    'certificate_number' => $cert->certificate_number,
                    'completion_date' => $cert->training_date,
                    'display_text' => $employeeName . ' - ' . $courseTitle . ' (Cert: ' . $cert->certificate_number . ')'
                ];
            });
    }

    private function getCertificateStatuses(Collection $simulations): array
    {
        $certificateStatuses = [];
        
        foreach ($simulations as $sim) {
            $latestCert = \App\Models\TrainingRecordCertificateTracking::with('course')
                ->where('employee_id', $sim->employee_id)
                ->orderByDesc('training_date')
                ->first();
                
            $certificateStatuses[$sim->id] = $latestCert ? [
                'status' => $latestCert->status,
                'date' => $latestCert->training_date,
                'course' => $latestCert->course ? $latestCert->course->course_title : null,
                'certificate_number' => $latestCert->certificate_number,
                'certificate_expiry' => $latestCert->certificate_expiry,
                'certificate_url' => $latestCert->certificate_url,
                'remarks' => $latestCert->remarks,
            ] : null;
        }
        
        return $certificateStatuses;
    }

    private function getOrganizationalPositions(): Collection
    {
        return collect([
            (object)['id' => 1, 'position_title' => 'Chief Executive Officer', 'department' => 'Executive', 'level' => 1],
            (object)['id' => 2, 'position_title' => 'Chief Technology Officer', 'department' => 'Technology', 'level' => 2],
            (object)['id' => 3, 'position_title' => 'Chief Human Resources Officer', 'department' => 'Human Resources', 'level' => 2],
            (object)['id' => 4, 'position_title' => 'Chief Financial Officer', 'department' => 'Finance', 'level' => 2],
            (object)['id' => 5, 'position_title' => 'Software Development Manager', 'department' => 'Technology', 'level' => 3],
            (object)['id' => 6, 'position_title' => 'HR Manager', 'department' => 'Human Resources', 'level' => 3],
            (object)['id' => 7, 'position_title' => 'Finance Manager', 'department' => 'Finance', 'level' => 3],
            (object)['id' => 8, 'position_title' => 'Operations Manager', 'department' => 'Operations', 'level' => 3]
        ]);
    }

    private function calculateReadinessScore($competencyProfiles): float
    {
        if ($competencyProfiles->isEmpty()) {
            return 0;
        }

        $avgProficiency = $competencyProfiles->avg('proficiency_level') ?? 0;
        $leadershipCount = $competencyProfiles->filter(function($profile) {
            return $profile->competency && 
                   str_contains(strtolower($profile->competency->competency_name ?? ''), 'leadership');
        })->count();
        $totalCompetencies = $competencyProfiles->count();

        // Weighted scoring: 40% proficiency, 30% leadership, 30% total competencies
        return ($avgProficiency * 0.4) + 
               (min($leadershipCount * 20, 100) * 0.3) + 
               (min($totalCompetencies * 10, 100) * 0.3);
    }

    private function getTopCandidatesForPositions(Collection $positions): array
    {
        $topCandidates = [];
        
        foreach ($positions as $position) {
            $candidates = Employee::with('competencyProfiles.competency')
                ->get()
                ->map(function($employee) {
                    $readinessScore = $this->calculateReadinessScore($employee->competencyProfiles);
                    
                    return [
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'employee_id' => $employee->employee_id,
                        'readiness_score' => round($readinessScore, 1)
                    ];
                })
                ->sortByDesc('readiness_score')
                ->take(3);

            $topCandidates[$position->id] = $candidates;
        }
        
        return $topCandidates;
    }

    private function calculateDashboardMetrics(): array
    {
        $employees = Employee::with('competencyProfiles.competency')->get();
        
        $totalCandidates = $employees->filter(function($employee) {
            return $employee->competencyProfiles->isNotEmpty();
        })->count();

        $readyLeaders = $employees->filter(function($employee) {
            $readinessScore = $this->calculateReadinessScore($employee->competencyProfiles);
            return $readinessScore >= 80;
        })->count();

        $inDevelopment = $employees->filter(function($employee) {
            $readinessScore = $this->calculateReadinessScore($employee->competencyProfiles);
            return $readinessScore >= 40 && $readinessScore < 80;
        })->count();

        return [
            'totalCandidates' => $totalCandidates,
            'readyLeaders' => $readyLeaders,
            'inDevelopment' => $inDevelopment,
            'keyPositions' => $this->getOrganizationalPositions()->count()
        ];
    }

    private function generateScenarioData(Collection $positions, array $topCandidates, array $metrics): array
    {
        $scenarioData = [];
        
        // CEO Departure Scenario
        $ceoPosition = $positions->where('level', 1)->first();
        if ($ceoPosition) {
            $ceoCandidates = $topCandidates[1] ?? collect();
            $topCeoCandidate = $ceoCandidates->first();
            
            $scenarioData[] = [
                'id' => 'ceo_departure',
                'title' => 'CEO Departure',
                'description' => 'Sudden departure of Chief Executive Officer',
                'impact_level' => 'High',
                'ready_successor' => $topCeoCandidate ? 
                    $topCeoCandidate['name'] . ' (' . round($topCeoCandidate['readiness_score']) . '% ready)' : 
                    'No immediate successor',
                'transition_time' => $topCeoCandidate && $topCeoCandidate['readiness_score'] >= 80 ? 
                    '2-3 months' : '6-12 months',
                'affected_positions' => '3-5 executive roles'
            ];
        }

        // Department Restructuring Scenario
        $departments = $positions->pluck('department')->unique()->count();
        $scenarioData[] = [
            'id' => 'dept_restructure',
            'title' => 'Department Restructuring',
            'description' => 'Merging departments to optimize operations',
            'impact_level' => 'Medium',
            'affected_positions' => $departments . ' departments',
            'timeline' => '4-6 months',
            'positions_affected' => round($positions->count() * 0.6) . ' roles'
        ];

        // Rapid Growth Scenario
        $managerPositions = $positions->where('level', 3)->count();
        $scenarioData[] = [
            'id' => 'rapid_growth',
            'title' => 'Rapid Growth Scenario',
            'description' => 'Company expansion requiring new leadership',
            'impact_level' => 'Manageable',
            'new_positions' => ($managerPositions * 2) . '+ roles',
            'leadership_gap' => round($managerPositions * 1.5) . ' positions',
            'timeline' => '12-18 months'
        ];

        // Key Manager Departure Scenario
        $criticalRoles = $positions->where('level', '<=', 2)->count();
        $scenarioData[] = [
            'id' => 'manager_departure',
            'title' => 'Key Manager Departure',
            'description' => 'Multiple senior managers leave simultaneously',
            'impact_level' => 'Medium',
            'positions_at_risk' => $criticalRoles . ' critical roles',
            'recovery_time' => $metrics['readyLeaders'] >= 3 ? '3-4 months' : '6-8 months',
            'succession_readiness' => round(($metrics['readyLeaders'] / max($metrics['totalCandidates'], 1)) * 100) . '%'
        ];

        return $scenarioData;
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'simulation_result' => 'required|string',
            'created_at' => 'required|date',
        ]);
        $employee = Employee::find($request->employee_id);
        $sim = SuccessionSimulation::create([
            'employee_id' => $request->employee_id,
            'simulation_result' => $request->simulation_result,
            'created_at' => $request->created_at,
        ]);
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Succession Simulation',
            'description' => 'Added simulation entry (ID: ' . $sim->id . ')',
        ]);
        return redirect()->route('succession_simulations.index')->with('success', 'Simulation entry added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'simulation_result' => 'required|string',
            'created_at' => 'required|date',
        ]);

        $sim = SuccessionSimulation::findOrFail($id);
        $sim->update([
            'employee_id' => $request->employee_id,
            'simulation_result' => $request->simulation_result,
            'created_at' => $request->created_at,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'module' => 'Succession Simulation',
            'description' => 'Updated simulation entry (ID: ' . $sim->id . ')',
        ]);

        return response()->json(['success' => true, 'message' => 'Simulation entry updated successfully.']);
    }

    public function destroy($id)
    {
        $sim = SuccessionSimulation::findOrFail($id);
        $sim->delete();
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Succession Simulation',
            'description' => 'Deleted simulation entry (ID: ' . $sim->id . ')',
        ]);
        return redirect()->route('succession_simulations.index')->with('success', 'Simulation entry deleted successfully.');
    }
}
