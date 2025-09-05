-- Create competency_gaps table directly
-- This script will create the table if it doesn't exist

CREATE TABLE IF NOT EXISTS competency_gaps (
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

-- Show table structure
DESCRIBE competency_gaps;

-- Show current record count
SELECT COUNT(*) as record_count FROM competency_gaps;

SELECT 'competency_gaps table ready!' as status;
