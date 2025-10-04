<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Check if table already exists to prevent duplicate creation
        if (!Schema::hasTable('destination_knowledge_trainings')) {
            Schema::create('destination_knowledge_trainings', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id', 20);
                $table->string('destination_name');
                $table->string('training_title')->nullable();
                $table->text('details');
                $table->string('delivery_mode')->nullable();
                $table->date('date_completed')->nullable();
                $table->date('expired_date')->nullable();
                $table->integer('progress')->default(0);
                $table->string('status')->default('not-started');
                $table->boolean('is_active')->default(true);
                $table->boolean('admin_approved_for_upcoming')->default(false);
                $table->string('training_type')->default('destination');
                $table->string('source')->default('destination_knowledge_training');
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('employee_id')
                    ->references('employee_id')
                    ->on('employees')
                    ->onDelete('cascade');
            });
        } else {
            // Table exists, ensure all columns are present
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (!Schema::hasColumn('destination_knowledge_trainings', 'training_title')) {
                    $table->string('training_title')->nullable();
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'delivery_mode')) {
                    $table->string('delivery_mode')->nullable();
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'date_completed')) {
                    $table->date('date_completed')->nullable();
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'expired_date')) {
                    $table->date('expired_date')->nullable();
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'progress')) {
                    $table->integer('progress')->default(0);
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'status')) {
                    $table->string('status')->default('not-started');
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'admin_approved_for_upcoming')) {
                    $table->boolean('admin_approved_for_upcoming')->default(false);
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'training_type')) {
                    $table->string('training_type')->default('destination');
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'source')) {
                    $table->string('source')->default('destination_knowledge_training');
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'remarks')) {
                    $table->text('remarks')->nullable();
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_knowledge_trainings');
    }
};
