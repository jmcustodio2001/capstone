<?php
// app/Models/CompetencyGap.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Models\CompetencyLibrary; // Ensure you have this model imported

class CompetencyGap extends Model
{
    // REMOVED: No longer auto-creating destination knowledge training records
    // This prevents non-destination competencies from appearing in destination knowledge training
    protected $fillable = [
        'employee_id', 'competency_id', 'required_level', 'current_level', 'gap', 'gap_description', 'expired_date', 'is_active'
    ];

    protected $casts = [
        'expired_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($competencyGap) {
            // Automatically set expiration date to 1 week from now when creating
            if (!$competencyGap->expired_date) {
                $competencyGap->expired_date = now()->addWeek();
            }
            // Set as active by default
            if ($competencyGap->is_active === null) {
                $competencyGap->is_active = true;
            }
        });
    }


    public function employee()
    {
        // Use Employee model and correct PK
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id', 'employee_id');
    }

    public function competency()
    {
        return $this->belongsTo(CompetencyLibrary::class, 'competency_id', 'id');
    }

    // Check if the competency gap has expired
    public function isExpired()
    {
        // Check if expired_date column exists and has a value
        if (Schema::hasColumn('competency_gaps', 'expired_date') && $this->expired_date) {
            return now()->isAfter($this->expired_date);
        }
        return false; // Not expired if column doesn't exist or no date set
    }

    // Check if the competency gap is accessible (active and not expired)
    public function isAccessible()
    {
        // Check if is_active column exists
        $isActive = Schema::hasColumn('competency_gaps', 'is_active') ? $this->is_active : true;
        return $isActive && !$this->isExpired();
    }

    // Scope to get only active and non-expired competency gaps
    public function scopeAccessible($query)
    {
        // Check if columns exist before using them
        if (Schema::hasColumn('competency_gaps', 'is_active') && Schema::hasColumn('competency_gaps', 'expired_date')) {
            return $query->where('is_active', true)
                        ->where(function($q) {
                            $q->whereNull('expired_date')
                              ->orWhere('expired_date', '>', now());
                        });
        }
        // Fallback: return all records if columns don't exist yet
        return $query;
    }

    // Scope to get expired competency gaps
    public function scopeExpired($query)
    {
        // Check if expired_date column exists before using it
        if (Schema::hasColumn('competency_gaps', 'expired_date')) {
            return $query->where('expired_date', '<=', now());
        }
        // Fallback: return empty collection if column doesn't exist yet
        return $query->whereRaw('1 = 0');
    }
}
