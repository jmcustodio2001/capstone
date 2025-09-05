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
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('leave_id')->nullable();
            $table->datetime('application_date')->nullable();
            $table->string('leave_type');
            $table->integer('leave_days');
            $table->integer('days_requested');
            $table->string('status')->default('Pending');
            $table->text('reason');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('contact_info')->nullable();
            $table->datetime('applied_date')->nullable();
            $table->string('approved_by')->nullable();
            $table->datetime('approved_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index('employee_id');
            $table->index('status');
            $table->index('leave_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
