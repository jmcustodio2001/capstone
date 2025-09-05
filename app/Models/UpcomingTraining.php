<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpcomingTraining extends Model {
    use HasFactory;
    
    protected $table = 'upcoming_trainings';
    protected $primaryKey = 'upcoming_id';
    
    protected $fillable = [
        'employee_id', 'training_title', 'start_date', 'end_date', 'status',
        'source', 'assigned_by', 'assigned_date', 'destination_training_id', 'needs_response'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
}
