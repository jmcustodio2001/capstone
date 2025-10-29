<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'admin_id',
        'admin_name',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Disable updated_at timestamp
     */
    public $timestamps = false;

    /**
     * Get the admin that performed the action
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Log an admin action
     */
    public static function logAction($action, $details = [], $adminId = null, $adminName = null)
    {
        try {
            return static::create([
                'admin_id' => $adminId,
                'admin_name' => $adminName ?? 'System',
                'action' => $action,
                'details' => $details,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create audit log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get recent audit logs
     */
    public static function getRecent($limit = 50)
    {
        return static::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by admin
     */
    public static function getByAdmin($adminId, $limit = 50)
    {
        return static::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by action type
     */
    public static function getByAction($action, $limit = 50)
    {
        return static::where('action', 'like', '%' . $action . '%')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean old logs (keep only recent ones)
     */
    public static function cleanOldLogs($daysToKeep = 90)
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return static::where('created_at', '<', $cutoffDate)->delete();
    }
}
