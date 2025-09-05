-- Direct SQL fix for competency_gaps table
-- Run this in your MySQL database (hr2system)

-- Drop table if exists to ensure clean creation
DROP TABLE IF EXISTS competency_gaps;

-- Create competency_gaps table with all required columns
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

-- Add foreign key constraints (optional - will fail if referenced tables don't exist)
-- ALTER TABLE competency_gaps 
-- ADD CONSTRAINT competency_gaps_employee_id_foreign 
-- FOREIGN KEY (employee_id) REFERENCES employees (employee_id) ON DELETE CASCADE;

-- ALTER TABLE competency_gaps 
-- ADD CONSTRAINT competency_gaps_competency_id_foreign 
-- FOREIGN KEY (competency_id) REFERENCES competency_library (id) ON DELETE CASCADE;

-- Verify table creation
DESCRIBE competency_gaps;

-- Show success message
SELECT 'competency_gaps table created successfully!' as status;
