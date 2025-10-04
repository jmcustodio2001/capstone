-- Create succession_readiness_ratings table directly
CREATE TABLE IF NOT EXISTS `succession_readiness_ratings` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `employee_id` varchar(20) NOT NULL,
    `readiness_score` int(11) NOT NULL DEFAULT 0,
    `readiness_level` varchar(255) DEFAULT NULL,
    `assessment_notes` text DEFAULT NULL,
    `assessment_date` date DEFAULT NULL,
    `assessed_by` varchar(255) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `succession_readiness_ratings_employee_id_index` (`employee_id`),
    KEY `succession_readiness_ratings_readiness_score_index` (`readiness_score`),
    KEY `succession_readiness_ratings_readiness_level_index` (`readiness_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraint if employees table exists
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'employees');
SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE `succession_readiness_ratings` ADD CONSTRAINT `succession_readiness_ratings_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;',
    'SELECT "Employees table not found, skipping foreign key constraint" as message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
