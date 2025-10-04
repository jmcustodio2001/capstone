<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('upcoming_trainings', 'source')) {
                $table->string('source')->nullable()->after('status');
            }
            if (!Schema::hasColumn('upcoming_trainings', 'assigned_by')) {
                $table->string('assigned_by')->nullable()->after('source');
            }
            if (!Schema::hasColumn('upcoming_trainings', 'assigned_date')) {
                $table->timestamp('assigned_date')->nullable()->after('assigned_by');
            }
            if (!Schema::hasColumn('upcoming_trainings', 'destination_training_id')) {
                $table->unsignedBigInteger('destination_training_id')->nullable()->after('assigned_date');
            }
            if (!Schema::hasColumn('upcoming_trainings', 'needs_response')) {
                $table->boolean('needs_response')->default(false)->after('destination_training_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            // Check if columns exist before dropping them
            $columnsToDrop = [];
            
            if (Schema::hasColumn('upcoming_trainings', 'source')) {
                $columnsToDrop[] = 'source';
            }
            if (Schema::hasColumn('upcoming_trainings', 'assigned_by')) {
                $columnsToDrop[] = 'assigned_by';
            }
            if (Schema::hasColumn('upcoming_trainings', 'assigned_date')) {
                $columnsToDrop[] = 'assigned_date';
            }
            if (Schema::hasColumn('upcoming_trainings', 'destination_training_id')) {
                $columnsToDrop[] = 'destination_training_id';
            }
            if (Schema::hasColumn('upcoming_trainings', 'needs_response')) {
                $columnsToDrop[] = 'needs_response';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
