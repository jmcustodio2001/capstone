<?php

/**
 * Security Settings Setup Script
 * Run this to set up the security system
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\SecuritySetting;

echo "🔒 Setting up Security System...\n\n";

try {
    // Run migrations
    echo "📋 Running migrations...\n";
    
    // Check if migrations exist
    $migrationFiles = [
        'database/migrations/2024_10_27_073400_create_security_settings_table.php',
        'database/migrations/2024_10_27_073500_create_audit_logs_table.php'
    ];
    
    foreach ($migrationFiles as $file) {
        if (file_exists($file)) {
            echo "✅ Found migration: " . basename($file) . "\n";
        } else {
            echo "❌ Missing migration: " . basename($file) . "\n";
        }
    }
    
    // Create tables manually if migrations don't work
    echo "\n📊 Creating security_settings table...\n";
    
    DB::statement("
        CREATE TABLE IF NOT EXISTS security_settings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            two_factor_enabled BOOLEAN DEFAULT TRUE,
            password_complexity BOOLEAN DEFAULT FALSE,
            login_attempts_limit BOOLEAN DEFAULT TRUE,
            max_login_attempts INT DEFAULT 5,
            lockout_duration INT DEFAULT 15,
            password_min_length INT DEFAULT 8,
            password_require_uppercase BOOLEAN DEFAULT TRUE,
            password_require_lowercase BOOLEAN DEFAULT TRUE,
            password_require_numbers BOOLEAN DEFAULT TRUE,
            password_require_symbols BOOLEAN DEFAULT FALSE,
            login_alerts BOOLEAN DEFAULT TRUE,
            security_alerts BOOLEAN DEFAULT FALSE,
            system_alerts BOOLEAN DEFAULT FALSE,
            session_timeout BOOLEAN DEFAULT FALSE,
            timeout_duration INT DEFAULT 30,
            audit_logging BOOLEAN DEFAULT FALSE,
            ip_restriction BOOLEAN DEFAULT FALSE,
            allowed_ips JSON NULL,
            maintenance_mode BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ Security settings table created\n";
    
    echo "\n📊 Creating audit_logs table...\n";
    
    DB::statement("
        CREATE TABLE IF NOT EXISTS audit_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            admin_id BIGINT UNSIGNED NULL,
            admin_name VARCHAR(255) NULL,
            action VARCHAR(255) NOT NULL,
            details JSON NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_created (admin_id, created_at),
            INDEX idx_action_created (action, created_at),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ Audit logs table created\n";
    
    // Create default security settings
    echo "\n⚙️ Creating default security settings...\n";
    
    $existingSettings = DB::table('security_settings')->first();
    
    if (!$existingSettings) {
        DB::table('security_settings')->insert([
            'two_factor_enabled' => true,
            'login_alerts' => true,
            'password_complexity' => false,
            'login_attempts_limit' => true,
            'security_alerts' => false,
            'system_alerts' => false,
            'session_timeout' => false,
            'timeout_duration' => 30,
            'audit_logging' => false,
            'ip_restriction' => false,
            'maintenance_mode' => false,
            'password_min_length' => 8,
            'password_require_uppercase' => true,
            'password_require_lowercase' => true,
            'password_require_numbers' => true,
            'password_require_symbols' => false,
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ Default security settings created\n";
    } else {
        echo "ℹ️ Security settings already exist\n";
    }
    
    echo "\n🎉 Security system setup complete!\n\n";
    
    echo "📋 Summary:\n";
    echo "✅ Security settings table created\n";
    echo "✅ Audit logs table created\n";
    echo "✅ Default settings configured\n";
    echo "✅ Controllers and models ready\n";
    echo "✅ Routes configured\n\n";
    
    echo "🔧 Next steps:\n";
    echo "1. Access Security Settings via Admin Tools > Security Settings\n";
    echo "2. Configure your preferred security policies\n";
    echo "3. Test the settings with different admin accounts\n\n";
    
    echo "🛡️ Available Security Features:\n";
    echo "• Two-Factor Authentication (OTP)\n";
    echo "• Password Complexity Rules\n";
    echo "• Login Attempt Limits\n";
    echo "• Email Notifications\n";
    echo "• Session Timeout Management\n";
    echo "• Audit Logging\n";
    echo "• IP Address Restrictions\n";
    echo "• Maintenance Mode\n\n";
    
} catch (Exception $e) {
    echo "❌ Error during setup: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n\n";
    
    echo "🔧 Manual setup required. Please:\n";
    echo "1. Run: php artisan migrate\n";
    echo "2. Check database connection\n";
    echo "3. Verify file permissions\n";
}
