<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Destination Knowledge Training Consolidation\n";
echo "=================================================\n\n";

try {
    // Run the consolidation method
    echo "Running consolidation method...\n";
    $result = \App\Models\DestinationKnowledgeTraining::consolidateTables();
    
    if ($result['success']) {
        echo "✅ SUCCESS: " . $result['message'] . "\n";
        echo "📊 Migrated records: " . $result['migrated_records'] . "\n\n";
        
        // Verify the consolidation
        echo "Verifying consolidation...\n";
        
        $totalRecords = \Illuminate\Support\Facades\DB::table('employee_training_dashboards')->count();
        $destinationRecords = \Illuminate\Support\Facades\DB::table('employee_training_dashboards')
            ->where('training_type', 'destination')->count();
        $courseRecords = \Illuminate\Support\Facades\DB::table('employee_training_dashboards')
            ->where('training_type', 'course')->count();
            
        echo "📈 Total records in employee_training_dashboards: {$totalRecords}\n";
        echo "🏝️  Destination training records: {$destinationRecords}\n";
        echo "📚 Course training records: {$courseRecords}\n\n";
        
        // Test the model
        echo "Testing DestinationKnowledgeTraining model...\n";
        $destinations = \App\Models\DestinationKnowledgeTraining::destinationTrainings()->get();
        echo "🔍 Found {$destinations->count()} destination training records via model\n\n";
        
        if ($destinations->count() > 0) {
            echo "Sample destination training record:\n";
            $sample = $destinations->first();
            echo "- Employee ID: {$sample->employee_id}\n";
            echo "- Destination: {$sample->destination_name}\n";
            echo "- Training Type: {$sample->training_type}\n";
            echo "- Source: {$sample->source}\n";
            echo "- Progress: {$sample->progress}%\n\n";
        }
        
        echo "🎉 CONSOLIDATION COMPLETED SUCCESSFULLY!\n";
        echo "You can now safely drop the old tables:\n";
        echo "- DROP VIEW IF EXISTS destination_knowledge_training;\n";
        echo "- DROP TABLE IF EXISTS destination_knowledge_trainings;\n";
        
    } else {
        echo "❌ ERROR: " . $result['message'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
