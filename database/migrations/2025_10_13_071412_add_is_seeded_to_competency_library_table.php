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
        Schema::table('competency_library', function (Blueprint $table) {
            $table->boolean('is_seeded')->default(false)->after('rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competency_library', function (Blueprint $table) {
            $table->dropColumn('is_seeded');
        });
    }
};
