-- Create succession_simulations table
CREATE TABLE IF NOT EXISTS `succession_simulations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(255) NOT NULL,
  `position_id` varchar(255) DEFAULT NULL,
  `simulation_name` varchar(255) NOT NULL,
  `simulation_type` enum('leadership', 'technical', 'management', 'strategic') DEFAULT 'leadership',
  `scenario_description` text DEFAULT NULL,
  `simulation_date` date NOT NULL,
  `duration_hours` decimal(4,2) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `max_score` decimal(5,2) DEFAULT 100.00,
  `performance_rating` enum('excellent', 'good', 'satisfactory', 'needs_improvement', 'poor') DEFAULT NULL,
  `competencies_assessed` json DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `areas_for_improvement` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `assessor_id` varchar(255) DEFAULT NULL,
  `status` enum('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_position_id` (`position_id`),
  KEY `idx_simulation_date` (`simulation_date`),
  KEY `idx_status` (`status`),
  KEY `idx_assessor_id` (`assessor_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data for testing
INSERT INTO `succession_simulations` (`employee_id`, `simulation_name`, `simulation_type`, `scenario_description`, `simulation_date`, `duration_hours`, `score`, `performance_rating`, `status`, `created_at`, `updated_at`) VALUES
('EMP001', 'Leadership Crisis Management', 'leadership', 'Handling a major customer complaint and team conflict resolution', '2024-01-15', 2.50, 85.00, 'good', 'completed', NOW(), NOW()),
('EMP002', 'Strategic Planning Simulation', 'strategic', 'Developing a 5-year business expansion plan', '2024-01-20', 4.00, 92.00, 'excellent', 'completed', NOW(), NOW()),
('EMP003', 'Team Management Challenge', 'management', 'Managing a diverse team through organizational change', '2024-02-01', 3.00, 78.00, 'satisfactory', 'completed', NOW(), NOW());
