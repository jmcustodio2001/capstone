-- Create training_requests table if it doesn't exist
CREATE TABLE IF NOT EXISTS `training_requests` (
  `request_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `training_title` varchar(255) NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `requested_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `training_requests_course_id_foreign` (`course_id`),
  KEY `training_requests_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing (only if table is empty)
INSERT IGNORE INTO `training_requests` (`employee_id`, `course_id`, `training_title`, `reason`, `status`, `requested_date`, `created_at`, `updated_at`) 
VALUES ('ID-ESP001', 1, 'BAESA', 'IWANT TO DEVELOPMENT MY SKILLS', 'Pending', CURDATE(), NOW(), NOW());

-- Show table status
SELECT COUNT(*) as total_requests FROM training_requests;
SELECT * FROM training_requests LIMIT 5;
