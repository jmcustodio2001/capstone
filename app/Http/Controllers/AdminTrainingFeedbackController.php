<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TrainingFeedback;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminTrainingFeedbackController extends Controller
{
    public function index(Request $request)
    {
        // Get all feedback with filters
        $query = TrainingFeedback::with('employee');
        
        // Apply filters
        if ($request->employee) {
            $query->where('employee_id', $request->employee);
        }
        
        if ($request->training) {
            $query->where('training_title', 'LIKE', '%' . $request->training . '%');
        }
        
        if ($request->rating) {
            $query->where('overall_rating', $request->rating);
        }
        
        if ($request->date_range) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('submitted_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('submitted_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('submitted_at', Carbon::now()->month);
                    break;
                case 'quarter':
                    $query->whereBetween('submitted_at', [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()]);
                    break;
            }
        }
        
        $allFeedback = $query->orderBy('submitted_at', 'desc')->paginate(20);
        
        // Get statistics
        $totalFeedback = TrainingFeedback::count();
        $avgRating = TrainingFeedback::avg('overall_rating');
        $thisWeekFeedback = TrainingFeedback::whereBetween('submitted_at', [
            Carbon::now()->startOfWeek(), 
            Carbon::now()->endOfWeek()
        ])->count();
        $recommendationRate = TrainingFeedback::where('recommend_training', true)->count() / max($totalFeedback, 1) * 100;
        
        // Get filter options
        $employees = Employee::select('employee_id', 'first_name', 'last_name')->orderBy('first_name')->get();
        $trainings = TrainingFeedback::distinct()->pluck('training_title')->filter()->sort()->values();
        
        return view('Employee_Self_Service.employee_feedback', compact(
            'allFeedback', 
            'totalFeedback', 
            'avgRating', 
            'thisWeekFeedback', 
            'recommendationRate',
            'employees',
            'trainings'
        ));
    }
    
    public function show($id)
    {
        $feedback = TrainingFeedback::with('employee')->findOrFail($id);
        return response()->json($feedback);
    }
    
    public function markAsReviewed($id)
    {
        $feedback = TrainingFeedback::findOrFail($id);
        $feedback->update(['admin_reviewed' => true, 'reviewed_at' => now()]);
        
        // Log activity
        ActivityLog::create([
            'employee_id' => 'ADMIN',
            'activity_type' => 'Feedback Review',
            'description' => "Marked feedback {$feedback->feedback_id} as reviewed for training: {$feedback->training_title}",
            'activity_date' => now()
        ]);
        
        return response()->json(['success' => true, 'message' => 'Feedback marked as reviewed']);
    }
    
    public function respond(Request $request, $id)
    {
        $request->validate([
            'admin_response' => 'required|string|max:1000',
            'action_taken' => 'nullable|string|max:255',
            'notify_employee' => 'boolean'
        ]);
        
        $feedback = TrainingFeedback::findOrFail($id);
        
        $feedback->update([
            'admin_response' => $request->admin_response,
            'action_taken' => $request->action_taken,
            'admin_reviewed' => true,
            'reviewed_at' => now(),
            'response_date' => now()
        ]);
        
        // Log activity
        ActivityLog::create([
            'employee_id' => 'ADMIN',
            'activity_type' => 'Feedback Response',
            'description' => "Responded to feedback {$feedback->feedback_id} for training: {$feedback->training_title}",
            'activity_date' => now()
        ]);
        
        // If notify employee is checked, you could add email notification here
        if ($request->notify_employee) {
            // Add notification logic here if needed
            ActivityLog::create([
                'employee_id' => $feedback->employee_id,
                'activity_type' => 'Feedback Response',
                'description' => "Admin responded to your feedback for training: {$feedback->training_title}",
                'activity_date' => now()
            ]);
        }
        
        return response()->json(['success' => true, 'message' => 'Response sent successfully']);
    }
    
    public function export(Request $request)
    {
        $query = TrainingFeedback::with('employee');
        
        // Apply same filters as index
        if ($request->employee) {
            $query->where('employee_id', $request->employee);
        }
        
        if ($request->training) {
            $query->where('training_title', 'LIKE', '%' . $request->training . '%');
        }
        
        if ($request->rating) {
            $query->where('overall_rating', $request->rating);
        }
        
        if ($request->date_range) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('submitted_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('submitted_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('submitted_at', Carbon::now()->month);
                    break;
                case 'quarter':
                    $query->whereBetween('submitted_at', [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()]);
                    break;
            }
        }
        
        $feedback = $query->orderBy('submitted_at', 'desc')->get();
        
        // Create CSV content
        $csvContent = "Feedback ID,Employee Name,Employee ID,Training Title,Overall Rating,Recommend,Format,Content Quality,Instructor Effectiveness,Material Relevance,Training Duration,What Learned,Most Valuable,Improvements,Additional Topics,Comments,Submitted Date,Admin Reviewed,Admin Response,Action Taken\n";
        
        foreach ($feedback as $f) {
            $csvContent .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $f->feedback_id ?? '',
                ($f->employee ? $f->employee->first_name . ' ' . $f->employee->last_name : 'Unknown'),
                $f->employee_id ?? '',
                $f->training_title ?? '',
                $f->overall_rating ?? '',
                $f->recommend_training ? 'Yes' : 'No',
                $f->training_format ?? '',
                $f->content_quality ?? '',
                $f->instructor_effectiveness ?? '',
                $f->material_relevance ?? '',
                $f->training_duration ?? '',
                str_replace('"', '""', $f->what_learned ?? ''),
                str_replace('"', '""', $f->most_valuable ?? ''),
                str_replace('"', '""', $f->improvements ?? ''),
                str_replace('"', '""', $f->additional_topics ?? ''),
                str_replace('"', '""', $f->comments ?? ''),
                $f->submitted_at ? $f->submitted_at->format('Y-m-d H:i:s') : '',
                $f->admin_reviewed ? 'Yes' : 'No',
                str_replace('"', '""', $f->admin_response ?? ''),
                $f->action_taken ?? ''
            );
        }
        
        // Return CSV download
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="training_feedback_export_' . date('Y-m-d_H-i-s') . '.csv"');
    }
    
    public function getAnalytics()
    {
        $analytics = [
            'total_feedback' => TrainingFeedback::count(),
            'average_rating' => round(TrainingFeedback::avg('overall_rating'), 1),
            'this_week_feedback' => TrainingFeedback::whereBetween('submitted_at', [
                Carbon::now()->startOfWeek(), 
                Carbon::now()->endOfWeek()
            ])->count(),
            'recommendation_rate' => round(
                TrainingFeedback::where('recommend_training', true)->count() / 
                max(TrainingFeedback::count(), 1) * 100, 1
            ),
            'rating_distribution' => TrainingFeedback::select('overall_rating', DB::raw('count(*) as count'))
                ->groupBy('overall_rating')
                ->orderBy('overall_rating')
                ->get(),
            'top_trainings' => TrainingFeedback::select('training_title', DB::raw('count(*) as feedback_count'), DB::raw('avg(overall_rating) as avg_rating'))
                ->groupBy('training_title')
                ->orderBy('feedback_count', 'desc')
                ->limit(10)
                ->get(),
            'recent_trends' => TrainingFeedback::select(
                DB::raw('DATE(submitted_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw('avg(overall_rating) as avg_rating')
            )
                ->where('submitted_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];
        
        return response()->json($analytics);
    }
}
