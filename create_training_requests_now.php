<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    echo "Creating training_requests table...\n";
    
    // Check if table exists
    if (Schema::hasTable('training_requests')) {
        echo "✅ training_requests table already exists.\n";
    } else {
        // Create the table
        Schema::create('training_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('employee_id', 20);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('training_title');
            $table->text('reason');
            $table->string('status')->default('Pending');
            $table->date('requested_date');
            $table->timestamps();
            
            // Add foreign key if course_management table exists
            if (Schema::hasTable('course_management')) {
                $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('set null');
            }
        });
        
        echo "✅ training_requests table created successfully!\n";
        
        // Insert sample data
        DB::table('training_requests')->insert([
            [
                'employee_id' => 'EMP001',
                'training_title' => 'Customer Service Excellence',
                'reason' => 'Need to improve customer interaction skills',
                'status' => 'Pending',
                'requested_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 'EMP002',
                'training_title' => 'Leadership Development',
                'reason' => 'Preparing for management role',
                'status' => 'Pending',
                'requested_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        echo "✅ Sample training requests added!\n";
    }
    
    // Test the TrainingRequest model
    $count = \App\Models\TrainingRequest::count();
    echo "📊 Total training requests: $count\n";
    
    if ($count > 0) {
        echo "\n📋 Sample training requests:\n";
        $requests = \App\Models\TrainingRequest::with('employee')->limit(3)->get();
        
        foreach ($requests as $request) {
            $employeeName = $request->employee ? $request->employee->first_name . ' ' . $request->employee->last_name : 'Unknown Employee';
            echo "- ID: {$request->request_id}, Employee: {$employeeName} ({$request->employee_id}), Title: {$request->training_title}, Status: {$request->status}\n";
        }
    }
    
    echo "\n✅ Training requests table is ready! You can now test the approval functionality.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
