<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_service_sales_skills_training', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->unsignedBigInteger('training_id')->nullable(); // Make nullable since trainings table will be dropped
            $table->date('date_completed')->nullable();
            $table->string('skill_topic');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            // Don't create foreign key to trainings table since it will be dropped
            // $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_service_sales_skills_training');
    }
};
