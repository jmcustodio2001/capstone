<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrainingNotification extends Model 
{
    use HasFactory;
    
    protected $table = 'training_notifications';
    protected $primaryKey = 'id'; // Use default 'id' primary key
    
    protected $fillable = [
        'employee_id', 'message', 'sent_at'
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Ensure the training_notifications table exists with correct structure
     */
    public static function ensureTableExists()
    {
        try {
            if (!Schema::hasTable('training_notifications')) {
                Log::info('Creating training_notifications table...');
                
                Schema::create('training_notifications', function (Blueprint $table) {
                    $table->id();
                    $table->string('employee_id', 20);
                    $table->text('message');
                    $table->timestamp('sent_at')->nullable();
                    $table->timestamps();
                    
                    $table->index('employee_id');
                });
                
                Log::info('training_notifications table created successfully');
                
                Log::info('training_notifications table created successfully');
            }
        } catch (\Exception $e) {
            Log::error('Error creating training_notifications table: ' . $e->getMessage());
            
            // Fallback to direct SQL
            try {
                DB::statement("
                    CREATE TABLE IF NOT EXISTS `training_notifications` (
                        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `employee_id` varchar(20) NOT NULL,
                        `message` text NOT NULL,
                        `sent_at` timestamp NULL DEFAULT NULL,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `training_notifications_employee_id_index` (`employee_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                Log::info('training_notifications table created using direct SQL');
                
                Log::info('training_notifications table created using direct SQL');
                
            } catch (\Exception $sqlError) {
                Log::error('Failed to create training_notifications table: ' . $sqlError->getMessage());
                throw $sqlError;
            }
        }
    }
    
}
