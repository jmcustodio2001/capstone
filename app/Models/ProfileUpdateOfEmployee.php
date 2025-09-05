<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileUpdateOfEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        // Add your fields here, e.g.:
        'employee_id',
        'field_name',
        'old_value',
        'new_value',
        'status',
        // Add more fields as needed
    ];
}
