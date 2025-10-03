-- Create training_record_certificate_tracking table
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
    KEY `training_record_certificate_tracking_employee_id_foreign` (`employee_id`),
    KEY `training_record_certificate_tracking_course_id_foreign` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints if the referenced tables exist
-- Note: These will be added only if the referenced tables exist
SET @sql = 'ALTER TABLE `training_record_certificate_tracking` 
    ADD CONSTRAINT `training_record_certificate_tracking_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE';

SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'employees');

SET @sql = IF(@table_exists > 0, @sql, 'SELECT "employees table not found, skipping foreign key"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'ALTER TABLE `training_record_certificate_tracking` 
    ADD CONSTRAINT `training_record_certificate_tracking_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE';

SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'course_management');

SET @sql = IF(@table_exists > 0, @sql, 'SELECT "course_management table not found, skipping foreign key"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
