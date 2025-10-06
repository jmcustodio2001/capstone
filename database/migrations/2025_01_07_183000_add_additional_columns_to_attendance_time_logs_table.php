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
        Schema::table('attendance_time_logs', function (Blueprint $table) {
            $table->time('break_start_time')->nullable()->after('time_out');
            $table->time('break_end_time')->nullable()->after('break_start_time');
            $table->decimal('total_hours', 5, 2)->nullable()->after('break_end_time');
            $table->decimal('overtime_hours', 5, 2)->default(0)->after('total_hours');
            $table->string('location')->nullable()->after('status');
            $table->string('ip_address')->nullable()->after('location');
            $table->text('notes')->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_time_logs', function (Blueprint $table) {
            $table->dropColumn([
                'break_start_time',
                'break_end_time', 
                'total_hours',
                'overtime_hours',
                'location',
                'ip_address',
                'notes'
            ]);
        });
    }
};
