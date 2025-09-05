<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Checking if training_requests table exists...\n";
    
    if (Schema::hasTable('training_requests')) {
        echo "✅ training_requests table already exists!\n";
        
        // Check if table has data
        $count = DB::table('training_requests')->count();
        echo "📊 Table contains {$count} records\n";
        
        // Show table structure
        $columns = Schema::getColumnListing('training_requests');
        echo "📋 Table columns: " . implode(', ', $columns) . "\n";
        
    } else {
        echo "❌ training_requests table does not exist. Creating it now...\n";
        
        Schema::create('training_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('employee_id', 20);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('training_title');
            $table->text('reason');
            $table->string('status')->default('Pending');
            $table->date('requested_date');
            $table->timestamps();
            
            // Add foreign key constraint if course_management table exists
            if (Schema::hasTable('course_management')) {
                $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('set null');
                echo "🔗 Added foreign key constraint to course_management table\n";
            }
            
            // Add foreign key constraint if employees table exists
            if (Schema::hasTable('employees')) {
                $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
                echo "🔗 Added foreign key constraint to employees table\n";
            }
        });
        
        echo "✅ training_requests table created successfully!\n";
        
        // Insert sample data for testing
        echo "📝 Inserting sample training request for testing...\n";
        
        DB::table('training_requests')->insert([
            'employee_id' => 'ID-ESP001', // Using the employee from the screenshot
            'course_id' => 1, // Assuming BAESA course has ID 1
            'training_title' => 'BAESA',
            'reason' => 'IWANT TO DEVELOPMENT MY SKILLS',
            'status' => 'Pending',
            'requested_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ Sample training request inserted!\n";
    }
    
    // Test the TrainingRequest model
    echo "\n🧪 Testing TrainingRequest model...\n";
    
    $trainingRequests = \App\Models\TrainingRequest::with(['employee', 'course'])->get();
    echo "📊 Found {$trainingRequests->count()} training requests in database\n";
    
    foreach ($trainingRequests as $request) {
        echo "  - Request #{$request->request_id}: {$request->employee_id} -> {$request->training_title} ({$request->status})\n";
    }
    
    echo "\n✅ All checks completed successfully!\n";
    echo "🎯 The approve/reject buttons should now work properly.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    
    // Log the error
    Log::error('Error creating training_requests table: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
