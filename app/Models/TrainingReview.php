<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'course_id',
        'training_title',
        'reviewed_at',
        'review_status'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(CourseManagement::class, 'course_id', 'course_id');
    }
}
