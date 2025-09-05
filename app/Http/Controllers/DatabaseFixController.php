<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class DatabaseFixController extends Controller
{
    public function createEmployeeTrainingDashboardTable()
    {
        try {
            // Check if table exists
            if (!Schema::hasTable('employee_training_dashboard')) {
                Log::info('Creating employee_training_dashboard table...');
                
                Schema::create('employee_training_dashboard', function (Blueprint $table) {
                    $table->id();
                    $table->string('employee_id', 20);
                    $table->unsignedBigInteger('course_id');
                    $table->date('training_date')->nullable();
                    $table->integer('progress')->default(0);
                    $table->string('status')->default('Not Started');
                    $table->text('remarks')->nullable();
                    $table->timestamp('last_accessed')->nullable();
                    $table->unsignedBigInteger('assigned_by')->nullable();
                    $table->timestamp('expired_date')->nullable();
                    $table->timestamps();

                    // Add indexes for better performance
                    $table->index('employee_id');
                    $table->index('course_id');
                    $table->index('status');
                });
                
                Log::info('employee_training_dashboard table created successfully');
                return response()->json(['success' => true, 'message' => 'Table created successfully']);
            } else {
                Log::info('employee_training_dashboard table already exists');
                return response()->json(['success' => true, 'message' => 'Table already exists']);
            }
        } catch (\Exception $e) {
            Log::error('Error creating employee_training_dashboard table: ' . $e->getMessage());
            
            // Try direct SQL approach as fallback
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS `employee_training_dashboard` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(20) NOT NULL,
                    `course_id` bigint(20) UNSIGNED NOT NULL,
                    `training_date` date DEFAULT NULL,
                    `progress` int(11) NOT NULL DEFAULT 0,
                    `status` varchar(255) NOT NULL DEFAULT 'Not Started',
                    `remarks` text DEFAULT NULL,
                    `last_accessed` timestamp NULL DEFAULT NULL,
                    `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
                    `expired_date` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `employee_training_dashboard_employee_id_index` (`employee_id`),
                    KEY `employee_training_dashboard_course_id_index` (`course_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                
                Log::info('employee_training_dashboard table created using direct SQL');
                return response()->json(['success' => true, 'message' => 'Table created using direct SQL']);
            } catch (\Exception $sqlError) {
                Log::error('SQL Error: ' . $sqlError->getMessage());
                return response()->json(['success' => false, 'message' => 'Failed to create table: ' . $sqlError->getMessage()]);
            }
        }
    }
}
