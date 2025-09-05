@echo off
echo Creating upcoming_trainings table...

mysql -u root hr2system -e "CREATE TABLE IF NOT EXISTS upcoming_trainings (upcoming_id bigint(20) unsigned NOT NULL AUTO_INCREMENT, employee_id varchar(255) NOT NULL, training_title varchar(255) NOT NULL, start_date date NOT NULL, end_date date DEFAULT NULL, status varchar(255) NOT NULL DEFAULT 'Assigned', source varchar(255) DEFAULT NULL, assigned_by varchar(255) DEFAULT NULL, assigned_date timestamp NULL DEFAULT NULL, destination_training_id bigint(20) unsigned DEFAULT NULL, needs_response tinyint(1) NOT NULL DEFAULT 0, created_at timestamp NULL DEFAULT NULL, updated_at timestamp NULL DEFAULT NULL, PRIMARY KEY (upcoming_id), KEY upcoming_trainings_employee_id_foreign (employee_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"

echo Table created successfully!
echo Testing the assign to upcoming training button now...
pause
