-- SQL Script to update marks for examID 30
-- This script will:
-- 1. Update marks (you can modify the marks values)
-- 2. Calculate grades from grade_definitions table
-- 3. Calculate remarks (Pass/Fail)
-- 4. Update status to 'allowed'

-- ============================================
-- OPTION 1: Update with RANDOM marks (for testing)
-- ============================================
-- Uncomment the section below if you want random marks

/*
UPDATE results r
SET r.marks = CASE 
    WHEN RAND() < 0.3 THEN FLOOR(RAND() * 30)  -- 30% will fail (0-29)
    WHEN RAND() < 0.7 THEN 30 + FLOOR(RAND() * 35)  -- 40% will get average (30-64)
    ELSE 65 + FLOOR(RAND() * 36)  -- 30% will pass well (65-100)
END
WHERE r.examID = 30 AND r.marks IS NULL AND r.status = 'not_allowed';
*/

-- ============================================
-- OPTION 2: Update with SPECIFIC marks
-- ============================================
-- Example: Update specific resultID with specific marks
-- Replace resultID and marks values as needed

/*
UPDATE results 
SET marks = 75.00  -- Replace with actual marks
WHERE resultID = 11808 AND examID = 30;
*/

-- ============================================
-- OPTION 3: Bulk update marks from a CSV or specific values
-- ============================================
-- You can create a temporary table and join to update marks
-- Example structure:
/*
CREATE TEMPORARY TABLE temp_marks (
    resultID BIGINT UNSIGNED,
    marks DECIMAL(5,2)
);

-- Insert your marks data here
INSERT INTO temp_marks (resultID, marks) VALUES
(11808, 75.00),
(11809, 65.50),
(11810, 45.00);
-- Add more rows as needed

UPDATE results r
INNER JOIN temp_marks t ON r.resultID = t.resultID
SET r.marks = t.marks
WHERE r.examID = 30;

DROP TEMPORARY TABLE temp_marks;
*/

-- ============================================
-- STEP 2: Calculate grades from grade_definitions table
-- ============================================
-- This uses the grade_definitions table to get grades based on classID and marks
UPDATE results r
INNER JOIN subclasses s ON r.subclassID = s.subclassID
INNER JOIN classes c ON s.classID = c.classID
INNER JOIN grade_definitions gd ON c.classID = gd.classID
SET r.grade = gd.grade
WHERE r.examID = 30 
  AND r.marks IS NOT NULL 
  AND r.grade IS NULL
  AND r.marks >= gd.first 
  AND r.marks <= gd.last;

-- ============================================
-- STEP 3: Fallback - Calculate grades if grade_definitions not found
-- ============================================
-- This will only update results that don't have grades yet
UPDATE results r
INNER JOIN subclasses s ON r.subclassID = s.subclassID
INNER JOIN classes c ON s.classID = c.classID
INNER JOIN schools sch ON c.schoolID = sch.schoolID
SET r.grade = CASE 
    -- For Secondary schools (Form One-Four) - O-Level grading
    WHEN sch.school_type = 'Secondary' AND LOWER(REPLACE(REPLACE(c.class_name, ' ', '_'), '-', '_')) IN ('form_one', 'form_two', 'form_three', 'form_four', 'form_1', 'form_2', 'form_3', 'form_4') THEN
        CASE 
            WHEN r.marks >= 75 THEN 'A'
            WHEN r.marks >= 65 THEN 'B'
            WHEN r.marks >= 45 THEN 'C'
            WHEN r.marks >= 30 THEN 'D'
            WHEN r.marks >= 20 THEN 'E'
            ELSE 'F'
        END
    -- For Secondary schools (Form Five-Six) - A-Level grading
    WHEN sch.school_type = 'Secondary' AND LOWER(REPLACE(REPLACE(c.class_name, ' ', '_'), '-', '_')) IN ('form_five', 'form_six', 'form_5', 'form_6') THEN
        CASE 
            WHEN r.marks >= 80 THEN 'A'
            WHEN r.marks >= 70 THEN 'B'
            WHEN r.marks >= 60 THEN 'C'
            WHEN r.marks >= 50 THEN 'D'
            WHEN r.marks >= 40 THEN 'E'
            ELSE 'S/F'
        END
    -- For Primary schools - Simple grading
    WHEN sch.school_type = 'Primary' THEN
        CASE 
            WHEN r.marks >= 75 THEN 'A'
            WHEN r.marks >= 65 THEN 'B'
            WHEN r.marks >= 45 THEN 'C'
            WHEN r.marks >= 30 THEN 'D'
            ELSE 'F'
        END
    -- Default (fallback)
    ELSE 
        CASE 
            WHEN r.marks >= 75 THEN 'A'
            WHEN r.marks >= 65 THEN 'B'
            WHEN r.marks >= 45 THEN 'C'
            WHEN r.marks >= 30 THEN 'D'
            ELSE 'F'
        END
END
WHERE r.examID = 30 
  AND r.marks IS NOT NULL 
  AND r.grade IS NULL;

-- ============================================
-- STEP 4: Update remarks (Pass/Fail)
-- ============================================
UPDATE results r
SET r.remark = CASE 
    WHEN r.marks >= 30 THEN 'Pass'
    ELSE 'Fail'
END
WHERE r.examID = 30 
  AND r.marks IS NOT NULL 
  AND r.remark IS NULL;

-- ============================================
-- STEP 5: Update status to 'allowed'
-- ============================================
UPDATE results r
SET r.status = 'allowed'
WHERE r.examID = 30 
  AND r.marks IS NOT NULL 
  AND r.status = 'not_allowed';

-- ============================================
-- VERIFICATION: Check updated results
-- ============================================
SELECT 
    COUNT(*) as total_results,
    COUNT(CASE WHEN marks IS NOT NULL THEN 1 END) as results_with_marks,
    COUNT(CASE WHEN grade IS NOT NULL THEN 1 END) as results_with_grades,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as results_allowed
FROM results 
WHERE examID = 30;

