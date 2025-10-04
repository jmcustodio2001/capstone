-- Complete Database Fix Script
-- This script combines all fixes for a comprehensive database cleanup

-- 1. Drop the problematic view first
DROP VIEW IF EXISTS `destination_knowledge_training`;

-- 2. Drop unnecessary tables
DROP TABLE IF EXISTS `employee_trainings`;
DROP TABLE IF EXISTS `trainings`;
DROP TABLE IF EXISTS `my_trainings`;
DROP TABLE IF EXISTS `employee_my_trainings`;
DROP TABLE IF EXISTS `succession_assessments`;
DROP TABLE IF EXISTS `succession_candidates`;
DROP TABLE IF EXISTS `succession_development_activities`;
DROP TABLE IF EXISTS `succession_history`;
DROP TABLE IF EXISTS `succession_readiness_ratings`;
DROP TABLE IF EXISTS `succession_scenarios`;
DROP TABLE IF EXISTS `succession_simulations`;
DROP TABLE IF EXISTS `training_notifications`;
DROP TABLE IF EXISTS `training_progress`;
DROP TABLE IF EXISTS `training_record_certificate_tracking`;
DROP TABLE IF EXISTS `training_reviews`;

-- 3. Fix PK/FK alignment
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing foreign keys
ALTER TABLE `employee_training_dashboards` DROP FOREIGN KEY IF EXISTS `employee_training_dashboards_employee_id_foreign`;
ALTER TABLE `employee_training_dashboards` DROP FOREIGN KEY IF EXISTS `employee_training_dashboards_course_id_foreign`;
ALTER TABLE `completed_trainings` DROP FOREIGN KEY IF EXISTS `completed_trainings_employee_id_foreign`;
ALTER TABLE `completed_trainings` DROP FOREIGN KEY IF EXISTS `completed_trainings_course_id_foreign`;
ALTER TABLE `training_requests` DROP FOREIGN KEY IF EXISTS `training_requests_employee_id_foreign`;
ALTER TABLE `training_requests` DROP FOREIGN KEY IF EXISTS `training_requests_course_id_foreign`;
ALTER TABLE `destination_knowledge_trainings` DROP FOREIGN KEY IF EXISTS `destination_knowledge_trainings_employee_id_foreign`;
ALTER TABLE `competency_gaps` DROP FOREIGN KEY IF EXISTS `competency_gaps_employee_id_foreign`;
ALTER TABLE `competency_gaps` DROP FOREIGN KEY IF EXISTS `competency_gaps_competency_id_foreign`;
ALTER TABLE `employee_competency_profiles` DROP FOREIGN KEY IF EXISTS `employee_competency_profiles_employee_id_foreign`;
ALTER TABLE `employee_competency_profiles` DROP FOREIGN KEY IF EXISTS `employee_competency_profiles_competency_id_foreign`;
ALTER TABLE `training_feedback` DROP FOREIGN KEY IF EXISTS `training_feedback_employee_id_foreign`;
ALTER TABLE `training_feedback` DROP FOREIGN KEY IF EXISTS `training_feedback_course_id_foreign`;
ALTER TABLE `upcoming_trainings` DROP FOREIGN KEY IF EXISTS `upcoming_trainings_employee_id_foreign`;
ALTER TABLE `leave_applications` DROP FOREIGN KEY IF EXISTS `leave_applications_employee_id_foreign`;
ALTER TABLE `claim_reimbursements` DROP FOREIGN KEY IF EXISTS `claim_reimbursements_employee_id_foreign`;
ALTER TABLE `payslips` DROP FOREIGN KEY IF EXISTS `payslips_employee_id_foreign`;
ALTER TABLE `attendance_time_logs` DROP FOREIGN KEY IF EXISTS `attendance_time_logs_employee_id_foreign`;
ALTER TABLE `customer_service_sales_skills_training` DROP FOREIGN KEY IF EXISTS `customer_service_sales_skills_training_employee_id_foreign`;

-- Fix primary key data types
ALTER TABLE `employees` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `course_management` MODIFY `course_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `competency_library` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;

-- Fix foreign key column data types
ALTER TABLE `employee_training_dashboards` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `employee_training_dashboards` MODIFY `course_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `completed_trainings` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `completed_trainings` MODIFY `course_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `training_requests` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `training_requests` MODIFY `course_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `destination_knowledge_trainings` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `competency_gaps` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `competency_gaps` MODIFY `competency_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `employee_competency_profiles` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `employee_competency_profiles` MODIFY `competency_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `training_feedback` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `training_feedback` MODIFY `course_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `upcoming_trainings` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `leave_applications` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `claim_reimbursements` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `payslips` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `attendance_time_logs` MODIFY `employee_id` VARCHAR(20) NOT NULL;
ALTER TABLE `customer_service_sales_skills_training` MODIFY `employee_id` VARCHAR(20) NOT NULL;

-- Clean orphaned records
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

-- Recreate foreign key constraints
ALTER TABLE `employee_training_dashboards` ADD CONSTRAINT `employee_training_dashboards_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
ALTER TABLE `employee_training_dashboards` ADD CONSTRAINT `employee_training_dashboards_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

ALTER TABLE `completed_trainings` ADD CONSTRAINT `completed_trainings_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
ALTER TABLE `completed_trainings` ADD CONSTRAINT `completed_trainings_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

ALTER TABLE `training_requests` ADD CONSTRAINT `training_requests_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
ALTER TABLE `training_requests` ADD CONSTRAINT `training_requests_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

ALTER TABLE `destination_knowledge_trainings` ADD CONSTRAINT `destination_knowledge_trainings_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

ALTER TABLE `competency_gaps` ADD CONSTRAINT `competency_gaps_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
ALTER TABLE `competency_gaps` ADD CONSTRAINT `competency_gaps_competency_id_foreign` 
    FOREIGN KEY (`competency_id`) REFERENCES `competency_library` (`id`) ON DELETE CASCADE;

ALTER TABLE `employee_competency_profiles` ADD CONSTRAINT `employee_competency_profiles_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
ALTER TABLE `employee_competency_profiles` ADD CONSTRAINT `employee_competency_profiles_competency_id_foreign` 
    FOREIGN KEY (`competency_id`) REFERENCES `competency_library` (`id`) ON DELETE CASCADE;

ALTER TABLE `training_feedback` ADD CONSTRAINT `training_feedback_employee_id_foreign` 
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
ALTER TABLE `training_feedback` ADD CONSTRAINT `training_feedback_course_id_foreign` 
    FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE;

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

-- 4. Drop the old view - no longer needed, using table directly
DROP VIEW IF EXISTS `destination_knowledge_training`;

-- 5. Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- 6. Optimize all tables
OPTIMIZE TABLE `users`;
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
