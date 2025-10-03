<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the table exists and if employee_id is currently an integer
        if (Schema::hasTable('upcoming_trainings')) {
            // Drop foreign key constraint if it exists
            try {
                Schema::table('upcoming_trainings', function (Blueprint $table) {
                    $table->dropForeign(['employee_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Change employee_id from unsignedBigInteger to string to match employees table
            Schema::table('upcoming_trainings', function (Blueprint $table) {
                $table->string('employee_id', 20)->change();
            });
            
            // Add foreign key constraint to employees table
            Schema::table('upcoming_trainings', function (Blueprint $table) {
                $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('upcoming_trainings')) {
            // Drop foreign key constraint
            try {
                Schema::table('upcoming_trainings', function (Blueprint $table) {
                    $table->dropForeign(['employee_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Change back to unsignedBigInteger
            Schema::table('upcoming_trainings', function (Blueprint $table) {
                $table->unsignedBigInteger('employee_id')->change();
            });
        }
    }
};
