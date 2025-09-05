<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('succession_simulations', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->string('simulation_result');
            $table->date('created_at');

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('succession_simulations');
    }
};
