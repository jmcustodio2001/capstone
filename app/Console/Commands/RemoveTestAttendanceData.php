<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceTimeLog;

class RemoveTestAttendanceData extends Command
{
    protected $signature = 'attendance:remove-test-data';
    protected $description = 'Remove only the test attendance entries (EMP001) from the database';

    public function handle()
    {
        $this->info('Removing test attendance data...');
        
        // Count current test entries
        $testEntries = AttendanceTimeLog::where('employee_id', 'EMP001')->get();
        $testCount = $testEntries->count();
        
        if ($testCount === 0) {
            $this->info('No test attendance entries found.');
            return 0;
        }
        
        $this->info("Found {$testCount} test attendance entries for EMP001:");
        
        // Show test entries
        foreach ($testEntries as $entry) {
            $this->line("ID: {$entry->id}, Employee: {$entry->employee_id}, Date: {$entry->log_date}, In: {$entry->time_in}, Out: {$entry->time_out}, Status: {$entry->status}");
        }
        
        // Confirm deletion
        if ($this->confirm('Delete these test entries?')) {
            // Delete only EMP001 entries
            $deletedCount = AttendanceTimeLog::where('employee_id', 'EMP001')->delete();
            $this->info("✅ Deleted {$deletedCount} test attendance entries.");
            
            // Verify cleanup
            $remainingTestEntries = AttendanceTimeLog::where('employee_id', 'EMP001')->count();
            $totalRemaining = AttendanceTimeLog::count();
            
            $this->info("Remaining EMP001 entries: {$remainingTestEntries}");
            $this->info("Total attendance entries remaining: {$totalRemaining}");
            
            $this->info('✅ Test attendance data cleaned successfully!');
            $this->info('✅ Real employee attendance data preserved.');
        } else {
            $this->info('Operation cancelled.');
        }
        
        return 0;
    }
}
