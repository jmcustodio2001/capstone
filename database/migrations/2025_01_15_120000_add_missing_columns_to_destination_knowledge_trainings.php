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
        // Check if table exists before trying to modify it
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                // Add missing columns if they don't exist
                if (!Schema::hasColumn('destination_knowledge_trainings', 'training_type')) {
                    $table->string('training_type')->default('destination')->after('admin_approved_for_upcoming');
                }
                
                if (!Schema::hasColumn('destination_knowledge_trainings', 'source')) {
                    $table->string('source')->default('destination_knowledge_training')->after('training_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (Schema::hasColumn('destination_knowledge_trainings', 'training_type')) {
                    $table->dropColumn('training_type');
                }
                if (Schema::hasColumn('destination_knowledge_trainings', 'source')) {
                    $table->dropColumn('source');
                }
            });
        }
    }
};