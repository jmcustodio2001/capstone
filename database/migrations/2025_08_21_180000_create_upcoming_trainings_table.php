<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('upcoming_trainings', function (Blueprint $table) {
            $table->id('upcoming_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('training_title');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('Scheduled');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('upcoming_trainings');
    }
};
