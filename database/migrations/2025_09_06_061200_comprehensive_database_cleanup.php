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
        // Disable foreign key checks to allow dropping tables with constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Drop unnecessary tables identified from database analysis
        
        // Training related unused tables
        Schema::dropIfExists('employee_trainings');
        Schema::dropIfExists('trainings');
        Schema::dropIfExists('my_trainings');
        Schema::dropIfExists('employee_my_trainings');
        
        // Succession planning tables that may be unused
        // Drop child tables first to avoid foreign key constraint issues
        Schema::dropIfExists('succession_assessments');
        Schema::dropIfExists('succession_development_activities');
        Schema::dropIfExists('succession_history');
        Schema::dropIfExists('succession_readiness_ratings');
        Schema::dropIfExists('succession_scenarios');
        Schema::dropIfExists('succession_simulations');
        Schema::dropIfExists('succession_candidates'); // Drop this last
        
        // Other potentially unused tables
        Schema::dropIfExists('training_notifications');
        Schema::dropIfExists('training_progress');
        // Don't drop training_record_certificate_tracking as it's needed
        // Schema::dropIfExists('training_record_certificate_tracking');
        Schema::dropIfExists('training_reviews');
        
        // Clean up any test or temporary tables
        Schema::dropIfExists('test_table');
        Schema::dropIfExists('temp_table');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This is a destructive operation
        // Tables can be recreated from their original migrations if needed
        // We're not recreating them here to avoid complexity
        
        // If you need to rollback, restore from backup or run original migrations
    }
};
