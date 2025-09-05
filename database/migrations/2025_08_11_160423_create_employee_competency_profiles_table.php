<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('employee_competency_profiles')) {
            Schema::create('employee_competency_profiles', function (Blueprint $table) {
                $table->id();

                // Match employees PK
                $table->string('employee_id', 20);
                $table->unsignedBigInteger('competency_id');

                $table->string('proficiency_level');
                $table->date('assessment_date');
                $table->timestamps();

                // Foreign keys with correct columns
                $table->foreign('employee_id')
                    ->references('employee_id') // match employees table PK
                    ->on('employees')
                    ->onDelete('cascade');

                $table->foreign('competency_id')
                    ->references('id') // matches competency_library PK
                    ->on('competency_library')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_competency_profiles');
    }
};
