<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_update_of_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('field_name');
            $table->text('old_value');
            $table->text('new_value');
            $table->string('status')->default('Pending');
            $table->timestamps();
            // $table->foreign('employee_id')->references('id')->on('employees'); // Uncomment if you want FK
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_update_of_employees');
    }
};
