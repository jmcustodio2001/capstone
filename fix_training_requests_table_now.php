<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

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
    echo "Checking training_requests table...\n";
    
    // Check if table exists
    $tableExists = Capsule::schema()->hasTable('training_requests');
    
    if (!$tableExists) {
        echo "Creating training_requests table...\n";
        
        Capsule::schema()->create('training_requests', function ($table) {
            $table->id('request_id');
            $table->string('employee_id', 20);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('training_title');
            $table->text('reason');
            $table->string('status')->default('Pending');
            $table->date('requested_date');
            $table->timestamps();
            
            // Add indexes
            $table->index('employee_id');
            $table->index('course_id');
            $table->index('status');
        });
        
        echo "✓ training_requests table created successfully!\n";
    } else {
        echo "✓ training_requests table already exists.\n";
        
        // Check if all required columns exist
        $columns = [
            'request_id' => 'bigint unsigned',
            'employee_id' => 'varchar(20)',
            'course_id' => 'bigint unsigned',
            'training_title' => 'varchar(255)',
            'reason' => 'text',
            'status' => 'varchar(255)',
            'requested_date' => 'date',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp'
        ];
        
        foreach ($columns as $column => $type) {
            if (!Capsule::schema()->hasColumn('training_requests', $column)) {
                echo "Adding missing column: $column\n";
                
                Capsule::schema()->table('training_requests', function ($table) use ($column, $type) {
                    switch ($column) {
                        case 'request_id':
                            $table->id('request_id');
                            break;
                        case 'employee_id':
                            $table->string('employee_id', 20);
                            break;
                        case 'course_id':
                            $table->unsignedBigInteger('course_id')->nullable();
                            break;
                        case 'training_title':
                            $table->string('training_title');
                            break;
                        case 'reason':
                            $table->text('reason');
                            break;
                        case 'status':
                            $table->string('status')->default('Pending');
                            break;
                        case 'requested_date':
                            $table->date('requested_date');
                            break;
                        case 'created_at':
                        case 'updated_at':
                            $table->timestamps();
                            break;
                    }
                });
            }
        }
    }
    
    // Add some sample data if table is empty
    $count = Capsule::table('training_requests')->count();
    if ($count == 0) {
        echo "Adding sample training requests...\n";
        
        // Get some employees and courses for sample data
        $employees = Capsule::table('employees')->limit(3)->get();
        $courses = Capsule::table('course_management')->limit(3)->get();
        
        if ($employees->count() > 0 && $courses->count() > 0) {
            $sampleRequests = [
                [
                    'employee_id' => $employees[0]->employee_id,
                    'course_id' => $courses[0]->course_id,
                    'training_title' => $courses[0]->course_title,
                    'reason' => 'Professional development and skill enhancement',
                    'status' => 'Pending',
                    'requested_date' => date('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            if ($employees->count() > 1 && $courses->count() > 1) {
                $sampleRequests[] = [
                    'employee_id' => $employees[1]->employee_id,
                    'course_id' => $courses[1]->course_id,
                    'training_title' => $courses[1]->course_title,
                    'reason' => 'Required for career advancement',
                    'status' => 'Approved',
                    'requested_date' => date('Y-m-d', strtotime('-1 week')),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            foreach ($sampleRequests as $request) {
                Capsule::table('training_requests')->insert($request);
            }
            
            echo "✓ Sample training requests added successfully!\n";
        }
    }
    
    // Show current training requests
    $requests = Capsule::table('training_requests')->get();
    echo "\nCurrent training requests in database:\n";
    echo "Total records: " . $requests->count() . "\n";
    
    foreach ($requests as $request) {
        echo "- Request #{$request->request_id}: {$request->employee_id} - {$request->training_title} ({$request->status})\n";
    }
    
    echo "\n✓ Training requests table is now properly set up!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
