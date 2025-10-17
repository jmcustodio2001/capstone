<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyLibrary extends Model
{
    use HasFactory;

    // Fix: specify the correct table name
    protected $table = 'competency_library';
    protected $primaryKey = 'id';

    protected $fillable = [
        'competency_name',
        'description',
        'category',
        'rate',
        'is_seeded',
    ];

    public function courseManagement()
    {
        return $this->hasMany(CourseManagement::class, 'competency_id');
    }

    public function attempts()
    {
        return $this->hasManyThrough(
            ExamAttempt::class,
            CourseManagement::class,
            'competency_id', // Foreign key on course_management table
            'course_id' // Foreign key on exam_attempt table
        );
    }
}
