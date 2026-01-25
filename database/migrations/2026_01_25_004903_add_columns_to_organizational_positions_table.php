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
        Schema::table('organizational_positions', function (Blueprint $table) {
            $table->text('qualification')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('work_arrangement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizational_positions', function (Blueprint $table) {
            $table->dropColumn(['qualification', 'employment_type', 'work_arrangement']);
        });
    }
};
