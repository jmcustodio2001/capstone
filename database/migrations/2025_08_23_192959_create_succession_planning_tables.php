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
        // Table for organizational positions/roles
        Schema::create('organizational_positions', function (Blueprint $table) {
            $table->id();
            $table->string('position_name');
            $table->string('position_code')->unique();
            $table->text('description')->nullable();
            $table->string('department');
            $table->string('level'); // executive, senior, manager, supervisor
            $table->integer('hierarchy_level'); // 1=CEO, 2=C-level, 3=Director, etc.
            $table->unsignedBigInteger('reports_to')->nullable();
            $table->json('required_competencies')->nullable(); // competency IDs and levels
            $table->integer('min_experience_years')->default(0);
            $table->decimal('min_readiness_score', 5, 2)->default(75.00);
            $table->boolean('is_critical_position')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('reports_to')->references('id')->on('organizational_positions')->onDelete('set null');
        });

        // Table for succession candidates
        Schema::create('succession_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->unsignedBigInteger('target_position_id');
            $table->decimal('readiness_score', 5, 2)->default(0.00);
            $table->string('readiness_level'); // ready, developing, potential
            $table->date('target_ready_date')->nullable();
            $table->text('development_plan')->nullable();
            $table->json('competency_gaps')->nullable(); // gaps analysis
            $table->json('strengths')->nullable();
            $table->json('development_areas')->nullable();
            $table->string('status')->default('active'); // active, inactive, promoted
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('target_position_id')->references('id')->on('organizational_positions')->onDelete('cascade');
            $table->unique(['employee_id', 'target_position_id']);
        });

        // Table for succession scenarios/simulations
        Schema::create('succession_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_name');
            $table->string('scenario_type'); // resignation, promotion, restructuring, growth
            $table->text('description');
            $table->json('affected_positions'); // position IDs
            $table->string('impact_level'); // high, medium, low
            $table->integer('estimated_timeline_days')->nullable();
            $table->json('simulation_results')->nullable();
            $table->decimal('success_probability', 5, 2)->nullable();
            $table->text('recommendations')->nullable();
            $table->string('status')->default('draft'); // draft, active, completed
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        // Table for succession readiness assessments
        Schema::create('succession_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_id');
            $table->string('assessment_type'); // competency, performance, potential
            $table->json('assessment_data'); // detailed scores and feedback
            $table->decimal('overall_score', 5, 2);
            $table->date('assessment_date');
            $table->string('assessor_id')->nullable();
            $table->text('feedback')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
            
            $table->foreign('candidate_id')->references('id')->on('succession_candidates')->onDelete('cascade');
            $table->foreign('assessor_id')->references('employee_id')->on('employees')->onDelete('set null');
        });

        // Table for succession development activities
        Schema::create('succession_development_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_id');
            $table->string('activity_type'); // training, mentoring, project, rotation
            $table->string('activity_name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('target_completion_date');
            $table->date('actual_completion_date')->nullable();
            $table->string('status')->default('planned'); // planned, in_progress, completed, cancelled
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->json('competencies_targeted')->nullable(); // competency IDs
            $table->text('outcomes')->nullable();
            $table->string('assigned_by')->nullable();
            $table->timestamps();
            
            $table->foreign('candidate_id')->references('id')->on('succession_candidates')->onDelete('cascade');
            $table->foreign('assigned_by')->references('employee_id')->on('employees')->onDelete('set null');
        });

        // Table for succession planning history/audit trail
        Schema::create('succession_history', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // candidate, position, scenario
            $table->unsignedBigInteger('entity_id');
            $table->string('action'); // created, updated, promoted, removed
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('reason')->nullable();
            $table->string('performed_by');
            $table->timestamp('performed_at');
            $table->timestamps();
            
            $table->foreign('performed_by')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('succession_history');
        Schema::dropIfExists('succession_development_activities');
        Schema::dropIfExists('succession_assessments');
        Schema::dropIfExists('succession_scenarios');
        Schema::dropIfExists('succession_candidates');
        Schema::dropIfExists('organizational_positions');
    }
};
