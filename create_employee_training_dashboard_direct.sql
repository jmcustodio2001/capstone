-- Create employee_training_dashboard table (singular) as expected by the SQL query
CREATE TABLE IF NOT EXISTS `employee_training_dashboard` (
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
    KEY `employee_training_dashboard_employee_id_index` (`employee_id`),
    KEY `employee_training_dashboard_course_id_index` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
