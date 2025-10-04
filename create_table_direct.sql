-- Direct SQL to create training_record_certificate_tracking table
-- Execute this in your MySQL database (phpMyAdmin, MySQL Workbench, or command line)

USE hr2system;

-- Drop table if it exists (to ensure clean creation)
DROP TABLE IF EXISTS `training_record_certificate_tracking`;

-- Create the table based on the migration file
CREATE TABLE `training_record_certificate_tracking` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `employee_id` varchar(50) NOT NULL,
    `course_id` bigint(20) unsigned NOT NULL,
    `training_date` date NOT NULL,
    `certificate_number` varchar(255) DEFAULT NULL,
    `certificate_expiry` date DEFAULT NULL,
    `status` varchar(255) NOT NULL DEFAULT 'Active',
    `remarks` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `training_record_certificate_tracking_employee_id_index` (`employee_id`),
    KEY `training_record_certificate_tracking_course_id_index` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update migrations table to mark this migration as completed
INSERT INTO `migrations` (`migration`, `batch`) 
VALUES ('2025_08_16_140000_create_training_record_certificate_tracking_table', 1)
ON DUPLICATE KEY UPDATE `batch` = 1;

-- Verify table was created
SELECT 'Table created successfully' as status;
DESCRIBE `training_record_certificate_tracking`;
