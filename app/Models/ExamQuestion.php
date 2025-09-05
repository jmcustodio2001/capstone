<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'type',
        'question',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean'
    ];

    public function course()
    {
        return $this->belongsTo(CourseManagement::class, 'course_id', 'course_id');
    }

    // Get questions for exam/quiz
    public static function getQuestionsForCourse($courseId, $type, $limit = null)
    {
        $query = self::where('course_id', $courseId)
            ->where('type', $type)
            ->where('is_active', true)
            ->inRandomOrder();
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
}
