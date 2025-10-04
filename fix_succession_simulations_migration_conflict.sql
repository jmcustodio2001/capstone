-- Fix succession_simulations migration conflict
-- This script will resolve the duplicate migration issue

-- First, check if the table exists and what migrations are recorded
SELECT 'Checking migrations table...' as status;
SELECT migration, batch FROM migrations WHERE migration LIKE '%succession_simulations%' ORDER BY batch;

-- Check if succession_simulations table exists
SELECT 'Checking if table exists...' as status;
SHOW TABLES LIKE 'succession_simulations';

-- If table exists, check its structure
SELECT 'Table structure:' as status;
DESCRIBE succession_simulations;

-- Insert the missing migration record to prevent re-running
-- We'll mark the newer, more complete migration as run
INSERT IGNORE INTO migrations (migration, batch) 
SELECT '2025_09_06_053425_create_succession_simulations_table', 
       COALESCE((SELECT MAX(batch) FROM migrations), 0) + 1
WHERE NOT EXISTS (
    SELECT 1 FROM migrations 
    WHERE migration = '2025_09_06_053425_create_succession_simulations_table'
);

-- Also mark the older migration as run if it's not already
INSERT IGNORE INTO migrations (migration, batch) 
SELECT '2025_08_19_180000_create_succession_simulations_table', 
       COALESCE((SELECT MAX(batch) FROM migrations), 0)
WHERE NOT EXISTS (
    SELECT 1 FROM migrations 
    WHERE migration = '2025_08_19_180000_create_succession_simulations_table'
);

-- Verify the fix
SELECT 'Final migration status:' as status;
SELECT migration, batch FROM migrations WHERE migration LIKE '%succession_simulations%' ORDER BY batch;
