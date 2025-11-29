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
     * Fix missing columns in the training_record_certificate_tracking table
     * This method ensures all required columns exist with proper structure
     */
    public static function fixMissingColumns()
    {
        try {
            $results = [];
            
            // Get current table structure
            $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM training_record_certificate_tracking");
            $existingColumns = array_column($columns, 'Field');
            
            // Define required columns with their SQL definitions
            $requiredColumns = [
                'training_date' => "ADD COLUMN `training_date` DATE DEFAULT NULL AFTER `course_id`",
                'certificate_number' => "ADD COLUMN `certificate_number` VARCHAR(255) DEFAULT NULL AFTER `training_date`",
                'certificate_expiry' => "ADD COLUMN `certificate_expiry` DATE DEFAULT NULL AFTER `certificate_number`",
                'certificate_url' => "ADD COLUMN `certificate_url` VARCHAR(255) DEFAULT NULL AFTER `certificate_expiry`",
                'issue_date' => "ADD COLUMN `issue_date` DATE DEFAULT NULL AFTER `certificate_url`",
                'status' => "ADD COLUMN `status` VARCHAR(255) DEFAULT 'Active' AFTER `issue_date`",
                'remarks' => "ADD COLUMN `remarks` TEXT DEFAULT NULL AFTER `status`"
            ];
            
            // Add missing columns
            foreach ($requiredColumns as $columnName => $alterStatement) {
                if (!in_array($columnName, $existingColumns)) {
                    try {
                        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `training_record_certificate_tracking` " . $alterStatement);
                        $results[] = "Added column: {$columnName}";
                    } catch (\Exception $e) {
                        $results[] = "Failed to add column {$columnName}: " . $e->getMessage();
                    }
                }
            }
            
            // Update existing records with default values
            if (!empty($results)) {
                // Set default training_date for existing records
                \Illuminate\Support\Facades\DB::statement("
                    UPDATE `training_record_certificate_tracking` 
                    SET `training_date` = DATE(COALESCE(`created_at`, NOW())) 
                    WHERE `training_date` IS NULL
                ");
                
                // Set default certificate_expiry (2 years from training_date or creation)
                \Illuminate\Support\Facades\DB::statement("
                    UPDATE `training_record_certificate_tracking` 
                    SET `certificate_expiry` = DATE_ADD(COALESCE(`training_date`, DATE(`created_at`), NOW()), INTERVAL 2 YEAR) 
                    WHERE `certificate_expiry` IS NULL
                ");
                
                // Set default issue_date
                \Illuminate\Support\Facades\DB::statement("
                    UPDATE `training_record_certificate_tracking` 
                    SET `issue_date` = COALESCE(`training_date`, DATE(`created_at`), NOW()) 
                    WHERE `issue_date` IS NULL
                ");
                
                // Set default status
                \Illuminate\Support\Facades\DB::statement("
                    UPDATE `training_record_certificate_tracking` 
                    SET `status` = 'Active' 
                    WHERE `status` IS NULL OR `status` = ''
                ");
            }
            
            return [
                'success' => true,
                'message' => 'Successfully fixed table structure. Changes: ' . implode(', ', $results),
                'action' => 'columns_fixed',
                'changes' => $results
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fixing table structure: ' . $e->getMessage(),
                'action' => 'error'
            ];
        }
    }

    /**
     * Fix missing training_date column using direct SQL
     * Call this method to add the missing column that's causing SQLSTATE[42S22] error
     * @deprecated Use fixMissingColumns() instead
     */
    public static function fixMissingTrainingDateColumn()
    {
        return self::fixMissingColumns();
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

    /**
     * Check if all required columns exist in the table
     */
    public static function hasAllRequiredColumns()
    {
        try {
            $requiredColumns = [
                'employee_id', 'course_id', 'training_date', 'certificate_number', 
                'certificate_expiry', 'certificate_url', 'issue_date', 'status', 'remarks'
            ];
            
            foreach ($requiredColumns as $column) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('training_record_certificate_tracking', $column)) {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get missing columns from the table
     */
    public static function getMissingColumns()
    {
        try {
            $requiredColumns = [
                'employee_id', 'course_id', 'training_date', 'certificate_number', 
                'certificate_expiry', 'certificate_url', 'issue_date', 'status', 'remarks'
            ];
            
            $missingColumns = [];
            
            foreach ($requiredColumns as $column) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('training_record_certificate_tracking', $column)) {
                    $missingColumns[] = $column;
                }
            }
            
            return $missingColumns;
        } catch (\Exception $e) {
            return [];
        }
    }
}
