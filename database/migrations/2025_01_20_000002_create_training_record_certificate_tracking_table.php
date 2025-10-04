<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('training_record_certificate_tracking');
        
        Schema::create('training_record_certificate_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->date('training_date')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('certificate_expiry')->nullable();
            $table->string('certificate_url')->nullable();
            $table->string('status')->default('Active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index('employee_id');
            $table->index('course_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_record_certificate_tracking');
    }
};