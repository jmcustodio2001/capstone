<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('destination_knowledge_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->string('destination_name');
            $table->text('details');
            $table->date('date_completed')->nullable();
            $table->integer('progress')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_knowledge_trainings');
    }
};
