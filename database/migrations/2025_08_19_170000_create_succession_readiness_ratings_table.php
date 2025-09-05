<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('succession_readiness_ratings', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->integer('readiness_score');
            $table->date('assessment_date');
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('succession_readiness_ratings');
    }
};
