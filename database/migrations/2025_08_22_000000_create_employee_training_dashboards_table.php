<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
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
                $table->timestamps();

                $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
                $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('cascade');
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('employee_training_dashboards');
    }
};
