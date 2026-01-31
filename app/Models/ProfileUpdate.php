<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_name',
        'employee_email',
        'employee_profile_picture',
        'field_name',
        'old_value',
        'new_value',
        'reason',
        'status',
        'requested_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the profile update.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Get the admin who approved/rejected the update.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending updates.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved updates.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected updates.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the status badge class for UI display.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'approved' => 'bg-success',
            'pending' => 'bg-warning',
            'rejected' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Get formatted field name for display.
     */
    public function getFormattedFieldNameAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->field_name));
    }

    /**
     * Get the display value for the current value.
     */
    public function getDisplayOldValueAttribute()
    {
        if ($this->field_name === 'profile_picture' && $this->old_value && $this->old_value !== 'N/A') {
            return '<img src="' . asset('storage/' . $this->old_value) . '" alt="Current Profile Picture" style="max-width: 50px; max-height: 50px; border-radius: 4px;">';
        }
        return $this->old_value;
    }

    /**
     * Get the display value for the new value.
     */
    public function getDisplayNewValueAttribute()
    {
        if ($this->field_name === 'profile_picture' && $this->new_value) {
            return '<img src="' . asset('storage/' . $this->new_value) . '" alt="New Profile Picture" style="max-width: 50px; max-height: 50px; border-radius: 4px;">';
        }
        return $this->new_value;
    }
}
