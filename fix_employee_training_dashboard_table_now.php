<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'hr2system',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $schema = $capsule->schema();
    
    // Check if table exists
    if (!$schema->hasTable('employee_training_dashboards')) {
        echo "Creating employee_training_dashboards table...\n";
        
        $schema->create('employee_training_dashboards', function (Blueprint $table) {
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
        
        echo "✅ employee_training_dashboards table created successfully!\n";
    } else {
        echo "ℹ️  employee_training_dashboards table already exists.\n";
    }
    
    // Verify table structure
    $columns = $schema->getColumnListing('employee_training_dashboards');
    echo "Table columns: " . implode(', ', $columns) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Script completed successfully!\n";
