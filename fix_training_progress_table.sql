-- Fix missing training_progress table
-- This script creates the training_progress table if it doesn't exist

-- Drop table if it exists (to ensure clean creation)
DROP TABLE IF EXISTS `training_progress`;

-- Create training_progress table
CREATE TABLE `training_progress` (
  `progress_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `training_title` varchar(255) NOT NULL,
  `progress_percentage` int(11) NOT NULL DEFAULT 0,
  `last_updated` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`progress_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_training_title` (`training_title`),
  KEY `idx_last_updated` (`last_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data if needed (optional)
-- INSERT INTO `training_progress` (`employee_id`, `training_title`, `progress_percentage`, `last_updated`, `created_at`, `updated_at`) 
-- VALUES 
-- (1, 'Sample Training', 0, NOW(), NOW(), NOW());

SELECT 'training_progress table created successfully' as status;
