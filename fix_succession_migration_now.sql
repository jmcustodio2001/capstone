-- Fix succession_simulations migration conflict by marking it as completed
-- This prevents the "table already exists" error

INSERT IGNORE INTO migrations (migration, batch) 
VALUES ('2025_09_06_053425_create_succession_simulations_table', 
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) as temp));

-- Verify the fix
SELECT 'Migration status after fix:' as info;
SELECT migration, batch FROM migrations WHERE migration LIKE '%succession_simulations%';
