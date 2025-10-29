<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SecuritySetting;
use App\Models\AuditLog;

class SecuritySettingsController extends Controller
{
    // Middleware is applied in routes/web.php instead of constructor

    /**
     * Get current security settings
     */
    public function getSettings()
    {
        try {
            $settings = SecuritySetting::first();
            
            if (!$settings) {
                // Create default settings if none exist
                $settings = SecuritySetting::create([
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
                ]);
            }

            return response()->json([
                'success' => true,
                'settings' => $settings->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get security settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load security settings'
            ], 500);
        }
    }

    /**
     * Update security settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'two_factor_enabled' => 'boolean',
                'login_alerts' => 'boolean',
                'password_complexity' => 'boolean',
                'login_attempts_limit' => 'boolean',
                'security_alerts' => 'boolean',
                'system_alerts' => 'boolean',
                'session_timeout' => 'boolean',
                'timeout_duration' => 'integer|min:10|max:480',
                'audit_logging' => 'boolean',
                'ip_restriction' => 'boolean',
                'maintenance_mode' => 'boolean',
            ]);

            // Convert JavaScript boolean names to database column names
            $settingsData = [
                'two_factor_enabled' => $request->input('two_factor', false),
                'login_alerts' => $request->input('login_alerts', false),
                'password_complexity' => $request->input('password_complexity', false),
                'login_attempts_limit' => $request->input('login_attempts', false),
                'security_alerts' => $request->input('security_alerts', false),
                'system_alerts' => $request->input('system_alerts', false),
                'session_timeout' => $request->input('session_timeout', false),
                'timeout_duration' => $request->input('timeout_duration', 10),
                'audit_logging' => $request->input('audit_logging', false),
                'ip_restriction' => $request->input('ip_restriction', false),
                'maintenance_mode' => $request->input('maintenance_mode', false),
            ];

            $settings = SecuritySetting::first();
            
            if ($settings) {
                $settings->update($settingsData);
            } else {
                $settings = SecuritySetting::create($settingsData);
            }

            // Log the security settings change
            $this->logSecurityChange('Security settings updated', $settingsData);

            // Clear cache to force reload of settings
            Cache::forget('security_settings');
            Cache::put('security_settings', $settings, now()->addHours(24));

            return response()->json([
                'success' => true,
                'message' => 'Security settings updated successfully',
                'settings' => $settings->toArray()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update security settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update security settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify admin password
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::guard('admin')->user();
        
        if (!$user) {
            return response()->json([
                'valid' => false, 
                'success' => false, 
                'message' => 'Not authenticated'
            ], 401);
        }

        if (Hash::check($request->password, $user->password)) {
            // Log successful password verification
            $this->logSecurityChange('Admin password verified for security settings access');
            
            // Store verification in session for this admin user (same as AdminController)
            $request->session()->put('admin_password_verified_' . $user->id, true);
            
            return response()->json([
                'valid' => true, 
                'success' => true
            ]);
        }

        // Log failed password verification
        $this->logSecurityChange('Failed admin password verification attempt', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'valid' => false, 
            'success' => false, 
            'message' => 'Invalid password'
        ], 401);
    }

    /**
     * Get current security settings (cached)
     */
    public static function getSecuritySettings()
    {
        return Cache::remember('security_settings', now()->addHours(24), function () {
            $settings = SecuritySetting::first();
            
            if (!$settings) {
                return [
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
                ];
            }
            
            return $settings->toArray();
        });
    }

    /**
     * Check if a specific security feature is enabled
     */
    public static function isEnabled($feature)
    {
        $settings = self::getSecuritySettings();
        return $settings[$feature] ?? false;
    }

    /**
     * Log security-related changes
     */
    private function logSecurityChange($action, $details = [])
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            AuditLog::create([
                'admin_id' => $admin ? $admin->id : null,
                'admin_name' => $admin ? $admin->name : 'System',
                'action' => $action,
                'details' => json_encode($details),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log security change: ' . $e->getMessage());
        }
    }

    /**
     * Get audit logs
     */
    public function getAuditLogs(Request $request)
    {
        try {
            $logs = AuditLog::orderBy('created_at', 'desc')
                ->limit($request->input('limit', 50))
                ->get();

            return response()->json([
                'success' => true,
                'logs' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get audit logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load audit logs'
            ], 500);
        }
    }

    /**
     * Get session timeout settings for JavaScript
     */
    public function getTimeoutSettings()
    {
        try {
            $settings = SecuritySetting::getInstance();
            
            return response()->json([
                'success' => true,
                'session_timeout_enabled' => $settings->session_timeout,
                'timeout_duration' => $settings->timeout_duration, // in minutes
                'warning_time' => max(5, $settings->timeout_duration - 5) // warn 5 minutes before, or at start if duration is less than 5
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get timeout settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'session_timeout_enabled' => false,
                'timeout_duration' => 10,
                'warning_time' => 10
            ]);
        }
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenanceMode(Request $request)
    {
        try {
            $enabled = $request->input('enabled', false);
            
            if ($enabled) {
                // Enable maintenance mode
                file_put_contents(storage_path('framework/maintenance.php'), '<?php return [];');
                $this->logSecurityChange('Maintenance mode enabled');
            } else {
                // Disable maintenance mode
                if (file_exists(storage_path('framework/maintenance.php'))) {
                    unlink(storage_path('framework/maintenance.php'));
                }
                $this->logSecurityChange('Maintenance mode disabled');
            }

            // Update setting in database
            $settings = SecuritySetting::first();
            if ($settings) {
                $settings->update(['maintenance_mode' => $enabled]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenance mode ' . ($enabled ? 'enabled' : 'disabled'),
                'maintenance_mode' => $enabled
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to toggle maintenance mode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle maintenance mode'
            ], 500);
        }
    }
}
