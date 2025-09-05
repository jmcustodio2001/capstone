<?php

require_once 'vendor/autoload.php';

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

$schema = Capsule::schema();

try {
    echo "Checking training_requests table...\n";
    
    // Check if table exists
    if (!$schema->hasTable('training_requests')) {
        echo "❌ training_requests table does not exist. Creating it...\n";
        
        $schema->create('training_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('employee_id', 20);
            $table->string('course_id', 255)->nullable();
            $table->string('training_title');
            $table->text('reason');
            $table->string('status')->default('Pending');
            $table->date('requested_date');
            $table->timestamps();
        });
        
        echo "✅ training_requests table created successfully!\n";
    } else {
        echo "✅ training_requests table exists.\n";
        
        // Fix course_id column to handle string values
        echo "Fixing course_id column to VARCHAR(255)...\n";
        
        try {
            // Drop foreign key constraint if it exists
            Capsule::statement("ALTER TABLE training_requests DROP FOREIGN KEY IF EXISTS training_requests_course_id_foreign");
        } catch (Exception $e) {
            // Ignore if constraint doesn't exist
        }
        
        // Modify column to VARCHAR
        Capsule::statement("ALTER TABLE training_requests MODIFY COLUMN course_id VARCHAR(255) NULL");
        
        echo "✅ course_id column fixed to VARCHAR(255).\n";
    }
    
    // Verify table structure
    echo "\nTable structure verification:\n";
    $columns = $schema->getColumnListing('training_requests');
    $requiredColumns = ['request_id', 'employee_id', 'course_id', 'training_title', 'reason', 'status', 'requested_date', 'created_at', 'updated_at'];
    
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "✅ Column '$column' exists\n";
        } else {
            echo "❌ Column '$column' missing\n";
        }
    }
    
    // Check if there are any training requests
    $count = Capsule::table('training_requests')->count();
    echo "\nTotal training requests in database: $count\n";
    
    if ($count > 0) {
        echo "\nSample training requests:\n";
        $samples = Capsule::table('training_requests')
            ->select('request_id', 'employee_id', 'training_title', 'status', 'requested_date')
            ->limit(5)
            ->get();
        
        foreach ($samples as $request) {
            echo "- ID: {$request->request_id}, Employee: {$request->employee_id}, Title: {$request->training_title}, Status: {$request->status}\n";
        }
    }
    
    echo "\n✅ Training requests table check completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
