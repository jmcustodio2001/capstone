<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop unused tables that are not actively used in the application
        
        // Drop employee_trainings table (superseded by employee_training_dashboards)
        Schema::dropIfExists('employee_trainings');
        
        // Drop basic trainings table (replaced by more specific training tables)
        Schema::dropIfExists('trainings');
        
        // Drop employee_my_trainings table (limited usage, data can be consolidated)
        Schema::dropIfExists('employee_my_trainings');
        
        // Drop my_trainings table if it exists (alternative name)
        Schema::dropIfExists('my_trainings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the dropped tables in case rollback is needed
        
        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->unsignedBigInteger('course_id');
            $table->string('training_title');
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled']);
            $table->date('assigned_date');
            $table->date('due_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->timestamps();
        });
        
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('training_title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        Schema::create('employee_my_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('training_title');
            $table->date('training_date');
            $table->enum('status', ['assigned', 'in_progress', 'completed']);
            $table->integer('progress')->default(0);
            $table->text('feedback')->nullable();
            $table->string('notification_type')->nullable();
            $table->text('notification_message')->nullable();
            $table->timestamps();
        });
        
        Schema::create('my_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('training_title');
            $table->date('training_date');
            $table->enum('status', ['assigned', 'in_progress', 'completed']);
            $table->integer('progress')->default(0);
            $table->timestamps();
        });
    }
};
