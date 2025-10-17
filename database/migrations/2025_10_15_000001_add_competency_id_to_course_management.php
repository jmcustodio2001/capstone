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
        Schema::table('course_management', function (Blueprint $table) {
            $table->unsignedBigInteger('competency_id')->nullable()->after('course_id');
            $table->foreign('competency_id')
                  ->references('id')
                  ->on('competency_library')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_management', function (Blueprint $table) {
            $table->dropForeign(['competency_id']);
            $table->dropColumn('competency_id');
        });
    }
};
