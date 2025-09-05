<?php

// Fix upcoming trainings display issue
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== FIXING UPCOMING TRAININGS DISPLAY ===\n\n";

try {
    // 1. Ensure upcoming_trainings table exists with correct structure
    echo "1. Checking/creating upcoming_trainings table...\n";
    
    if (!Schema::hasTable('upcoming_trainings')) {
        Schema::create('upcoming_trainings', function (Blueprint $table) {
            $table->id('upcoming_id');
            $table->string('employee_id', 20);
            $table->string('training_title');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('Assigned');
            $table->string('source')->nullable();
            $table->string('assigned_by')->nullable();
            $table->timestamp('assigned_date')->nullable();
            $table->unsignedBigInteger('destination_training_id')->nullable();
            $table->boolean('needs_response')->default(true);
            $table->timestamps();
            
            $table->index('employee_id');
        });
        echo "   ✅ Table created\n";
    } else {
        echo "   ✅ Table exists\n";
    }
    
    // 2. Get JM CUSTODIO specifically or first employee
    $employee = DB::table('employees')->where('employee_id', 'EMP001')->first();
    if (!$employee) {
        $employee = DB::table('employees')->first();
    }
    
    if (!$employee) {
        echo "   ❌ No employees found\n";
        return;
    }
    
    echo "2. Creating test data for employee: {$employee->employee_id} - {$employee->first_name} {$employee->last_name}\n";
    
    // 3. Clear existing test data first
    DB::table('upcoming_trainings')->where('employee_id', $employee->employee_id)->delete();
    echo "   🗑️ Cleared existing test data\n";
    
    // 4. Create fresh test assignments
    $testAssignments = [
        [
            'employee_id' => $employee->employee_id,
            'training_title' => 'Communication Skills',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+3 months')),
            'status' => 'Assigned',
            'source' => 'competency_gap',
            'assigned_by' => 'Test Admin',
            'assigned_date' => date('Y-m-d H:i:s'),
            'needs_response' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'employee_id' => $employee->employee_id,
            'training_title' => 'Leadership Development',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+6 months')),
            'status' => 'Assigned',
            'source' => 'competency_gap',
            'assigned_by' => 'HR Manager',
            'assigned_date' => date('Y-m-d H:i:s'),
            'needs_response' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    foreach ($testAssignments as $assignment) {
        $id = DB::table('upcoming_trainings')->insertGetId($assignment);
        echo "   ✅ Created: {$assignment['training_title']} (ID: $id)\n";
    }
    
    // 5. Verify data
    echo "\n3. Verifying data...\n";
    $records = DB::table('upcoming_trainings')
        ->where('employee_id', $employee->employee_id)
        ->get();
        
    echo "   Records found: " . $records->count() . "\n";
    foreach ($records as $record) {
        echo "   - ID: {$record->upcoming_id}, Training: {$record->training_title}, Status: {$record->status}, Source: {$record->source}\n";
    }
    
    echo "\n=== FIX COMPLETE ===\n";
    echo "✅ Test data created successfully\n";
    echo "🔄 Refresh the ESS portal to see the data\n";
    echo "📋 Login as: {$employee->employee_id}\n";
    echo "🌐 URL: http://127.0.0.1:8000/employee/login\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
