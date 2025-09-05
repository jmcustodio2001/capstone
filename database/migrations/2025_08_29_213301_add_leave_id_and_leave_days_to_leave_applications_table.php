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
        Schema::table('leave_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_applications', 'leave_id')) {
                $table->string('leave_id')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('leave_applications', 'leave_days')) {
                $table->integer('leave_days')->nullable()->after('leave_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            if (Schema::hasColumn('leave_applications', 'leave_id')) {
                $table->dropColumn('leave_id');
            }
            if (Schema::hasColumn('leave_applications', 'leave_days')) {
                $table->dropColumn('leave_days');
            }
        });
    }
};
