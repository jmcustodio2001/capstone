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
        // Drop and recreate the table with correct schema
        Schema::dropIfExists('training_record_certificate_tracking');
        
        Schema::create('training_record_certificate_tracking', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_id', 50); // String to match Employee model
            $table->unsignedBigInteger('course_id');
            $table->date('training_date');
            $table->string('certificate_number')->nullable();
            $table->date('certificate_expiry')->nullable();
            $table->string('certificate_url')->nullable();
            $table->string('status')->default('Active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Add foreign key constraints
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_record_certificate_tracking');
    }
};
