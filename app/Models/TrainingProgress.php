<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TrainingProgress extends Model {
    use HasFactory;
    protected $table = 'training_progress';
    protected $primaryKey = 'progress_id';
    protected $fillable = [
        'employee_id', 'training_title', 'progress_percentage', 'last_updated'
    ];
}
