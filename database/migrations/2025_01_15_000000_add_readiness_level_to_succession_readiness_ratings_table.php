<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('succession_readiness_ratings') && !Schema::hasColumn('succession_readiness_ratings', 'readiness_level')) {
            Schema::table('succession_readiness_ratings', function (Blueprint $table) {
                $table->string('readiness_level')->after('readiness_score')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('succession_readiness_ratings') && Schema::hasColumn('succession_readiness_ratings', 'readiness_level')) {
            Schema::table('succession_readiness_ratings', function (Blueprint $table) {
                $table->dropColumn('readiness_level');
            });
        }
    }
};