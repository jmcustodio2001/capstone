<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DestinationMaster extends Model
{
    use HasFactory;

    protected $table = 'destination_masters';

    protected $fillable = [
        'destination_name',
        'details',
        'objectives',
        'duration',
        'delivery_mode',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope to get only active destinations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get destination knowledge trainings that use this destination
     */
    public function destinationKnowledgeTrainings()
    {
        return $this->hasMany(DestinationKnowledgeTraining::class, 'destination_name', 'destination_name');
    }

    /**
     * Mark destination as inactive instead of deleting
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Mark destination as active
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }
}
