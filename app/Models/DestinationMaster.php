<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DestinationMaster extends Model
{
    use HasFactory;

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
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
