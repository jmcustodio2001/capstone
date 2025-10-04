<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpcomingTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upcoming_trainings', function (Blueprint $table) {
            $table->id('upcoming_id');
            $table->string('employee_id', 20);
            $table->string('training_title');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('expired_date')->nullable();
            $table->string('status')->default('Assigned');
            $table->string('source')->nullable();
            $table->string('assigned_by')->nullable();
            $table->string('assigned_by_name')->nullable();
            $table->timestamp('assigned_date')->nullable();
            $table->unsignedBigInteger('destination_training_id')->nullable();
            $table->boolean('needs_response')->default(false);
            $table->timestamps();
        });

        // Add foreign key constraints after table creation
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');

            $table->foreign('destination_training_id')
                  ->references('id')
                  ->on('destination_knowledge_trainings')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upcoming_trainings');
    }
}
