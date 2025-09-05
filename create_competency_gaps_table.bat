@echo off
echo Creating competency_gaps table...

mysql -u root -p hr2system -e "DROP TABLE IF EXISTS competency_gaps; CREATE TABLE competency_gaps (id bigint unsigned NOT NULL AUTO_INCREMENT, employee_id varchar(20) NOT NULL, competency_id bigint unsigned NOT NULL, required_level int NOT NULL, current_level int NOT NULL, gap int NOT NULL, gap_description text, expired_date timestamp NULL DEFAULT NULL, is_active tinyint(1) NOT NULL DEFAULT 1, created_at timestamp NULL DEFAULT NULL, updated_at timestamp NULL DEFAULT NULL, PRIMARY KEY (id), KEY idx_employee_id (employee_id), KEY idx_competency_id (competency_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; DESCRIBE competency_gaps; SELECT 'Table created successfully!' as result;"

echo Done!
pause
