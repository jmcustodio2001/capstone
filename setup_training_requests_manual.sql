-- Manual setup script for training_requests table
-- Run this in your MySQL/phpMyAdmin to create the missing table

USE hr2system;

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
    KEY `training_requests_course_id_foreign` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample training requests for testing
INSERT IGNORE INTO `training_requests` 
(`employee_id`, `training_title`, `reason`, `status`, `requested_date`, `created_at`, `updated_at`) 
VALUES 
('EMP001', 'Customer Service Excellence', 'Need to improve customer interaction skills', 'Pending', CURDATE(), NOW(), NOW()),
('EMP002', 'Leadership Development', 'Preparing for management role', 'Pending', CURDATE(), NOW(), NOW()),
('EMP003', 'Technical Skills Training', 'Need to update technical competencies', 'Pending', CURDATE(), NOW(), NOW());

-- Verify table creation
SELECT 'Training requests table setup completed!' as status;
SELECT COUNT(*) as total_requests FROM training_requests;
SELECT request_id, employee_id, training_title, status FROM training_requests LIMIT 5;
