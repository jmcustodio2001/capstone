<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    use HasFactory;

    protected $table = 'security_alerts';

    protected $fillable = [
        'type',
        'title',
        'message',
        'details',
        'severity',
        'is_read',
        'admin_id',
        'created_at'
    ];

    protected $casts = [
        'details' => 'array',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Alert types
     */
    const TYPE_LOGIN = 'login_alert';
    const TYPE_SECURITY = 'security_alert';
    const TYPE_SYSTEM = 'system_alert';

    /**
     * Alert severities
     */
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    /**
     * Get unread alerts count
     */
    public static function getUnreadCount()
    {
        return static::where('is_read', false)->count();
    }

    /**
     * Get recent alerts
     */
    public static function getRecent($limit = 10)
    {
        return static::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get alerts by type
     */
    public static function getByType($type, $limit = 10)
    {
        return static::where('type', $type)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get alerts by severity
     */
    public static function getBySeverity($severity, $limit = 10)
    {
        return static::where('severity', $severity)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark alert as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Get alert icon class
     */
    public function getIconAttribute()
    {
        $icons = [
            self::TYPE_LOGIN => 'bi bi-person-check',
            self::TYPE_SECURITY => 'bi bi-shield-exclamation',
            self::TYPE_SYSTEM => 'bi bi-gear'
        ];

        return $icons[$this->type] ?? 'bi bi-bell';
    }

    /**
     * Get alert color class based on severity
     */
    public function getColorClassAttribute()
    {
        $colors = [
            self::SEVERITY_INFO => 'text-info',
            self::SEVERITY_WARNING => 'text-warning',
            self::SEVERITY_HIGH => 'text-danger',
            self::SEVERITY_CRITICAL => 'text-danger'
        ];

        return $colors[$this->severity] ?? 'text-secondary';
    }

    /**
     * Get formatted details
     */
    public function getFormattedDetailsAttribute()
    {
        if (!$this->details) {
            return [];
        }

        $formatted = [];
        
        foreach ($this->details as $key => $value) {
            $formatted[] = [
                'label' => ucwords(str_replace('_', ' ', $key)),
                'value' => is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value
            ];
        }

        return $formatted;
    }

    /**
     * Clean old alerts (keep only recent ones)
     */
    public static function cleanOldAlerts($daysToKeep = 30)
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return static::where('created_at', '<', $cutoffDate)->delete();
    }
}
