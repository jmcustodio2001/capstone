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
        'issue_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'training_date' => 'datetime',
        'certificate_expiry' => 'datetime',
        'issue_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id', 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\CourseManagement::class, 'course_id', 'course_id');
    }

    /**
     * Fix missing training_date column using direct SQL
     * Call this method to add the missing column that's causing SQLSTATE[42S22] error
     */
    public static function fixMissingTrainingDateColumn()
    {
        try {
            // Use raw SQL to add the missing column
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE `training_record_certificate_tracking` 
                ADD COLUMN IF NOT EXISTS `training_date` DATE NOT NULL DEFAULT '2025-01-01' 
                AFTER `course_id`
            ");
            
            // Update existing records with proper dates
            \Illuminate\Support\Facades\DB::statement("
                UPDATE `training_record_certificate_tracking` 
                SET `training_date` = DATE(COALESCE(`created_at`, NOW())) 
                WHERE `training_date` = '2025-01-01' OR `training_date` IS NULL
            ");
            
            return [
                'success' => true,
                'message' => 'Successfully added training_date column to training_record_certificate_tracking table',
                'action' => 'column_added'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fixing training_date column: ' . $e->getMessage(),
                'action' => 'error'
            ];
        }
    }

    /**
     * Check if the training_date column exists in the database
     */
    public static function hasTrainingDateColumn()
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn('training_record_certificate_tracking', 'training_date');
        } catch (\Exception $e) {
            return false;
        }
    }
}
