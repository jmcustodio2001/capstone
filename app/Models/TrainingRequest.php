<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TrainingRequest extends Model {
    use HasFactory;
    protected $table = 'training_requests';
    protected $primaryKey = 'request_id';
    protected $fillable = [
        'employee_id', 'course_id', 'training_title', 'reason', 'status', 'requested_date'
    ];

    public function course()
    {
        return $this->belongsTo(\App\Models\CourseManagement::class, 'course_id', 'course_id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id', 'employee_id');
    }
}
