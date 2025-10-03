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
        // Force create table even if it exists (for migrate:fresh)
        Schema::dropIfExists('succession_readiness_ratings');
        
        Schema::create('succession_readiness_ratings', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->integer('readiness_score')->default(0);
            $table->string('readiness_level')->nullable();
            $table->text('assessment_notes')->nullable();
            $table->date('assessment_date')->nullable();
            $table->string('assessed_by')->nullable();
            $table->timestamps();
            
            $table->index('employee_id');
            $table->index('readiness_score');
            $table->index('readiness_level');
        });
        
        // Add foreign key constraint after table creation to avoid dependency issues
        if (Schema::hasTable('employees')) {
            Schema::table('succession_readiness_ratings', function (Blueprint $table) {
                $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('succession_readiness_ratings');
    }
};
