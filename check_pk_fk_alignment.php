<?php

// Primary Key and Foreign Key Alignment Checker
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking Primary Key and Foreign Key Alignment...\n";
echo "================================================\n\n";

$databaseName = env('DB_DATABASE');

// Get all tables
$tables = DB::select("SHOW TABLES");
$tableColumn = 'Tables_in_' . $databaseName;

$issues = [];
$recommendations = [];

foreach ($tables as $table) {
    $tableName = $table->$tableColumn;
    
    echo "Analyzing table: {$tableName}\n";
    
    // Get primary key info
    $primaryKeys = DB::select("
        SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_KEY = 'PRI'
    ", [$databaseName, $tableName]);
    
    // Get foreign key info
    $foreignKeys = DB::select("
        SELECT 
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME,
            CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
    ", [$databaseName, $tableName]);
    
    // Check each foreign key alignment
    foreach ($foreignKeys as $fk) {
        $fkColumn = $fk->COLUMN_NAME;
        $refTable = $fk->REFERENCED_TABLE_NAME;
        $refColumn = $fk->REFERENCED_COLUMN_NAME;
        
        // Get FK column data type
        $fkColumnInfo = DB::select("
            SELECT DATA_TYPE, COLUMN_TYPE, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ", [$databaseName, $tableName, $fkColumn]);
        
        // Get referenced column data type
        $refColumnInfo = DB::select("
            SELECT DATA_TYPE, COLUMN_TYPE, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ", [$databaseName, $refTable, $refColumn]);
        
        if (!empty($fkColumnInfo) && !empty($refColumnInfo)) {
            $fkType = $fkColumnInfo[0]->COLUMN_TYPE;
            $refType = $refColumnInfo[0]->COLUMN_TYPE;
            
            if ($fkType !== $refType) {
                $issues[] = [
                    'table' => $tableName,
                    'fk_column' => $fkColumn,
                    'fk_type' => $fkType,
                    'ref_table' => $refTable,
                    'ref_column' => $refColumn,
                    'ref_type' => $refType,
                    'issue' => 'Data type mismatch'
                ];
                
                echo "  ❌ FK Issue: {$fkColumn} ({$fkType}) -> {$refTable}.{$refColumn} ({$refType})\n";
            } else {
                echo "  ✅ FK OK: {$fkColumn} -> {$refTable}.{$refColumn}\n";
            }
        } else {
            $issues[] = [
                'table' => $tableName,
                'fk_column' => $fkColumn,
                'ref_table' => $refTable,
                'ref_column' => $refColumn,
                'issue' => 'Referenced table or column not found'
            ];
            echo "  ❌ FK Issue: {$fkColumn} -> {$refTable}.{$refColumn} (table/column not found)\n";
        }
    }
    
    // Display primary keys
    foreach ($primaryKeys as $pk) {
        echo "  🔑 PK: {$pk->COLUMN_NAME} ({$pk->COLUMN_TYPE})\n";
    }
    
    echo "\n";
}

// Summary and recommendations
echo "\n=== ANALYSIS SUMMARY ===\n";
echo "Total issues found: " . count($issues) . "\n\n";

if (!empty($issues)) {
    echo "ISSUES FOUND:\n";
    echo "=============\n";
    
    foreach ($issues as $issue) {
        echo "Table: {$issue['table']}\n";
        echo "  Issue: {$issue['issue']}\n";
        echo "  FK Column: {$issue['fk_column']} ({$issue['fk_type']})\n";
        echo "  References: {$issue['ref_table']}.{$issue['ref_column']} ({$issue['ref_type']})\n";
        echo "\n";
        
        // Generate fix recommendations
        if ($issue['issue'] === 'Data type mismatch') {
            $recommendations[] = "ALTER TABLE `{$issue['table']}` MODIFY `{$issue['fk_column']}` {$issue['ref_type']};";
        } elseif ($issue['issue'] === 'Referenced table or column not found') {
            $recommendations[] = "-- Drop invalid FK: ALTER TABLE `{$issue['table']}` DROP FOREIGN KEY `{$issue['fk_column']}_foreign`;";
        }
    }
    
    echo "\nRECOMMENDED FIXES:\n";
    echo "==================\n";
    foreach ($recommendations as $rec) {
        echo $rec . "\n";
    }
} else {
    echo "✅ No PK/FK alignment issues found!\n";
}

echo "\nAnalysis completed!\n";
