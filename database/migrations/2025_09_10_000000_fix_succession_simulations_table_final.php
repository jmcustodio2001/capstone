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
        // Drop existing table if it exists to start fresh
        Schema::dropIfExists('succession_simulations');

        // Create the table with the complete structure matching the model
        Schema::create('succession_simulations', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('position_id')->nullable();
            $table->string('simulation_name');
            $table->enum('simulation_type', ['leadership', 'technical', 'management', 'strategic'])->default('leadership');
            $table->text('scenario_description')->nullable();
            $table->date('simulation_date');
            $table->decimal('duration_hours', 4, 2)->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->enum('performance_rating', ['excellent', 'good', 'satisfactory', 'needs_improvement', 'poor'])->nullable();
            $table->json('competencies_assessed')->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('assessor_id')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            
            // Legacy field for backward compatibility
            $table->text('simulation_result')->nullable();
            
            $table->timestamps();

            // Add indexes for better performance
            $table->index('employee_id');
            $table->index('position_id');
            $table->index('simulation_date');
            $table->index('status');
            $table->index('assessor_id');
            $table->index('created_at');

            // Foreign key constraints
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('succession_simulations');
    }
};
