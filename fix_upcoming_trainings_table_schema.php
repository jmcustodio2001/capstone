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
    echo "Checking upcoming_trainings table schema...\n";
    
    // Check if table exists
    if (!Capsule::schema()->hasTable('upcoming_trainings')) {
        echo "Table upcoming_trainings does not exist. Creating with correct schema...\n";
        
        Capsule::schema()->create('upcoming_trainings', function (Blueprint $table) {
            $table->id('upcoming_id');
            $table->string('employee_id', 20); // VARCHAR for employee IDs like EMP001
            $table->string('training_title');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('Assigned');
            $table->string('source')->nullable();
            $table->string('assigned_by')->nullable();
            $table->timestamp('assigned_date')->nullable();
            $table->unsignedBigInteger('destination_training_id')->nullable();
            $table->boolean('needs_response')->default(false);
            $table->timestamps();
            
            $table->index('employee_id');
            $table->index('destination_training_id');
        });
        
        echo "✅ Created upcoming_trainings table with correct schema\n";
    } else {
        echo "Table exists. Checking employee_id column type...\n";
        
        // Get column information
        $columns = Capsule::select("SHOW COLUMNS FROM upcoming_trainings WHERE Field = 'employee_id'");
        
        if (!empty($columns)) {
            $column = $columns[0];
            echo "Current employee_id column type: " . $column->Type . "\n";
            
            // Check if it's not VARCHAR
            if (strpos(strtolower($column->Type), 'varchar') === false && strpos(strtolower($column->Type), 'char') === false) {
                echo "❌ employee_id column is not VARCHAR. Fixing...\n";
                
                // Modify the column to VARCHAR(20)
                Capsule::statement("ALTER TABLE upcoming_trainings MODIFY COLUMN employee_id VARCHAR(20)");
                
                echo "✅ Fixed employee_id column to VARCHAR(20)\n";
            } else {
                echo "✅ employee_id column is already correct type\n";
            }
        }
    }
    
    // Show final table structure
    echo "\nFinal table structure:\n";
    $columns = Capsule::select("SHOW COLUMNS FROM upcoming_trainings");
    foreach ($columns as $column) {
        echo "- {$column->Field}: {$column->Type}\n";
    }
    
    echo "\n✅ Table schema verification completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
