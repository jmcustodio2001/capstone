<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->string('employee_id', 20)->primary(); // Changed to string type for alphanumeric IDs
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('position')->nullable();
            $table->string('status')->default('active');
            $table->string('password'); // for login

            $table->string('profile_picture')->nullable(); // <-- added column

            $table->rememberToken();
            $table->timestamps();

            // Optionally add foreign key for department_id if you have departments table
            // $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}

