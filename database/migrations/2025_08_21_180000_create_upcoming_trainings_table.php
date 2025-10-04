<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Check if table already exists (from earlier migration)
        if (!Schema::hasTable('upcoming_trainings')) {
            Schema::create('upcoming_trainings', function (Blueprint $table) {
                $table->id('upcoming_id');
                $table->unsignedBigInteger('employee_id');
                $table->string('training_title');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status')->default('Scheduled');
                $table->timestamps();
            });
        } else {
            // Table exists, add any missing columns from this migration
            Schema::table('upcoming_trainings', function (Blueprint $table) {
                // Check and add columns that might be missing from the basic structure
                if (!Schema::hasColumn('upcoming_trainings', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->after('upcoming_id');
                }
                if (!Schema::hasColumn('upcoming_trainings', 'training_title')) {
                    $table->string('training_title')->after('employee_id');
                }
                if (!Schema::hasColumn('upcoming_trainings', 'start_date')) {
                    $table->date('start_date')->after('training_title');
                }
                if (!Schema::hasColumn('upcoming_trainings', 'end_date')) {
                    $table->date('end_date')->after('start_date');
                }
                if (!Schema::hasColumn('upcoming_trainings', 'status')) {
                    $table->string('status')->default('Scheduled')->after('end_date');
                }
            });
        }
    }

    public function down(): void {
        // Only drop if this migration created the table
        // Since an earlier migration likely created it, we'll leave it alone
        // Schema::dropIfExists('upcoming_trainings');
    }
};
