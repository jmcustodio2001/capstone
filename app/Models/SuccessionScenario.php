<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessionScenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'scenario_name',
        'scenario_type',
        'description',
        'affected_positions',
        'impact_level',
        'estimated_timeline_days',
        'simulation_results',
        'success_probability',
        'recommendations',
        'status',
        'created_by'
    ];

    protected $casts = [
        'affected_positions' => 'array',
        'simulation_results' => 'array',
        'success_probability' => 'decimal:2'
    ];

    // Relationship to the user who created this scenario
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'employee_id');
    }

    // Get affected positions with details
    public function getAffectedPositionsWithDetails()
    {
        if (!$this->affected_positions) {
            return collect();
        }

        return OrganizationalPosition::whereIn('id', $this->affected_positions)->get();
    }

    // Run simulation for this scenario
    public function runSimulation()
    {
        $results = [];
        $affectedPositions = $this->getAffectedPositionsWithDetails();
        
        foreach ($affectedPositions as $position) {
            $candidates = $position->candidates()->where('status', 'active')->get();
            $readyCandidates = $candidates->where('readiness_level', 'ready');
            
            $results[] = [
                'position_id' => $position->id,
                'position_name' => $position->position_name,
                'total_candidates' => $candidates->count(),
                'ready_candidates' => $readyCandidates->count(),
                'best_candidate' => $readyCandidates->sortByDesc('readiness_score')->first(),
                'risk_level' => $readyCandidates->count() > 0 ? 'low' : 'high'
            ];
        }
        
        $this->simulation_results = $results;
        $this->save();
        
        return $results;
    }
}
