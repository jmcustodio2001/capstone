-- Alternative: Drop and recreate upcoming_trainings table with correct structure
-- Run this SQL script in your MySQL database management tool

USE hr2system;

-- Backup existing data (optional - uncomment if you have important data)
-- CREATE TABLE upcoming_trainings_backup AS SELECT * FROM upcoming_trainings;

-- Drop the existing table with wrong column types
DROP TABLE IF EXISTS `upcoming_trainings`;

-- Create the table with correct column types
CREATE TABLE `upcoming_trainings` (
  `upcoming_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `training_title` varchar(255) NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Scheduled',
  `source` varchar(255) DEFAULT NULL,
  `assigned_by` varchar(255) DEFAULT NULL,
  `assigned_date` timestamp NULL DEFAULT NULL,
  `destination_training_id` bigint(20) unsigned DEFAULT NULL,
  `needs_response` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`upcoming_id`),
  KEY `upcoming_trainings_employee_id_index` (`employee_id`),
  KEY `upcoming_trainings_destination_training_id_index` (`destination_training_id`),
  CONSTRAINT `upcoming_trainings_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Show the new table structure
DESCRIBE upcoming_trainings;

SELECT 'upcoming_trainings table has been recreated with correct structure!' as Status;
