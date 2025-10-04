<?php

// Direct database cleanup script
// This script will drop unnecessary tables directly from the database

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Starting database cleanup...\n";

// List of tables to drop (unnecessary tables identified)
$tablesToDrop = [
    'employee_trainings',
    'trainings', 
    'my_trainings',
    'employee_my_trainings',
    'succession_assessments',
    'succession_candidates', 
    'succession_development_activities',
    'succession_history',
    'succession_readiness_ratings',
    'succession_scenarios',
    'succession_simulations',
    'training_notifications',
    'training_progress',
    'training_record_certificate_tracking',
    'training_reviews'
];

$droppedTables = [];
$notFoundTables = [];

foreach ($tablesToDrop as $table) {
    try {
        if (Schema::hasTable($table)) {
            Schema::dropIfExists($table);
            $droppedTables[] = $table;
            echo "✓ Dropped table: {$table}\n";
        } else {
            $notFoundTables[] = $table;
            echo "- Table not found: {$table}\n";
        }
    } catch (Exception $e) {
        echo "✗ Error dropping table {$table}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== CLEANUP SUMMARY ===\n";
echo "Tables dropped: " . count($droppedTables) . "\n";
echo "Tables not found: " . count($notFoundTables) . "\n";

if (!empty($droppedTables)) {
    echo "\nDropped tables:\n";
    foreach ($droppedTables as $table) {
        echo "- {$table}\n";
    }
}

echo "\nDatabase cleanup completed!\n";
