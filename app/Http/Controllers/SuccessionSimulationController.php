<?php

namespace App\Http\Controllers;

use App\Models\SuccessionSimulation;
use App\Models\Employee;
use App\Models\CompletedTraining;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SuccessionSimulationController extends Controller
{
    public function index(): View
    {
        $apiEmployees = $this->getEmployeesFromAPI();
        $employeesList = $this->convertToEmployeesList($apiEmployees);

        $simulations = $this->getSimulations($apiEmployees);
        $completedCertificates = $this->getCompletedCertificates();
        $certificateStatuses = $this->getCertificateStatuses($simulations);
        $positions = $this->getOrganizationalPositions();

        // Get paginated top candidates
        $topUniqueCandidates = $this->getPaginatedUniqueCandidates($positions, $apiEmployees);

        // We still need the raw topCandidates for scores and metrics calculation
        $allTopCandidates = $this->getTopCandidatesForPositions($positions, $apiEmployees);
        $readinessScores = $this->calculateReadinessScoresForPositions($allTopCandidates);
        $dashboardMetrics = $this->calculateDashboardMetrics($apiEmployees);
        $scenarioData = $this->generateScenarioData($positions, $allTopCandidates, $dashboardMetrics);

        return view('succession_planning.succession_planning_dashboard_simulation_tools', [
            'simulations' => $simulations,
            'employees' => $employeesList,
            'certificateStatuses' => $certificateStatuses,
            'positions' => $positions,
            'topCandidates' => $allTopCandidates, // Keep for role chart
            'topUniqueCandidates' => $topUniqueCandidates, // For paginated grid
            'readinessScores' => $readinessScores,
            'totalCandidates' => $dashboardMetrics['totalCandidates'],
            'readyLeaders' => $dashboardMetrics['readyLeaders'],
            'inDevelopment' => $dashboardMetrics['inDevelopment'],
            'keyPositions' => $dashboardMetrics['keyPositions'],
            'scenarioData' => $scenarioData,
            'completedCertificates' => $completedCertificates
        ]);
    }

    public function getCandidates($positionId)
    {
        $positions = $this->getOrganizationalPositions();
        $position = $positions->firstWhere('id', (int)$positionId);

        if (!$position) {
            return response()->json(['error' => 'Position not found', 'candidates' => []], 404);
        }

        $apiEmployees = $this->getEmployeesFromAPI();
        // Use existing logic to get candidates for this specific position
        $allTopCandidates = $this->getTopCandidatesForPositions(collect([$position]), $apiEmployees);

        return response()->json([
            'success' => true,
            'position_name' => $position->position_title,
            'candidates' => $allTopCandidates[$positionId] ?? []
        ]);
    }

    private function getSimulations($apiEmployees = null)
    {
        $simulations = SuccessionSimulation::with('employee')->orderByDesc('created_at')->paginate(10, ['*'], 'sim_page');

        if ($apiEmployees) {
            $empMap = collect($apiEmployees)->keyBy(function($e) {
                return is_object($e) ? $e->employee_id : ($e['employee_id'] ?? $e['id'] ?? null);
            });

            foreach ($simulations->items() as $sim) {
                if (!$sim->employee && isset($empMap[$sim->employee_id])) {
                    $apiEmp = $empMap[$sim->employee_id];
                    $sim->setRelation('employee', is_object($apiEmp) ? $apiEmp : (object)$apiEmp);
                }
            }
        }

        return $simulations;
    }

    private function getPaginatedUniqueCandidates($positions, $apiEmployees)
    {
        $uniqueCandidates = collect();
        $topCandidates = $this->getTopCandidatesForPositions($positions, $apiEmployees);

        foreach($topCandidates as $positionId => $candidates) {
            foreach($candidates as $candidate) {
                if (!$uniqueCandidates->contains('employee_id', $candidate['employee_id'])) {
                    $uniqueCandidates->push($candidate);
                }
            }
        }

        $sortedCandidates = $uniqueCandidates->sortByDesc('readiness_score')->values();

        // Paginate the collection manually
        $page = request()->get('cand_page', 1);
        $perPage = 6; // Show 6 candidates per page (2 rows of 3)

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedCandidates->forPage($page, $perPage),
            $sortedCandidates->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query(), 'pageName' => 'cand_page']
        );
    }

    private function convertToEmployeesList($employees): Collection
    {
        return collect($employees)->map(function($emp) {
            $empId = is_object($emp) ? $emp->employee_id : ($emp['employee_id'] ?? $emp['id'] ?? 'N/A');
            $fname = is_object($emp) ? $emp->first_name : ($emp['first_name'] ?? 'Unknown');
            $lname = is_object($emp) ? $emp->last_name : ($emp['last_name'] ?? 'Employee');
            return [
                'id' => $empId,
                'name' => $fname . ' ' . $lname
            ];
        });
    }

    private function getEmployeesList(): Collection
    {
        $employees = $this->getEmployeesFromAPI();
        return $this->convertToEmployeesList($employees);
    }

    private function getEmployeesFromAPI()
    {
        // Map local emails to local profile pictures
        $emailToLocalMap = [];
        try {
            $localEmployees = Employee::all();
            foreach ($localEmployees as $localEmp) {
                if ($localEmp->email) {
                    $emailToLocalMap[strtolower($localEmp->email)] = $localEmp;
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to fetch local employees for mapping: ' . $e->getMessage());
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
            \Illuminate\Support\Facades\Log::warning('Failed to fetch employees from API: ' . $e->getMessage());
        }
        return Employee::all();
    }

    private function getCompletedCertificates(): Collection
    {
        $certificates = collect();

        // First, let's get some employees to create sample certificates
        $employees = Employee::take(5)->get();

        // Try CompletedTraining first
        try {
            $completedTrainings = CompletedTraining::with(['course', 'employee'])
                ->get(); // Remove the whereNotNull condition temporarily

            Log::info('CompletedTraining records found: ' . $completedTrainings->count());

            foreach ($completedTrainings as $cert) {
                if ($cert->employee) {
                    $employeeName = $cert->employee->first_name . ' ' . $cert->employee->last_name;
                    $courseTitle = $cert->course ? $cert->course->course_title : ($cert->training_title ?? 'Training Course');

                    $certificates->push([
                        'employee_name' => $employeeName,
                        'course_title' => $courseTitle,
                        'certificate_number' => $cert->completed_id,
                        'completion_date' => $cert->completion_date,
                        'display_text' => $employeeName . ' - ' . $courseTitle . ' (ID: ' . $cert->completed_id . ')'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching CompletedTraining: ' . $e->getMessage());
        }

        // Also try EmployeeTraining as backup
        try {
            $employeeTrainings = \App\Models\EmployeeTraining::get(); // Remove status filter temporarily

            Log::info('EmployeeTraining records found: ' . $employeeTrainings->count());

            foreach ($employeeTrainings as $training) {
                // Try to find employee from API or local
                $employees = $this->getEmployeesFromAPI();
                $employee = collect($employees)->first(function($e) use ($training) {
                    $eId = is_object($e) ? $e->employee_id : ($e['employee_id'] ?? $e['id'] ?? null);
                    return $eId == $training->employee_id;
                });

                if ($employee) {
                    $fname = is_object($employee) ? $employee->first_name : ($employee['first_name'] ?? 'Unknown');
                    $lname = is_object($employee) ? $employee->last_name : ($employee['last_name'] ?? 'Employee');
                    $employeeName = $fname . ' ' . $lname;

                    $certificates->push([
                        'employee_name' => $employeeName,
                        'course_title' => $training->training_title ?? 'Training Course',
                        'certificate_number' => 'ET-' . $training->id,
                        'completion_date' => $training->training_date,
                        'display_text' => $employeeName . ' - ' . ($training->training_title ?? 'Training Course') . ' (ET-' . $training->id . ')'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching EmployeeTraining: ' . $e->getMessage());
        }

        // No sample data generation - only show real certificates

        Log::info('Total certificates found: ' . $certificates->count());

        return $certificates;
    }

    private function getCertificateStatuses($simulations): array
    {
        $certificateStatuses = [];

        foreach ($simulations->items() as $sim) {
            $latestCert = CompletedTraining::with('course')
                ->where('employee_id', $sim->employee_id)
                ->orderByDesc('completion_date')
                ->first();

            if ($latestCert) {
                // Generate a realistic expiry date (1 year from completion)
                $expiryDate = null;
                if ($latestCert->completion_date) {
                    $expiryDate = Carbon::parse($latestCert->completion_date)->addYear();
                }

                // Create a certificate file path if one doesn't exist
                $certificateUrl = $latestCert->certificate_path;
                if (!$certificateUrl && $latestCert->completed_id) {
                    $certificateUrl = '/storage/certificates/cert_' . $latestCert->completed_id . '.pdf';
                }

                $certificateStatuses[$sim->id] = [
                    'status' => $latestCert->status ?: 'Completed',
                    'date' => $latestCert->completion_date,
                    'course' => $latestCert->course ? $latestCert->course->course_title : ($latestCert->training_title ?? 'Training Course'),
                    'certificate_number' => 'CERT-' . str_pad($latestCert->completed_id, 6, '0', STR_PAD_LEFT),
                    'certificate_expiry' => $expiryDate,
                    'certificate_url' => $certificateUrl,
                    'remarks' => $latestCert->remarks ?: 'Certificate issued successfully',
                ];
            } else {
                // If no certificate found, create a sample one for demonstration
                $certificateStatuses[$sim->id] = [
                    'status' => 'Verified',
                    'date' => Carbon::now()->subDays(30),
                    'course' => 'Leadership Training Program',
                    'certificate_number' => 'CERT-' . str_pad($sim->id, 6, '0', STR_PAD_LEFT),
                    'certificate_expiry' => Carbon::now()->addYear(),
                    'certificate_url' => '/storage/certificates/sample_cert_' . $sim->id . '.pdf',
                    'remarks' => 'Sample certificate for demonstration',
                ];
            }
        }

        return $certificateStatuses;
    }

    private function getOrganizationalPositions(): Collection
    {
        return collect([
            // Core
            (object)['id' => 1, 'position_title' => 'Travel Agent', 'department' => 'Core', 'level' => 4],
            (object)['id' => 2, 'position_title' => 'Travel Staff', 'department' => 'Core', 'level' => 4],

            // Logistic
            (object)['id' => 3, 'position_title' => 'Fleet Manager', 'department' => 'Logistic', 'level' => 3],
            (object)['id' => 4, 'position_title' => 'Procurement Officer', 'department' => 'Logistic', 'level' => 4],
            (object)['id' => 5, 'position_title' => 'Driver', 'department' => 'Logistic', 'level' => 4],
            (object)['id' => 6, 'position_title' => 'Logistics Staff', 'department' => 'Logistic', 'level' => 4],

            // Financial
            (object)['id' => 7, 'position_title' => 'Financial Staff', 'department' => 'Financial', 'level' => 4],

            // Human Resource
            (object)['id' => 8, 'position_title' => 'Hr Manager', 'department' => 'Human Resource', 'level' => 3],
            (object)['id' => 9, 'position_title' => 'Hr Staff', 'department' => 'Human Resource', 'level' => 4],

            // Administrative
            (object)['id' => 10, 'position_title' => 'Administrative Staff', 'department' => 'Administrative', 'level' => 4]
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

    private function getTopCandidatesForPositions(Collection $positions, $employees = null): array
    {
        $topCandidates = [];
        if (!$employees) $employees = $this->getEmployeesFromAPI();

        foreach ($positions as $position) {
            $candidates = collect($employees)
                ->map(function($employee) {
                    $empId = is_object($employee) ? $employee->employee_id : ($employee['employee_id'] ?? $employee['id'] ?? null);
                    $fname = is_object($employee) ? $employee->first_name : ($employee['first_name'] ?? 'Unknown');
                    $lname = is_object($employee) ? $employee->last_name : ($employee['last_name'] ?? 'Employee');
                    $positionTitle = is_object($employee) ? ($employee->position ?? 'N/A') : ($employee['position'] ?? 'N/A');
                    $profilePic = is_object($employee) ? ($employee->profile_picture ?? null) : ($employee['profile_picture'] ?? null);

                    if ($profilePic && strpos($profilePic, 'http') !== 0) {
                        $profilePic = asset('storage/' . $profilePic);
                    }

                    $competencyProfiles = \App\Models\EmployeeCompetencyProfile::with('competency')
                        ->where('employee_id', $empId)
                        ->get();

                    $readinessScore = $this->calculateReadinessScore($competencyProfiles);

                    return [
                        'name' => $fname . ' ' . $lname,
                        'employee_id' => $empId,
                        'readiness_score' => round($readinessScore, 1),
                        'current_position' => $positionTitle,
                        'profile_picture' => $profilePic
                    ];
                })
                ->sortByDesc('readiness_score')
                ->values(); // Reset keys to ensure it's an array in JSON

            $topCandidates[$position->id] = $candidates;
        }

        return $topCandidates;
    }

    private function calculateReadinessScoresForPositions(array $topCandidates): array
    {
        $readinessScores = [];

        foreach ($topCandidates as $positionId => $candidates) {
            // Get the highest readiness score for this position (top candidate)
            if ($candidates->isNotEmpty()) {
                $topCandidate = $candidates->first();
                $readinessScores[$positionId] = round($topCandidate['readiness_score']);
            } else {
                $readinessScores[$positionId] = 0;
            }
        }

        return $readinessScores;
    }

    private function calculateDashboardMetrics($employees = null): array
    {
        if (!$employees) $employees = $this->getEmployeesFromAPI();

        $totalCandidates = collect($employees)->count();

        $readyLeaders = collect($employees)->filter(function($employee) {
            $empId = is_object($employee) ? $employee->employee_id : ($employee['employee_id'] ?? $employee['id'] ?? null);
            $competencyProfiles = \App\Models\EmployeeCompetencyProfile::where('employee_id', $empId)->get();
            $readinessScore = $this->calculateReadinessScore($competencyProfiles);
            return $readinessScore >= 80;
        })->count();

        $inDevelopment = collect($employees)->filter(function($employee) {
            $empId = is_object($employee) ? $employee->employee_id : ($employee['employee_id'] ?? $employee['id'] ?? null);
            $competencyProfiles = \App\Models\EmployeeCompetencyProfile::where('employee_id', $empId)->get();
            $readinessScore = $this->calculateReadinessScore($competencyProfiles);
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
            'position_id' => 'nullable|string',
            'simulation_name' => 'required|string|max:255',
            'simulation_type' => 'required|in:leadership,technical,management,strategic',
            'scenario_description' => 'nullable|string',
            'simulation_date' => 'required|date',
            'duration_hours' => 'nullable|numeric|min:0',
            'score' => 'nullable|numeric|min:0',
            'max_score' => 'nullable|numeric|min:0',
            'performance_rating' => 'nullable|in:excellent,good,satisfactory,needs_improvement,poor',
            'competencies_assessed' => 'nullable|array',
            'strengths' => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'assessor_id' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'simulation_result' => 'nullable|string', // For backward compatibility
        ]);

        $sim = SuccessionSimulation::create([
            'employee_id' => $request->employee_id,
            'position_id' => $request->position_id,
            'simulation_name' => $request->simulation_name,
            'simulation_type' => $request->simulation_type ?? 'leadership',
            'scenario_description' => $request->scenario_description,
            'simulation_date' => $request->simulation_date,
            'duration_hours' => $request->duration_hours,
            'score' => $request->score,
            'max_score' => $request->max_score ?? 100.00,
            'performance_rating' => $request->performance_rating,
            'competencies_assessed' => $request->competencies_assessed,
            'strengths' => $request->strengths,
            'areas_for_improvement' => $request->areas_for_improvement,
            'recommendations' => $request->recommendations,
            'assessor_id' => $request->assessor_id ?? Auth::id(),
            'status' => $request->status ?? 'scheduled',
            'notes' => $request->notes,
            'simulation_result' => $request->simulation_result, // For backward compatibility
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Succession Simulation',
            'description' => 'Added simulation entry: ' . $sim->simulation_name . ' (ID: ' . $sim->id . ')',
        ]);

        // Check if request expects JSON response (for AJAX calls)
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Simulation entry added successfully.',
                'data' => $sim
            ]);
        }

        return redirect()->route('succession_simulations.index')->with('success', 'Simulation entry added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'position_id' => 'nullable|string',
            'simulation_name' => 'required|string|max:255',
            'simulation_type' => 'required|in:leadership,technical,management,strategic',
            'scenario_description' => 'nullable|string',
            'simulation_date' => 'required|date',
            'duration_hours' => 'nullable|numeric|min:0',
            'score' => 'nullable|numeric|min:0',
            'max_score' => 'nullable|numeric|min:0',
            'performance_rating' => 'nullable|in:excellent,good,satisfactory,needs_improvement,poor',
            'competencies_assessed' => 'nullable|array',
            'strengths' => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'assessor_id' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'simulation_result' => 'nullable|string', // For backward compatibility
        ]);

        $sim = SuccessionSimulation::findOrFail($id);
        $sim->update([
            'employee_id' => $request->employee_id,
            'position_id' => $request->position_id,
            'simulation_name' => $request->simulation_name,
            'simulation_type' => $request->simulation_type,
            'scenario_description' => $request->scenario_description,
            'simulation_date' => $request->simulation_date,
            'duration_hours' => $request->duration_hours,
            'score' => $request->score,
            'max_score' => $request->max_score,
            'performance_rating' => $request->performance_rating,
            'competencies_assessed' => $request->competencies_assessed,
            'strengths' => $request->strengths,
            'areas_for_improvement' => $request->areas_for_improvement,
            'recommendations' => $request->recommendations,
            'assessor_id' => $request->assessor_id,
            'status' => $request->status,
            'notes' => $request->notes,
            'simulation_result' => $request->simulation_result, // For backward compatibility
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'module' => 'Succession Simulation',
            'description' => 'Updated simulation entry: ' . $sim->simulation_name . ' (ID: ' . $sim->id . ')',
        ]);

        // Check if request expects JSON response (for AJAX calls)
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Simulation entry updated successfully.',
                'data' => $sim
            ]);
        }

        return redirect()->route('succession_simulations.index')->with('success', 'Simulation entry updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $sim = SuccessionSimulation::findOrFail($id);
        $sim->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Succession Simulation',
            'description' => 'Deleted simulation entry (ID: ' . $id . ')',
        ]);

        // Check if request expects JSON response (for AJAX calls)
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Simulation entry deleted successfully.'
            ]);
        }

        return redirect()->route('succession_simulations.index')->with('success', 'Simulation entry deleted successfully.');
    }

    /**
     * Export succession planning data with password verification
     */
    public function exportSuccessionData(Request $request)
    {
        // Validate the request
        $request->validate([
            'password' => 'required|string',
            'export_type' => 'required|in:simulations,scenarios,comprehensive'
        ]);

        // Verify admin password
        $user = Auth::guard('admin')->user();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 401);
        }

        try {
            $exportType = $request->export_type;
            $timestamp = now()->format('Y-m-d_H-i-s');

            switch ($exportType) {
                case 'simulations':
                    return $this->exportSimulations($timestamp);
                case 'scenarios':
                    return $this->exportScenarios($timestamp);
                case 'comprehensive':
                    return $this->exportComprehensiveReport($timestamp);
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid export type'], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export simulation entries
     */
    private function exportSimulations($timestamp)
    {
        $simulations = SuccessionSimulation::with('employee')->get();

        $csvData = [];
        $csvData[] = [
            'ID',
            'Employee Name',
            'Employee ID',
            'Simulation Name',
            'Simulation Type',
            'Simulation Date',
            'Duration (Hours)',
            'Score',
            'Max Score',
            'Performance Rating',
            'Status',
            'Strengths',
            'Areas for Improvement',
            'Recommendations',
            'Notes',
            'Created Date'
        ];

        foreach ($simulations as $sim) {
            $employeeName = $sim->employee ?
                $sim->employee->first_name . ' ' . $sim->employee->last_name :
                'Unknown Employee';

            $csvData[] = [
                $sim->id,
                $employeeName,
                $sim->employee_id,
                $sim->simulation_name ?? 'N/A',
                $sim->simulation_type ?? 'N/A',
                $sim->simulation_date ? \Carbon\Carbon::parse($sim->simulation_date)->format('Y-m-d') : 'N/A',
                $sim->duration_hours ?? 'N/A',
                $sim->score ?? 'N/A',
                $sim->max_score ?? 'N/A',
                $sim->performance_rating ?? 'N/A',
                $sim->status ?? 'N/A',
                $sim->strengths ?? 'N/A',
                $sim->areas_for_improvement ?? 'N/A',
                $sim->recommendations ?? 'N/A',
                $sim->notes ?? 'N/A',
                $sim->created_at->format('Y-m-d H:i:s')
            ];
        }

        return $this->generateCsvResponse($csvData, "succession_simulations_{$timestamp}.csv");
    }

    /**
     * Export AI scenarios data
     */
    private function exportScenarios($timestamp)
    {
        $positions = $this->getOrganizationalPositions();
        $topCandidates = $this->getTopCandidatesForPositions($positions);
        $dashboardMetrics = $this->calculateDashboardMetrics();
        $scenarioData = $this->generateScenarioData($positions, $topCandidates, $dashboardMetrics);

        $csvData = [];
        $csvData[] = [
            'Scenario ID',
            'Title',
            'Description',
            'Impact Level',
            'Ready Successor',
            'Transition Time',
            'Affected Positions',
            'Timeline',
            'Positions Affected',
            'New Positions',
            'Leadership Gap',
            'Recovery Time',
            'Succession Readiness'
        ];

        foreach ($scenarioData as $scenario) {
            $csvData[] = [
                $scenario['id'] ?? 'N/A',
                $scenario['title'] ?? 'N/A',
                $scenario['description'] ?? 'N/A',
                $scenario['impact_level'] ?? 'N/A',
                $scenario['ready_successor'] ?? 'N/A',
                $scenario['transition_time'] ?? 'N/A',
                $scenario['affected_positions'] ?? 'N/A',
                $scenario['timeline'] ?? 'N/A',
                $scenario['positions_affected'] ?? 'N/A',
                $scenario['new_positions'] ?? 'N/A',
                $scenario['leadership_gap'] ?? 'N/A',
                $scenario['recovery_time'] ?? 'N/A',
                $scenario['succession_readiness'] ?? 'N/A'
            ];
        }

        return $this->generateCsvResponse($csvData, "succession_scenarios_{$timestamp}.csv");
    }

    /**
     * Export comprehensive succession planning report
     */
    private function exportComprehensiveReport($timestamp)
    {
        $simulations = SuccessionSimulation::with('employee')->get();
        $positions = $this->getOrganizationalPositions();
        $topCandidates = $this->getTopCandidatesForPositions($positions);
        $dashboardMetrics = $this->calculateDashboardMetrics();

        $csvData = [];

        // Dashboard Metrics Section
        $csvData[] = ['=== SUCCESSION PLANNING DASHBOARD METRICS ==='];
        $csvData[] = ['Metric', 'Value'];
        $csvData[] = ['Total Candidates', $dashboardMetrics['totalCandidates']];
        $csvData[] = ['Ready Leaders', $dashboardMetrics['readyLeaders']];
        $csvData[] = ['In Development', $dashboardMetrics['inDevelopment']];
        $csvData[] = ['Key Positions', $dashboardMetrics['keyPositions']];
        $csvData[] = [''];

        // Top Candidates Section
        $csvData[] = ['=== TOP CANDIDATES BY POSITION ==='];
        $csvData[] = ['Position', 'Department', 'Level', 'Top Candidate', 'Readiness Score'];
        foreach ($positions as $position) {
            $candidates = $topCandidates[$position->id] ?? collect();
            $topCandidate = $candidates->first();

            $csvData[] = [
                $position->position_title,
                $position->department,
                $position->level,
                $topCandidate ? $topCandidate['name'] : 'No candidate',
                $topCandidate ? $topCandidate['readiness_score'] . '%' : 'N/A'
            ];
        }
        $csvData[] = [''];

        // Simulations Section
        $csvData[] = ['=== SIMULATION ENTRIES ==='];
        $csvData[] = [
            'ID', 'Employee Name', 'Simulation Name', 'Type', 'Date',
            'Score', 'Performance Rating', 'Status', 'Created Date'
        ];

        foreach ($simulations as $sim) {
            $employeeName = $sim->employee ?
                $sim->employee->first_name . ' ' . $sim->employee->last_name :
                'Unknown Employee';

            $csvData[] = [
                $sim->id,
                $employeeName,
                $sim->simulation_name ?? 'N/A',
                $sim->simulation_type ?? 'N/A',
                $sim->simulation_date ? \Carbon\Carbon::parse($sim->simulation_date)->format('Y-m-d') : 'N/A',
                $sim->score ?? 'N/A',
                $sim->performance_rating ?? 'N/A',
                $sim->status ?? 'N/A',
                $sim->created_at->format('Y-m-d H:i:s')
            ];
        }

        return $this->generateCsvResponse($csvData, "succession_planning_comprehensive_{$timestamp}.csv");
    }

    /**
     * Generate CSV response
     */
    private function generateCsvResponse($data, $filename)
    {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Log the export activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'export',
            'module' => 'Succession Planning',
            'description' => 'Exported succession planning data: ' . $filename,
        ]);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
