<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('competency_gaps')) {
            Schema::create('competency_gaps', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id', 20);
                $table->unsignedBigInteger('competency_id');
                $table->integer('required_level');
                $table->integer('current_level');
                $table->integer('gap');
                $table->text('gap_description')->nullable();
                $table->timestamp('expired_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Add indexes for better performance
                $table->index(['employee_id', 'competency_id']);
                $table->index('is_active');
                $table->index('expired_date');
            });
        }

        // Add foreign keys only if they don't exist
        if (Schema::hasTable('competency_gaps') && Schema::hasTable('employees') && Schema::hasTable('competency_library')) {
            Schema::table('competency_gaps', function (Blueprint $table) {
                // Check if foreign keys don't exist before adding them
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'competency_gaps' AND REFERENCED_TABLE_NAME IS NOT NULL");
                $existingConstraints = array_column($foreignKeys, 'CONSTRAINT_NAME');

                if (!in_array('competency_gaps_employee_id_foreign', $existingConstraints)) {
                    $table->foreign('employee_id')
                        ->references('employee_id')
                        ->on('employees')
                        ->onDelete('cascade');
                }

                if (!in_array('competency_gaps_competency_id_foreign', $existingConstraints)) {
                    $table->foreign('competency_id')
                        ->references('id')
                        ->on('competency_library')
                        ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_gaps');
    }
};
