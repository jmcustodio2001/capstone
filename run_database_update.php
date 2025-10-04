<?php

// Direct MySQL database update script
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating HR2ESS MySQL Database...\n";
echo "==================================\n\n";

// Read the SQL file
$sqlFile = 'complete_database_fix.sql';
if (!file_exists($sqlFile)) {
    echo "❌ Error: {$sqlFile} not found!\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
$statements = explode(';', $sql);

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }
    
    try {
        DB::statement($statement);
        $successCount++;
        
        // Show progress for major operations
        if (strpos($statement, 'DROP TABLE') !== false) {
            preg_match('/DROP TABLE.*`([^`]+)`/', $statement, $matches);
            if ($matches) {
                echo "✓ Dropped table: {$matches[1]}\n";
            }
        } elseif (strpos($statement, 'DROP VIEW') !== false) {
            echo "✓ Dropped view: destination_knowledge_training\n";
        } elseif (strpos($statement, 'CREATE VIEW') !== false) {
            echo "✓ Created view: destination_knowledge_training\n";
        } elseif (strpos($statement, 'ALTER TABLE') !== false && strpos($statement, 'ADD CONSTRAINT') !== false) {
            preg_match('/ALTER TABLE.*`([^`]+)`.*ADD CONSTRAINT.*`([^`]+)`/', $statement, $matches);
            if ($matches) {
                echo "✓ Added FK constraint: {$matches[1]}.{$matches[2]}\n";
            }
        } elseif (strpos($statement, 'OPTIMIZE TABLE') !== false) {
            preg_match('/OPTIMIZE TABLE.*`([^`]+)`/', $statement, $matches);
            if ($matches) {
                echo "✓ Optimized table: {$matches[1]}\n";
            }
        }
        
    } catch (Exception $e) {
        $errorCount++;
        echo "⚠ Warning: " . $e->getMessage() . "\n";
    }
}

echo "\n=== DATABASE UPDATE SUMMARY ===\n";
echo "Successful operations: {$successCount}\n";
echo "Warnings/Errors: {$errorCount}\n";

if ($errorCount == 0) {
    echo "\n✅ Database update completed successfully!\n";
    echo "\nChanges applied:\n";
    echo "- Removed unnecessary tables\n";
    echo "- Fixed PK/FK alignment issues\n";
    echo "- Recreated destination_knowledge_training view\n";
    echo "- Cleaned orphaned records\n";
    echo "- Optimized all tables\n";
} else {
    echo "\n⚠ Database update completed with some warnings.\n";
    echo "Most operations should have succeeded.\n";
}

echo "\nYour HR2ESS database has been updated!\n";
