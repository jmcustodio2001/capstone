-- Create training_requests table for HR2ESS system
USE hr2system;

-- Drop table if exists (for clean recreation)
DROP TABLE IF EXISTS `training_requests`;

-- Create training_requests table
CREATE TABLE `training_requests` (
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
    KEY `training_requests_course_id_foreign` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraint if course_management table exists
-- ALTER TABLE `training_requests` ADD CONSTRAINT `training_requests_course_id_foreign` 
-- FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE SET NULL;

-- Insert sample data for testing
INSERT INTO `training_requests` 
(`employee_id`, `training_title`, `reason`, `status`, `requested_date`, `created_at`, `updated_at`) 
VALUES 
('EMP001', 'Customer Service Excellence', 'Need to improve customer interaction skills', 'Pending', CURDATE(), NOW(), NOW()),
('EMP002', 'Leadership Development', 'Preparing for management role', 'Pending', CURDATE(), NOW(), NOW()),
('EMP003', 'Technical Skills Training', 'Need to update technical competencies', 'Approved', CURDATE() - INTERVAL 1 DAY, NOW(), NOW());

-- Verify the table was created
SELECT 'Table created successfully!' as status;
SELECT COUNT(*) as total_requests FROM training_requests;
SELECT * FROM training_requests;
