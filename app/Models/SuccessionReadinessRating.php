<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessionReadinessRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'readiness_score',
        'readiness_level',
        'assessment_notes',
        'assessment_date',
        'assessed_by',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Create sample succession readiness rating data
     */
    public static function createSampleData()
    {
        // First ensure employees exist
        $employees = [
            ['employee_id' => 'EMP001', 'first_name' => 'John', 'last_name' => 'Smith', 'position' => 'Senior Manager'],
            ['employee_id' => 'EMP002', 'first_name' => 'Sarah', 'last_name' => 'Johnson', 'position' => 'Team Lead'],
            ['employee_id' => 'EMP003', 'first_name' => 'Mike', 'last_name' => 'Davis', 'position' => 'Developer'],
            ['employee_id' => 'EMP004', 'first_name' => 'Lisa', 'last_name' => 'Wilson', 'position' => 'Analyst'],
            ['employee_id' => 'EMP005', 'first_name' => 'Tom', 'last_name' => 'Brown', 'position' => 'Supervisor'],
            ['employee_id' => 'EMP006', 'first_name' => 'Anna', 'last_name' => 'Garcia', 'position' => 'Manager'],
        ];

        foreach ($employees as $emp) {
            Employee::updateOrCreate(
                ['employee_id' => $emp['employee_id']],
                $emp
            );
        }

        // Clear existing succession readiness ratings
        self::truncate();

        // Create succession readiness ratings
        $ratings = [
            ['employee_id' => 'EMP001', 'readiness_score' => 85, 'readiness_level' => 'Ready Now', 'assessment_date' => '2024-09-01'],
            ['employee_id' => 'EMP002', 'readiness_score' => 72, 'readiness_level' => 'Ready Soon', 'assessment_date' => '2024-09-02'],
            ['employee_id' => 'EMP003', 'readiness_score' => 68, 'readiness_level' => 'Ready Soon', 'assessment_date' => '2024-09-03'],
            ['employee_id' => 'EMP004', 'readiness_score' => 45, 'readiness_level' => 'Needs Development', 'assessment_date' => '2024-09-04'],
            ['employee_id' => 'EMP005', 'readiness_score' => 78, 'readiness_level' => 'Ready Soon', 'assessment_date' => '2024-09-05'],
            ['employee_id' => 'EMP006', 'readiness_score' => 92, 'readiness_level' => 'Ready Now', 'assessment_date' => '2024-09-06'],
        ];

        foreach ($ratings as $rating) {
            self::create($rating);
        }

        return self::count();
    }
}
