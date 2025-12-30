-- SQL Script to populate results table with random marks and grades
-- This will create a mix of passed (30%), failed (30%), and average (40%) students

-- Step 1: Update marks with random values (0-100)
-- Distribution: 30% fail (0-29), 40% average (30-64), 30% pass well (65-100)
UPDATE results r
SET r.marks = CASE 
    WHEN RAND() < 0.3 THEN FLOOR(RAND() * 30)  -- 30% will fail (0-29)
    WHEN RAND() < 0.7 THEN 30 + FLOOR(RAND() * 35)  -- 40% will get average (30-64)
    ELSE 65 + FLOOR(RAND() * 36)  -- 30% will pass well (65-100)
END
WHERE r.marks IS NULL AND r.status = 'not_allowed';

-- Step 2: Update grades based on marks, school type, and class name
UPDATE results r
INNER JOIN examinations e ON r.examID = e.examID
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
WHERE r.marks IS NOT NULL AND r.grade IS NULL;

-- Step 3: Update remarks (Pass/Fail)
UPDATE results r
SET r.remark = CASE 
    WHEN r.marks >= 30 THEN 'Pass'
    ELSE 'Fail'
END
WHERE r.marks IS NOT NULL AND r.remark IS NULL;

-- Step 4: Update status to 'allowed'
UPDATE results r
SET r.status = 'allowed'
WHERE r.marks IS NOT NULL AND r.status = 'not_allowed';



