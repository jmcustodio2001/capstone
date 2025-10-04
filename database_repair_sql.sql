-- Comprehensive Database Cleanup and Repair Script
-- Run this SQL script directly in phpMyAdmin or MySQL command line

-- 1. Drop the problematic view
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

-- 3. Clean up orphaned records (check if tables exist first)
DELETE FROM `employee_training_dashboards` 
WHERE `course_id` NOT IN (SELECT `course_id` FROM `course_management`);

DELETE FROM `completed_trainings` 
WHERE `course_id` NOT IN (SELECT `course_id` FROM `course_management`);

DELETE FROM `training_requests` 
WHERE `course_id` NOT IN (SELECT `course_id` FROM `course_management`);

-- 4. Fix foreign key constraints
ALTER TABLE `competency_course_assignments` DROP FOREIGN KEY IF EXISTS `competency_course_assignments_course_id_foreign`;

-- 5. Drop the old view - no longer needed, using table directly
DROP VIEW IF EXISTS `destination_knowledge_training`;

-- 6. Optimize remaining tables
OPTIMIZE TABLE `users`;
OPTIMIZE TABLE `employees`;
OPTIMIZE TABLE `course_management`;
OPTIMIZE TABLE `employee_training_dashboards`;
OPTIMIZE TABLE `destination_knowledge_trainings`;
OPTIMIZE TABLE `customer_service_sales_skills_training`;
OPTIMIZE TABLE `competency_library`;
OPTIMIZE TABLE `competency_gaps`;
OPTIMIZE TABLE `employee_competency_profiles`;
OPTIMIZE TABLE `training_requests`;
OPTIMIZE TABLE `completed_trainings`;
OPTIMIZE TABLE `upcoming_trainings`;
OPTIMIZE TABLE `training_feedback`;
OPTIMIZE TABLE `leave_applications`;
OPTIMIZE TABLE `claim_reimbursements`;
OPTIMIZE TABLE `payslips`;
OPTIMIZE TABLE `attendance_time_logs`;
OPTIMIZE TABLE `activity_logs`;
