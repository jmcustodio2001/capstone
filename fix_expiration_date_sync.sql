-- Add expired_date column to upcoming_trainings table if it doesn't exist
ALTER TABLE upcoming_trainings ADD COLUMN IF NOT EXISTS expired_date TIMESTAMP NULL AFTER end_date;

-- Update existing records to sync expired_date with end_date where expired_date is null
UPDATE upcoming_trainings SET expired_date = end_date WHERE expired_date IS NULL AND end_date IS NOT NULL;

-- Show the structure to verify
DESCRIBE upcoming_trainings;
