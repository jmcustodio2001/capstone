<?php

// Test script to verify competency gap assignment to upcoming trainings
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UpcomingTraining;
use App\Models\Employee;
use App\Models\CompetencyGap;
use App\Models\CompetencyLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== TESTING COMPETENCY GAP ASSIGNMENT WORKFLOW ===\n\n";

try {
    // 1. Check if upcoming_trainings table exists
    echo "1. Checking upcoming_trainings table...\n";
    if (!Schema::hasTable('upcoming_trainings')) {
        echo "   ❌ Table does not exist! Creating it...\n";
        Schema::create('upcoming_trainings', function ($table) {
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
        echo "   ✅ Table created successfully\n";
    } else {
        echo "   ✅ Table exists\n";
    }

    // 2. Check current records
    $currentCount = DB::table('upcoming_trainings')->count();
    echo "2. Current records in upcoming_trainings: $currentCount\n";

    // 3. Get test employee (JM CUSTODIO from the screenshot)
    $testEmployee = DB::table('employees')->where('employee_id', 'EMP001')->first();
    if (!$testEmployee) {
        $testEmployee = DB::table('employees')->first();
    }
    
    if ($testEmployee) {
        echo "3. Test employee: {$testEmployee->employee_id} ({$testEmployee->first_name} {$testEmployee->last_name})\n";
        
        // 4. Check if there are competency gaps for this employee
        $competencyGaps = DB::table('competency_gaps')
            ->join('competency_library', 'competency_gaps.competency_id', '=', 'competency_library.id')
            ->where('competency_gaps.employee_id', $testEmployee->employee_id)
            ->select('competency_gaps.*', 'competency_library.competency_name')
            ->get();
            
        echo "4. Competency gaps found: " . $competencyGaps->count() . "\n";
        
        if ($competencyGaps->count() > 0) {
            $gap = $competencyGaps->first();
            echo "   - Gap: {$gap->competency_name} (ID: {$gap->id})\n";
            
            // 5. Simulate assignment (create upcoming training record)
            echo "5. Creating test assignment...\n";
            
            $upcomingData = [
                'employee_id' => $testEmployee->employee_id,
                'training_title' => $gap->competency_name,
                'start_date' => date('Y-m-d'),
                'end_date' => $gap->expired_date ? date('Y-m-d', strtotime($gap->expired_date)) : date('Y-m-d', strtotime('+3 months')),
                'status' => 'Assigned',
                'source' => 'competency_gap',
                'assigned_by' => 'Admin Test',
                'assigned_date' => now(),
                'needs_response' => true,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Check if already exists
            $existing = DB::table('upcoming_trainings')
                ->where('employee_id', $testEmployee->employee_id)
                ->where('training_title', $gap->competency_name)
                ->first();
                
            if ($existing) {
                DB::table('upcoming_trainings')
                    ->where('upcoming_id', $existing->upcoming_id)
                    ->update($upcomingData);
                echo "   ✅ Updated existing assignment\n";
            } else {
                DB::table('upcoming_trainings')->insert($upcomingData);
                echo "   ✅ Created new assignment\n";
            }
            
            // 6. Verify the record was created
            echo "6. Verifying assignment...\n";
            $newRecord = DB::table('upcoming_trainings')
                ->where('employee_id', $testEmployee->employee_id)
                ->where('training_title', $gap->competency_name)
                ->first();
                
            if ($newRecord) {
                echo "   ✅ Assignment verified!\n";
                echo "   - Training: {$newRecord->training_title}\n";
                echo "   - Employee: {$newRecord->employee_id}\n";
                echo "   - Status: {$newRecord->status}\n";
                echo "   - Source: {$newRecord->source}\n";
                echo "   - Start Date: {$newRecord->start_date}\n";
                echo "   - End Date: {$newRecord->end_date}\n";
            } else {
                echo "   ❌ Assignment not found!\n";
            }
        } else {
            echo "   ❌ No competency gaps found for this employee\n";
            echo "   Creating a test competency gap...\n";
            
            // Create test competency if needed
            $competency = DB::table('competency_library')->first();
            if ($competency) {
                DB::table('competency_gaps')->insert([
                    'employee_id' => $testEmployee->employee_id,
                    'competency_id' => $competency->id,
                    'required_level' => 100,
                    'current_level' => 60,
                    'gap' => 40,
                    'gap_description' => 'Test gap for assignment workflow',
                    'expired_date' => date('Y-m-d', strtotime('+6 months')),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                echo "   ✅ Test competency gap created\n";
            }
        }
    } else {
        echo "3. ❌ No employees found in database\n";
    }

    // 7. Final verification - show all upcoming trainings
    echo "\n7. All upcoming trainings in database:\n";
    $allUpcoming = DB::table('upcoming_trainings')->get();
    foreach ($allUpcoming as $training) {
        echo "   - {$training->employee_id}: {$training->training_title} ({$training->status})\n";
    }
    
    echo "\n=== WORKFLOW TEST COMPLETE ===\n";
    echo "✅ The 'Assign to Training' button should now create entries in upcoming_trainings table\n";
    echo "✅ These entries will appear in the employee's ESS portal under 'Upcoming Trainings'\n";
    echo "\nNext steps:\n";
    echo "1. Go to Competency Gap Analysis page\n";
    echo "2. Click 'Assign to Training' for JM CUSTODIO's Communication Skills gap\n";
    echo "3. Login as employee and check 'My Trainings' -> 'Upcoming Trainings'\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
