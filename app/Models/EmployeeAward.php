<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAward extends Model
{
    protected $fillable = [
        'employee_id',
        'award_type',
        'award_name',
        'description',
        'award_date',
        'awarded_by',
        'status',
        'notes'
    ];

    protected $casts = [
        'award_date' => 'date',
    ];

    // Predefined award types
    public static function getAwardTypes()
    {
        return [
            'Employee of the Month' => 'Employee of the Month',
            'Outstanding Performance' => 'Outstanding Performance',
            'Customer Service Excellence' => 'Customer Service Excellence',
            'Innovation Award' => 'Innovation Award',
            'Team Player Award' => 'Team Player Award',
            'Leadership Excellence' => 'Leadership Excellence',
            'Perfect Attendance' => 'Perfect Attendance',
            'Sales Achievement' => 'Sales Achievement',
            'Safety Award' => 'Safety Award',
            'Training Completion' => 'Training Completion',
            'Years of Service' => 'Years of Service',
            'Special Recognition' => 'Special Recognition'
        ];
    }

    // Relationship with Employee
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    // Status badge helper
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'pending' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    // Status text helper
    public function getStatusText()
    {
        return match($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'pending' => 'Pending',
            default => 'Unknown'
        };
    }
}
