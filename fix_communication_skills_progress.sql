-- Fix Communication Skills progress for EMP001
-- This script will update the employee_training_dashboard table with the correct progress

-- First, let's check the current state
SELECT 
    etd.employee_id,
    etd.course_id,
    cm.course_title,
    etd.progress as current_progress,
    etd.status as current_status,
    ea.score as exam_score,
    ea.status as exam_status,
    ea.completed_at as exam_completed_at
FROM employee_training_dashboard etd
JOIN course_management cm ON etd.course_id = cm.course_id
LEFT JOIN exam_attempts ea ON ea.employee_id = etd.employee_id AND ea.course_id = etd.course_id
WHERE etd.employee_id = 'EMP001' 
AND cm.course_title LIKE '%Communication Skills%'
ORDER BY ea.completed_at DESC;

-- Update the progress based on the latest exam score
UPDATE employee_training_dashboard etd
JOIN course_management cm ON etd.course_id = cm.course_id
JOIN (
    SELECT 
        employee_id,
        course_id,
        score,
        ROW_NUMBER() OVER (PARTITION BY employee_id, course_id ORDER BY completed_at DESC) as rn
    FROM exam_attempts 
    WHERE employee_id = 'EMP001'
) latest_exam ON latest_exam.employee_id = etd.employee_id 
    AND latest_exam.course_id = etd.course_id 
    AND latest_exam.rn = 1
SET 
    etd.progress = CASE 
        WHEN latest_exam.score >= 80 THEN 100 
        ELSE latest_exam.score 
    END,
    etd.status = CASE 
        WHEN latest_exam.score >= 80 THEN 'Completed' 
        ELSE 'Failed' 
    END,
    etd.updated_at = NOW()
WHERE etd.employee_id = 'EMP001' 
AND cm.course_title LIKE '%Communication Skills%';

-- Verify the update
SELECT 
    etd.employee_id,
    etd.course_id,
    cm.course_title,
    etd.progress as updated_progress,
    etd.status as updated_status,
    etd.updated_at,
    ea.score as exam_score,
    ea.status as exam_status
FROM employee_training_dashboard etd
JOIN course_management cm ON etd.course_id = cm.course_id
LEFT JOIN exam_attempts ea ON ea.employee_id = etd.employee_id AND ea.course_id = etd.course_id
WHERE etd.employee_id = 'EMP001' 
AND cm.course_title LIKE '%Communication Skills%'
ORDER BY ea.completed_at DESC
LIMIT 1;
