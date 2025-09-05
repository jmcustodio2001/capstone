<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessionAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'assessment_type',
        'assessment_data',
        'overall_score',
        'assessment_date',
        'assessor_id',
        'feedback',
        'recommendations'
    ];

    protected $casts = [
        'assessment_data' => 'array',
        'overall_score' => 'decimal:2',
        'assessment_date' => 'date'
    ];

    public function candidate()
    {
        return $this->belongsTo(SuccessionCandidate::class, 'candidate_id');
    }

    public function assessor()
    {
        return $this->belongsTo(Employee::class, 'assessor_id', 'employee_id');
    }
}
