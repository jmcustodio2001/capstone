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
        Schema::table('employee_competency_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_competency_profiles', 'created_from_api_skills')) {
                $table->boolean('created_from_api_skills')->default(false)->after('assessment_date')
                    ->comment('Marks if profile was auto-created from API employee skills');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_competency_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('employee_competency_profiles', 'created_from_api_skills')) {
                $table->dropColumn('created_from_api_skills');
            }
        });
    }
};
