<?php
require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    // Check if table exists
    if (!Schema::hasTable('employee_training_dashboards')) {
        echo "Creating employee_training_dashboards table...\n";
        
        Schema::create('employee_training_dashboards', function (Blueprint $table) {
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
        
        echo "Table 'employee_training_dashboards' created successfully!\n";
    } else {
        echo "Table 'employee_training_dashboards' already exists.\n";
    }
    
    // Verify table exists
    if (Schema::hasTable('employee_training_dashboards')) {
        echo "Verification: Table exists and is ready to use.\n";
        
        // Show table structure
        $columns = DB::select("DESCRIBE employee_training_dashboards");
        echo "Table structure:\n";
        foreach ($columns as $column) {
            echo "- {$column->Field} ({$column->Type})\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
