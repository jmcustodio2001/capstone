<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MyTraining extends Model
{
    use HasFactory;
    protected $table = 'employee_my_trainings';
    protected $fillable = [
        'employee_id', 'training_title', 'training_date', 'status', 'progress', 'feedback', 'notification_type', 'notification_message'
    ];
    public $timestamps = false;
}
