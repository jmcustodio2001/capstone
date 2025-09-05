-- Fix upcoming_trainings table column types
-- Run this SQL script in your MySQL database management tool (phpMyAdmin, MySQL Workbench, etc.)

USE hr2system;

-- Check if table exists and show current structure
DESCRIBE upcoming_trainings;

-- Drop foreign key constraint if it exists (to allow column modification)
SET FOREIGN_KEY_CHECKS = 0;

-- Modify employee_id column to VARCHAR(20) to match employees table
ALTER TABLE `upcoming_trainings` MODIFY COLUMN `employee_id` VARCHAR(20) NOT NULL;

-- Modify date columns to TIMESTAMP for consistency with Laravel
ALTER TABLE `upcoming_trainings` MODIFY COLUMN `start_date` TIMESTAMP NULL;
ALTER TABLE `upcoming_trainings` MODIFY COLUMN `end_date` TIMESTAMP NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Re-add foreign key constraint
ALTER TABLE `upcoming_trainings` 
ADD CONSTRAINT `upcoming_trainings_employee_id_foreign` 
FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

-- Show updated table structure
DESCRIBE upcoming_trainings;

SELECT 'upcoming_trainings table has been fixed successfully!' as Status;
