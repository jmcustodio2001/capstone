<?php

namespace App\Http\Controllers;

use App\Models\SuccessionReadinessRating;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SuccessionReadinessRatingController extends Controller
{
    /**
     * Recalculate readiness scores for all employees (Score Refresh).
     */
    public function refresh(Request $request)
    {
        // Fetch employees from API with local fallback
        $employees = $this->getEmployeesFromAPI();

        $count = 0;
        foreach ($employees as $employee) {
            $empId = is_object($employee) ? $employee->employee_id : $employee['employee_id'];
            
            // Pass employee data directly to avoid redundant lookups
            $score = $this->calculateEmployeeReadinessScore($empId, $employee);
            $assessmentDate = now()->format('Y-m-d');
            
            $rating = \App\Models\SuccessionReadinessRating::updateOrCreate(
                ['employee_id' => $empId],
                [
                    'readiness_score' => $score,
                    'readiness_level' => match(true) {
                        $score >= 80 => 'Ready Now',
                        $score >= 60 => 'Ready Soon',
                        default => 'Needs Development'
                    },
                    'assessment_date' => $assessmentDate,
                ]
            );
            $count++;
        }
        
        // Log activity
        \App\Models\ActivityLog::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'action' => 'refresh',
            'module' => 'Succession Readiness Rating',
            'description' => 'Refreshed readiness scores for ' . $count . ' employees.',
        ]);
        
        return redirect()->back()->with('success', 'Readiness scores recalculated for all employees.');
    }

    /**
     * Calculate readiness score based on: Hire Date (10%), Training Records (3%), and Competency Profiles (additive)
     */
    public function calculateEmployeeReadinessScore($employeeId, $employeeData = null)
    {
        // Get employee data for tenure calculation (API supported)
        $employee = $employeeData;
        
        if (!$employee) {
            $employee = \App\Models\Employee::where('employee_id', $employeeId)->first();
        }
        
        // 1. HIRE DATE COMPONENT (10%)
        $hireDateScore = 0;
        $yearsOfService = 0;
        
        $hireDate = null;
        if ($employee) {
            if (is_object($employee)) {
                $hireDate = $employee->hire_date ?? $employee->date_hired ?? null;
            } else {
                $hireDate = $employee['hire_date'] ?? $employee['date_hired'] ?? null;
            }
        }

        if ($hireDate) {
            $hireCarbon = \Carbon\Carbon::parse($hireDate);
            // Use whole years: < 1 year = 0, 1+ years = integer count
            $yearsOfService = max(0, $hireCarbon->diffInYears(now()));
            $hireDateScore = min(10, $yearsOfService * 1);
        }
        
        // 2. TRAINING RECORDS COMPONENT (3%)
        $trainingRecordsScore = 0;
        try {
            $certificates = \App\Models\TrainingRecordCertificateTracking::where('employee_id', $employeeId)->count();
            $trainingRecordsScore = min(3, $certificates * 0.5);
        } catch (\Exception $e) {
            $trainingRecordsScore = 0;
        }

        // 3. EMPLOYEE COMPETENCY PROFILES COMPONENT (Additive based on proficiency level)
        $competencyScore = 0;
        $competencyProfiles = \App\Models\EmployeeCompetencyProfile::where('employee_id', $employeeId)->get();
            
        foreach ($competencyProfiles as $profile) {
            $proficiencyLevel = (int)$profile->proficiency_level;
            $competencyScore += $proficiencyLevel * 2; 
        }

        $totalScore = $hireDateScore + $trainingRecordsScore + $competencyScore;
        $minimumScore = $yearsOfService < 1 ? 5 : 15;
        $finalScore = max($minimumScore, min(100, $totalScore));
        
        return $finalScore;
    }

    public function edit($id)
    {
        $rating = SuccessionReadinessRating::findOrFail($id);
        $employees = $this->getEmployeesFromAPI();
        $ratings = SuccessionReadinessRating::latest()->paginate(10);
        $this->mapEmployeesToRatings($ratings, $employees);
        $editMode = true;
        return view('succession_planning.succession_readiness_rating', compact('rating', 'employees', 'ratings', 'editMode'));
    }

    public function show($id)
    {
        $rating = SuccessionReadinessRating::findOrFail($id);
        $employees = $this->getEmployeesFromAPI();
        $ratings = SuccessionReadinessRating::latest()->paginate(10);
        $this->mapEmployeesToRatings($ratings, $employees);
        $showMode = true;
        return view('succession_planning.succession_readiness_rating', compact('rating', 'employees', 'ratings', 'showMode'));
    }

    public function index()
    {
        $ratings = SuccessionReadinessRating::latest()->paginate(10);
        $employees = $this->getEmployeesFromAPI();
        $this->mapEmployeesToRatings($ratings, $employees);
        return view('succession_planning.succession_readiness_rating', compact('ratings', 'employees'));
    }

    private function getEmployeesFromAPI()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            $apiEmployees = $response->successful() ? $response->json() : [];
            
            if (isset($apiEmployees['data']) && is_array($apiEmployees['data'])) {
                $apiEmployees = $apiEmployees['data'];
            }

            if (is_array($apiEmployees) && !empty($apiEmployees)) {
                return collect($apiEmployees)->map(function($emp) {
                    return (object) [
                        'employee_id' => $emp['employee_id'] ?? $emp['id'] ?? $emp['external_employee_id'] ?? 'N/A',
                        'first_name' => $emp['first_name'] ?? 'Unknown',
                        'last_name' => $emp['last_name'] ?? 'Employee',
                        'position' => $emp['role'] ?? $emp['position'] ?? 'N/A',
                        'profile_picture' => $emp['profile_picture'] ?? null,
                        'hire_date' => $emp['date_hired'] ?? $emp['hire_date'] ?? null
                    ];
                });
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to fetch employees from API: ' . $e->getMessage());
        }
        return Employee::all();
    }

    private function mapEmployeesToRatings($ratings, $employees)
    {
        $employeeMap = [];
        foreach ($employees as $employee) {
            $empId = is_object($employee) ? $employee->employee_id : $employee['employee_id'];
            $employeeMap[$empId] = $employee;
        }

        foreach ($ratings as $rating) {
            if (isset($employeeMap[$rating->employee_id])) {
                $apiEmployee = $employeeMap[$rating->employee_id];
                $employeeObj = new \stdClass();
                $employeeObj->employee_id = $rating->employee_id;
                $employeeObj->first_name = is_object($apiEmployee) ? $apiEmployee->first_name : ($apiEmployee['first_name'] ?? 'Unknown');
                $employeeObj->last_name = is_object($apiEmployee) ? $apiEmployee->last_name : ($apiEmployee['last_name'] ?? 'Employee');
                $employeeObj->profile_picture = is_object($apiEmployee) ? ($apiEmployee->profile_picture ?? null) : ($apiEmployee['profile_picture'] ?? null);
                
                $rating->setRelation('employee', $employeeObj);
            }
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required',
                'readiness_level' => 'required|string|in:Ready Now,Ready Soon,Needs Development',
                'assessment_date' => 'required|date',
            ]);
            
            $readinessScore = match($request->readiness_level) {
                'Ready Now' => 90,
                'Ready Soon' => 75,
                'Needs Development' => 50,
                default => 50
            };
            
            $rating = SuccessionReadinessRating::create([
                'employee_id' => $request->employee_id,
                'readiness_score' => $readinessScore,
                'readiness_level' => $request->readiness_level,
                'assessment_date' => $request->assessment_date,
            ]);
            
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'module' => 'Succession Readiness Rating',
                'description' => 'Added readiness rating (ID: ' . $rating->id . ')',
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Succession readiness rating added successfully.', 'rating' => $rating]);
            }
            return redirect()->route('succession_readiness_ratings.index')->with('success', 'Readiness rating added successfully.');
        } catch (\Exception $e) {
            Log::error('Error storing succession readiness rating: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while saving the rating.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while saving the rating.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rating = SuccessionReadinessRating::findOrFail($id);
            $request->validate([
                'employee_id' => 'required',
                'readiness_level' => 'required|string|in:Ready Now,Ready Soon,Needs Development',
                'assessment_date' => 'required|date',
            ]);
            
            $readinessScore = match($request->readiness_level) {
                'Ready Now' => 90,
                'Ready Soon' => 75,
                'Needs Development' => 50,
                default => 50
            };
            
            $rating->update([
                'employee_id' => $request->employee_id,
                'readiness_score' => $readinessScore,
                'readiness_level' => $request->readiness_level,
                'assessment_date' => $request->assessment_date,
            ]);
            
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'module' => 'Succession Readiness Rating',
                'description' => 'Updated readiness rating (ID: ' . $rating->id . ')',
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Succession readiness rating updated successfully.', 'rating' => $rating]);
            }
            return redirect()->route('succession_readiness_ratings.index')->with('success', 'Readiness rating updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating succession readiness rating: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while updating the rating.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while updating the rating.');
        }
    }

    public function destroy($id)
    {
        $rating = SuccessionReadinessRating::findOrFail($id);
        $rating->delete();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Succession Readiness Rating',
            'description' => 'Deleted readiness rating (ID: ' . $rating->id . ')',
        ]);
        return redirect()->route('succession_readiness_ratings.index')->with('success', 'Readiness rating deleted successfully.');
    }

    public function getCompetencyData($employeeId)
    {
        try {
            $employee = null;
            $employees = $this->getEmployeesFromAPI();
            foreach ($employees as $emp) {
                if ($emp->employee_id == $employeeId) {
                    $employee = $emp;
                    break;
                }
            }

            if (!$employee) {
                $employee = Employee::where('employee_id', $employeeId)->first();
            }
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found', 'has_data' => false], 200);
            }
            
            $competencyProfiles = \App\Models\EmployeeCompetencyProfile::with('competency')->where('employee_id', $employeeId)->get();
            $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)->get();
            
            $avgProficiency = $competencyProfiles->avg('proficiency_level') ?? 0;
            $totalCompetencies = $competencyProfiles->count();
            
            $strongCompetencies = $competencyProfiles->filter(fn($p) => (int)$p->proficiency_level >= 4)->pluck('competency.competency_name')->take(4);
            $developmentCompetencies = $competencyProfiles->filter(fn($p) => (int)$p->proficiency_level <= 2)->pluck('competency.competency_name')->take(3);

            return response()->json([
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_id' => $employeeId,
                'years_of_service' => max(0, $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->diffInYears(now()) : 0),
                'has_data' => true,
                'avg_proficiency_level' => round($avgProficiency, 1),
                'total_competencies_assessed' => $totalCompetencies,
                'training_progress' => round($trainingRecords->avg('progress') ?? 0, 1),
                'calculated_readiness_score' => $this->calculateEmployeeReadinessScore($employeeId, $employee),
                'strong_competencies' => $strongCompetencies->toArray(),
                'development_competencies' => $developmentCompetencies->toArray(),
                'all_competencies' => $competencyProfiles->map(fn($p) => ['name' => $p->competency->competency_name, 'level' => $p->proficiency_level])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getBatchAnalysis(Request $request)
    {
        try {
            $employees = $this->getEmployeesFromAPI();
            $analysisData = $employees->map(function ($employee) {
                $empId = is_object($employee) ? $employee->employee_id : $employee['employee_id'];
                $rating = SuccessionReadinessRating::where('employee_id', $empId)->first();
                $score = $rating ? $rating->readiness_score : $this->calculateEmployeeReadinessScore($empId, $employee);
                
                return [
                    'name' => is_object($employee) ? ($employee->first_name . ' ' . $employee->last_name) : 'Unknown',
                    'id' => $empId,
                    'readiness' => $score,
                    'level' => match(true) { $score >= 80 => 'Ready Now', $score >= 60 => 'Ready Soon', default => 'Needs Development' }
                ];
            });

            return response()->json(['success' => true, 'data' => ['results' => $analysisData->values(), 'summary' => ['total' => $analysisData->count()]]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAIInsights(Request $request)
    {
        try {
            $employees = $this->getEmployeesFromAPI();
            $scores = $employees->map(fn($e) => $this->calculateEmployeeReadinessScore($e->employee_id, $e));
            
            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => [
                        'totalEmployees' => $employees->count(),
                        'avgReadiness' => round($scores->avg())
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}