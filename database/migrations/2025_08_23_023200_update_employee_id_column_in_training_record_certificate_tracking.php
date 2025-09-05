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
        Schema::table('training_record_certificate_tracking', function (Blueprint $table) {
            // Change employee_id from unsignedBigInteger to string to match Employee model
            $table->string('employee_id', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_record_certificate_tracking', function (Blueprint $table) {
            // Revert back to unsignedBigInteger
            $table->unsignedBigInteger('employee_id')->change();
        });
    }
};
