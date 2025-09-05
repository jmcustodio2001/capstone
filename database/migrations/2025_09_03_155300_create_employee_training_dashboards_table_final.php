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
        if (!Schema::hasTable('employee_training_dashboards')) {
            Schema::create('employee_training_dashboards', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id', 20);
                $table->unsignedBigInteger('course_id');
                $table->date('training_date')->nullable();
                $table->integer('progress')->default(0);
                $table->string('status')->default('Not Started');
                $table->text('remarks')->nullable();
                $table->timestamp('last_accessed')->nullable();
                $table->unsignedBigInteger('assigned_by')->nullable();
                $table->timestamp('expired_date')->nullable();
                $table->timestamps();

                // Add indexes for better performance
                $table->index('employee_id');
                $table->index('course_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_training_dashboards');
    }
};
