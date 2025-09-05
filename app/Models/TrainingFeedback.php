<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingFeedback extends Model {
    use HasFactory;
    
    protected $table = 'training_feedback';
    protected $primaryKey = 'id';
    public $incrementing = true;
    
    protected $fillable = [
        'feedback_id', 'employee_id', 'course_id', 'training_title', 
        'overall_rating', 'content_quality', 'instructor_effectiveness', 
        'material_relevance', 'training_duration', 'what_learned', 
        'most_valuable', 'improvements', 'additional_topics', 'comments',
        'recommend_training', 'training_format', 'training_completion_date', 
        'submitted_at', 'admin_reviewed', 'reviewed_at', 'admin_response',
        'action_taken', 'response_date'
    ];

    protected $dates = [
        'training_completion_date',
        'submitted_at',
        'reviewed_at',
        'response_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'recommend_training' => 'boolean',
        'admin_reviewed' => 'boolean',
        'training_completion_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'response_date' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(CourseManagement::class, 'course_id');
    }

    // Helper methods
    public function getOverallRatingStarsAttribute()
    {
        return str_repeat('★', $this->overall_rating) . str_repeat('☆', 5 - $this->overall_rating);
    }

    public function getAverageDetailedRatingAttribute()
    {
        $ratings = [
            $this->content_quality,
            $this->instructor_effectiveness,
            $this->material_relevance,
            $this->training_duration
        ];
        
        $validRatings = array_filter($ratings, function($rating) {
            return !is_null($rating);
        });
        
        return count($validRatings) > 0 ? round(array_sum($validRatings) / count($validRatings), 1) : null;
    }

    // Scopes
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByTraining($query, $trainingTitle)
    {
        return $query->where('training_title', 'like', '%' . $trainingTitle . '%');
    }

    public function scopeHighRated($query, $minRating = 4)
    {
        return $query->where('overall_rating', '>=', $minRating);
    }
}
