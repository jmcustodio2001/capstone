<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('completed_trainings', function (Blueprint $table) {
            $table->id('completed_id');
            $table->string('employee_id', 20);
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->string('training_title');
            $table->date('completion_date');
            $table->text('remarks')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('status')->default('Pending');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('completed_trainings');
    }
};
