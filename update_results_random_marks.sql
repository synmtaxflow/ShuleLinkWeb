-- Quick SQL Script to update marks with RANDOM values for examID 30
-- This will generate random marks and automatically calculate grades and remarks

-- Step 1: Update marks with random values (0-100)
-- Distribution: 30% fail (0-29), 40% average (30-64), 30% pass well (65-100)
UPDATE results r
SET r.marks = CASE 
    WHEN RAND() < 0.3 THEN FLOOR(RAND() * 30)  -- 30% will fail (0-29)
    WHEN RAND() < 0.7 THEN 30 + FLOOR(RAND() * 35)  -- 40% will get average (30-64)
    ELSE 65 + FLOOR(RAND() * 36)  -- 30% will pass well (65-100)
END
WHERE r.examID = 30 AND r.marks IS NULL AND r.status = 'not_allowed';

-- Step 2: Calculate grades from grade_definitions table (if available)
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

-- Step 3: Fallback - Calculate grades if grade_definitions not found
UPDATE results r
INNER JOIN subclasses s ON r.subclassID = s.subclassID
INNER JOIN classes c ON s.classID = c.classID
INNER JOIN schools sch ON c.schoolID = sch.schoolID
SET r.grade = CASE 
    WHEN sch.school_type = 'Secondary' AND LOWER(REPLACE(REPLACE(c.class_name, ' ', '_'), '-', '_')) IN ('form_one', 'form_two', 'form_three', 'form_four', 'form_1', 'form_2', 'form_3', 'form_4') THEN
        CASE 
            WHEN r.marks >= 75 THEN 'A'
            WHEN r.marks >= 65 THEN 'B'
            WHEN r.marks >= 45 THEN 'C'
            WHEN r.marks >= 30 THEN 'D'
            WHEN r.marks >= 20 THEN 'E'
            ELSE 'F'
        END
    WHEN sch.school_type = 'Secondary' AND LOWER(REPLACE(REPLACE(c.class_name, ' ', '_'), '-', '_')) IN ('form_five', 'form_six', 'form_5', 'form_6') THEN
        CASE 
            WHEN r.marks >= 80 THEN 'A'
            WHEN r.marks >= 70 THEN 'B'
            WHEN r.marks >= 60 THEN 'C'
            WHEN r.marks >= 50 THEN 'D'
            WHEN r.marks >= 40 THEN 'E'
            ELSE 'S/F'
        END
    WHEN sch.school_type = 'Primary' THEN
        CASE 
            WHEN r.marks >= 75 THEN 'A'
            WHEN r.marks >= 65 THEN 'B'
            WHEN r.marks >= 45 THEN 'C'
            WHEN r.marks >= 30 THEN 'D'
            ELSE 'F'
        END
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

-- Step 4: Update remarks (Pass/Fail)
UPDATE results r
SET r.remark = CASE 
    WHEN r.marks >= 30 THEN 'Pass'
    ELSE 'Fail'
END
WHERE r.examID = 30 
  AND r.marks IS NOT NULL 
  AND r.remark IS NULL;

-- Step 5: Update status to 'allowed'
UPDATE results r
SET r.status = 'allowed'
WHERE r.examID = 30 
  AND r.marks IS NOT NULL 
  AND r.status = 'not_allowed';

-- Verification
SELECT 
    COUNT(*) as total_results,
    COUNT(CASE WHEN marks IS NOT NULL THEN 1 END) as results_with_marks,
    COUNT(CASE WHEN grade IS NOT NULL THEN 1 END) as results_with_grades,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as results_allowed
FROM results 
WHERE examID = 30;

