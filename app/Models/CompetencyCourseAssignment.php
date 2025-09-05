<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetencyCourseAssignment extends Model
{
    protected $fillable = [
        'employee_id',
        'course_id',
        'assigned_date',
        'status',
        'progress',
        'is_destination_knowledge'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(CourseManagement::class, 'course_id', 'course_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
