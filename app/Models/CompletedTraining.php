<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class CompletedTraining extends Model {
    use HasFactory;
    protected $table = 'completed_trainings';
    protected $primaryKey = 'completed_id';
    protected $fillable = [
        'employee_id', 'training_title', 'completion_date', 'remarks', 'certificate_path', 'status', 'course_id'
    ];

    public function employee() {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function course() {
        return $this->belongsTo(CourseManagement::class, 'course_id', 'course_id');
    }
}
