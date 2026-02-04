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
        if (!Schema::hasTable('training_record_certificate_tracking')) {
            Schema::create('training_record_certificate_tracking', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id', 50);
                $table->unsignedBigInteger('course_id');
                $table->date('training_date');
                $table->string('certificate_number')->nullable();
                $table->date('certificate_expiry')->nullable();
                $table->string('certificate_url')->nullable();
                $table->date('issue_date')->nullable();
                $table->string('status')->default('Active');
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index('employee_id');
                $table->index('course_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_record_certificate_tracking');
    }
};
