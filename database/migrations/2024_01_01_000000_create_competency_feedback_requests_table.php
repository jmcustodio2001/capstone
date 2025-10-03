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
        // Check if table already exists before creating
        if (!Schema::hasTable('competency_feedback_requests')) {
            Schema::create('competency_feedback_requests', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id');
                $table->unsignedBigInteger('competency_id');
                $table->text('request_message')->nullable();
                $table->enum('status', ['pending', 'responded', 'closed'])->default('pending');
                $table->text('manager_response')->nullable();
                $table->unsignedBigInteger('manager_id')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'status']);
                $table->index(['competency_id']);
                $table->index(['manager_id']);
                $table->index(['created_at']);
            });
        } else {
            // Table exists, just ensure all columns are present
            Schema::table('competency_feedback_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('competency_feedback_requests', 'manager_response')) {
                    $table->text('manager_response')->nullable();
                }
                if (!Schema::hasColumn('competency_feedback_requests', 'manager_id')) {
                    $table->unsignedBigInteger('manager_id')->nullable();
                }
                if (!Schema::hasColumn('competency_feedback_requests', 'responded_at')) {
                    $table->timestamp('responded_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_feedback_requests');
    }
};
