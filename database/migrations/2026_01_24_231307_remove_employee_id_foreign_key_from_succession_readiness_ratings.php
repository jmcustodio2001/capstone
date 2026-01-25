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
        Schema::table('succession_readiness_ratings', function (Blueprint $table) {
            // Drop the foreign key constraint
            try {
                $table->dropForeign(['employee_id']);
            } catch (\Exception $e) {
                // Ignore if constraint doesn't exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('succession_readiness_ratings', function (Blueprint $table) {
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }
};
