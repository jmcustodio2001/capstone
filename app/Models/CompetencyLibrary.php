<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyLibrary extends Model
{
    use HasFactory;

    // Fix: specify the correct table name
    protected $table = 'competency_library';

    protected $fillable = [
        'competency_name',
        'description',
        'category',
        'rate', // ✅ Added rate field
    ];
}
