-- OPTION 2: Manual SQL execution for training_record_certificate_tracking table
-- Execute this in phpMyAdmin or MySQL Workbench

USE hr2system;

-- Step 1: Remove existing migration record
DELETE FROM migrations WHERE migration = '2025_08_16_140000_create_training_record_certificate_tracking_table';

-- Step 2: Drop table if exists
DROP TABLE IF EXISTS training_record_certificate_tracking;

-- Step 3: Create the table with proper structure
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
    KEY `training_record_certificate_tracking_employee_id_index` (`employee_id`),
    KEY `training_record_certificate_tracking_course_id_index` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Add migration record back
INSERT INTO migrations (migration, batch) VALUES ('2025_08_16_140000_create_training_record_certificate_tracking_table', 1);

-- Step 5: Verify table creation
SELECT 'Table created successfully!' as status;
DESCRIBE training_record_certificate_tracking;
