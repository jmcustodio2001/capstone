-- Create upcoming_trainings table if it doesn't exist
  CREATE TABLE IF NOT EXISTS `upcoming_trainings` (
    `upcoming_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `employee_id` varchar(255) NOT NULL,
    `training_title` varchar(255) NOT NULL,
    `start_date` date NOT NULL,
    `end_date` date DEFAULT NULL,
    `status` varchar(255) NOT NULL DEFAULT 'Assigned',
    `source` varchar(255) DEFAULT NULL,
    `assigned_by` varchar(255) DEFAULT NULL,
    `assigned_date` timestamp NULL DEFAULT NULL,
    `destination_training_id` bigint(20) unsigned DEFAULT NULL,
    `needs_response` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`upcoming_id`),
    KEY `upcoming_trainings_employee_id_foreign` (`employee_id`),
    KEY `upcoming_trainings_destination_training_id_foreign` (`destination_training_id`),
    CONSTRAINT `upcoming_trainings_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
    CONSTRAINT `upcoming_trainings_destination_training_id_foreign` FOREIGN KEY (`destination_training_id`) REFERENCES `destination_knowledge_trainings` (`id`) ON DELETE SET NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

  -- Add missing columns if they don't exist
  ALTER TABLE `upcoming_trainings` 
  ADD COLUMN IF NOT EXISTS `source` varchar(255) DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `assigned_by` varchar(255) DEFAULT NULL AFTER `source`,
  ADD COLUMN IF NOT EXISTS `assigned_date` timestamp NULL DEFAULT NULL AFTER `assigned_by`,
  ADD COLUMN IF NOT EXISTS `destination_training_id` bigint(20) unsigned DEFAULT NULL AFTER `assigned_date`,
  ADD COLUMN IF NOT EXISTS `needs_response` tinyint(1) NOT NULL DEFAULT 0 AFTER `destination_training_id`;

  -- Add foreign key constraints if they don't exist
  ALTER TABLE `upcoming_trainings` 
  ADD CONSTRAINT IF NOT EXISTS `upcoming_trainings_destination_training_id_foreign` 
  FOREIGN KEY (`destination_training_id`) REFERENCES `destination_knowledge_trainings` (`id`) ON DELETE SET NULL;

  SELECT 'upcoming_trainings table created/updated successfully!' as result;
