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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
            $table->string('profile_picture')->nullable()->after('role');
            $table->timestamp('last_login_at')->nullable()->after('profile_picture');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->text('last_user_agent')->nullable()->after('last_login_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'profile_picture', 
                'last_login_at',
                'last_login_ip',
                'last_user_agent'
            ]);
        });
    }
};
