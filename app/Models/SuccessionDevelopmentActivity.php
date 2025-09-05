<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessionDevelopmentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'activity_type',
        'activity_name',
        'description',
        'start_date',
        'target_completion_date',
        'actual_completion_date',
        'status',
        'progress_percentage',
        'competencies_targeted',
        'outcomes',
        'assigned_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'competencies_targeted' => 'array'
    ];

    public function candidate()
    {
        return $this->belongsTo(SuccessionCandidate::class, 'candidate_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(Employee::class, 'assigned_by', 'employee_id');
    }
}
