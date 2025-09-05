<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Training;

class DestinationKnowledgeTraining extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'destination_knowledge_trainings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'destination_name',
        'details',
        'date_completed',
        'expired_date',
        'delivery_mode',
        'progress',
        'remarks',
        'status',
        'is_active',
        'admin_approved_for_upcoming',
    ];

    protected $casts = [
        'date_completed' => 'date',
        'expired_date' => 'date',
        'is_active' => 'boolean',
        'admin_approved_for_upcoming' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}
