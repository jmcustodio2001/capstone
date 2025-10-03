<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('upcoming_trainings', 'expired_date')) {
                $table->timestamp('expired_date')->nullable()->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('upcoming_trainings', 'expired_date')) {
                $table->dropColumn('expired_date');
            }
        });
    }
};
