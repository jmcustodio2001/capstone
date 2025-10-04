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
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                // Add missing columns if they don't exist
                if (!Schema::hasColumn('destination_knowledge_trainings', 'objectives')) {
                    $table->text('objectives')->nullable()->after('details');
                }
                
                if (!Schema::hasColumn('destination_knowledge_trainings', 'duration')) {
                    $table->string('duration')->nullable()->after('objectives');
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
                if (Schema::hasColumn('destination_knowledge_trainings', 'objectives')) {
                    $table->dropColumn('objectives');
                }
                if (Schema::hasColumn('destination_knowledge_trainings', 'duration')) {
                    $table->dropColumn('duration');
                }
            });
        }
    }
};