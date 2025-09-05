<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\OrganizationalPosition;
use App\Models\SuccessionCandidate;
use App\Models\SuccessionScenario;
use App\Models\Competency;
use App\Models\EmployeeCompetencyProfile;
use App\Services\SuccessionEligibilityService;

class SuccessionPlanningController extends Controller
{
    protected $eligibilityService;

    public function __construct(SuccessionEligibilityService $eligibilityService)
    {
        $this->eligibilityService = $eligibilityService;
    }

    public function index()
    {
        // Get all employees with their competency data
        $employees = Employee::with(['competencyProfiles.competency'])->get();
        
        // Get organizational positions
        $positions = OrganizationalPosition::where('is_active', true)->get();
        
        // Automatically evaluate all employees for all positions
        $candidateEvaluations = $this->eligibilityService->evaluateAllCandidates();
        
        // Get existing simulations
        $simulations = SuccessionScenario::with('createdBy')->latest()->get();
        
        // Calculate dashboard statistics
        $totalCandidates = SuccessionCandidate::where('status', 'active')->count();
        $readyLeaders = SuccessionCandidate::where('status', 'active')
            ->where('readiness_score', '>=', 90)->count();
        $inDevelopment = SuccessionCandidate::where('status', 'active')
            ->whereBetween('readiness_score', [70, 89])->count();
        $keyPositions = OrganizationalPosition::where('is_active', true)
            ->where('is_critical_position', true)->count();
        
        // Get top candidates for each position
        $topCandidates = [];
        foreach ($positions as $position) {
            $candidates = $this->eligibilityService->getCandidatesForPosition($position);
            $topCandidates[$position->id] = collect($candidates)->take(3);
        }
        
        // Get scenario data
        $scenarioData = [
            [
                'id' => 'ceo_resignation',
                'title' => 'CEO Resignation Scenario',
                'description' => 'What happens if the current CEO resigns unexpectedly?',
                'impact_level' => 'High',
                'ready_successor' => 'John Smith (95% ready)',
                'transition_time' => '2-3 months'
            ],
            [
                'id' => 'rapid_growth',
                'title' => 'Rapid Growth Scenario',
                'description' => 'Company doubles in size within 12 months',
                'impact_level' => 'Tremendous',
                'new_positions' => '25+ roles',
                'leadership_gap' => '12 positions'
            ],
            [
                'id' => 'department_restructure',
                'title' => 'Department Restructuring',
                'description' => 'Impact of merging IT and Operations departments',
                'impact_level' => 'Medium',
                'affected_positions' => '9 roles',
                'timeline' => '6 months'
            ],
            [
                'id' => 'key_manager_departure',
                'title' => 'Key Manager Departure',
                'description' => 'Multiple senior managers leave simultaneously',
                'impact_level' => 'Extreme',
                'positions_at_risk' => '5 critical roles',
                'recovery_time' => '4-6 months'
            ]
        ];
        
        return view('succession_planning.succession_planning_dashboard_simulation_tools', compact(
            'employees', 
            'positions', 
            'candidateEvaluations', 
            'simulations', 
            'totalCandidates',
            'readyLeaders',
            'inDevelopment',
            'keyPositions',
            'topCandidates',
            'scenarioData'
        ));
    }

    public function getEmployeeEligibility($employeeId)
    {
        $employee = Employee::with(['competencyProfiles.competency'])->where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $eligibility = $this->eligibilityService->evaluateEmployeeForAllPositions($employee);
        
        return response()->json($eligibility);
    }

    public function getPositionCandidates($positionId)
    {
        $position = OrganizationalPosition::find($positionId);
        
        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        $candidates = $this->eligibilityService->getCandidatesForPosition($position);
        
        return response()->json($candidates);
    }

    public function updateCandidateStatus(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'position_id' => 'required|integer',
            'status' => 'required|string|in:active,inactive,promoted'
        ]);

        $candidate = SuccessionCandidate::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'target_position_id' => $request->position_id
            ],
            [
                'status' => $request->status,
                'updated_by' => auth('employee')->user()->employee_id ?? 'system'
            ]
        );

        return response()->json(['success' => true, 'candidate' => $candidate]);
    }

    public function addCandidate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'target_position_id' => 'required|integer|exists:organizational_positions,id',
            'status' => 'required|string|in:active,inactive,under_review',
            'development_priority' => 'nullable|string|in:high,medium,low',
            'notes' => 'nullable|string'
        ]);

        // Check if candidate already exists for this position
        $existingCandidate = SuccessionCandidate::where('employee_id', $request->employee_id)
            ->where('target_position_id', $request->target_position_id)
            ->first();

        if ($existingCandidate) {
            return response()->json([
                'success' => false,
                'message' => 'This employee is already a candidate for this position.'
            ], 400);
        }

        // Get employee and position for readiness calculation
        $employee = Employee::with(['competencyProfiles.competency'])->where('employee_id', $request->employee_id)->first();
        $position = OrganizationalPosition::find($request->target_position_id);

        // Calculate readiness score
        $readinessData = $this->eligibilityService->calculatePositionReadiness($employee, $position);

        $candidate = SuccessionCandidate::create([
            'employee_id' => $request->employee_id,
            'target_position_id' => $request->target_position_id,
            'status' => $request->status,
            'readiness_score' => $readinessData['score'],
            'development_priority' => $request->development_priority ?? 'medium',
            'notes' => $request->notes,
            'created_by' => auth('employee')->user()->employee_id ?? 'system',
            'updated_by' => auth('employee')->user()->employee_id ?? 'system'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate added successfully!',
            'candidate' => $candidate,
            'readiness_data' => $readinessData
        ]);
    }

    public function runScenarioSimulation(Request $request)
    {
        $request->validate([
            'scenario_type' => 'required|string',
            'affected_positions' => 'required|array'
        ]);

        $scenario = SuccessionScenario::create([
            'scenario_name' => $request->scenario_name ?? 'Simulation ' . now()->format('Y-m-d H:i'),
            'scenario_type' => $request->scenario_type,
            'description' => $request->description ?? 'Automated scenario simulation',
            'affected_positions' => $request->affected_positions,
            'impact_level' => $request->impact_level ?? 'medium',
            'created_by' => auth('employee')->user()->employee_id ?? 'system'
        ]);

        $results = $scenario->runSimulation();
        
        return response()->json([
            'success' => true,
            'scenario' => $scenario,
            'results' => $results
        ]);
    }
}
