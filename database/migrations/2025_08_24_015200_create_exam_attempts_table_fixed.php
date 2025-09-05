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
        // Drop table if exists to avoid conflicts
        Schema::dropIfExists('exam_attempts');
        
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20); // Match employees table type
            $table->unsignedBigInteger('course_id');
            $table->enum('type', ['exam', 'quiz']);
            $table->integer('attempt_number')->default(1);
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('total_questions')->nullable();
            $table->integer('correct_answers')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'failed'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('answers')->nullable();
            $table->timestamps();

            // Add indexes first
            $table->index('employee_id');
            $table->index('course_id');
        });
        
        // Add foreign keys separately to avoid constraint issues
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
