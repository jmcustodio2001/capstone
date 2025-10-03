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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        // Debug: Log all request data
        Log::info('Training Feedback Store Request Data:', $request->all());
        
        try {
            $request->validate([
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
                'recommend_training' => 'nullable|in:0,1',
                'training_format' => 'nullable|in:Online,In-Person,Hybrid,Self-Paced',
                'training_completion_date' => 'nullable|date',
                'course_id' => 'required|string',
                'training_title' => 'nullable|string|max:255'
            ]);
            Log::info('Training Feedback Validation Passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Training Feedback Validation Failed:', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        }

        $employeeId = Auth::guard('employee')->user()->employee_id;
        Log::info('Employee ID:', ['employee_id' => $employeeId]);
        
        // Get training title from the selected course or form data
        $course = null;
        $trainingTitle = $request->input('training_title', 'Unknown Training');
        
        // Try to find course by course_id if it's numeric
        if (is_numeric($request->course_id)) {
            $course = CourseManagement::where('course_id', $request->course_id)->first();
            if ($course) {
                $trainingTitle = $course->course_title;
            }
        }
        
        // If no course found and no training_title provided, try to extract from course_id
        if (!$course && empty($trainingTitle)) {
            // For manual entries like 'manual_1', try to get from completed trainings
            $trainingTitle = $request->input('training_title', 'Completed Training');
        }
        
        // Ensure we have a valid training title
        if (empty($trainingTitle) || $trainingTitle === 'Unknown Training') {
            $trainingTitle = 'Training Feedback - ' . date('Y-m-d');
        }
        
        Log::info('Course lookup result:', [
            'course_id' => $request->course_id,
            'course_found' => $course ? true : false,
            'training_title' => $trainingTitle
        ]);
        
        // Generate unique feedback ID
        $feedbackId = 'FB' . date('Y') . str_pad(TrainingFeedback::count() + 1, 3, '0', STR_PAD_LEFT);
        Log::info('Generated feedback ID:', ['feedback_id' => $feedbackId]);

        // Ensure training_feedback table exists
        $this->ensureTrainingFeedbackTableExists();
        
        try {
            $feedback = TrainingFeedback::create([
                'feedback_id' => $feedbackId,
                'employee_id' => $employeeId,
                'course_id' => $request->course_id,
                'training_title' => $trainingTitle,
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
                'recommend_training' => $request->input('recommend_training', 0) == 1,
                'training_format' => $request->training_format,
                'training_completion_date' => $request->training_completion_date,
                'submitted_at' => now(),
                'admin_reviewed' => false
            ]);
            Log::info('Training feedback created successfully:', [
                'feedback_id' => $feedback->id,
                'database_id' => $feedback->feedback_id,
                'employee_id' => $feedback->employee_id,
                'training_title' => $feedback->training_title,
                'rating' => $feedback->overall_rating
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create training feedback:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Failed to submit feedback. Please try again.'])->withInput();
        }

        // Log activity
        ActivityLog::createLog([
            'module' => 'Training Management',
            'action' => 'Submit Feedback',
            'description' => "Employee {$employeeId} submitted feedback for training: {$trainingTitle} (Rating: {$request->overall_rating}/5)",
            'model_type' => 'TrainingFeedback',
            'model_id' => $feedback->id
        ]);

        Log::info('Training feedback submission completed successfully', [
            'feedback_id' => $feedback->feedback_id,
            'employee_id' => $employeeId,
            'training_title' => $trainingTitle
        ]);

        return redirect()->route('employee.my_trainings.index', ['tab' => 'feedback'])
            ->with('success', 'Training feedback submitted successfully! Your feedback ID is: ' . $feedback->feedback_id);
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
            'recommend_training' => 'nullable|in:0,1',
            'training_format' => 'nullable|in:Online,In-Person,Hybrid,Self-Paced',
            'training_completion_date' => 'nullable|date'
        ]);

        $employeeId = Auth::guard('employee')->user()->employee_id;
        
        $feedback = TrainingFeedback::byEmployee($employeeId)->findOrFail($id);
        
        // Get training title from the course relationship (keep existing title if no course)
        $trainingTitle = $feedback->training_title; // Keep existing title as fallback
        if ($feedback->course_id) {
            $course = \App\Models\CourseManagement::where('course_id', $feedback->course_id)->first();
            if ($course) {
                $trainingTitle = $course->course_title;
            }
        }
        
        $feedback->update([
            'training_title' => $trainingTitle,
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
            'recommend_training' => $request->input('recommend_training', 0) == 1,
            'training_format' => $request->training_format,
            'training_completion_date' => $request->training_completion_date
        ]);

        // Log activity
        ActivityLog::createLog([
            'module' => 'Training Management',
            'action' => 'Update Feedback',
            'description' => "Employee {$employeeId} updated feedback for training: {$trainingTitle} (Rating: {$request->overall_rating}/5)",
            'model_type' => 'TrainingFeedback',
            'model_id' => $feedback->id
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
        ActivityLog::createLog([
            'module' => 'Training Management',
            'action' => 'Delete Feedback',
            'description' => "Employee {$employeeId} deleted feedback for training: {$trainingTitle}",
            'model_type' => 'TrainingFeedback',
            'model_id' => $id
        ]);

        return redirect()->route('employee.my_trainings.index', ['tab' => 'feedback'])->with('success', 'Training feedback deleted successfully!');
    }

    public function getCompletedTrainings()
    {
        $employeeId = Auth::guard('employee')->user()->employee_id;
        
        $completedTrainings = DB::table('course_management')
            ->where('employee_id', $employeeId)
            ->where('progress', '>=', 100)
            ->select('course_id', 'course_title', 'progress', 'updated_at')
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
    
    /**
     * Ensure the training_feedback table exists
     */
    private function ensureTrainingFeedbackTableExists()
    {
        if (!Schema::hasTable('training_feedback')) {
            Log::info('Creating training_feedback table...');
            
            Schema::create('training_feedback', function ($table) {
                $table->id();
                $table->string('feedback_id')->nullable();
                $table->string('employee_id')->nullable();
                $table->integer('course_id')->nullable();
                $table->string('training_title')->nullable();
                $table->integer('overall_rating')->nullable();
                $table->integer('content_quality')->nullable();
                $table->integer('instructor_effectiveness')->nullable();
                $table->integer('material_relevance')->nullable();
                $table->integer('training_duration')->nullable();
                $table->text('what_learned')->nullable();
                $table->text('most_valuable')->nullable();
                $table->text('improvements')->nullable();
                $table->text('additional_topics')->nullable();
                $table->text('comments')->nullable();
                $table->boolean('recommend_training')->default(false);
                $table->string('training_format')->nullable();
                $table->date('training_completion_date')->nullable();
                $table->datetime('submitted_at')->nullable();
                $table->boolean('admin_reviewed')->default(false);
                $table->datetime('reviewed_at')->nullable();
                $table->text('admin_response')->nullable();
                $table->text('action_taken')->nullable();
                $table->datetime('response_date')->nullable();
                $table->timestamps();
                
                // Add indexes for better performance
                $table->index('employee_id');
                $table->index('course_id');
                $table->index('overall_rating');
                $table->index('admin_reviewed');
                $table->index('submitted_at');
            });
            
            Log::info('Training feedback table created successfully');
        } else {
            Log::info('Training feedback table already exists');
        }
    }
}
