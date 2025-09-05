<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "=== DEBUG: Training Requests ===\n\n";
    
    // Check if table exists
    if (Schema::hasTable('training_requests')) {
        echo "✅ training_requests table EXISTS\n";
        
        // Get table structure
        $columns = Schema::getColumnListing('training_requests');
        echo "Table columns: " . implode(', ', $columns) . "\n\n";
        
        // Count total records
        $totalCount = DB::table('training_requests')->count();
        echo "Total records: {$totalCount}\n";
        
        if ($totalCount > 0) {
            // Show pending requests
            $pendingCount = DB::table('training_requests')->where('status', 'Pending')->count();
            echo "Pending requests: {$pendingCount}\n\n";
            
            // Show sample data
            echo "Sample training requests:\n";
            $requests = DB::table('training_requests')
                ->select('request_id', 'employee_id', 'training_title', 'status', 'requested_date')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
                
            foreach ($requests as $request) {
                echo "- ID: {$request->request_id}, Employee: {$request->employee_id}, Training: {$request->training_title}, Status: {$request->status}\n";
            }
        } else {
            echo "❌ No training requests found in database\n";
        }
        
    } else {
        echo "❌ training_requests table does NOT exist\n";
    }
    
    echo "\n=== Testing Model Access ===\n";
    
    // Test model access
    try {
        $modelCount = \App\Models\TrainingRequest::count();
        echo "✅ TrainingRequest model works, count: {$modelCount}\n";
        
        if ($modelCount > 0) {
            $sample = \App\Models\TrainingRequest::with(['employee', 'course'])->first();
            echo "Sample request: ID {$sample->request_id}, Title: {$sample->training_title}\n";
            echo "Employee relation: " . ($sample->employee ? $sample->employee->first_name : 'NULL') . "\n";
            echo "Course relation: " . ($sample->course ? $sample->course->course_title : 'NULL') . "\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ TrainingRequest model error: " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
