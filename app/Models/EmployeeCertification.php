<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'certificate_name',
        'issuing_organization',
        'issue_date',
        'expiry_date',
        'certificate_file'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the employee that owns the certification
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Get the status of the certification
     */
    public function getStatusAttribute()
    {
        if (!$this->expiry_date) {
            return 'No Expiry';
        }

        return Carbon::now()->greaterThan(Carbon::parse($this->expiry_date)) ? 'Expired' : 'Active';
    }

    /**
     * Get certifications that are about to expire (within 30 days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    /**
     * Get expired certifications
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
    }

    /**
     * Get active certifications
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now());
        });
    }

    /**
     * Automatically delete certificate file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($certification) {
            if ($certification->certificate_file) {
                Storage::disk('public')->delete('certificates/' . $certification->certificate_file);
            }
        });
    }

    /**
     * Get the full file path for the certificate
     */
    public function getFilePathAttribute()
    {
        if (!$this->certificate_file) {
            return null;
        }

        return asset('storage/certificates/' . $this->certificate_file);
    }

    /**
     * Check if the certificate file exists
     */
    public function hasFile()
    {
        return $this->certificate_file && Storage::disk('public')->exists('certificates/' . $this->certificate_file);
    }
}
