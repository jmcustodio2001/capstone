-- Remove automatic attendance entries
-- This script will clean the attendance_time_logs table

USE hr2system;

-- Show current data before cleanup
SELECT 'Current attendance entries:' as status, COUNT(*) as count FROM attendance_time_logs;

-- Show sample entries
SELECT 'Sample entries:' as info;
SELECT log_id, employee_id, log_date, time_in, time_out, hours_worked, status 
FROM attendance_time_logs 
ORDER BY log_date DESC 
LIMIT 10;

-- Delete all automatic entries
DELETE FROM attendance_time_logs;

-- Show results after cleanup
SELECT 'Entries after cleanup:' as status, COUNT(*) as count FROM attendance_time_logs;

-- Reset auto increment
ALTER TABLE attendance_time_logs AUTO_INCREMENT = 1;
