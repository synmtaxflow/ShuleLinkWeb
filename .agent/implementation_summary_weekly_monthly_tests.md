# Weekly/Monthly Test Exam Paper Upload Implementation

## Overview
Implemented a comprehensive system for uploading exam papers for weekly and monthly tests with context-aware logic that considers test periods, holidays, and scheduled subjects.

## Database Changes

### 1. Migration: `2026_02_11_183056_add_test_week_to_exam_papers_table.php`
- **Added columns to `exam_papers` table:**
  - `test_week` (string, nullable) - Stores the week/month period (e.g., "Week of 2026-01-06 to 2026-01-12")
  - `test_date` (date, nullable) - Stores specific date if applicable
- **Status:** ✅ Migrated successfully

### 2. Model Updates: `ExamPaper.php`
- Added `test_week` and `test_date` to fillable attributes
- Maintains backward compatibility with existing exam papers

## Backend Implementation

### 1. New API Endpoints in `ManageExaminationController.php`

#### `getTestByTypeYear(Request $request)`
- **Route:** `GET /get_test_by_type_year`
- **Purpose:** Fetches weekly or monthly test examination for a specific year
- **Parameters:**
  - `type`: 'weekly_test' or 'monthly_test'
  - `year`: Year (e.g., 2026)
- **Returns:** Examination object if found

#### `getAvailablePeriods(Request $request)`
- **Route:** `GET /get_available_periods`
- **Purpose:** Returns available weeks/months for a year, **excluding holidays**
- **Parameters:**
  - `year`: Year to fetch periods for
  - `test_type`: 'weekly_test' or 'monthly_test'
- **Features:**
  - Integrates with `Holiday` model and `TanzaniaHolidaysService`
  - Filters out weeks that fall entirely on holidays
  - Returns weeks with at least one working day
  - For monthly tests, returns all 12 months
  - For weekly tests, calculates 53 weeks starting from first Monday
- **Returns:** Array of periods with id and text

#### `getScheduledSubjects(Request $request)`
- **Route:** `GET /get_scheduled_subjects`
- **Purpose:** Fetches subjects scheduled for a specific test period
- **Parameters:**
  - `examID`: Examination ID
  - `test_week`: Selected week/month period
- **Features:**
  - Queries `weekly_test_schedules` table
  - Filters by teacher, test type (weekly/monthly)
  - Returns subjects with class, day, and time information
- **Returns:** Array of scheduled subjects

### 2. Updated `storeExamPaper` Method
- **Added validation for:**
  - `test_week` (nullable string)
  - `test_date` (nullable date)
- **Enhanced duplicate checking:**
  - For weekly/monthly tests, checks for existing papers with same exam, subject, AND test_week
  - For regular exams, checks without test_week
- **Stores test_week and test_date** when creating exam paper

### 3. Updated `getExamPapers` Method
- **Added filtering by `test_week`**
- Allows admins to view exam papers for specific weeks/months

## Frontend Implementation

### 1. Teacher Exam Papers View (`exam_papers.blade.php`)

#### New UI Components
- **Test Fields Container** (hidden by default):
  - **Year Selector:** Dropdown for selecting year (current year - 2 to current year)
  - **Period Selector:** Dynamically populated with weeks/months (excluding holidays)
  - **Scheduled Subject Selector:** Shows only subjects scheduled for the selected period
  
#### Dynamic Behavior (JavaScript)
1. **Exam Selection Handler:**
   - Detects if selected exam is "Weekly Test" or "Monthly Test"
   - Shows test-specific fields and hides regular subject selection
   - Loads available periods for current year

2. **Year Change Handler:**
   - Reloads available periods when year changes

3. **Period Change Handler:**
   - Loads scheduled subjects for the selected period
   - Displays subject with class, day, and time

4. **Helper Functions:**
   - `loadAvailablePeriods(year, testType)` - Fetches and populates period dropdown
   - `loadScheduledSubjects(examID, testWeek)` - Fetches and populates subject dropdown

## Integration with Existing Systems

### 1. Holiday Integration
- Uses `Holiday` model to fetch school-specific holidays
- Uses `TanzaniaHolidaysService` for auto-detected public holidays
- Combines both sources to exclude all non-working days from available periods

### 2. Weekly Test Schedules
- Leverages existing `weekly_test_schedules` table
- Filters subjects by:
  - School ID
  - Test type (weekly/monthly)
  - Teacher ID
  - Scope (school_wide, class, subclass)

### 3. Backward Compatibility
- Regular exam paper uploads work exactly as before
- Test-specific fields only appear for Weekly/Monthly tests
- Nullable columns ensure existing data remains intact

## User Flow

### For Teachers Uploading Weekly/Monthly Test Papers:
1. Navigate to "Exam Papers" page
2. Select "Weekly Test" or "Monthly Test" from examination dropdown
3. **System automatically:**
   - Shows year, period, and subject selectors
   - Hides regular subject dropdown
4. Select year (defaults to current year)
5. Select period (week/month) - **only non-holiday periods shown**
6. Select subject from scheduled subjects for that period
7. Upload exam paper file
8. Submit

### For Admins Viewing Test Papers:
- Can filter exam papers by test week
- See date range of the week in the exam paper details
- View all past weeks within the year

## Key Features

✅ **Holiday Awareness:** Automatically skips holidays when showing available weeks
✅ **Subject Filtering:** Only shows subjects scheduled for the specific test period
✅ **Year Selection:** Supports multiple years for historical test uploads
✅ **Duplicate Prevention:** Prevents uploading same paper for same week/subject
✅ **Context-Aware UI:** Different upload flow for tests vs regular exams
✅ **Backward Compatible:** Existing exam paper functionality unchanged

## Testing Recommendations

1. **Test with holidays:**
   - Create holidays in calendar
   - Verify they're excluded from available weeks

2. **Test with weekly test schedules:**
   - Create weekly/monthly test schedules
   - Verify only scheduled subjects appear

3. **Test duplicate prevention:**
   - Try uploading same subject for same week twice
   - Verify error message

4. **Test year switching:**
   - Switch between years
   - Verify periods reload correctly

5. **Test admin view:**
   - View exam papers as admin
   - Filter by test week
   - Verify date ranges display correctly

## Files Modified

### Backend:
- `app/Http/Controllers/ManageExaminationController.php` - Added 3 new methods, updated 2 existing
- `app/Models/ExamPaper.php` - Added fillable attributes
- `database/migrations/2026_02_11_183056_add_test_week_to_exam_papers_table.php` - New migration
- `routes/web.php` - Added 3 new routes

### Frontend:
- `resources/views/Teacher/exam_papers.blade.php` - Added test-specific UI and JavaScript logic

## Next Steps (Optional Enhancements)

1. **Admin Printing Unit:** Update to show test week in exam paper listings
2. **Reporting:** Add reports for test paper submission rates by week
3. **Notifications:** SMS notifications could include week information
4. **Bulk Upload:** Allow uploading papers for multiple weeks at once
5. **Calendar View:** Show test paper deadlines on calendar

## Notes

- The implementation uses the existing `weekly_test_schedules` table structure
- Holiday data is fetched from both manual entries and auto-detected Tanzania holidays
- The system is designed to scale for future test types (e.g., quarterly tests)
- All AJAX calls include proper error handling
