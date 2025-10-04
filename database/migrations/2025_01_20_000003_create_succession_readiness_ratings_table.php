<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('succession_readiness_ratings')) {
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
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('succession_readiness_ratings');
    }
};