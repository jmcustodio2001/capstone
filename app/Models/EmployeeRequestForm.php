<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRequestForm extends Model
{
    use HasFactory;

    protected $fillable = [
        // Add your fields here, e.g.:
        'employee_id',
        'request_type',
        'request_details',
        'status',
        // Add more fields as needed
    ];
}
