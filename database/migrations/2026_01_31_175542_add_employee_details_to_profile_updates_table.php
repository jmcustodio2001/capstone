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
        Schema::table('profile_updates', function (Blueprint $table) {
            $table->string('employee_name')->nullable()->after('employee_id');
            $table->string('employee_email')->nullable()->after('employee_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_updates', function (Blueprint $table) {
            $table->dropColumn(['employee_name', 'employee_email']);
        });
    }
};
