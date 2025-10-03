-- SQL to create the missing training_record_certificate_tracking table
-- Execute this in your MySQL database

USE hr2system;

CREATE TABLE IF NOT EXISTS `training_record_certificate_tracking` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `employee_id` varchar(50) NOT NULL,
    `course_id` bigint(20) unsigned NOT NULL,
    `training_date` date NOT NULL,
    `certificate_number` varchar(255) DEFAULT NULL,
    `certificate_expiry` date DEFAULT NULL,
    `certificate_url` varchar(255) DEFAULT NULL,
    `status` varchar(255) NOT NULL DEFAULT 'Active',
    `remarks` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_employee_id` (`employee_id`),
    INDEX `idx_course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify the table was created
DESCRIBE training_record_certificate_tracking;
