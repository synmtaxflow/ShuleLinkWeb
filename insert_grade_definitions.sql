-- Insert Grade Definitions for all classes
-- Based on O-Level and A-Level grading systems from ResultManagementController

-- O-Level Grading System (Form 1-4): 75-100=A, 65-74=B, 45-64=C, 30-44=D, 0-29=F

-- FORM ONE (classID = 6)
INSERT INTO grade_definitions (classID, first, last, grade, created_at, updated_at) VALUES
(6, 75.00, 100.00, 'A', NOW(), NOW()),
(6, 65.00, 74.00, 'B', NOW(), NOW()),
(6, 45.00, 64.00, 'C', NOW(), NOW()),
(6, 30.00, 44.00, 'D', NOW(), NOW()),
(6, 0.00, 29.00, 'F', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    first = VALUES(first),
    last = VALUES(last),
    updated_at = NOW();

-- FORM TWO (classID = 15)
INSERT INTO grade_definitions (classID, first, last, grade, created_at, updated_at) VALUES
(15, 75.00, 100.00, 'A', NOW(), NOW()),
(15, 65.00, 74.00, 'B', NOW(), NOW()),
(15, 45.00, 64.00, 'C', NOW(), NOW()),
(15, 30.00, 44.00, 'D', NOW(), NOW()),
(15, 0.00, 29.00, 'F', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    first = VALUES(first),
    last = VALUES(last),
    updated_at = NOW();

-- FORM THREE (classID = 13)
INSERT INTO grade_definitions (classID, first, last, grade, created_at, updated_at) VALUES
(13, 75.00, 100.00, 'A', NOW(), NOW()),
(13, 65.00, 74.00, 'B', NOW(), NOW()),
(13, 45.00, 64.00, 'C', NOW(), NOW()),
(13, 30.00, 44.00, 'D', NOW(), NOW()),
(13, 0.00, 29.00, 'F', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    first = VALUES(first),
    last = VALUES(last),
    updated_at = NOW();

-- FORM FOUR (classID = 14) - Update existing to match O-Level standard (75-100 for A)
INSERT INTO grade_definitions (classID, first, last, grade, created_at, updated_at) VALUES
(14, 75.00, 100.00, 'A', NOW(), NOW()),
(14, 65.00, 74.00, 'B', NOW(), NOW()),
(14, 45.00, 64.00, 'C', NOW(), NOW()),
(14, 30.00, 44.00, 'D', NOW(), NOW()),
(14, 0.00, 29.00, 'F', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    first = VALUES(first),
    last = VALUES(last),
    updated_at = NOW();

-- A-Level Grading System (Form 5-6): >=80=A, >=70=B, >=60=C, >=50=D, >=40=E, <40=S/F

-- FORM FIVE (classID = 10)
INSERT INTO grade_definitions (classID, first, last, grade, created_at, updated_at) VALUES
(10, 80.00, 100.00, 'A', NOW(), NOW()),
(10, 70.00, 79.00, 'B', NOW(), NOW()),
(10, 60.00, 69.00, 'C', NOW(), NOW()),
(10, 50.00, 59.00, 'D', NOW(), NOW()),
(10, 40.00, 49.00, 'E', NOW(), NOW()),
(10, 0.00, 39.00, 'S/F', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    first = VALUES(first),
    last = VALUES(last),
    updated_at = NOW();

-- FORM SIX (classID = 11)
INSERT INTO grade_definitions (classID, first, last, grade, created_at, updated_at) VALUES
(11, 80.00, 100.00, 'A', NOW(), NOW()),
(11, 70.00, 79.00, 'B', NOW(), NOW()),
(11, 60.00, 69.00, 'C', NOW(), NOW()),
(11, 50.00, 59.00, 'D', NOW(), NOW()),
(11, 40.00, 49.00, 'E', NOW(), NOW()),
(11, 0.00, 39.00, 'S/F', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    first = VALUES(first),
    last = VALUES(last),
    updated_at = NOW();











