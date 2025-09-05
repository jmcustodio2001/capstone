-- Create competency_gaps table if it doesn't exist
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

-- Add foreign key constraints if they don't exist
-- Note: These may fail if the referenced tables don't exist, but that's okay
ALTER TABLE competency_gaps 
ADD CONSTRAINT competency_gaps_employee_id_foreign 
FOREIGN KEY (employee_id) REFERENCES employees (employee_id) ON DELETE CASCADE;

ALTER TABLE competency_gaps 
ADD CONSTRAINT competency_gaps_competency_id_foreign 
FOREIGN KEY (competency_id) REFERENCES competency_library (id) ON DELETE CASCADE;
