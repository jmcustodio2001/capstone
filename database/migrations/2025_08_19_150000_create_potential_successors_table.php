<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('potential_successors', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->string('potential_role');
            $table->date('identified_date');
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('potential_successors');
    }
};
