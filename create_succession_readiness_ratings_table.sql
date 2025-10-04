-- Create succession_readiness_ratings table
-- This fixes the SQLSTATE[42S02] error: Base table or view not found

CREATE TABLE IF NOT EXISTS `succession_readiness_ratings` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `employee_id` varchar(20) NOT NULL,
    `readiness_score` int(11) NOT NULL,
    `assessment_date` date NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `succession_readiness_ratings_employee_id_foreign` (`employee_id`),
    CONSTRAINT `succession_readiness_ratings_employee_id_foreign` 
        FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data for testing (optional)
INSERT INTO `succession_readiness_ratings` (`employee_id`, `readiness_score`, `assessment_date`, `created_at`, `updated_at`) VALUES
('EMP001', 85, '2024-01-15', NOW(), NOW()),
('EMP002', 92, '2024-01-15', NOW(), NOW()),
('EMP003', 78, '2024-01-15', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
