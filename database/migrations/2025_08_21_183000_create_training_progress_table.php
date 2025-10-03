<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('training_progress')) {
            Schema::create('training_progress', function (Blueprint $table) {
                $table->id('progress_id');
                $table->unsignedBigInteger('employee_id');
                $table->string('training_title');
                $table->integer('progress_percentage');
                $table->dateTime('last_updated');
                $table->timestamps();
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('training_progress');
    }
};
