<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationalPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_name',
        'position_code',
        'description',
        'department',
        'level',
        'hierarchy_level',
        'reports_to',
        'required_competencies',
        'min_experience_years',
        'min_readiness_score',
        'is_critical_position',
        'is_active'
    ];

    protected $casts = [
        'required_competencies' => 'array',
        'min_readiness_score' => 'decimal:2',
        'is_critical_position' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationship to parent position
    public function reportsTo()
    {
        return $this->belongsTo(OrganizationalPosition::class, 'reports_to');
    }

    // Relationship to subordinate positions
    public function subordinates()
    {
        return $this->hasMany(OrganizationalPosition::class, 'reports_to');
    }

    // Relationship to succession candidates
    public function candidates()
    {
        return $this->hasMany(SuccessionCandidate::class, 'target_position_id');
    }

    // Get ready candidates
    public function readyCandidates()
    {
        return $this->candidates()->where('readiness_level', 'ready');
    }

    // Get required competencies with details
    public function getRequiredCompetenciesWithDetails()
    {
        if (!$this->required_competencies) {
            return collect();
        }

        $competencyIds = collect($this->required_competencies)->pluck('competency_id');
        return Competency::whereIn('id', $competencyIds)->get();
    }
}
