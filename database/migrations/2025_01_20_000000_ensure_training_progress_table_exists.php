<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('training_progress')) {
            Schema::create('training_progress', function (Blueprint $table) {
                $table->id('progress_id');
                $table->string('employee_id', 20);
                $table->string('training_title');
                $table->integer('progress_percentage')->default(0);
                $table->dateTime('last_updated');
                $table->string('status')->default('In Progress');
                $table->timestamps();
                
                $table->index('employee_id');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('training_progress');
    }
};