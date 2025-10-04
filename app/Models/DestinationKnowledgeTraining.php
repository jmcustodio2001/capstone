<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Training;

class DestinationKnowledgeTraining extends Model
{
    use HasFactory;

    // Point to the correct destination knowledge training table
    protected $table = 'destination_knowledge_trainings';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'employee_id',
        'course_id',
        'training_type',
        'training_title',
        'destination_name',
        'delivery_mode',
        'details',
        'objectives',
        'duration',
        'training_date',
        'date_completed',
        'progress',
        'status',
        'remarks',
        'last_accessed',
        'assigned_by',
        'expired_date',
        'is_active',
        'admin_approved_for_upcoming',
        'source',
    ];

    protected $casts = [
        'training_date' => 'date',
        'expired_date' => 'datetime',
        'last_accessed' => 'datetime',
        'is_active' => 'boolean',
        'admin_approved_for_upcoming' => 'boolean',
    ];

    // Scope to only get destination training records
    public function scopeDestinationTrainings($query)
    {
        return $query->where('training_type', 'destination');
    }

    // Automatically set training_type when creating destination trainings
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->training_type) {
                $model->training_type = 'destination';
            }
            if (!$model->source) {
                $model->source = 'destination_knowledge_training';
            }
        });
    }

    // Fix missing columns in existing database table
    public static function fixMissingColumns()
    {
        try {
            // Check if columns exist and add them if missing
            if (!Schema::hasColumn('destination_knowledge_trainings', 'training_type')) {
                Schema::table('destination_knowledge_trainings', function ($table) {
                    $table->string('training_type')->default('destination')->after('progress');
                });
            }

            if (!Schema::hasColumn('destination_knowledge_trainings', 'source')) {
                Schema::table('destination_knowledge_trainings', function ($table) {
                    $table->string('source')->default('destination_knowledge_training')->after('training_type');
                });
            }

            if (!Schema::hasColumn('destination_knowledge_trainings', 'training_title')) {
                Schema::table('destination_knowledge_trainings', function ($table) {
                    $table->string('training_title')->nullable()->after('destination_name');
                });
            }

            if (!Schema::hasColumn('destination_knowledge_trainings', 'delivery_mode')) {
                Schema::table('destination_knowledge_trainings', function ($table) {
                    $table->string('delivery_mode')->nullable()->after('details');
                });
            }

            if (!Schema::hasColumn('destination_knowledge_trainings', 'expired_date')) {
                Schema::table('destination_knowledge_trainings', function ($table) {
                    $table->date('expired_date')->nullable()->after('date_completed');
                });
            }

            if (!Schema::hasColumn('destination_knowledge_trainings', 'status')) {
                Schema::table('destination_knowledge_trainings', function ($table) {
                    $table->string('status')->default('not-started')->after('progress');
                });
            }

            if (!Schema::hasColumn('destination_knowledge_trainings', 'is_active')) {
                Schema::table('destination_knowledge_trainings', function ($table) {
                    $table->boolean('is_active')->default(true)->after('status');
                });
            }

            if (!Schema::hasColumn('destination_knowledge_trainings', 'admin_approved_for_upcoming')) {
                Schema::table('destination_knowledge_trainings', function ($table) {
                    $table->boolean('admin_approved_for_upcoming')->default(false)->after('is_active');
                });
            }

            // Update existing records to have proper values
            DB::table('destination_knowledge_trainings')
                ->whereNull('training_type')
                ->orWhere('training_type', '')
                ->update([
                    'training_type' => 'destination',
                    'source' => 'destination_knowledge_training'
                ]);

            // Update training_title from destination_name if missing
            DB::table('destination_knowledge_trainings')
                ->whereNull('training_title')
                ->orWhere('training_title', '')
                ->update([
                    'training_title' => DB::raw('destination_name')
                ]);

            return [
                'success' => true,
                'message' => 'Missing columns added successfully to destination_knowledge_trainings table'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error adding missing columns: ' . $e->getMessage()
            ];
        }
    }

    // Simplified consolidation - just ensure table structure is correct
    public static function consolidateDestinationTraining()
    {
        try {
            // Drop the old view if it exists
            DB::statement('DROP VIEW IF EXISTS destination_knowledge_training');
            
            // Ensure all records have proper training_type and source
            DB::table('destination_knowledge_trainings')
                ->whereNull('training_type')
                ->orWhere('training_type', '')
                ->update([
                    'training_type' => 'destination',
                    'source' => 'destination_knowledge_training'
                ]);

            // Update training_title from destination_name if missing
            DB::table('destination_knowledge_trainings')
                ->whereNull('training_title')
                ->orWhere('training_title', '')
                ->update([
                    'training_title' => DB::raw('destination_name')
                ]);

            return [
                'success' => true,
                'message' => 'Destination training consolidated successfully - using single table approach'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error consolidating: ' . $e->getMessage()
            ];
        }
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(CourseManagement::class, 'course_id');
    }

}
