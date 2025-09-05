<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTrainingDashboard extends Model
{
    use HasFactory;

    protected $table = 'employee_training_dashboards'; // Explicitly set table name
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'course_id',
        'training_date',
        'status',
        'remarks',
        'progress',
        'last_accessed',
        'assigned_by',
        'expired_date',
        'source',
    ];

    protected $casts = [
        'expired_date' => 'datetime',
        'training_date' => 'datetime',
        'last_accessed' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($training) {
            // Automatically set expiration date to 90 days from now when creating training records
            if (!$training->expired_date) {
                $training->expired_date = Carbon::now()->addDays(90);
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(CourseManagement::class, 'course_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
