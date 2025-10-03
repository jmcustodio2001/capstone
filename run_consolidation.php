<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Running Destination Knowledge Training Consolidation...\n";
echo "====================================================\n\n";

try {
    $result = \App\Models\DestinationKnowledgeTraining::consolidateTables();
    
    if ($result['success']) {
        echo "✅ SUCCESS: " . $result['message'] . "\n";
        echo "📊 Migrated records: " . $result['migrated_records'] . "\n";
        echo "\n🎉 CONSOLIDATION COMPLETED!\n";
        echo "You can now safely drop the old table: destination_knowledge_trainings\n";
    } else {
        echo "❌ ERROR: " . $result['message'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
}
