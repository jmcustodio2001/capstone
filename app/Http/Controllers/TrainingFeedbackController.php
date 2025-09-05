<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TrainingFeedback;
use App\Models\Employee;
use App\Models\CourseManagement;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrainingFeedbackController extends Controller
{
    public function index()
    {
        // Redirect to main trainings page with feedback tab active
        return redirect()->route('employee.my_trainings.index', ['tab' => 'feedback']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'training_title' => 'required|string|max:255',
            'overall_rating' => 'required|integer|between:1,5',
            'content_quality' => 'nullable|integer|between:1,5',
            'instructor_effectiveness' => 'nullable|integer|between:1,5',
            'material_relevance' => 'nullable|integer|between:1,5',
            'training_duration' => 'nullable|integer|between:1,5',
            'what_learned' => 'nullable|string|max:1000',
            'most_valuable' => 'nullable|string|max:1000',
            'improvements' => 'nullable|string|max:1000',
            'additional_topics' => 'nullable|string|max:1000',
            'comments' => 'nullable|string|max:1000',
            'recommend_training' => 'boolean',
            'training_format' => 'nullable|in:Online,In-Person,Hybrid,Self-Paced',
            'training_completion_date' => 'nullable|date',
            'course_id' => 'nullable|exists:course_management,id'
        ]);

        $employeeId = Auth::guard('employee')->user()->employee_id;
        
        // Generate unique feedback ID
        $feedbackId = 'FB' . date('Y') . str_pad(TrainingFeedback::count() + 1, 3, '0', STR_PAD_LEFT);

        $feedback = TrainingFeedback::create([
            'feedback_id' => $feedbackId,
            'employee_id' => $employeeId,
            'course_id' => $request->course_id,
            'training_title' => $request->training_title,
            'overall_rating' => $request->overall_rating,
            'content_quality' => $request->content_quality,
            'instructor_effectiveness' => $request->instructor_effectiveness,
            'material_relevance' => $request->material_relevance,
            'training_duration' => $request->training_duration,
            'what_learned' => $request->what_learned,
            'most_valuable' => $request->most_valuable,
            'improvements' => $request->improvements,
            'additional_topics' => $request->additional_topics,
            'comments' => $request->comments,
            'recommend_training' => $request->has('recommend_training'),
            'training_format' => $request->training_format,
            'training_completion_date' => $request->training_completion_date,
            'submitted_at' => now()
        ]);

        // Log activity
        ActivityLog::create([
            'employee_id' => $employeeId,
            'activity_type' => 'Training Feedback',
            'description' => "Submitted feedback for training: {$request->training_title} (Rating: {$request->overall_rating}/5)",
            'activity_date' => now()
        ]);

        return redirect()->route('employee.my_trainings.index', ['tab' => 'feedback'])->with('success', 'Training feedback submitted successfully!');
    }

    public function show($id)
    {
        $employeeId = Auth::guard('employee')->user()->employee_id;
        
        $feedback = TrainingFeedback::byEmployee($employeeId)
            ->with('employee', 'course')
            ->findOrFail($id);

        return response()->json($feedback);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'training_title' => 'required|string|max:255',
            'overall_rating' => 'required|integer|between:1,5',
            'content_quality' => 'nullable|integer|between:1,5',
            'instructor_effectiveness' => 'nullable|integer|between:1,5',
            'material_relevance' => 'nullable|integer|between:1,5',
            'training_duration' => 'nullable|integer|between:1,5',
            'what_learned' => 'nullable|string|max:1000',
            'most_valuable' => 'nullable|string|max:1000',
            'improvements' => 'nullable|string|max:1000',
            'additional_topics' => 'nullable|string|max:1000',
            'comments' => 'nullable|string|max:1000',
            'recommend_training' => 'boolean',
            'training_format' => 'nullable|in:Online,In-Person,Hybrid,Self-Paced',
            'training_completion_date' => 'nullable|date'
        ]);

        $employeeId = Auth::guard('employee')->user()->employee_id;
        
        $feedback = TrainingFeedback::byEmployee($employeeId)->findOrFail($id);
        
        $feedback->update([
            'training_title' => $request->training_title,
            'overall_rating' => $request->overall_rating,
            'content_quality' => $request->content_quality,
            'instructor_effectiveness' => $request->instructor_effectiveness,
            'material_relevance' => $request->material_relevance,
            'training_duration' => $request->training_duration,
            'what_learned' => $request->what_learned,
            'most_valuable' => $request->most_valuable,
            'improvements' => $request->improvements,
            'additional_topics' => $request->additional_topics,
            'comments' => $request->comments,
            'recommend_training' => $request->has('recommend_training'),
            'training_format' => $request->training_format,
            'training_completion_date' => $request->training_completion_date
        ]);

        // Log activity
        ActivityLog::create([
            'employee_id' => $employeeId,
            'activity_type' => 'Training Feedback',
            'description' => "Updated feedback for training: {$request->training_title} (Rating: {$request->overall_rating}/5)",
            'activity_date' => now()
        ]);

        return redirect()->route('employee.my_trainings.index', ['tab' => 'feedback'])->with('success', 'Training feedback updated successfully!');
    }

    public function destroy($id)
    {
        $employeeId = Auth::guard('employee')->user()->employee_id;
        
        $feedback = TrainingFeedback::byEmployee($employeeId)->findOrFail($id);
        
        $trainingTitle = $feedback->training_title;
        $feedback->delete();

        // Log activity
        ActivityLog::create([
            'employee_id' => $employeeId,
            'activity_type' => 'Training Feedback',
            'description' => "Deleted feedback for training: {$trainingTitle}",
            'activity_date' => now()
        ]);

        return redirect()->route('employee.my_trainings.index', ['tab' => 'feedback'])->with('success', 'Training feedback deleted successfully!');
    }

    public function getCompletedTrainings()
    {
        $employeeId = Auth::guard('employee')->user()->employee_id;
        
        $completedTrainings = DB::table('course_management')
            ->where('employee_id', $employeeId)
            ->where('progress', '>=', 100)
            ->select('id', 'course_title', 'progress', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($completedTrainings);
    }

    public function getFeedbackStats()
    {
        $employeeId = Auth::guard('employee')->user()->employee_id;
        
        $stats = [
            'total_feedback' => TrainingFeedback::byEmployee($employeeId)->count(),
            'average_rating' => TrainingFeedback::byEmployee($employeeId)->avg('overall_rating'),
            'high_rated_count' => TrainingFeedback::byEmployee($employeeId)->highRated()->count(),
            'recent_feedback' => TrainingFeedback::byEmployee($employeeId)
                ->where('submitted_at', '>=', Carbon::now()->subDays(30))
                ->count()
        ];

        return response()->json($stats);
    }
}
