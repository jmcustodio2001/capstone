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
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            
            // Authentication settings
            $table->boolean('two_factor_enabled')->default(true);
            $table->boolean('password_complexity')->default(false);
            $table->boolean('login_attempts_limit')->default(true);
            $table->integer('max_login_attempts')->default(5);
            $table->integer('lockout_duration')->default(15); // minutes
            
            // Password complexity settings
            $table->integer('password_min_length')->default(8);
            $table->boolean('password_require_uppercase')->default(true);
            $table->boolean('password_require_lowercase')->default(true);
            $table->boolean('password_require_numbers')->default(true);
            $table->boolean('password_require_symbols')->default(false);
            
            // Notification settings
            $table->boolean('login_alerts')->default(true);
            $table->boolean('security_alerts')->default(false);
            $table->boolean('system_alerts')->default(false);
            
            // Session management
            $table->boolean('session_timeout')->default(false);
            $table->integer('timeout_duration')->default(30); // minutes
            
            // System security
            $table->boolean('audit_logging')->default(false);
            $table->boolean('ip_restriction')->default(false);
            $table->json('allowed_ips')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_settings');
    }
};
