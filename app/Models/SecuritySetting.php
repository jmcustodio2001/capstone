<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use HasFactory;

    protected $table = 'security_settings';

    protected $fillable = [
        'two_factor_enabled',
        'login_alerts',
        'password_complexity',
        'login_attempts_limit',
        'security_alerts',
        'system_alerts',
        'session_timeout',
        'timeout_duration',
        'audit_logging',
        'ip_restriction',
        'maintenance_mode',
        'allowed_ips',
        'password_min_length',
        'password_require_uppercase',
        'password_require_lowercase',
        'password_require_numbers',
        'password_require_symbols',
        'max_login_attempts',
        'lockout_duration',
    ];

    protected $casts = [
        'two_factor_enabled' => 'boolean',
        'login_alerts' => 'boolean',
        'password_complexity' => 'boolean',
        'login_attempts_limit' => 'boolean',
        'security_alerts' => 'boolean',
        'system_alerts' => 'boolean',
        'session_timeout' => 'boolean',
        'audit_logging' => 'boolean',
        'ip_restriction' => 'boolean',
        'maintenance_mode' => 'boolean',
        'password_require_uppercase' => 'boolean',
        'password_require_lowercase' => 'boolean',
        'password_require_numbers' => 'boolean',
        'password_require_symbols' => 'boolean',
        'timeout_duration' => 'integer',
        'password_min_length' => 'integer',
        'max_login_attempts' => 'integer',
        'lockout_duration' => 'integer',
        'allowed_ips' => 'array',
    ];

    protected $attributes = [
        'two_factor_enabled' => true,
        'login_alerts' => true,
        'password_complexity' => false,
        'login_attempts_limit' => true,
        'security_alerts' => false,
        'system_alerts' => false,
        'session_timeout' => false,
        'timeout_duration' => 10,
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
    ];

    /**
     * Get the singleton instance of security settings
     */
    public static function getInstance()
    {
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([]);
        }
        
        return $settings;
    }

    /**
     * Check if a feature is enabled
     */
    public static function isFeatureEnabled($feature)
    {
        $settings = static::getInstance();
        return $settings->{$feature} ?? false;
    }

    /**
     * Get password complexity rules
     */
    public function getPasswordRules()
    {
        $rules = ['required', 'string', 'min:' . $this->password_min_length];
        
        if ($this->password_complexity) {
            if ($this->password_require_uppercase) {
                $rules[] = 'regex:/[A-Z]/';
            }
            if ($this->password_require_lowercase) {
                $rules[] = 'regex:/[a-z]/';
            }
            if ($this->password_require_numbers) {
                $rules[] = 'regex:/[0-9]/';
            }
            if ($this->password_require_symbols) {
                $rules[] = 'regex:/[@$!%*?&]/';
            }
        }
        
        return $rules;
    }

    /**
     * Get allowed IPs array
     */
    public function getAllowedIPs()
    {
        return $this->allowed_ips ?? [];
    }

    /**
     * Check if IP is allowed
     */
    public function isIPAllowed($ip)
    {
        if (!$this->ip_restriction) {
            return true;
        }
        
        $allowedIPs = $this->getAllowedIPs();
        
        if (empty($allowedIPs)) {
            return true; // If no IPs specified, allow all
        }
        
        return in_array($ip, $allowedIPs);
    }
}
