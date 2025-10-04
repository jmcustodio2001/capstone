-- Complete fix for destination_knowledge_training view
-- This fixes the truncated view creation statement

-- Drop the existing view if it exists
DROP VIEW IF EXISTS `destination_knowledge_training`;

-- Create the complete view with all columns
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `destination_knowledge_training` AS 
SELECT 
    `destination_knowledge_trainings`.`id` AS `id`,
    `destination_knowledge_trainings`.`employee_id` AS `employee_id`,
    `destination_knowledge_trainings`.`destination_name` AS `destination_name`,
    `destination_knowledge_trainings`.`details` AS `details`,
    `destination_knowledge_trainings`.`date_completed` AS `date_completed`,
    `destination_knowledge_trainings`.`expired_date` AS `expired_date`,
    `destination_knowledge_trainings`.`delivery_mode` AS `delivery_mode`,
    `destination_knowledge_trainings`.`progress` AS `progress`,
    `destination_knowledge_trainings`.`remarks` AS `remarks`,
    `destination_knowledge_trainings`.`status` AS `status`,
    `destination_knowledge_trainings`.`is_active` AS `is_active`,
    `destination_knowledge_trainings`.`admin_approved_for_upcoming` AS `admin_approved_for_upcoming`,
    `destination_knowledge_trainings`.`created_at` AS `created_at`,
    `destination_knowledge_trainings`.`updated_at` AS `updated_at`,
    `destination_knowledge_trainings`.`deleted_at` AS `deleted_at`
FROM `destination_knowledge_trainings`
WHERE `destination_knowledge_trainings`.`deleted_at` IS NULL;
