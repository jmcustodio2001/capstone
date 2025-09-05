<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClaimReimbursement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'claim_reimbursements';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'claim_id',
        'claim_type',
        'description',
        'amount',
        'claim_date',
        'receipt_file',
        'status',
        'approved_by',
        'approved_date',
        'rejected_reason',
        'processed_date',
        'payment_method',
        'reference_number',
        'remarks'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'claim_date' => 'date',
        'approved_date' => 'datetime',
        'processed_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'Processed');
    }

    // Helper methods
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'Pending' => 'badge-warning',
            'Approved' => 'badge-success',
            'Rejected' => 'badge-danger',
            'Processed' => 'badge-info',
            default => 'badge-secondary'
        };
    }

    public function getFormattedAmount()
    {
        return '₱' . number_format((float)$this->amount, 2);
    }

    public function canBeEdited()
    {
        return $this->status === 'Pending';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['Pending', 'Approved']);
    }

    // Generate unique claim ID
    public static function generateClaimId()
    {
        $year = date('Y');
        $lastClaim = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastClaim ? (int)substr($lastClaim->claim_id, -4) + 1 : 1;
        
        return 'CR' . $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Boot method for auto-generating claim ID
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->claim_id)) {
                $model->claim_id = self::generateClaimId();
            }
        });
    }
}
