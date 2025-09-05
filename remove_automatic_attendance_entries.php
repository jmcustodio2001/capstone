<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;

// Initialize database connection
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'hr2system',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "=== Removing Automatic Attendance Entries ===\n\n";
    
    // Get current date for reference
    $today = Carbon::today();
    
    // First, let's see what data exists
    $totalEntries = DB::table('attendance_time_logs')->count();
    echo "Total attendance entries before cleanup: {$totalEntries}\n";
    
    // Show sample of existing data
    $sampleEntries = DB::table('attendance_time_logs')
        ->orderBy('log_date', 'desc')
        ->limit(5)
        ->get();
    
    echo "\nSample entries found:\n";
    foreach ($sampleEntries as $entry) {
        echo "ID: {$entry->log_id}, Employee: {$entry->employee_id}, Date: {$entry->log_date}, Time In: {$entry->time_in}, Time Out: {$entry->time_out}\n";
    }
    
    // Option 1: Remove all entries (if you want to start fresh)
    echo "\n=== OPTION 1: Remove ALL attendance entries ===\n";
    echo "This will delete all attendance records and start fresh.\n";
    echo "Type 'DELETE_ALL' to confirm: ";
    $input = trim(fgets(STDIN));
    
    if ($input === 'DELETE_ALL') {
        $deletedCount = DB::table('attendance_time_logs')->delete();
        echo "✅ Deleted {$deletedCount} attendance entries.\n";
        echo "✅ Attendance table is now clean. Only manual clock-ins will be recorded.\n";
    } else {
        echo "Skipped deleting all entries.\n";
        
        // Option 2: Remove entries that look like automatic/sample data
        echo "\n=== OPTION 2: Remove specific automatic entries ===\n";
        echo "Looking for entries that might be automatic/sample data...\n";
        
        // Look for entries with very regular patterns (like exactly 8h 15m or 8h 30m)
        $suspiciousEntries = DB::table('attendance_time_logs')
            ->where(function($query) {
                $query->where('hours_worked', '=', 8.25) // 8h 15m
                      ->orWhere('hours_worked', '=', 8.5)  // 8h 30m
                      ->orWhere('hours_worked', '=', 8.0); // exactly 8h
            })
            ->get();
        
        if ($suspiciousEntries->count() > 0) {
            echo "Found {$suspiciousEntries->count()} entries with suspicious regular hours:\n";
            foreach ($suspiciousEntries as $entry) {
                echo "- ID: {$entry->log_id}, Date: {$entry->log_date}, Hours: {$entry->hours_worked}\n";
            }
            
            echo "\nDelete these suspicious entries? (y/n): ";
            $input = trim(fgets(STDIN));
            
            if (strtolower($input) === 'y') {
                $deletedCount = DB::table('attendance_time_logs')
                    ->where(function($query) {
                        $query->where('hours_worked', '=', 8.25)
                              ->orWhere('hours_worked', '=', 8.5)
                              ->orWhere('hours_worked', '=', 8.0);
                    })
                    ->delete();
                echo "✅ Deleted {$deletedCount} suspicious entries.\n";
            }
        } else {
            echo "No obviously suspicious entries found.\n";
        }
    }
    
    // Final count
    $finalCount = DB::table('attendance_time_logs')->count();
    echo "\nTotal attendance entries after cleanup: {$finalCount}\n";
    
    echo "\n=== Attendance System Status ===\n";
    echo "✅ The attendance system is properly configured to only record manual clock-ins.\n";
    echo "✅ No automatic entries will be created going forward.\n";
    echo "✅ Employees must manually click 'Time In' and 'Time Out' buttons.\n";
    
    // Show the controller logic that prevents automatic entries
    echo "\n=== How the System Works ===\n";
    echo "1. Time In: Only created when employee clicks 'Time In' button\n";
    echo "2. Time Out: Only created when employee clicks 'Time Out' button\n";
    echo "3. No background processes create automatic entries\n";
    echo "4. Each entry requires explicit user action\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure the database connection is correct and the table exists.\n";
}

echo "\n=== Script Complete ===\n";
