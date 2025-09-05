<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_service_sales_skills_training', function (Blueprint $table) {
            // Drop unused columns that don't match the form
            $table->dropColumn(['skill_topic', 'description']);
            
            // Update foreign key to reference employee_training_dashboards instead of trainings
            $table->dropForeign(['training_id']);
            $table->foreign('training_id')->references('id')->on('employee_training_dashboards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_service_sales_skills_training', function (Blueprint $table) {
            // Add back the columns
            $table->string('skill_topic')->after('date_completed');
            $table->text('description')->nullable()->after('skill_topic');
            
            // Restore original foreign key
            $table->dropForeign(['training_id']);
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
        });
    }
};
