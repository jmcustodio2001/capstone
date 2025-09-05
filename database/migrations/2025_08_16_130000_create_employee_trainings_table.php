<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('course_id');
            $table->date('training_date');
            $table->integer('progress')->default(0); // Added progress column
            $table->dateTime('last_accessed')->nullable(); // Added last_accessed column
            $table->string('status')->default('Scheduled');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_trainings');
    }
};
