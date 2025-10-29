<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\SecuritySetting;
use App\Models\AuditLog;
use App\Models\SecurityAlert;

class SecurityAlertsController extends Controller
{
    /**
     * Send login alert notification
     */
    public static function sendLoginAlert($user, $request, $loginType = 'admin')
    {
        $settings = SecuritySetting::getInstance();
        
        if (!$settings->login_alerts) {
            return; // Login alerts disabled
        }

        try {
            // Get user name based on user type
            $userName = '';
            if ($loginType === 'employee') {
                // For employees, combine first_name and last_name
                $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                if (empty($userName)) {
                    $userName = $user->email ?? 'Unknown Employee';
                }
            } else {
                // For admins, use name field
                $userName = $user->name ?? $user->email ?? 'Unknown Admin';
            }
            
            // Create alert record
            $alert = SecurityAlert::create([
                'type' => 'login_alert',
                'title' => ucfirst($loginType) . ' Login Detected',
                'message' => "{$userName} logged in",
                'details' => [
                    'user_id' => $user->id ?? $user->employee_id,
                    'user_name' => $userName,
                    'user_email' => $user->email,
                    'login_type' => $loginType,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString(),
                    'location' => self::getLocationFromIP($request->ip())
                ],
                'severity' => 'info',
                'is_read' => false
            ]);

            // Send email notification if configured
            self::sendEmailAlert($alert);

            Log::info("Login alert sent for {$loginType} user: {$userName}");

        } catch (\Exception $e) {
            Log::error('Failed to send login alert: ' . $e->getMessage());
        }
    }

    /**
     * Send security alert notification
     */
    public static function sendSecurityAlert($title, $message, $details = [], $severity = 'warning')
    {
        $settings = SecuritySetting::getInstance();
        
        if (!$settings->security_alerts) {
            return; // Security alerts disabled
        }

        try {
            $alert = SecurityAlert::create([
                'type' => 'security_alert',
                'title' => $title,
                'message' => $message,
                'details' => array_merge($details, [
                    'timestamp' => now()->toISOString(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]),
                'severity' => $severity,
                'is_read' => false
            ]);

            // Send email for high severity alerts
            if (in_array($severity, ['high', 'critical'])) {
                self::sendEmailAlert($alert);
            }

            Log::info("Security alert created: {$title}");

        } catch (\Exception $e) {
            Log::error('Failed to send security alert: ' . $e->getMessage());
        }
    }

    /**
     * Send system alert notification
     */
    public static function sendSystemAlert($title, $message, $details = [], $severity = 'info')
    {
        $settings = SecuritySetting::getInstance();
        
        if (!$settings->system_alerts) {
            return; // System alerts disabled
        }

        try {
            $alert = SecurityAlert::create([
                'type' => 'system_alert',
                'title' => $title,
                'message' => $message,
                'details' => array_merge($details, [
                    'timestamp' => now()->toISOString(),
                    'server_info' => [
                        'php_version' => PHP_VERSION,
                        'memory_usage' => memory_get_usage(true),
                        'disk_space' => disk_free_space('/'),
                    ]
                ]),
                'severity' => $severity,
                'is_read' => false
            ]);

            // Send email for critical system alerts
            if ($severity === 'critical') {
                self::sendEmailAlert($alert);
            }

            Log::info("System alert created: {$title}");

        } catch (\Exception $e) {
            Log::error('Failed to send system alert: ' . $e->getMessage());
        }
    }

    /**
     * Get all alerts for admin notification dropdown
     */
    public function getAlerts(Request $request)
    {
        try {
            $alerts = SecurityAlert::orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($alert) {
                    return [
                        'id' => $alert->id,
                        'type' => $alert->type,
                        'title' => $alert->title,
                        'message' => $alert->message,
                        'severity' => $alert->severity,
                        'is_read' => $alert->is_read,
                        'time_ago' => $alert->created_at->diffForHumans(),
                        'icon' => self::getAlertIcon($alert->type, $alert->severity),
                        'details' => $alert->details
                    ];
                });

            return response()->json([
                'success' => true,
                'alerts' => $alerts,
                'unread_count' => SecurityAlert::where('is_read', false)->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get alerts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load alerts'
            ], 500);
        }
    }

    /**
     * Mark alert as read
     */
    public function markAsRead(Request $request, $alertId)
    {
        try {
            $alert = SecurityAlert::findOrFail($alertId);
            $alert->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Alert marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark alert as read'
            ], 500);
        }
    }

    /**
     * Mark all alerts as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            SecurityAlert::where('is_read', false)->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All alerts marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark alerts as read'
            ], 500);
        }
    }

    /**
     * Get alert icon based on type and severity
     */
    private static function getAlertIcon($type, $severity)
    {
        $icons = [
            'login_alert' => 'bi bi-person-check text-info',
            'security_alert' => [
                'info' => 'bi bi-shield-check text-info',
                'warning' => 'bi bi-shield-exclamation text-warning',
                'high' => 'bi bi-shield-x text-danger',
                'critical' => 'bi bi-shield-slash text-danger'
            ],
            'system_alert' => [
                'info' => 'bi bi-info-circle text-info',
                'warning' => 'bi bi-exclamation-triangle text-warning',
                'high' => 'bi bi-exclamation-octagon text-danger',
                'critical' => 'bi bi-x-octagon text-danger'
            ]
        ];

        if ($type === 'login_alert') {
            return $icons['login_alert'];
        }

        return $icons[$type][$severity] ?? 'bi bi-bell text-secondary';
    }

    /**
     * Send email alert
     */
    private static function sendEmailAlert($alert)
    {
        try {
            // Get admin emails (you can customize this)
            $adminEmails = ['admin@example.com']; // Replace with actual admin emails

            foreach ($adminEmails as $email) {
                Mail::raw($alert->message, function ($message) use ($alert, $email) {
                    $message->to($email)
                        ->subject('[Security Alert] ' . $alert->title);
                });
            }

        } catch (\Exception $e) {
            Log::error('Failed to send email alert: ' . $e->getMessage());
        }
    }

    /**
     * Get approximate location from IP address
     */
    private static function getLocationFromIP($ip)
    {
        try {
            // Simple IP location detection (you can use a service like ipinfo.io)
            if ($ip === '127.0.0.1' || $ip === '::1') {
                return 'Localhost';
            }

            // You can integrate with IP geolocation services here
            return 'Unknown Location';

        } catch (\Exception $e) {
            return 'Unknown Location';
        }
    }
}
