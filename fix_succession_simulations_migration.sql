-- Fix succession_simulations migration conflict
-- Mark the migration as already completed to prevent "table already exists" error

-- Check current migration status
SELECT 'Current succession_simulations migrations:' as info;
SELECT migration, batch FROM migrations WHERE migration LIKE '%succession_simulations%';

-- Get the next batch number
SET @next_batch = (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);

-- Mark the newer, complete migration as run if not already marked
INSERT IGNORE INTO migrations (migration, batch) 
VALUES ('2025_09_06_053425_create_succession_simulations_table', @next_batch);

-- Also mark the older migration as run to prevent any conflicts
INSERT IGNORE INTO migrations (migration, batch) 
VALUES ('2025_08_19_180000_create_succession_simulations_table', @next_batch - 1);

-- Verify the fix
SELECT 'Updated succession_simulations migrations:' as info;
SELECT migration, batch FROM migrations WHERE migration LIKE '%succession_simulations%' ORDER BY batch;

SELECT 'Migration conflict resolved!' as status;
