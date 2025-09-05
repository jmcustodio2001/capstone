-- Create employee_training_dashboards table
CREATE TABLE IF NOT EXISTS `employee_training_dashboards` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `training_date` date DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'Not Started',
  `remarks` text DEFAULT NULL,
  `last_accessed` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `expired_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_training_dashboards_employee_id_foreign` (`employee_id`),
  KEY `employee_training_dashboards_course_id_foreign` (`course_id`),
  CONSTRAINT `employee_training_dashboards_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  CONSTRAINT `employee_training_dashboards_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
