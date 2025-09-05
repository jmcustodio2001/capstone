<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProfileUpdatesTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile:create-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the profile_updates table with correct structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Drop existing table if it exists
            Schema::dropIfExists('profile_updates');
            $this->info('Dropped existing profile_updates table if it existed.');
            
            // Create table using raw SQL to ensure proper string handling
            DB::statement("
                CREATE TABLE profile_updates (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    employee_id VARCHAR(20) NOT NULL,
                    field_name VARCHAR(255) NOT NULL,
                    old_value TEXT NULL,
                    new_value TEXT NOT NULL,
                    reason TEXT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    requested_at TIMESTAMP NULL,
                    approved_at TIMESTAMP NULL,
                    approved_by BIGINT UNSIGNED NULL,
                    rejection_reason TEXT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    INDEX idx_employee_status (employee_id, status),
                    INDEX idx_requested_at (requested_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->info('✅ Profile updates table created successfully!');
            
            // Test the table by inserting and deleting a test record
            DB::table('profile_updates')->insert([
                'employee_id' => 'TEST001',
                'field_name' => 'test_field',
                'old_value' => 'old',
                'new_value' => 'new',
                'reason' => 'test',
                'status' => 'pending',
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::table('profile_updates')->where('employee_id', 'TEST001')->delete();
            
            $this->info('✅ Table functionality verified successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error creating table: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
