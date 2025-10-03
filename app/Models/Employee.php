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
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'last_otp_sent_at',
        'otp_verified',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'password' => 'hashed',
        'otp_expires_at' => 'datetime',
        'last_otp_sent_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'otp_verified' => 'boolean',
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


