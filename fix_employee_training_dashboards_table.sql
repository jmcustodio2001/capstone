-- Fix employee_training_dashboards table name and structure
-- Run this SQL script in your MySQL database management tool

USE hr2system;

-- Check if the singular table exists and rename it to plural
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
                    WHERE table_schema = 'hr2system' 
                    AND table_name = 'employee_training_dashboard');

-- If singular table exists, rename it to plural
SET @sql = IF(@table_exists > 0, 
    'RENAME TABLE employee_training_dashboard TO employee_training_dashboards;',
    'SELECT "Singular table does not exist" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create the table if it doesn't exist (with correct plural name)
CREATE TABLE IF NOT EXISTS `employee_training_dashboards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `course_id` varchar(20) NOT NULL,
  `training_title` varchar(255) DEFAULT NULL,
  `training_date` timestamp NULL DEFAULT NULL,
  `progress` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Not Started',
  `remarks` text DEFAULT NULL,
  `last_accessed` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `expired_date` timestamp NULL DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_training_dashboards_employee_id_index` (`employee_id`),
  KEY `employee_training_dashboards_course_id_index` (`course_id`),
  KEY `employee_training_dashboards_assigned_by_index` (`assigned_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints
ALTER TABLE `employee_training_dashboards` 
ADD CONSTRAINT `employee_training_dashboards_employee_id_foreign` 
FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

-- Show final table structure
DESCRIBE employee_training_dashboards;

SELECT 'employee_training_dashboards table is ready!' as Status;
