-- Clean up migration records for training_record_certificate_tracking
USE hr2system;

-- Remove the duplicate migration record that's causing the conflict
DELETE FROM migrations WHERE migration = '2025_09_05_230813_create_training_record_certificate_tracking_table_fix';

-- Check current migration records for training_record_certificate_tracking
SELECT * FROM migrations WHERE migration LIKE '%training_record_certificate_tracking%';

-- Verify table exists and has correct structure
DESCRIBE training_record_certificate_tracking;
