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
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('upcoming_trainings', 'assigned_by_name')) {
                $table->string('assigned_by_name')->nullable()->after('assigned_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('upcoming_trainings', 'assigned_by_name')) {
                $table->dropColumn('assigned_by_name');
            }
        });
    }
};