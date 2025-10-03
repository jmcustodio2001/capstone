-- Database Integrity Check - Primary Key and Foreign Key Alignment
-- Run this SQL script in phpMyAdmin to check PK/FK alignment

-- 1. Check all foreign key constraints and their data types
SELECT 
    kcu.TABLE_NAME,
    kcu.COLUMN_NAME as FK_COLUMN,
    kcu.REFERENCED_TABLE_NAME,
    kcu.REFERENCED_COLUMN_NAME,
    c1.DATA_TYPE as FK_DATA_TYPE,
    c1.COLUMN_TYPE as FK_COLUMN_TYPE,
    c2.DATA_TYPE as REF_DATA_TYPE,
    c2.COLUMN_TYPE as REF_COLUMN_TYPE,
    CASE 
        WHEN c1.COLUMN_TYPE = c2.COLUMN_TYPE THEN 'ALIGNED'
        ELSE 'MISALIGNED'
    END as STATUS
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
    LEFT JOIN INFORMATION_SCHEMA.COLUMNS c1 
        ON kcu.TABLE_SCHEMA = c1.TABLE_SCHEMA 
        AND kcu.TABLE_NAME = c1.TABLE_NAME 
        AND kcu.COLUMN_NAME = c1.COLUMN_NAME
    LEFT JOIN INFORMATION_SCHEMA.COLUMNS c2 
        ON kcu.REFERENCED_TABLE_SCHEMA = c2.TABLE_SCHEMA 
        AND kcu.REFERENCED_TABLE_NAME = c2.TABLE_NAME 
        AND kcu.REFERENCED_COLUMN_NAME = c2.COLUMN_NAME
WHERE 
    kcu.TABLE_SCHEMA = DATABASE()
    AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY 
    kcu.TABLE_NAME, kcu.COLUMN_NAME;

-- 2. Check for orphaned foreign key references
SELECT 'Orphaned Records Check' as CHECK_TYPE;

-- Check employee_training_dashboards
SELECT 'employee_training_dashboards' as TABLE_NAME, COUNT(*) as ORPHANED_COUNT
FROM employee_training_dashboards etd
LEFT JOIN employees e ON etd.employee_id = e.employee_id
LEFT JOIN course_management cm ON etd.course_id = cm.course_id
WHERE e.employee_id IS NULL OR cm.course_id IS NULL;

-- Check completed_trainings
SELECT 'completed_trainings' as TABLE_NAME, COUNT(*) as ORPHANED_COUNT
FROM completed_trainings ct
LEFT JOIN employees e ON ct.employee_id = e.employee_id
LEFT JOIN course_management cm ON ct.course_id = cm.course_id
WHERE e.employee_id IS NULL OR cm.course_id IS NULL;

-- Check training_requests
SELECT 'training_requests' as TABLE_NAME, COUNT(*) as ORPHANED_COUNT
FROM training_requests tr
LEFT JOIN employees e ON tr.employee_id = e.employee_id
LEFT JOIN course_management cm ON tr.course_id = cm.course_id
WHERE e.employee_id IS NULL OR cm.course_id IS NULL;

-- Check destination_knowledge_trainings
SELECT 'destination_knowledge_trainings' as TABLE_NAME, COUNT(*) as ORPHANED_COUNT
FROM destination_knowledge_trainings dkt
LEFT JOIN employees e ON dkt.employee_id = e.employee_id
WHERE e.employee_id IS NULL;

-- Check competency_gaps
SELECT 'competency_gaps' as TABLE_NAME, COUNT(*) as ORPHANED_COUNT
FROM competency_gaps cg
LEFT JOIN employees e ON cg.employee_id = e.employee_id
LEFT JOIN competency_library cl ON cg.competency_id = cl.id
WHERE e.employee_id IS NULL OR cl.id IS NULL;

-- 3. Check primary key consistency
SELECT 
    TABLE_NAME,
    COLUMN_NAME as PRIMARY_KEY,
    DATA_TYPE,
    COLUMN_TYPE,
    IS_NULLABLE
FROM 
    INFORMATION_SCHEMA.COLUMNS 
WHERE 
    TABLE_SCHEMA = DATABASE()
    AND COLUMN_KEY = 'PRI'
ORDER BY 
    TABLE_NAME;

-- 4. Identify tables without proper primary keys
SELECT 
    TABLE_NAME,
    'Missing AUTO_INCREMENT Primary Key' as ISSUE
FROM 
    INFORMATION_SCHEMA.TABLES t
WHERE 
    t.TABLE_SCHEMA = DATABASE()
    AND t.TABLE_TYPE = 'BASE TABLE'
    AND NOT EXISTS (
        SELECT 1 
        FROM INFORMATION_SCHEMA.COLUMNS c 
        WHERE c.TABLE_SCHEMA = t.TABLE_SCHEMA 
        AND c.TABLE_NAME = t.TABLE_NAME 
        AND c.COLUMN_KEY = 'PRI'
        AND c.EXTRA = 'auto_increment'
    )
    AND t.TABLE_NAME NOT IN ('migrations', 'sessions', 'cache', 'jobs', 'failed_jobs');
