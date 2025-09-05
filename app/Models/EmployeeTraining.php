<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class EmployeeTraining extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id', 'training_title', 'training_date', 'status', 'progress', 'feedback', 'notification_type', 'notification_message'
    ];
    public $timestamps = false;
}
