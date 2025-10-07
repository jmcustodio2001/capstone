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
        // Only add columns if the table exists
        if (Schema::hasTable('attendance_time_logs')) {
            Schema::table('attendance_time_logs', function (Blueprint $table) {
                // Check if columns don't already exist before adding them
                if (!Schema::hasColumn('attendance_time_logs', 'break_start_time')) {
                    $table->time('break_start_time')->nullable()->after('time_out');
                }
                if (!Schema::hasColumn('attendance_time_logs', 'break_end_time')) {
                    $table->time('break_end_time')->nullable()->after('break_start_time');
                }
                if (!Schema::hasColumn('attendance_time_logs', 'total_hours')) {
                    $table->decimal('total_hours', 5, 2)->nullable()->after('break_end_time');
                }
                if (!Schema::hasColumn('attendance_time_logs', 'overtime_hours')) {
                    $table->decimal('overtime_hours', 5, 2)->default(0)->after('total_hours');
                }
                if (!Schema::hasColumn('attendance_time_logs', 'location')) {
                    $table->string('location')->nullable()->after('status');
                }
                if (!Schema::hasColumn('attendance_time_logs', 'ip_address')) {
                    $table->string('ip_address')->nullable()->after('location');
                }
                if (!Schema::hasColumn('attendance_time_logs', 'notes')) {
                    $table->text('notes')->nullable()->after('ip_address');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop columns if the table exists
        if (Schema::hasTable('attendance_time_logs')) {
            Schema::table('attendance_time_logs', function (Blueprint $table) {
                $columnsToCheck = [
                    'break_start_time',
                    'break_end_time', 
                    'total_hours',
                    'overtime_hours',
                    'location',
                    'ip_address',
                    'notes'
                ];
                
                $columnsToDrop = [];
                foreach ($columnsToCheck as $column) {
                    if (Schema::hasColumn('attendance_time_logs', $column)) {
                        $columnsToDrop[] = $column;
                    }
                }
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
