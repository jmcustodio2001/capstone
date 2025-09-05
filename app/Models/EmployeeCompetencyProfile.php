<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCompetencyProfile extends Model
{
    protected $fillable = [
        'employee_id',
        'competency_id',
        'proficiency_level',
        'assessment_date',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

public function competency()
{
    return $this->belongsTo(CompetencyLibrary::class, 'competency_id');
}

}
