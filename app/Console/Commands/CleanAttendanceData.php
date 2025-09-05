<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceTimeLog;

class CleanAttendanceData extends Command
{
    protected $signature = 'attendance:clean';
    protected $description = 'Remove automatic attendance entries from the database';

    public function handle()
    {
        $this->info('Cleaning attendance data...');
        
        // Count current entries
        $totalBefore = AttendanceTimeLog::count();
        $this->info("Current attendance entries: {$totalBefore}");
        
        // Show sample data
        $sampleEntries = AttendanceTimeLog::orderBy('log_date', 'desc')->limit(5)->get();
        $this->info('Sample entries:');
        foreach ($sampleEntries as $entry) {
            $this->line("ID: {$entry->log_id}, Employee: {$entry->employee_id}, Date: {$entry->log_date}, In: {$entry->time_in}, Out: {$entry->time_out}");
        }
        
        // Confirm deletion
        if ($this->confirm('Delete all attendance entries to start fresh?')) {
            // Delete all entries
            $deletedCount = AttendanceTimeLog::truncate();
            $this->info("✅ Deleted all attendance entries.");
            
            // Verify cleanup
            $totalAfter = AttendanceTimeLog::count();
            $this->info("Remaining entries: {$totalAfter}");
            
            $this->info('✅ Attendance table cleaned successfully!');
            $this->info('✅ Only manual clock-ins will be recorded going forward.');
        } else {
            $this->info('Operation cancelled.');
        }
        
        return 0;
    }
}
