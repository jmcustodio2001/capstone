-- Create training_requests table if it doesn't exist
CREATE TABLE IF NOT EXISTS `training_requests` (
  `request_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `course_id` bigint(20) UNSIGNED DEFAULT NULL,
  `training_title` varchar(255) NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `requested_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `training_requests_employee_id_index` (`employee_id`),
  KEY `training_requests_course_id_index` (`course_id`),
  KEY `training_requests_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data if table is empty
INSERT IGNORE INTO `training_requests` (`employee_id`, `course_id`, `training_title`, `reason`, `status`, `requested_date`, `created_at`, `updated_at`) 
SELECT 
    e.employee_id,
    cm.course_id,
    cm.course_title,
    'Professional development request',
    'Pending',
    CURDATE(),
    NOW(),
    NOW()
FROM employees e
CROSS JOIN course_management cm
WHERE NOT EXISTS (SELECT 1 FROM training_requests)
LIMIT 3;
