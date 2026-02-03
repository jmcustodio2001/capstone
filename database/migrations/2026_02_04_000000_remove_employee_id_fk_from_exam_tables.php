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
        // 1. exam_attempts
        if (Schema::hasTable('exam_attempts')) {
            try {
                Schema::table('exam_attempts', function (Blueprint $table) {
                    $table->dropForeign('exam_attempts_employee_id_foreign');
                });
            } catch (\Exception $e) {
                try {
                    Schema::table('exam_attempts', function (Blueprint $table) {
                        $table->dropForeign(['employee_id']);
                    });
                } catch (\Exception $ex) {
                    // ignore
                }
            }
        }

        // 2. completed_trainings
        if (Schema::hasTable('completed_trainings')) {
            try {
                Schema::table('completed_trainings', function (Blueprint $table) {
                    $table->dropForeign('completed_trainings_employee_id_foreign');
                });
            } catch (\Exception $e) {
                try {
                    Schema::table('completed_trainings', function (Blueprint $table) {
                        $table->dropForeign(['employee_id']);
                    });
                } catch (\Exception $ex) {
                    // ignore
                }
            }
        }

        // 3. upcoming_trainings
        if (Schema::hasTable('upcoming_trainings')) {
            try {
                Schema::table('upcoming_trainings', function (Blueprint $table) {
                    $table->dropForeign('upcoming_trainings_employee_id_foreign');
                });
            } catch (\Exception $e) {
                try {
                    Schema::table('upcoming_trainings', function (Blueprint $table) {
                        $table->dropForeign(['employee_id']);
                    });
                } catch (\Exception $ex) {
                    // ignore
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We generally don't want to restore these constraints as they block external employees
    }
};
