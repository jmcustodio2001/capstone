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
        if (!Schema::hasTable('training_requests')) {
            Schema::create('training_requests', function (Blueprint $table) {
                $table->id('request_id');
                $table->string('employee_id', 20);
                $table->unsignedBigInteger('course_id')->nullable();
                $table->string('training_title');
                $table->text('reason');
                $table->string('status')->default('Pending');
                $table->date('requested_date');
                $table->timestamps();
                
                // Add foreign key constraint if course_management table exists
                if (Schema::hasTable('course_management')) {
                    $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_requests');
    }
};
