<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseManagement extends Model
{
    use HasFactory;

    protected $table = 'course_management';
    protected $primaryKey = 'course_id';
    protected $fillable = [
        'course_title',
        'description',
        'start_date',
        'end_date',
        'status',
        'source_type',
        'source_id',
        'requested_at',
        'requested_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    /**
     * Get the user who requested this course activation
     */
    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the employee associated with this course (if any)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
