<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessionCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'target_position_id',
        'readiness_score',
        'readiness_level',
        'target_ready_date',
        'development_plan',
        'competency_gaps',
        'strengths',
        'development_areas',
        'status',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'readiness_score' => 'decimal:2',
        'target_ready_date' => 'date',
        'competency_gaps' => 'array',
        'strengths' => 'array',
        'development_areas' => 'array'
    ];

    // Relationship to employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    // Relationship to target position
    public function targetPosition()
    {
        return $this->belongsTo(OrganizationalPosition::class, 'target_position_id');
    }

    // Relationship to assessments
    public function assessments()
    {
        return $this->hasMany(SuccessionAssessment::class, 'candidate_id');
    }

    // Relationship to development activities
    public function developmentActivities()
    {
        return $this->hasMany(SuccessionDevelopmentActivity::class, 'candidate_id');
    }

    // Get latest assessment
    public function latestAssessment()
    {
        return $this->assessments()->latest('assessment_date')->first();
    }

    // Calculate readiness based on competency gaps and assessments
    public function calculateReadinessScore()
    {
        $competencyProfile = $this->employee->competencyProfiles()->get();
        $targetPosition = $this->targetPosition;
        
        if (!$targetPosition || !$targetPosition->required_competencies) {
            return 0;
        }

        $totalScore = 0;
        $totalWeight = 0;
        
        foreach ($targetPosition->required_competencies as $requirement) {
            $competencyId = $requirement['competency_id'];
            $requiredLevel = $requirement['required_level'];
            $weight = $requirement['weight'] ?? 1;
            
            $profile = $competencyProfile->where('competency_id', $competencyId)->first();
            $currentLevel = $profile ? $profile->proficiency_level : 0;
            
            $score = min(($currentLevel / $requiredLevel) * 100, 100);
            $totalScore += $score * $weight;
            $totalWeight += $weight;
        }
        
        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
    }

    // Get readiness level based on score
    public function getReadinessLevelAttribute()
    {
        $score = $this->readiness_score;
        
        if ($score >= 90) return 'ready';
        if ($score >= 70) return 'developing';
        return 'potential';
    }
}
