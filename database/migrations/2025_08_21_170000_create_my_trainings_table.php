<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_my_trainings')) {
            Schema::create('employee_my_trainings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('training_title');
                $table->date('training_date');
                $table->string('status')->default('Upcoming');
                $table->integer('progress')->nullable();
                $table->text('feedback')->nullable();
                $table->string('notification_type')->nullable();
                $table->string('notification_message')->nullable();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('employee_my_trainings');
    }
};
