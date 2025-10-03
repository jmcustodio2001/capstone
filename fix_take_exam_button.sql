-- Fix Take Exam button by ensuring Communication Skills has a course_id

-- First, create Communication Skills course if it doesn't exist
INSERT IGNORE INTO courses (course_title, description, duration_hours, created_at, updated_at)
VALUES ('Communication Skills', 'Develop effective communication skills for professional success', 8, NOW(), NOW());

-- Get the course_id for Communication Skills
SET @course_id = (SELECT course_id FROM courses WHERE course_title = 'Communication Skills' LIMIT 1);

-- Update training_requests to set course_id for Communication Skills entries that don't have one
UPDATE training_requests 
SET course_id = @course_id 
WHERE training_title = 'Communication Skills' 
AND (course_id IS NULL OR course_id = 0);

-- Verify the update
SELECT request_id, training_title, course_id, status 
FROM training_requests 
WHERE training_title = 'Communication Skills';
