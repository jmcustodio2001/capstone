-- Direct SQL script to create the missing training_record_certificate_tracking table
-- Run this script directly in your MySQL database

-- Check if table exists and drop if it does (for clean creation)
DROP TABLE IF EXISTS `training_record_certificate_tracking`;

-- Create the training_record_certificate_tracking table
CREATE TABLE `training_record_certificate_tracking` (
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
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints if the referenced tables exist
-- Note: These may fail if the referenced tables don't exist or have different structures
-- You can run these separately if needed

-- Foreign key for employee_id (if employees table exists)
-- ALTER TABLE `training_record_certificate_tracking` 
-- ADD CONSTRAINT `fk_training_cert_employee_id` 
-- FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

-- Foreign key for course_id (if course_management table exists)
-- ALTER TABLE `training_record_certificate_tracking` 
-- ADD CONSTRAINT `fk_training_cert_course_id` 
-- FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

-- Verify table creation
SELECT 'training_record_certificate_tracking table created successfully' as status;
DESCRIBE training_record_certificate_tracking;
