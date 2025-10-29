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
        Schema::table('profile_updates', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('rejection_reason');
            $table->text('user_agent')->nullable()->after('ip_address');
            
            // Add index for IP tracking
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_updates', function (Blueprint $table) {
            $table->dropIndex(['ip_address']);
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};
