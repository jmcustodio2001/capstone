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
        // Drop existing table if it exists
        Schema::dropIfExists('attendance_time_logs');
        
        // Create table with correct structure including all additional columns
        Schema::create('attendance_time_logs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('employee_id');
            $table->date('log_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->time('break_start_time')->nullable();
            $table->time('break_end_time')->nullable();
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->string('status', 50)->nullable();
            $table->string('location')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'log_date']);
            $table->index('log_date');
        });
        
        // Sample data insertion removed - table should be empty for new employee accounts
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_time_logs');
    }
};
