<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop table if exists first
        Schema::dropIfExists('profile_updates');
        
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_updates');
    }
};
