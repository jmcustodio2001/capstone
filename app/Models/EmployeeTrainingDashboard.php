<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class EmployeeTrainingDashboard extends Model
{
    use HasFactory;

    protected $table = 'employee_training_dashboards'; // Explicitly set table name
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'course_id',
        'training_title',
        'training_date',
        'status',
        'remarks',
        'progress',
        'last_accessed',
        'assigned_by',
        'expired_date',
        'source',
    ];

    protected $casts = [
        'expired_date' => 'datetime',
        'training_date' => 'datetime',
        'last_accessed' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($training) {
            // Automatically set expiration date to 90 days from now when creating training records
            if (!$training->expired_date) {
                $training->expired_date = Carbon::now()->addDays(90);
            }
            
            // Auto-populate training_title from course if missing
            if (!$training->training_title && $training->course_id) {
                $course = \App\Models\CourseManagement::find($training->course_id);
                if ($course) {
                    $training->training_title = $course->course_title;
                }
            }
        });
        
        static::retrieved(function ($training) {
            // Auto-populate training_title from course if missing when retrieving records
            if (!$training->training_title && $training->course_id && $training->course) {
                $training->training_title = $training->course->course_title;
                $training->save();
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(CourseManagement::class, 'course_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get training title - prioritize training_title field, fallback to course title
     */
    public function getTrainingTitleAttribute($value)
    {
        // If training_title exists, use it
        if ($value) {
            return $value;
        }
        
        // If no training_title but has course relationship, use course title
        if ($this->course && $this->course->course_title) {
            return $this->course->course_title;
        }
        
        // Fallback
        return 'Training Course';
    }

    /**
     * Fix missing training titles for existing records
     */
    public static function fixMissingTrainingTitles()
    {
        $updated = 0;
        $records = self::with('course')
            ->whereNull('training_title')
            ->whereNotNull('course_id')
            ->get();
            
        foreach ($records as $record) {
            if ($record->course && $record->course->course_title) {
                $record->update(['training_title' => $record->course->course_title]);
                $updated++;
            }
        }
        
        return $updated;
    }

    /**
     * Consolidate duplicate employee_training_dashboard tables
     * Merges data from singular table to plural table and drops the duplicate
     */
    public static function consolidateDuplicateTables()
    {
        try {
            $db = DB::connection();
            
            // Check if both tables exist
            $singularExists = $db->select("SHOW TABLES LIKE 'employee_training_dashboard'");
            $pluralExists = $db->select("SHOW TABLES LIKE 'employee_training_dashboards'");
            
            if (empty($singularExists)) {
                return ['status' => 'success', 'message' => 'No duplicate table found. Only employee_training_dashboards exists.'];
            }
            
            if (empty($pluralExists)) {
                return ['status' => 'error', 'message' => 'Main table employee_training_dashboards does not exist.'];
            }
            
            // Get data from singular table
            $singularData = $db->select("SELECT * FROM employee_training_dashboard");
            $mergedCount = 0;
            $skippedCount = 0;
            
            foreach ($singularData as $record) {
                // Check if record already exists in plural table to avoid duplicates
                $exists = $db->select("
                    SELECT id FROM employee_training_dashboards 
                    WHERE employee_id = ? AND course_id = ? AND training_date = ?
                ", [$record->employee_id, $record->course_id, $record->training_date]);
                
                if (empty($exists)) {
                    // Insert record into plural table
                    $db->insert("
                        INSERT INTO employee_training_dashboards 
                        (employee_id, course_id, training_title, training_date, status, remarks, progress, last_accessed, assigned_by, expired_date, source, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ", [
                        $record->employee_id,
                        $record->course_id,
                        $record->training_title ?? null,
                        $record->training_date,
                        $record->status ?? 'Not Started',
                        $record->remarks,
                        $record->progress ?? 0,
                        $record->last_accessed,
                        $record->assigned_by ?? null,
                        $record->expired_date ?? null,
                        $record->source ?? 'migrated',
                        $record->created_at ?? now(),
                        $record->updated_at ?? now()
                    ]);
                    $mergedCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            // Drop the singular table after successful merge
            $db->statement("DROP TABLE employee_training_dashboard");
            
            return [
                'status' => 'success', 
                'message' => "Successfully consolidated tables. Merged: {$mergedCount} records, Skipped duplicates: {$skippedCount}. Dropped duplicate table.",
                'merged' => $mergedCount,
                'skipped' => $skippedCount
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error', 
                'message' => 'Error consolidating tables: ' . $e->getMessage()
            ];
        }
    }
}
