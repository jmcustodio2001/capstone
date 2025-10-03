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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('otp_code', 6)->nullable()->after('password');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->integer('otp_attempts')->default(0)->after('otp_expires_at');
            $table->timestamp('last_otp_sent_at')->nullable()->after('otp_attempts');
            $table->boolean('otp_verified')->default(false)->after('last_otp_sent_at');
            $table->timestamp('email_verified_at')->nullable()->after('otp_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'otp_code',
                'otp_expires_at',
                'otp_attempts',
                'last_otp_sent_at',
                'otp_verified',
                'email_verified_at'
            ]);
        });
    }
};
