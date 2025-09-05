<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerServiceSalesSkillsTraining extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customer_service_sales_skills_training';
    protected $primaryKey = 'id';

    protected $fillable = [
        'employee_id',
        'training_id',
        'date_completed',
    ];

    protected $dates = ['date_completed', 'deleted_at'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function training()
    {
        return $this->belongsTo(EmployeeTrainingDashboard::class, 'training_id', 'id');
    }
}
