<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestForm extends Model
{
    use HasFactory;

    protected $primaryKey = 'request_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'request_type',
        'reason',
        'status',
        'requested_date',
        'rejection_reason',
    ];

    public $timestamps = false;

    /**
     * Get the employee that owns the request
     */
    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id', 'employee_id');
    }
}
