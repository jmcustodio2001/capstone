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
        if (!Schema::hasTable('training_feedback')) {
            Schema::create('training_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('feedback_id')->nullable();
            $table->string('employee_id')->nullable();
            $table->integer('course_id')->nullable();
            $table->string('training_title')->nullable();
            $table->integer('overall_rating')->nullable();
            $table->integer('content_quality')->nullable();
            $table->integer('instructor_effectiveness')->nullable();
            $table->integer('material_relevance')->nullable();
            $table->integer('training_duration')->nullable();
            $table->text('what_learned')->nullable();
            $table->text('most_valuable')->nullable();
            $table->text('improvements')->nullable();
            $table->text('additional_topics')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('recommend_training')->default(false);
            $table->string('training_format')->nullable();
            $table->date('training_completion_date')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->boolean('admin_reviewed')->default(false);
            $table->datetime('reviewed_at')->nullable();
            $table->text('admin_response')->nullable();
            $table->text('action_taken')->nullable();
            $table->datetime('response_date')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('employee_id');
            $table->index('course_id');
            $table->index('overall_rating');
            $table->index('admin_reviewed');
            $table->index('submitted_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_feedback');
    }
};
