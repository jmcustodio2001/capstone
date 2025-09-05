-- Create upcoming_trainings table if it doesn't exist
CREATE TABLE IF NOT EXISTS upcoming_trainings (
    upcoming_id bigint unsigned NOT NULL AUTO_INCREMENT,
    employee_id varchar(20) NOT NULL,
    training_title varchar(255) NOT NULL,
    start_date date NOT NULL,
    end_date date DEFAULT NULL,
    status varchar(255) DEFAULT 'Assigned',
    source varchar(255) DEFAULT NULL,
    assigned_by varchar(255) DEFAULT NULL,
    assigned_date timestamp NULL DEFAULT NULL,
    destination_training_id bigint unsigned DEFAULT NULL,
    needs_response tinyint(1) DEFAULT 1,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (upcoming_id),
    KEY idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert test data for JM CUSTODIO (EMP001)
INSERT INTO upcoming_trainings 
(employee_id, training_title, start_date, end_date, status, source, assigned_by, assigned_date, needs_response, created_at, updated_at)
VALUES 
('EMP001', 'Communication Skills', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'Assigned', 'competency_gap', 'Admin', NOW(), 1, NOW(), NOW()),
('EMP001', 'Leadership Development', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Assigned', 'competency_gap', 'HR Manager', NOW(), 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at);

-- Verify the data
SELECT * FROM upcoming_trainings WHERE employee_id = 'EMP001';
