# Multi-Step Student Registration System

## Overview
This is a comprehensive 5-step student registration form with health information, emergency contacts, and declaration fields.

## Database Migrations

Before using the system, run the migrations to add the required columns to the `students` and `parents` tables:

```bash
php artisan migrate
```

This will:
1. Add `relationship_to_student` column to the `parents` table
2. Add multiple health, emergency contact, and declaration columns to the `students` table

### New Columns in `parents` table:
- `relationship_to_student` (varchar) - Parent/Guardian relationship type

### New Columns in `students` table:
- `birth_certificate_number` - Birth certificate ID
- `religion` - Student's religion
- `nationality` - Student's nationality
- `general_health_condition` - Health condition description
- `has_disability` - Boolean flag
- `disability_details` - Disability description
- `has_chronic_illness` - Boolean flag
- `chronic_illness_details` - Chronic illness description
- `immunization_details` - Vaccination records
- `emergency_contact_name` - Emergency contact person name
- `emergency_contact_relationship` - Relationship to student
- `emergency_contact_phone` - Emergency contact phone
- `parent_declaration` - Declaration text
- `parent_signature` - Parent signature
- `declaration_date` - Declaration date
- `registering_officer_name` - Officer name
- `registering_officer_title` - Officer title
- `registering_officer_signature` - Officer signature
- `school_stamp` - School stamp image path
- `registration_status` - Registration status (Draft/Submitted/Completed)

## Registration Flow

### Step 1: Student Particulars (SECTION A)
- Full name (first, middle, last)
- Gender
- Date of birth (age auto-calculated)
- Birth certificate number
- Religion
- Nationality
- Student photo (optional)

### Step 2: Parent/Guardian Information (SECTION B)
- Search existing parent by phone
- If found, use existing parent details
- If not found, enter new parent details:
  - Name (first, middle, last)
  - Phone number
  - Relationship to student (Parent, Guardian, Next of Kin, Other)
  - Occupation
  - Email
  - Residential address

### Step 3: Health Information (SECTION C)
- General health condition
- Disability status with details (optional)
- Chronic illness status with details (optional)
- Immunization/vaccination records

### Step 4: Emergency Contact (SECTION D)
- Emergency contact person name
- Relationship to student
- Emergency contact phone number

### Step 5: Declaration & Official Use (SECTIONS E & F)
- Parent/Guardian declaration checkbox
- Declaration date
- Parent signature (optional)
- Registering officer details (optional)
- School stamp/seal upload (optional)

## Accessing the Registration System

**URL:** `/student-registration/step1`

From the dashboard or admin panel, you can add a link to start the registration:

```blade
<a href="{{ route('student.registration.step1') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Register New Student
</a>
```

## Key Features

### 1. Session-Based Multi-Step Form
- Data is stored in Laravel sessions between steps
- Users can navigate back to previous steps
- Cancel button clears session and redirects to step 1

### 2. Parent/Guardian Search
- Users can search for existing parents by phone number
- If found, parent details are displayed and form uses existing parent ID
- If not found, users can add new parent details
- Phone number is unique per school, preventing duplicates

### 3. Smart Age Calculation
- Age is automatically calculated from date of birth
- Uses JavaScript for instant calculation

### 4. Health & Emergency Information
- Conditional fields for disability and chronic illness
- Detailed immunization tracking
- Emergency contact information for school coordination

### 5. Auto-Generated Admission Number
Format: `YYYY-00001` (Year-5 digit count)

Example: `2026-00001`, `2026-00002`, etc.

### 6. Session Data Persistence
- All form data is stored in Laravel sessions
- User can go back and edit previous steps
- Session data is cleared on successful registration or cancellation

## Model Relationships

### Student Model
- Belongs to School
- Belongs to Subclass
- Belongs to Parent (optional)

### Parent Model
- Belongs to School
- Has many Students

## Validation

All required fields are validated:
- Step 1: Name, gender, DOB required
- Step 2: Phone and relationship required
- Step 3: No required fields (optional)
- Step 4: Emergency contact name, relationship, phone required
- Step 5: Declaration checkbox, date required

## Success Page

After completing all steps, users see:
- Success message with student's name and admission number
- Summary of all entered information
- Options to return to dashboard or register another student

## Code Structure

```
app/Http/Controllers/StudentRegistrationController.php
├── showStep1() / storeStep1()
├── showStep2() / storeStep2()
├── searchParentByPhone()
├── showStep3() / storeStep3()
├── showStep4() / storeStep4()
├── showStep5() / storeStep5()
├── showSuccess()
├── generateAdmissionNumber()
├── cancelRegistration()

resources/views/student_registration/
├── step1.blade.php      (Student Particulars)
├── step2.blade.php      (Parent/Guardian Info)
├── step3.blade.php      (Health Information)
├── step4.blade.php      (Emergency Contact)
├── step5.blade.php      (Declaration & Official Use)
└── success.blade.php    (Success Page)

routes/web.php
├── GET  /student-registration/step1
├── POST /student-registration/step1
├── GET  /student-registration/step2
├── POST /student-registration/search-parent
├── POST /student-registration/step2
├── ... (similar for steps 3, 4, 5)
└── GET  /student-registration/success/{studentID}
```

## Customization Tips

### 1. Change Admission Number Format
Edit `generateAdmissionNumber()` method in `StudentRegistrationController.php`

### 2. Add More Religions/Nationalities
Change input fields to select dropdowns in `step1.blade.php`

### 3. Add More Relationship Types
Update the select options in `step2.blade.php` under "Relationship to Student"

### 4. Customize Colors
Update the CSS `bg-primary-custom` class color from `#940000` to your school color

### 5. Add Signature Capture
Replace text input with HTML5 Canvas signature pad in `step5.blade.php`

## Troubleshooting

### Issue: "No active class found for admission"
**Solution:** Create at least one active class before registering students. The system assigns new students to the first active class by default.

### Issue: Sessions not persisting
**Solution:** Check that `SESSION_DRIVER` in `.env` is set to `file` or `database`

### Issue: Photo not saving
**Solution:** Ensure `storage/app/public` directory exists and is writable

## Security Notes

- All routes are protected by default (modify in `StudentRegistrationController` to add middleware if needed)
- CSRF token is included in all forms
- Phone number uniqueness is enforced at database level (unique constraint)
- Parent search validates schoolID to prevent cross-school access

## Future Enhancements

- [ ] Email confirmation after registration
- [ ] SMS notification to parent
- [ ] Digital signature capture
- [ ] Document upload (birth certificate, vaccination card, etc.)
- [ ] Parent portal to complete registration
- [ ] Bulk import from Excel
- [ ] Registration approval workflow
