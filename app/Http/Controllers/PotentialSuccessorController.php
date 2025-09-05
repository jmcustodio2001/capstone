<?php

namespace App\Http\Controllers;

use App\Models\PotentialSuccessor;
use App\Models\Employee;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyLibrary;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PotentialSuccessorController extends Controller
{
    // Removed duplicate index() method. Only the eager loading version remains below.
        public function index()
        {
            // Eager load employee and their competency profiles and competencies
            $successors = PotentialSuccessor::with(['employee.competencyProfiles.competency'])->latest()->paginate(10);
            $employees = Employee::all();
            return view('succession_planning.potential_successor', compact('successors', 'employees'));
        }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'potential_role' => 'required|string|max:255',
            'identified_date' => 'required|date',
        ]);
        $successor = PotentialSuccessor::create($request->only(['employee_id', 'potential_role', 'identified_date']));
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Potential Successor',
            'description' => 'Added potential successor (ID: ' . $successor->id . ')',
        ]);
        return redirect()->route('potential_successors.index')->with('success', 'Potential successor added successfully.');
    }

    public function destroy($id)
    {
        $successor = PotentialSuccessor::findOrFail($id);
        $successor->delete();
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Potential Successor',
            'description' => 'Deleted potential successor (ID: ' . $successor->id . ')',
        ]);
        return redirect()->route('potential_successors.index')->with('success', 'Potential successor deleted successfully.');
    }
    public function show($id)
    {
        $successor = PotentialSuccessor::with('employee')->findOrFail($id);
        $employees = Employee::all();
        $successors = PotentialSuccessor::with('employee')->latest()->paginate(10);
        return view('succession_planning.potential_successor', compact('successors', 'employees', 'successor'))->with('showMode', true);
    }

    public function edit($id)
    {
        $successor = PotentialSuccessor::with('employee')->findOrFail($id);
        $employees = Employee::all();
        $successors = PotentialSuccessor::with('employee')->latest()->paginate(10);
        return view('succession_planning.potential_successor', compact('successors', 'employees', 'successor'))->with('editMode', true);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'potential_role' => 'required|string|max:255',
            'identified_date' => 'required|date',
        ]);
        $successor = PotentialSuccessor::with('employee')->findOrFail($id);
        $successor->update($request->only(['employee_id', 'potential_role', 'identified_date']));
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'module' => 'Potential Successor',
            'description' => 'Updated potential successor (ID: ' . $successor->id . ')',
        ]);
        return redirect()->route('potential_successors.index')->with('success', 'Potential successor updated successfully.');
    }

    /**
     * Get AI successor suggestions based on real competency profile data
     */
    public function getAISuccessorSuggestions(Request $request)
    {
        $targetRole = $request->input('target_role');
        $readinessFilter = $request->input('readiness_filter');
        $departmentFilter = $request->input('department_filter');

        // Get all employees with their competency profiles
        $employees = Employee::with(['competencyProfiles.competency'])->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'error' => 'No employees found',
                'message' => 'No employee data available for analysis.'
            ], 404);
        }

        // Define role-specific competency requirements
        $roleRequirements = $this->getRoleRequirements($targetRole);

        $suggestions = [];
        foreach ($employees as $employee) {
            $competencyProfiles = $employee->competencyProfiles;
            
            if ($competencyProfiles->isEmpty()) {
                continue; // Skip employees without competency data
            }

            // Calculate suitability score based on real competency data
            $analysis = $this->analyzeEmployeeForRole($employee, $competencyProfiles, $roleRequirements);
            
            // Apply filters
            if ($readinessFilter && $analysis['readinessLevel'] !== $readinessFilter) {
                continue;
            }

            $suggestions[] = $analysis;
        }

        // Sort by suitability score (highest first)
        usort($suggestions, function($a, $b) {
            return $b['suitabilityScore'] - $a['suitabilityScore'];
        });

        // Limit to top 10 suggestions
        $suggestions = array_slice($suggestions, 0, 10);

        return response()->json([
            'success' => true,
            'data' => [
                'suggestions' => $suggestions,
                'targetRole' => $targetRole,
                'totalCandidates' => count($suggestions),
                'dataSource' => 'competency_profiles'
            ]
        ]);
    }

    private function getRoleRequirements($role)
    {
        $requirements = [
            'Travel Consultant' => [
                'required' => ['Communication', 'Customer Service', 'Technical'],
                'preferred' => ['Sales', 'Problem Solving'],
                'leadership_weight' => 0.2,
                'technical_weight' => 0.3,
                'communication_weight' => 0.3,
                'customer_service_weight' => 0.2
            ],
            'Tour Guide' => [
                'required' => ['Communication', 'Leadership', 'Customer Service'],
                'preferred' => ['Cultural Knowledge', 'Public Speaking'],
                'leadership_weight' => 0.4,
                'communication_weight' => 0.3,
                'customer_service_weight' => 0.3
            ],
            'Travel Operations Manager' => [
                'required' => ['Management', 'Leadership', 'Strategic'],
                'preferred' => ['Technical', 'Analytical'],
                'leadership_weight' => 0.4,
                'management_weight' => 0.3,
                'strategic_weight' => 0.3
            ],
            'Travel Sales Executive' => [
                'required' => ['Sales', 'Communication', 'Customer Service'],
                'preferred' => ['Negotiation', 'Relationship Building'],
                'communication_weight' => 0.3,
                'customer_service_weight' => 0.4,
                'sales_weight' => 0.3
            ],
            'Tourism Marketing Manager' => [
                'required' => ['Marketing', 'Creative', 'Strategic'],
                'preferred' => ['Digital Marketing', 'Brand Management'],
                'creative_weight' => 0.3,
                'strategic_weight' => 0.4,
                'marketing_weight' => 0.3
            ]
        ];

        return $requirements[$role] ?? [
            'required' => ['Communication', 'Leadership'],
            'preferred' => ['Technical', 'Problem Solving'],
            'leadership_weight' => 0.3,
            'communication_weight' => 0.3,
            'general_weight' => 0.4
        ];
    }

    private function analyzeEmployeeForRole($employee, $competencyProfiles, $roleRequirements)
    {
        // Calculate average proficiency using same conversion as AI readiness analysis
        $proficiencyLevels = $competencyProfiles->pluck('proficiency_level')->map(function($level) {
            // Convert proficiency level to numeric (same as AI analysis)
            return match(strtolower($level)) {
                'beginner', '1' => 1,
                'developing', '2' => 2,
                'proficient', '3' => 3,
                'advanced', '4' => 4,
                'expert', '5' => 5,
                default => is_numeric($level) ? (int)$level : 3 // default to proficient
            };
        });
        
        $avgProficiency = $proficiencyLevels->avg();
        $totalCompetencies = $competencyProfiles->count();

        // Count leadership competencies using comprehensive detection
        $leadershipCompetencies = $competencyProfiles->filter(function($profile) {
            $competencyName = strtolower($profile->competency->competency_name ?? '');
            $category = strtolower($profile->competency->category ?? '');
            
            // Enhanced leadership detection (same logic as profile modal)
            $leadershipCategories = ['leadership', 'management', 'strategic thinking', 'team leadership', 'executive skills'];
            $leadershipKeywords = ['leadership', 'management', 'strategic', 'decision making', 'team building', 
                                 'communication', 'delegation', 'coaching', 'mentoring', 'vision', 'planning'];
            
            // Check category match
            foreach ($leadershipCategories as $leadershipCat) {
                if (stripos($category, $leadershipCat) !== false) return true;
            }
            
            // Check keyword match in competency name
            foreach ($leadershipKeywords as $keyword) {
                if (stripos($competencyName, $keyword) !== false) return true;
            }
            
            return false;
        })->count();

        $communicationCompetencies = $competencyProfiles->filter(function($profile) {
            return in_array($profile->competency->category, ['Communication', 'Behavioral']);
        })->count();

        $technicalCompetencies = $competencyProfiles->filter(function($profile) {
            return in_array($profile->competency->category, ['Technical', 'Analytical']);
        })->count();

        // Calculate role-specific suitability score
        $suitabilityScore = $this->calculateRoleSuitability(
            $avgProficiency, 
            $leadershipCompetencies, 
            $communicationCompetencies,
            $technicalCompetencies,
            $totalCompetencies,
            $roleRequirements
        );

        // Determine readiness level
        $readinessLevel = $suitabilityScore >= 85 ? 'Ready Now' : 
                         ($suitabilityScore >= 70 ? 'Ready Soon' : 'Needs Development');

        // Get top strengths and development areas
        $strengths = $competencyProfiles->sortByDesc('proficiency_level')
            ->take(3)
            ->pluck('competency.competency_name')
            ->toArray();

        $developmentAreas = $competencyProfiles->sortBy('proficiency_level')
            ->take(2)
            ->pluck('competency.competency_name')
            ->toArray();

        return [
            'employeeId' => $employee->employee_id,
            'employeeName' => $employee->first_name . ' ' . $employee->last_name,
            'suitabilityScore' => round($suitabilityScore),
            'readinessLevel' => $readinessLevel,
            'avgProficiency' => round($avgProficiency, 1),
            'totalCompetencies' => $totalCompetencies,
            'leadershipCompetencies' => $leadershipCompetencies,
            'strengths' => $strengths,
            'developmentAreas' => $developmentAreas,
            'recommendation' => $this->getSuccessorRecommendation($readinessLevel, $suitabilityScore),
            'confidenceLevel' => $totalCompetencies >= 10 ? 'High' : ($totalCompetencies >= 5 ? 'Medium' : 'Low')
        ];
    }

    private function calculateRoleSuitability($avgProficiency, $leadership, $communication, $technical, $total, $requirements)
    {
        // ULTRA-CONSERVATIVE algorithm to significantly lower suitability scores
        $proficiencyScore = min(20, ($avgProficiency / 5) * 20); // Max 20% (was 100%)
        $leadershipScore = min(12, $leadership * 2.4); // Max 12% (was 100%), requires 5+ leadership competencies
        $totalCompetenciesScore = min(8, ($total / 75) * 8); // Max 8% (was 100%), requires 75+ competencies
        
        // Ultra-conservative weighted scoring: 70% proficiency + 20% leadership + 10% total competencies
        $readinessScore = ($proficiencyScore * 0.7) + ($leadershipScore * 0.2) + ($totalCompetenciesScore * 0.1);

        return $readinessScore;
    }

    private function getSuccessorRecommendation($readinessLevel, $score)
    {
        switch($readinessLevel) {
            case 'Ready Now':
                return 'Excellent candidate for immediate succession. Strong competency profile.';
            case 'Ready Soon':
                return 'Good potential successor. Provide targeted development for 3-6 months.';
            case 'Needs Development':
                return 'Requires significant development. Focus on competency building for 6-12 months.';
            default:
                return 'Assessment needed to determine succession readiness.';
        }
    }

    /**
     * Get predictive analytics for succession planning
     */
    public function getPredictiveAnalytics(Request $request)
    {
        $targetRole = $request->input('target_role');
        
        // Get employees with competency and training data
        $employees = Employee::with(['competencyProfiles.competency'])->get();
        $trainingData = \App\Models\EmployeeTrainingDashboard::with('employee', 'course')->get();
        
        $analytics = [
            'targetRole' => $targetRole,
            'totalCandidates' => $employees->count(),
            'readyNow' => 0,
            'readySoon' => 0,
            'needsDevelopment' => 0,
            'avgCompetencyScore' => 0,
            'topCompetencyGaps' => [],
            'trainingRecommendations' => [],
            'riskAssessment' => [],
            'timeline' => []
        ];
        
        $totalScore = 0;
        $competencyGaps = [];
        
        foreach ($employees as $employee) {
            if ($employee->competencyProfiles->isEmpty()) continue;
            
            $roleRequirements = $this->getRoleRequirements($targetRole);
            $analysis = $this->analyzeEmployeeForRole($employee, $employee->competencyProfiles, $roleRequirements);
            
            $totalScore += $analysis['suitabilityScore'];
            
            // Count readiness levels
            switch($analysis['readinessLevel']) {
                case 'Ready Now': $analytics['readyNow']++; break;
                case 'Ready Soon': $analytics['readySoon']++; break;
                case 'Needs Development': $analytics['needsDevelopment']++; break;
            }
            
            // Collect competency gaps
            foreach($analysis['developmentAreas'] as $area) {
                $competencyGaps[$area] = ($competencyGaps[$area] ?? 0) + 1;
            }
        }
        
        $analytics['avgCompetencyScore'] = $employees->count() > 0 ? round($totalScore / $employees->count()) : 0;
        
        // Top 5 competency gaps
        arsort($competencyGaps);
        $analytics['topCompetencyGaps'] = array_slice($competencyGaps, 0, 5, true);
        
        // Generate training recommendations based on gaps
        $analytics['trainingRecommendations'] = $this->generateTrainingRecommendations(array_keys($analytics['topCompetencyGaps']));
        
        // Risk assessment
        $analytics['riskAssessment'] = [
            'high' => $analytics['readyNow'] < 2 ? 'Insufficient ready successors' : null,
            'medium' => $analytics['readySoon'] < 3 ? 'Limited pipeline depth' : null,
            'low' => $analytics['avgCompetencyScore'] < 60 ? 'Overall competency levels need improvement' : null
        ];
        
        // Timeline projections
        $analytics['timeline'] = [
            'immediate' => $analytics['readyNow'],
            '3_months' => round($analytics['readySoon'] * 0.3),
            '6_months' => round($analytics['readySoon'] * 0.7),
            '12_months' => $analytics['needsDevelopment']
        ];
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    /**
     * Generate personalized development paths
     */
    public function getDevelopmentPaths(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $targetRole = $request->input('target_role');
        
        if (!$employeeId) {
            return response()->json(['error' => 'Employee ID required'], 400);
        }
        
        $employee = Employee::with(['competencyProfiles.competency'])->where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        $roleRequirements = $this->getRoleRequirements($targetRole);
        $analysis = $this->analyzeEmployeeForRole($employee, $employee->competencyProfiles, $roleRequirements);
        
        // Get training history
        $trainingHistory = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
            ->with('course')
            ->orderBy('training_date', 'desc')
            ->get();
        
        $developmentPath = [
            'employee' => [
                'id' => $employee->employee_id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'currentReadiness' => $analysis['readinessLevel'],
                'suitabilityScore' => $analysis['suitabilityScore']
            ],
            'targetRole' => $targetRole,
            'currentStrengths' => $analysis['strengths'],
            'developmentAreas' => $analysis['developmentAreas'],
            'recommendedPath' => $this->generateDevelopmentPlan($analysis, $targetRole),
            'trainingHistory' => $trainingHistory->map(function($training) {
                return [
                    'course' => $training->course->course_name ?? 'Unknown Course',
                    'status' => $training->status,
                    'progress' => $training->progress,
                    'date' => $training->training_date
                ];
            }),
            'timeline' => $this->generateDevelopmentTimeline($analysis['readinessLevel']),
            'milestones' => $this->generateMilestones($analysis, $targetRole)
        ];
        
        return response()->json([
            'success' => true,
            'data' => $developmentPath
        ]);
    }
    
    private function generateTrainingRecommendations($competencyGaps)
    {
        $recommendations = [];
        
        foreach($competencyGaps as $gap) {
            $recommendations[] = [
                'competency' => $gap,
                'priority' => 'High',
                'suggestedCourses' => $this->suggestCoursesForCompetency($gap),
                'estimatedDuration' => '2-4 weeks'
            ];
        }
        
        return $recommendations;
    }
    
    private function suggestCoursesForCompetency($competency)
    {
        $courseMap = [
            'Leadership' => ['Leadership Fundamentals', 'Advanced Leadership Skills', 'Team Management'],
            'Communication' => ['Effective Communication', 'Presentation Skills', 'Interpersonal Communication'],
            'Strategic Thinking' => ['Strategic Planning', 'Business Analysis', 'Decision Making'],
            'Customer Service' => ['Customer Experience Excellence', 'Service Recovery', 'Customer Relations'],
            'Technical Skills' => ['Technical Proficiency', 'System Administration', 'Process Improvement']
        ];
        
        foreach($courseMap as $category => $courses) {
            if(stripos($competency, strtolower($category)) !== false) {
                return $courses;
            }
        }
        
        return ['General Professional Development', 'Skills Enhancement Workshop'];
    }
    
    private function generateDevelopmentPlan($analysis, $targetRole)
    {
        $plan = [];
        
        switch($analysis['readinessLevel']) {
            case 'Ready Now':
                $plan = [
                    'phase1' => 'Leadership transition preparation (1 month)',
                    'phase2' => 'Role-specific training and mentoring (2 months)',
                    'phase3' => 'Gradual responsibility transfer (3 months)'
                ];
                break;
            case 'Ready Soon':
                $plan = [
                    'phase1' => 'Address key competency gaps (3 months)',
                    'phase2' => 'Leadership development program (3 months)',
                    'phase3' => 'Role preparation and mentoring (3 months)'
                ];
                break;
            case 'Needs Development':
                $plan = [
                    'phase1' => 'Foundational skills development (6 months)',
                    'phase2' => 'Advanced competency building (6 months)',
                    'phase3' => 'Leadership preparation program (6 months)'
                ];
                break;
        }
        
        return $plan;
    }
    
    private function generateDevelopmentTimeline($readinessLevel)
    {
        switch($readinessLevel) {
            case 'Ready Now': return '3-6 months';
            case 'Ready Soon': return '6-12 months';
            case 'Needs Development': return '12-18 months';
            default: return '12+ months';
        }
    }
    
    private function generateMilestones($analysis, $targetRole)
    {
        return [
            [
                'milestone' => 'Complete competency assessment',
                'target_date' => date('Y-m-d', strtotime('+1 month')),
                'status' => 'pending'
            ],
            [
                'milestone' => 'Address top development areas',
                'target_date' => date('Y-m-d', strtotime('+3 months')),
                'status' => 'pending'
            ],
            [
                'milestone' => 'Leadership readiness evaluation',
                'target_date' => date('Y-m-d', strtotime('+6 months')),
                'status' => 'pending'
            ],
            [
                'milestone' => 'Role transition preparation',
                'target_date' => date('Y-m-d', strtotime('+9 months')),
                'status' => 'pending'
            ]
        ];
    }
}
