<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyFeedbackRequest extends Model
{
    use HasFactory;

    protected $table = 'competency_feedback_requests';

    protected $fillable = [
        'employee_id',
        'competency_id',
        'request_message',
        'status',
        'manager_response',
        'manager_id',
        'responded_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the employee who made the request
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Get the competency for this request
     */
    public function competency()
    {
        return $this->belongsTo(CompetencyLibrary::class, 'competency_id', 'id');
    }

    /**
     * Get the manager who responded
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }
}
