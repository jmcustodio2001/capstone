<?php

require_once 'vendor/autoload.php';

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Checking upcoming_trainings table...\n";
    
    // Check if table exists
    if (!Schema::hasTable('upcoming_trainings')) {
        echo "Creating upcoming_trainings table...\n";
        
        Schema::create('upcoming_trainings', function (Blueprint $table) {
            $table->id('upcoming_id');
            $table->string('employee_id');
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
            
            // Add foreign key constraints
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('destination_training_id')->references('id')->on('destination_knowledge_trainings')->onDelete('set null');
        });
        
        echo "✅ upcoming_trainings table created successfully!\n";
    } else {
        echo "✅ upcoming_trainings table already exists.\n";
        
        // Check if required columns exist and add them if missing
        $columnsToAdd = [
            'source' => 'string',
            'assigned_by' => 'string', 
            'assigned_date' => 'timestamp',
            'destination_training_id' => 'unsignedBigInteger',
            'needs_response' => 'boolean'
        ];
        
        foreach ($columnsToAdd as $column => $type) {
            if (!Schema::hasColumn('upcoming_trainings', $column)) {
                echo "Adding missing column: $column\n";
                Schema::table('upcoming_trainings', function (Blueprint $table) use ($column, $type) {
                    switch ($type) {
                        case 'string':
                            $table->string($column)->nullable();
                            break;
                        case 'timestamp':
                            $table->timestamp($column)->nullable();
                            break;
                        case 'unsignedBigInteger':
                            $table->unsignedBigInteger($column)->nullable();
                            break;
                        case 'boolean':
                            $table->boolean($column)->default(false);
                            break;
                    }
                });
                echo "✅ Added column: $column\n";
            }
        }
    }
    
    echo "\n🎉 upcoming_trainings table is ready!\n";
    echo "You can now use the 'Assign to Upcoming Training' button.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
