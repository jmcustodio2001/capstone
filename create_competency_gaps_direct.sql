-- Direct SQL script to create competency_gaps table
-- This will fix the issue where competency gap data is not saving

-- Drop table if exists (for clean recreation)
DROP TABLE IF EXISTS competency_gaps;

-- Create competency_gaps table
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

-- Add foreign key constraints
ALTER TABLE competency_gaps 
ADD CONSTRAINT competency_gaps_employee_id_foreign 
FOREIGN KEY (employee_id) REFERENCES employees (employee_id) ON DELETE CASCADE;

ALTER TABLE competency_gaps 
ADD CONSTRAINT competency_gaps_competency_id_foreign 
FOREIGN KEY (competency_id) REFERENCES competency_library (id) ON DELETE CASCADE;

-- Insert a test record to verify the table works
-- This will be removed after testing
INSERT INTO competency_gaps (
    employee_id, 
    competency_id, 
    required_level, 
    current_level, 
    gap, 
    gap_description, 
    expired_date, 
    is_active, 
    created_at, 
    updated_at
) VALUES (
    (SELECT employee_id FROM employees LIMIT 1),
    (SELECT id FROM competency_library LIMIT 1),
    5,
    2,
    3,
    'Test record - table creation verification',
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    1,
    NOW(),
    NOW()
);

-- Show the test record
SELECT 'Test record created:' as status;
SELECT * FROM competency_gaps WHERE gap_description LIKE '%Test record%';

-- Clean up test record
DELETE FROM competency_gaps WHERE gap_description LIKE '%Test record%';

SELECT 'Competency gaps table created and tested successfully!' as result;
