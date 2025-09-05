<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseManagement;
use App\Models\EmployeeTrainingDashboard;
use App\Models\User;
use App\Models\Employee;
use App\Models\AttendanceTimeLog;
use App\Models\CompetencyLibrary;
// use App\Models\SuccessionPlan; // If you have a model for succession plans
use App\Models\CompetencyGap; // For competency score avg

class DashboardController extends Controller
{
    public function index()
    {
        // Get total employees from Employee model
        $totalEmployees = Employee::count();
        
        // Get active courses
        $activeCourses = CourseManagement::count();
        
        // Get training sessions
        $trainingSessions = EmployeeTrainingDashboard::count();
        
        // Get employee users (from User table with employee role)
        $employeeUsers = User::where('role', 'employee')->count();
        
        // Get succession plans count (placeholder)
        $successionPlans = 0; // SuccessionPlan model does not exist
        
        // Get competencies count
        $competencies = CompetencyLibrary::count();
        
        // Get attendance logs count
        $attendanceLogs = AttendanceTimeLog::count();
        
        // Get completed trainings count
        $completedTrainings = EmployeeTrainingDashboard::where('status', 'Completed')->count();

        // Fetch recent trainings (latest 5)
        $recentTrainings = EmployeeTrainingDashboard::with(['employee', 'course'])
            ->orderByDesc('training_date')
            ->limit(5)
            ->get();

        // Fetch top skills in demand (by number of employees with a gap > 0, descending)
        $topSkills = CompetencyGap::with('competency')
            ->where('gap', '>', 0)
            ->get()
            ->groupBy('competency_id')
            ->map(function($gaps) {
                $competency = $gaps->first()->competency;
                $count = $gaps->count();
                return [
                    'name' => $competency ? $competency->competency_name : 'Unknown',
                    'count' => $count,
                ];
            })
            ->sortByDesc('count')
            ->take(4)
            ->values();

        // For demo: assign a percentage based on count (normalize to 100 for top skill)
        $maxCount = $topSkills->max('count') ?: 1;
        $topSkills = $topSkills->map(function($skill) use ($maxCount) {
            $percent = round(($skill['count'] / $maxCount) * 100);
            return [
                'name' => $skill['name'],
                'percent' => $percent,
            ];
        });

        return view('admin_dashboard', compact(
            'totalEmployees',
            'activeCourses',
            'trainingSessions',
            'employeeUsers',
            'successionPlans',
            'competencies',
            'attendanceLogs',
            'completedTrainings',
            'recentTrainings',
            'topSkills'
        ));
    }
}
