<?php

// Execute the destination_knowledge_training view fix
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing destination_knowledge_training view...\n";
echo "============================================\n\n";

try {
    // Drop the existing view
    DB::statement('DROP VIEW IF EXISTS `destination_knowledge_training`');
    echo "✓ Dropped existing view\n";
    
    // Create the complete view
    $createViewSQL = "
    CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `destination_knowledge_training` AS 
    SELECT 
        `destination_knowledge_trainings`.`id` AS `id`,
        `destination_knowledge_trainings`.`employee_id` AS `employee_id`,
        `destination_knowledge_trainings`.`destination_name` AS `destination_name`,
        `destination_knowledge_trainings`.`details` AS `details`,
        `destination_knowledge_trainings`.`date_completed` AS `date_completed`,
        `destination_knowledge_trainings`.`expired_date` AS `expired_date`,
        `destination_knowledge_trainings`.`delivery_mode` AS `delivery_mode`,
        `destination_knowledge_trainings`.`progress` AS `progress`,
        `destination_knowledge_trainings`.`remarks` AS `remarks`,
        `destination_knowledge_trainings`.`status` AS `status`,
        `destination_knowledge_trainings`.`is_active` AS `is_active`,
        `destination_knowledge_trainings`.`admin_approved_for_upcoming` AS `admin_approved_for_upcoming`,
        `destination_knowledge_trainings`.`created_at` AS `created_at`,
        `destination_knowledge_trainings`.`updated_at` AS `updated_at`,
        `destination_knowledge_trainings`.`deleted_at` AS `deleted_at`
    FROM `destination_knowledge_trainings`
    WHERE `destination_knowledge_trainings`.`deleted_at` IS NULL
    ";
    
    DB::statement($createViewSQL);
    echo "✓ Created complete destination_knowledge_training view\n";
    
    // Test the view
    $count = DB::table('destination_knowledge_training')->count();
    echo "✓ View test successful - {$count} records accessible\n";
    
    echo "\n=== VIEW FIX COMPLETED ===\n";
    echo "The destination_knowledge_training view has been successfully recreated\n";
    echo "with all columns including the missing updated_at and deleted_at fields.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nPlease run the SQL script manually in phpMyAdmin:\n";
    echo "fix_destination_knowledge_training_view_complete.sql\n";
}
