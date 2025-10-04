<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UpcomingTraining extends Model {
    use HasFactory;
    
    protected $table = 'upcoming_trainings';
    protected $primaryKey = 'upcoming_id';
    
    protected $fillable = [
        'employee_id', 'training_title', 'start_date', 'end_date', 'expired_date', 'status',
        'source', 'assigned_by', 'assigned_by_name', 'assigned_date', 'destination_training_id', 'needs_response'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'expired_date' => 'date',
        'assigned_date' => 'datetime',
        'needs_response' => 'boolean',
        'destination_training_id' => 'integer'
    ];
    
    // Relationship to employee
    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id', 'employee_id');
    }
    
    // Relationship to destination training
    public function destinationTraining()
    {
        return $this->belongsTo(\App\Models\DestinationKnowledgeTraining::class, 'destination_training_id', 'id');
    }
    
    // Relationship to admin user who assigned the training
    public function assignedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by', 'id');
    }

    /**
     * Fix expiration dates to match competency gaps
     */
    public static function fixExpirationDates()
    {
        $updatedCount = 0;
        
        // Get all upcoming trainings from competency gaps
        $upcomingTrainings = self::where('source', 'competency_gap')->get();
        
        foreach ($upcomingTrainings as $training) {
            // Find matching competency gap
            $competencyGap = \App\Models\CompetencyGap::where('employee_id', $training->employee_id)
                ->where('competency_name', $training->training_title)
                ->first();
            
            if ($competencyGap && $training->end_date != $competencyGap->expired_date) {
                $training->end_date = $competencyGap->expired_date;
                $training->save();
                $updatedCount++;
                
                Log::info("Fixed expiration date for {$training->employee_id} - {$training->training_title}: {$competencyGap->expired_date}");
            }
        }
        
        return $updatedCount;
    }
}
