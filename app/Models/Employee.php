<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'employee_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'hire_date',
        'department_id',
        'position',
        'status',
        'password',
        'profile_picture',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function competencyProfiles()
    {
        return $this->hasMany(\App\Models\EmployeeCompetencyProfile::class, 'employee_id', 'employee_id');
    }

    public function successionReadinessRating()
    {
        return $this->hasOne(\App\Models\SuccessionReadinessRating::class, 'employee_id', 'employee_id');
    }
}


