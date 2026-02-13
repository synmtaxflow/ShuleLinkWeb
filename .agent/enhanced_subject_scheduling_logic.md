# Enhanced Subject Scheduling Logic for Weekly/Monthly Tests

## Problem Statement (Swahili)
Mwalimu akichagua wiki fulani ya mtihani (mfano wiki ya 7), system ilikuwa inaonyesha "No subjects scheduled for this period" hata kama:
- Schedule iko kwa main class (mfano Form 3)
- Mwalimu ana subject hiyo kwa subclass ya main class hiyo (mfano Form 3A, Form 3B)

## Solution Overview
Tumerekebisha logic ya `getScheduledSubjects` ili iangalie matukio matatu (3 cases):

### Case 1: Direct Teacher Assignment
Mwalimu amewekwa moja kwa moja kwenye schedule ya test.
- **Example:** Schedule iko kwa "Form 3A" na mwalimu ana Mathematics kwa Form 3A

### Case 2: Main Class Schedule with Subclass Teaching ⭐ **NEW**
Schedule iko kwa main class, lakini mwalimu ana subject hiyo kwa subclass ya main class hiyo.
- **Example:** 
  - Schedule iko kwa "Form 3" (main class)
  - Mwalimu ana Mathematics kwa "Form 3A" na "Form 3B" (subclasses)
  - System itaonyesha Mathematics kwa Form 3A na Form 3B

### Case 3: School-Wide Schedule
Schedule iko kwa shule nzima (scope = 'school_wide').
- **Example:**
  - Schedule iko kwa "All School"
  - Mwalimu ana subject hiyo kwa class yoyote
  - System itaonyesha subject zote za mwalimu

## Technical Implementation

### Database Structure
```sql
-- weekly_test_schedules table
- scope: 'school_wide' | 'class' | 'subclass'
- scope_id: classID or subclassID (depending on scope)
- teacher_id: Teacher assigned to schedule (nullable)

-- class_subjects table
- classID: Main class ID (always present)
- subclassID: Subclass ID (nullable - if null, assignment is for main class)
- teacherID: Teacher assigned to teach this subject
```

### Logic Flow

```php
// Get ALL schedules for test type (not filtered by teacher)
$allSchedules = WeeklyTestSchedule::where('test_type', $testType)->get();

foreach ($allSchedules as $schedule) {
    // Case 1: Direct assignment
    if ($schedule->teacher_id == $teacherID) {
        // Find class_subjects matching scope
    }
    
    // Case 2: Main class schedule, teacher has subclass
    if ($schedule->scope === 'class') {
        // Find subclass assignments where:
        // - classID matches schedule's scope_id (main class)
        // - subclassID is NOT NULL (it's a subclass assignment)
        // - teacherID matches current teacher
        // - subjectID matches schedule's subject
    }
    
    // Case 3: School-wide schedule
    if ($schedule->scope === 'school_wide') {
        // Find ALL teacher's subjects for this subject
    }
}
```

## Example Scenario

### Database State:
```
weekly_test_schedules:
- Week 7, Mathematics, scope='class', scope_id=5 (Form 3), teacher_id=NULL

class_subjects:
- teacherID=10, subjectID=2 (Math), classID=5 (Form 3), subclassID=8 (Form 3A)
- teacherID=10, subjectID=2 (Math), classID=5 (Form 3), subclassID=9 (Form 3B)
```

### Before Fix:
Teacher selects Week 7 → **"No subjects scheduled for this period"**
- Reason: System only looked for direct teacher assignment in schedule

### After Fix:
Teacher selects Week 7 → Shows:
- ✅ Mathematics (Form 3A) - Monday 08:00 - 09:00
- ✅ Mathematics (Form 3B) - Monday 08:00 - 09:00

## Benefits

1. **Flexibility:** Schedules can be created at main class level without assigning specific teachers
2. **Accuracy:** Teachers see all subjects they teach that fall within scheduled periods
3. **Scalability:** Works for school-wide, class-level, and subclass-level schedules
4. **No Duplicates:** Uses `unique('class_subjectID')` to prevent duplicate entries

## Code Changes

**File:** `app/Http/Controllers/ManageExaminationController.php`
**Method:** `getScheduledSubjects()`
**Lines:** ~6737-6815

### Key Changes:
1. Removed `->where('teacher_id', $teacherID)` from initial query
2. Added 3 distinct cases with separate logic for each
3. Added subclass detection: `->whereNotNull('subclassID')`
4. Added main class matching: `->where('classID', $schedule->scope_id)`

## Testing Checklist

- [ ] Test with schedule at main class level, teacher has subclass
- [ ] Test with schedule at subclass level, teacher has same subclass
- [ ] Test with school-wide schedule, teacher has multiple classes
- [ ] Test with teacher directly assigned to schedule
- [ ] Verify no duplicate subjects appear
- [ ] Verify correct day and time display

## Notes

- System maintains backward compatibility with existing schedules
- Performance: Queries are optimized with eager loading (`->with()`)
- Uniqueness: Final array uses `unique('class_subjectID')` to prevent duplicates
