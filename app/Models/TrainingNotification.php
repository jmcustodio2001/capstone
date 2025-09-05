<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TrainingNotification extends Model {
    use HasFactory;
    protected $table = 'training_notifications';
    protected $primaryKey = 'notification_id';
    protected $fillable = [
        'employee_id', 'message', 'sent_at'
    ];
}
