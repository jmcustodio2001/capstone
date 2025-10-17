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
        Schema::create('employee_awards', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('award_type');
            $table->string('award_name');
            $table->text('description')->nullable();
            $table->date('award_date');
            $table->string('awarded_by');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('employee_id');
            $table->index('status');
            $table->index('award_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_awards');
    }
};
