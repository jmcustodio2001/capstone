-- Fix Primary Key and Foreign Key Alignment Issues
-- Run this SQL script in phpMyAdmin to fix PK/FK alignment problems

-- 1. First, drop all existing foreign key constraints that might cause issues
SET FOREIGN_KEY_CHECKS = 0;

-- Drop foreign keys from employee_training_dashboards
ALTER TABLE `employee_training_dashboards` DROP FOREIGN KEY IF EXISTS `employee_training_dashboards_employee_id_foreign`;
ALTER TABLE `employee_training_dashboards` DROP FOREIGN KEY IF EXISTS `employee_training_dashboards_course_id_foreign`;

-- Drop foreign keys from completed_trainings
ALTER TABLE `completed_trainings` DROP FOREIGN KEY IF EXISTS `completed_trainings_employee_id_foreign`;
ALTER TABLE `completed_trainings` DROP FOREIGN KEY IF EXISTS `completed_trainings_course_id_foreign`;

-- Drop foreign keys from training_requests
ALTER TABLE `training_requests` DROP FOREIGN KEY IF EXISTS `training_requests_employee_id_foreign`;
ALTER TABLE `training_requests` DROP FOREIGN KEY IF EXISTS `training_requests_course_id_foreign`;

-- Drop foreign keys from destination_knowledge_trainings
ALTER TABLE `destination_knowledge_trainings` DROP FOREIGN KEY IF EXISTS `destination_knowledge_trainings_employee_id_foreign`;

-- Drop foreign keys from competency_gaps
ALTER TABLE `competency_gaps` DROP FOREIGN KEY IF EXISTS `competency_gaps_employee_id_foreign`;
ALTER TABLE `competency_gaps` DROP FOREIGN KEY IF EXISTS `competency_gaps_competency_id_foreign`;

-- Drop foreign keys from employee_competency_profiles
ALTER TABLE `employee_competency_profiles` DROP FOREIGN KEY IF EXISTS `employee_competency_profiles_employee_id_foreign`;
ALTER TABLE `employee_competency_profiles` DROP FOREIGN KEY IF EXISTS `employee_competency_profiles_competency_id_foreign`;

-- Drop foreign keys from training_feedback
ALTER TABLE `training_feedback` DROP FOREIGN KEY IF EXISTS `training_feedback_employee_id_foreign`;
ALTER TABLE `training_feedback` DROP FOREIGN KEY IF EXISTS `training_feedback_course_id_foreign`;

-- Drop foreign keys from upcoming_trainings
ALTER TABLE `upcoming_trainings` DROP FOREIGN KEY IF EXISTS `upcoming_trainings_employee_id_foreign`;

-- Drop foreign keys from leave_applications
ALTER TABLE `leave_applications` DROP FOREIGN KEY IF EXISTS `leave_applications_employee_id_foreign`;

-- Drop foreign keys from claim_reimbursements
ALTER TABLE `claim_reimbursements` DROP FOREIGN KEY IF EXISTS `claim_reimbursements_employee_id_foreign`;

-- Drop foreign keys from payslips
ALTER TABLE `payslips` DROP FOREIGN KEY IF EXISTS `payslips_employee_id_foreign`;

-- Drop foreign keys from attendance_time_logs
ALTER TABLE `attendance_time_logs` DROP FOREIGN KEY IF EXISTS `attendance_time_logs_employee_id_foreign`;

-- Drop foreign keys from customer_service_sales_skills_training
ALTER TABLE `customer_service_sales_skills_training` DROP FOREIGN KEY IF EXISTS `customer_service_sales_skills_training_employee_id_foreign`;

-- 2. Ensure primary key data types are consistent
-- Fix employees table primary key
ALTER TABLE `employees` MODIFY `employee_id` VARCHAR(20) NOT NULL;

-- Fix course_management primary key
ALTER TABLE `course_management` MODIFY `course_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;

-- Fix competency_library primary key
ALTER TABLE `competency_library` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;

-- 3. Fix foreign key column data types to match their referenced primary keys
-- Fix employee_id columns to match employees.employee_id (VARCHAR(20))
ALTER TABLE `employee_training_dashboards` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `completed_trainings` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `training_requests` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `destination_knowledge_trainings` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `competency_gaps` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `employee_competency_profiles` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `training_feedback` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `upcoming_trainings` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `leave_applications` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `claim_reimbursements` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `payslips` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `attendance_time_logs` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `customer_service_sales_skills_training` MODIFY `employee_id` VARCHAR(20) NOT NULL;

-- Fix course_id columns to match course_management.course_id (BIGINT UNSIGNED)
ALTER TABLE `employee_training_dashboards` MODIFY `course_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `completed_trainings` MODIFY `course_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `training_requests` MODIFY `course_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `training_feedback` MODIFY `course_id` BIGINT UNSIGNED NOT NULL;

-- Fix competency_id columns to match competency_library.id (BIGINT UNSIGNED)
ALTER TABLE `competency_gaps` MODIFY `competency_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `employee_competency_profiles` MODIFY `competency_id` BIGINT UNSIGNED NOT NULL;

-- 4. Clean up orphaned records before recreating foreign keys
DELETE FROM `employee_training_dashboards` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);
DELETE FROM `employee_training_dashboards` WHERE `course_id` NOT IN (SELECT `course_id` FROM `course_management`);

DELETE FROM `completed_trainings` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);
DELETE FROM `completed_trainings` WHERE `course_id` NOT IN (SELECT `course_id` FROM `course_management`);

DELETE FROM `training_requests` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);
DELETE FROM `training_requests` WHERE `course_id` NOT IN (SELECT `course_id` FROM `course_management`);

DELETE FROM `destination_knowledge_trainings` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);

DELETE FROM `competency_gaps` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);
DELETE FROM `competency_gaps` WHERE `competency_id` NOT IN (SELECT `id` FROM `competency_library`);

DELETE FROM `employee_competency_profiles` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);
DELETE FROM `employee_competency_profiles` WHERE `competency_id` NOT IN (SELECT `id` FROM `competency_library`);

DELETE FROM `training_feedback` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);
DELETE FROM `training_feedback` WHERE `course_id` NOT IN (SELECT `course_id` FROM `course_management`);

DELETE FROM `upcoming_trainings` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);

DELETE FROM `leave_applications` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);

DELETE FROM `claim_reimbursements` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);

DELETE FROM `payslips` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);

DELETE FROM `attendance_time_logs` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);

DELETE FROM `customer_service_sales_skills_training` WHERE `employee_id` NOT IN (SELECT `employee_id` FROM `employees`);

-- 5. Recreate foreign key constraints with proper alignment
-- Employee foreign keys
ALTER TABLE `employee_training_dashboards` ADD CONSTRAINT `employee_training_dashboards_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `completed_trainings` ADD CONSTRAINT `completed_trainings_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `training_requests` ADD CONSTRAINT `training_requests_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `destination_knowledge_trainings` ADD CONSTRAINT `destination_knowledge_trainings_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `competency_gaps` ADD CONSTRAINT `competency_gaps_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `employee_competency_profiles` ADD CONSTRAINT `employee_competency_profiles_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `training_feedback` ADD CONSTRAINT `training_feedback_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `upcoming_trainings` ADD CONSTRAINT `upcoming_trainings_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `leave_applications` ADD CONSTRAINT `leave_applications_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `claim_reimbursements` ADD CONSTRAINT `claim_reimbursements_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `payslips` ADD CONSTRAINT `payslips_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `attendance_time_logs` ADD CONSTRAINT `attendance_time_logs_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `customer_service_sales_skills_training` ADD CONSTRAINT `customer_service_sales_skills_training_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

-- Course foreign keys
ALTER TABLE `employee_training_dashboards` ADD CONSTRAINT `employee_training_dashboards_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

ALTER TABLE `completed_trainings` ADD CONSTRAINT `completed_trainings_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

ALTER TABLE `training_requests` ADD CONSTRAINT `training_requests_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

ALTER TABLE `training_feedback` ADD CONSTRAINT `training_feedback_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

-- Competency foreign keys
ALTER TABLE `competency_gaps` ADD CONSTRAINT `competency_gaps_competency_id_foreign` 
    FOREIGN KEY (`competency_id`) REFERENCES `competency_library` (`id`) ON DELETE CASCADE;

ALTER TABLE `employee_competency_profiles` ADD CONSTRAINT `employee_competency_profiles_competency_id_foreign` 
    FOREIGN KEY (`competency_id`) REFERENCES `competency_library` (`id`) ON DELETE CASCADE;

-- 6. Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- 7. Optimize tables after changes
OPTIMIZE TABLE `employees`;
OPTIMIZE TABLE `course_management`;
OPTIMIZE TABLE `competency_library`;
OPTIMIZE TABLE `employee_training_dashboards`;
OPTIMIZE TABLE `completed_trainings`;
OPTIMIZE TABLE `training_requests`;
OPTIMIZE TABLE `destination_knowledge_trainings`;
OPTIMIZE TABLE `competency_gaps`;
OPTIMIZE TABLE `employee_competency_profiles`;
OPTIMIZE TABLE `training_feedback`;
OPTIMIZE TABLE `upcoming_trainings`;
OPTIMIZE TABLE `leave_applications`;
OPTIMIZE TABLE `claim_reimbursements`;
OPTIMIZE TABLE `payslips`;
OPTIMIZE TABLE `attendance_time_logs`;
OPTIMIZE TABLE `customer_service_sales_skills_training`;
