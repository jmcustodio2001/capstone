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
        if (Schema::hasTable('competency_gaps')) {
            Schema::table('competency_gaps', function (Blueprint $table) {
                if (!Schema::hasColumn('competency_gaps', 'assigned_to_training')) {
                    $table->boolean('assigned_to_training')->default(false)->after('is_active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competency_gaps', function (Blueprint $table) {
            if (Schema::hasColumn('competency_gaps', 'assigned_to_training')) {
                $table->dropColumn('assigned_to_training');
            }
        });
    }
};