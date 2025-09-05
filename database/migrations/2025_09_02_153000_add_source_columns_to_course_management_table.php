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
            $table->string('source_type')->nullable()->after('status');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->timestamp('requested_at')->nullable()->after('source_id');
            $table->unsignedBigInteger('requested_by')->nullable()->after('requested_at');
            
            // Add foreign key constraint for requested_by
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_management', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropColumn(['source_type', 'source_id', 'requested_at', 'requested_by']);
        });
    }
};
