<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_id',
        'application_date',
        'leave_type',
        'leave_days',
        'days_requested',
        'status',
        'reason',
        'start_date',
        'end_date',
        'contact_info',
        'applied_date',
        'approved_by',
        'approved_date',
        'remarks'
    ];

    protected $dates = [
        'application_date',
        'start_date', 
        'end_date',
        'applied_date',
        'approved_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
