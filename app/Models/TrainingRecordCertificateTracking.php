<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingRecordCertificateTracking extends Model
{
    use HasFactory;

    protected $table = 'training_record_certificate_tracking';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'course_id',
        'training_date',
        'certificate_number',
        'certificate_expiry',
        'certificate_url',
        'status',
        'remarks',
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\CourseManagement::class, 'course_id', 'course_id');
    }
}
