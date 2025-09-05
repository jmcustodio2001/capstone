<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseManagementNotification extends Model
{
    use HasFactory;

    protected $table = 'course_management_notifications';
    protected $fillable = [
        'competency_id',
        'competency_name',
        'message',
        'notification_type',
        'is_read',
        'read_at',
        'created_by',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the competency that triggered this notification
     */
    public function competency()
    {
        return $this->belongsTo(CompetencyLibrary::class, 'competency_id', 'id');
    }

    /**
     * Get the admin who created this notification
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for notifications of specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('notification_type', $type);
    }
}
