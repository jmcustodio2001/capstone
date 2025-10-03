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
            if (Schema::hasColumn('customer_service_sales_skills_training', 'skill_topic')) {
                $table->dropColumn('skill_topic');
            }
            if (Schema::hasColumn('customer_service_sales_skills_training', 'description')) {
                $table->dropColumn('description');
            }
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
            
            // Don't recreate foreign key to trainings table since it doesn't exist
            // The trainings table has been dropped in the database cleanup
        });
    }
};
