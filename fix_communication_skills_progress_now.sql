-- Direct SQL fix for Communication Skills progress issue
-- This will update the progress to match the exam score

UPDATE employee_training_dashboard etd
JOIN course_management cm ON etd.course_id = cm.course_id
JOIN (
    SELECT 
        employee_id,
        course_id,
        score,
        ROW_NUMBER() OVER (PARTITION BY employee_id, course_id ORDER BY completed_at DESC) as rn
    FROM exam_attempts 
    WHERE employee_id = 'EMP001' AND status IN ('completed', 'failed')
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
    etd.updated_at = NOW(),
    etd.remarks = CONCAT('Updated from exam score: ', latest_exam.score, '%')
WHERE etd.employee_id = 'EMP001' 
AND cm.course_title LIKE '%Communication Skills%';

-- Verify the update
SELECT 
    etd.employee_id,
    cm.course_title,
    etd.progress,
    etd.status,
    etd.updated_at,
    ea.score as exam_score
FROM employee_training_dashboard etd
JOIN course_management cm ON etd.course_id = cm.course_id
LEFT JOIN exam_attempts ea ON ea.employee_id = etd.employee_id 
    AND ea.course_id = etd.course_id
    AND ea.id = (
        SELECT id FROM exam_attempts 
        WHERE employee_id = etd.employee_id 
        AND course_id = etd.course_id 
        ORDER BY completed_at DESC LIMIT 1
    )
WHERE etd.employee_id = 'EMP001' 
AND cm.course_title LIKE '%Communication Skills%';
