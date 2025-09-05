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
        
        // Create table with correct structure
        Schema::create('attendance_time_logs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('employee_id');
            $table->date('log_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'log_date']);
            $table->index('log_date');
        });
        
        // Insert sample data
        DB::table('attendance_time_logs')->insert([
            [
                'employee_id' => 'EMP001',
                'log_date' => '2025-08-27',
                'time_in' => '08:45:00',
                'time_out' => '17:15:00',
                'hours_worked' => 8.50,
                'status' => 'Present',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 'EMP001',
                'log_date' => '2025-08-28',
                'time_in' => '09:15:00',
                'time_out' => '17:30:00',
                'hours_worked' => 8.25,
                'status' => 'Late',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_time_logs');
    }
};
