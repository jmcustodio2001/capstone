<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop the problematic view first
        DB::statement('DROP VIEW IF EXISTS destination_knowledge_training');
        
        // 2. Drop unnecessary and error tables
        $tablesToDrop = [
            'employee_trainings',
            'trainings',
            'my_trainings',
            'employee_my_trainings',
            'succession_assessments',
            'succession_candidates',
            'succession_development_activities',
            'succession_history',
            'succession_readiness_ratings',
            'succession_scenarios',
            'succession_simulations',
            'training_notifications',
            'training_progress',
            'training_record_certificate_tracking',
            'training_reviews'
        ];
        
        foreach ($tablesToDrop as $table) {
            Schema::dropIfExists($table);
        }
        
        // 3. Check and fix foreign key constraints
        // Drop foreign keys that reference non-existent tables
        try {
            // Fix competency_course_assignments if it has invalid references
            if (Schema::hasTable('competency_course_assignments')) {
                Schema::table('competency_course_assignments', function (Blueprint $table) {
                    $table->dropForeign(['course_id']);
                });
            }
        } catch (Exception $e) {
            // Foreign key might not exist, continue
        }
        
        // 4. Clean up orphaned records in remaining tables
        // This will be done via raw SQL to handle potential issues
        
        // 5. Drop the old view - no longer needed, using table directly
        DB::statement('DROP VIEW IF EXISTS destination_knowledge_training');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the view
        DB::statement('DROP VIEW IF EXISTS destination_knowledge_training');
        
        // Note: We won't recreate the dropped tables as they were unnecessary
        // If needed, they can be recreated from their original migrations
    }
};
