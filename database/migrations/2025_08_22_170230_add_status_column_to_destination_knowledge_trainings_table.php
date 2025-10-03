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
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('destination_knowledge_trainings', 'status')) {
                $table->string('status')->default('Not Started')->after('progress');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('destination_knowledge_trainings', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
