<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessionSimulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'simulation_result',
        'created_at',
    ];

    public $timestamps = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
