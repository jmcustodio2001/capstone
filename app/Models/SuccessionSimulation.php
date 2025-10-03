<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessionSimulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'position_id',
        'simulation_name',
        'simulation_type',
        'scenario_description',
        'simulation_date',
        'duration_hours',
        'score',
        'max_score',
        'performance_rating',
        'competencies_assessed',
        'strengths',
        'areas_for_improvement',
        'recommendations',
        'assessor_id',
        'status',
        'notes',
        'simulation_result', // For backward compatibility with existing table
    ];

    protected $casts = [
        'competencies_assessed' => 'array',
        'simulation_date' => 'date',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'duration_hours' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function position()
    {
        return $this->belongsTo(OrganizationalPosition::class, 'position_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }
}
