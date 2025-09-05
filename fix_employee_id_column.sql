-- Fix employee_id column type in upcoming_trainings table
USE hr2system;

-- Check current table structure
DESCRIBE upcoming_trainings;

-- Modify employee_id column to VARCHAR(20) to handle employee IDs like 'EMP001'
ALTER TABLE upcoming_trainings MODIFY COLUMN employee_id VARCHAR(20);

-- Verify the change
DESCRIBE upcoming_trainings;

-- Show any existing data
SELECT * FROM upcoming_trainings LIMIT 5;
