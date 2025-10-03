
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
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            // Add columns to support destination trainings (check if they don't exist first)
            if (!Schema::hasColumn('employee_training_dashboards', 'training_type')) {
                $table->enum('training_type', ['course', 'destination', 'competency', 'other'])->default('course')->after('course_id');
            }
            if (!Schema::hasColumn('employee_training_dashboards', 'training_title')) {
                $table->string('training_title')->nullable()->after('training_type');
            }
            if (!Schema::hasColumn('employee_training_dashboards', 'destination_name')) {
                $table->string('destination_name')->nullable()->after('training_title');
            }
            if (!Schema::hasColumn('employee_training_dashboards', 'delivery_mode')) {
                $table->string('delivery_mode')->nullable()->after('destination_name');
            }
            if (!Schema::hasColumn('employee_training_dashboards', 'details')) {
                $table->text('details')->nullable()->after('delivery_mode');
            }
            if (!Schema::hasColumn('employee_training_dashboards', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('details');
            }
            if (!Schema::hasColumn('employee_training_dashboards', 'admin_approved_for_upcoming')) {
                $table->boolean('admin_approved_for_upcoming')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('employee_training_dashboards', 'source')) {
                $table->string('source')->nullable()->after('admin_approved_for_upcoming');
            }
        });
        
        // Make course_id nullable in separate statement
        DB::statement('ALTER TABLE employee_training_dashboards MODIFY course_id BIGINT(20) UNSIGNED NULL');
        
        // Add indexes for better performance (only if they don't exist)
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEX FROM employee_training_dashboards"))->pluck('Key_name')->toArray();
            
            if (!in_array('employee_training_dashboards_training_type_index', $indexes)) {
                $table->index('training_type');
            }
            if (!in_array('employee_training_dashboards_destination_name_index', $indexes)) {
                $table->index('destination_name');
            }
            if (!in_array('employee_training_dashboards_delivery_mode_index', $indexes)) {
                $table->index('delivery_mode');
            }
            if (!in_array('employee_training_dashboards_is_active_index', $indexes)) {
                $table->index('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            $table->dropIndex(['training_type']);
            $table->dropIndex(['destination_name']);
            $table->dropIndex(['delivery_mode']);
            $table->dropIndex(['is_active']);
            
            $table->dropColumn([
                'training_type',
                'training_title',
                'destination_name',
                'delivery_mode',
                'details',
                'is_active',
                'admin_approved_for_upcoming',
                'source'
            ]);
            
            // Restore course_id as not nullable
            $table->unsignedBigInteger('course_id')->nullable(false)->change();
        });
    }
};
