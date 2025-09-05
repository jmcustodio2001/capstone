USE hr2system;

-- Create competency_gaps table - Force recreation
DROP TABLE IF EXISTS competency_gaps;

CREATE TABLE competency_gaps (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    employee_id varchar(20) NOT NULL,
    competency_id bigint unsigned NOT NULL,
    required_level int NOT NULL,
    current_level int NOT NULL,
    gap int NOT NULL,
    gap_description text,
    expired_date timestamp NULL DEFAULT NULL,
    is_active tinyint(1) NOT NULL DEFAULT '1',
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY competency_gaps_employee_id_foreign (employee_id),
    KEY competency_gaps_competency_id_foreign (competency_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Show table structure to verify creation
DESCRIBE competency_gaps;

-- Show success message
SELECT 'competency_gaps table created successfully!' as result;

CREATE TABLE IF NOT EXISTS `employee_training_dashboards` (
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
    KEY `employee_training_dashboards_employee_id_index` (`employee_id`),
    KEY `employee_training_dashboards_course_id_index` (`course_id`),
    KEY `employee_training_dashboards_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Table created successfully' as Result;
