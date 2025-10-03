<?php
/**
 * Run this with: php artisan tinker
 * Then paste this code to create the training_progress table
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

// Check if table exists
if (!Schema::hasTable('training_progress')) {
    // Create training_progress table
    Schema::create('training_progress', function (Blueprint $table) {
        $table->id('progress_id');
        $table->unsignedBigInteger('employee_id');
        $table->string('training_title');
        $table->integer('progress_percentage')->default(0);
        $table->dateTime('last_updated');
        $table->timestamps();
        
        // Add indexes
        $table->index('employee_id', 'idx_employee_id');
        $table->index('training_title', 'idx_training_title');
        $table->index('last_updated', 'idx_last_updated');
    });
    
    echo "✓ training_progress table created successfully.\n";
    
    // Add sample data
    DB::table('training_progress')->insert([
        [
            'employee_id' => 1,
            'training_title' => 'Sample Training Progress',
            'progress_percentage' => 25,
            'last_updated' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'employee_id' => 2,
            'training_title' => 'Leadership Development',
            'progress_percentage' => 50,
            'last_updated' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    
    echo "✓ Sample data added.\n";
} else {
    echo "training_progress table already exists.\n";
}

// Verify table creation
$count = DB::table('training_progress')->count();
echo "Records in training_progress table: $count\n";
