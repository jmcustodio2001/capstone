<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('competency_course_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('course_id');
            $table->date('assigned_date');
            $table->string('status')->default('Not Started');
            $table->integer('progress')->default(0);
            $table->boolean('is_destination_knowledge')->default(false);
            $table->timestamps();

            $table->unique(['employee_id', 'course_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('competency_course_assignments');
    }
};
