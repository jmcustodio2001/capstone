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
        if (Schema::hasTable('training_requests') && !Schema::hasColumn('training_requests', 'course_id')) {
            Schema::table('training_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('course_id')->nullable()->after('employee_id');
                if (Schema::hasTable('course_management')) {
                    $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_requests', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });
    }
};
