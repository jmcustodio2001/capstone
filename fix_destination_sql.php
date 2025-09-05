<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing destination training assignments using direct SQL...\n\n";

try {
    // Update admin_approved_for_upcoming flag for active destination trainings
    $updatedRows = DB::update("
        UPDATE destination_knowledge_trainings 
        SET admin_approved_for_upcoming = 1 
        WHERE is_active = 1 AND admin_approved_for_upcoming = 0
    ");
    
    echo "Updated {$updatedRows} destination training records with admin_approved_for_upcoming = 1\n";
    
    // Get all approved destination trainings that don't have upcoming records
    $approvedTrainings = DB::select("
        SELECT dkt.id, dkt.employee_id, dkt.destination_name
        FROM destination_knowledge_trainings dkt
        LEFT JOIN upcoming_trainings ut ON dkt.employee_id = ut.employee_id 
            AND dkt.destination_name = ut.training_title
        WHERE dkt.admin_approved_for_upcoming = 1 
            AND ut.upcoming_id IS NULL
    ");
    
    $createdCount = 0;
    foreach ($approvedTrainings as $training) {
        DB::insert("
            INSERT INTO upcoming_trainings (employee_id, training_title, start_date, end_date, status, created_at, updated_at)
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH), 'Scheduled', NOW(), NOW())
        ", [$training->employee_id, $training->destination_name]);
        
        $createdCount++;
        echo "Created upcoming training record for Employee {$training->employee_id} - {$training->destination_name}\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Updated admin approval flags: {$updatedRows}\n";
    echo "Created upcoming training records: {$createdCount}\n";
    
    // Check BOHOL specifically
    $bohol = DB::select("
        SELECT dkt.employee_id, dkt.admin_approved_for_upcoming, 
               ut.upcoming_id as upcoming_exists
        FROM destination_knowledge_trainings dkt
        LEFT JOIN upcoming_trainings ut ON dkt.employee_id = ut.employee_id 
            AND ut.training_title = 'BOHOL'
        WHERE dkt.destination_name = 'BOHOL'
    ");
    
    if (!empty($bohol)) {
        $b = $bohol[0];
        echo "\nBOHOL Status:\n";
        echo "Employee ID: {$b->employee_id}\n";
        echo "Admin approved: " . ($b->admin_approved_for_upcoming ? 'YES' : 'NO') . "\n";
        echo "Upcoming record exists: " . ($b->upcoming_exists ? 'YES' : 'NO') . "\n";
    }
    
    echo "\n✓ All destination trainings should now appear in employee upcoming views!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
