<?php
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Find subject table name
$tables = DB::select('SHOW TABLES'); 
$subjectTable = 'subjects'; // Default guess
foreach($tables as $table) {
    $tableArray = (array)$table;
    $name = reset($tableArray);
    if ($name == 'school_subjects' || $name == 'subjects' || $name == 'subject') {
        $subjectTable = $name;
        break;
    }
}

$schoolID = 5;
$staffID = 227; // Michael (User ID)
$year = 2026;

// 1. Create Examination
$examID = DB::table('examinations')->insertGetId([
    'exam_name' => 'Weekly Test',
    'exam_category' => 'test',
    'exam_type' => 'specific_classes_all_subjects',
    'schoolID' => $schoolID,
    'year' => $year,
    'term' => 'first_term',
    'status' => 'ongoing',
    'approval_status' => 'Approved',
    'created_by' => $staffID,
    'start_date' => Carbon::now()->startOfWeek()->toDateString(),
    'end_date' => Carbon::now()->endOfWeek()->toDateString(),
    'created_at' => Carbon::now(),
    'updated_at' => Carbon::now()
]);

echo "Created Exam ID: $examID\n";

$subclasses = DB::table('subclasses')->whereIn('classID', [38, 39, 40, 41, 42, 43])->get();
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

foreach ($subclasses as $subclass) {
    echo "Processing Subclass: {$subclass->subclass_name} (ID: {$subclass->subclassID})\n";
    $subjects = DB::table('class_subjects')
        ->where('subclassID', $subclass->subclassID)
        ->get();

    $dayIndex = 0;
    foreach ($subjects as $subject) {
        $day = $days[$dayIndex % 5];
        
        // Create Schedule
        $scheduleID = DB::table('weekly_test_schedules')->insertGetId([
            'schoolID' => $schoolID,
            'examID' => $examID,
            'test_type' => 'weekly',
            'week_number' => 1,
            'day' => $day,
            'scope' => 'subclass',
            'scope_id' => $subclass->subclassID,
            'subjectID' => $subject->subjectID,
            'teacher_id' => $subject->teacherID ?? $staffID,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'created_by' => $staffID,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $subjectName = DB::table($subjectTable)->where('subjectID', $subject->subjectID)->value('subject_name') ?? 'Subject';

        // 5. Create Exam Paper with PREMIUM format
        $questionContent = "
        <div style='font-family: \"Century Gothic\", sans-serif; padding: 20px; border: 2px solid #940000; border-radius: 10px; background-color: #fdfdfd;'>
            <div style='text-align: center; border-bottom: 2px solid #940000; padding-bottom: 10px; margin-bottom: 20px;'>
                <h2 style='color: #940000; margin: 0;'>SHULE LINK SECONDARY SCHOOL</h2>
                <h3 style='margin: 5px 0;'>WEEKLY CONTINUOUS ASSESSMENT - WEEK 1</h3>
                <h4 style='color: #555; margin: 5px 0;'>SUBJECT: " . strtoupper($subjectName) . "</h4>
                <p style='font-size: 0.9rem; font-style: italic;'>Time Allowed: 1 Hour 30 Minutes</p>
            </div>
            
            <div style='margin-bottom: 20px;'>
                <h4 style='text-decoration: underline; color: #940000;'>SECTION A: GENERAL KNOWLEDGE (20 Marks)</h4>
                <ol>
                    <li style='margin-bottom: 10px;'>Discuss the significance of <strong>$subjectName</strong> in the context of modern technological advancements.</li>
                    <li style='margin-bottom: 10px;'>Identify and explain three key principles that govern the study of this subject.</li>
                </ol>
            </div>

            <div style='margin-bottom: 20px;'>
                <h4 style='text-decoration: underline; color: #940000;'>SECTION B: ANALYSIS AND EVALUATION (30 Marks)</h4>
                <ol start='3'>
                    <li style='margin-bottom: 10px;'>Critically evaluate the impact of recent global trends on the application of <em>$subjectName</em>.</li>
                    <li style='margin-bottom: 10px;'>Proposed a solution to a common challenge faced by professionals in this field today.</li>
                </ol>
            </div>

            <div style='text-align: center; margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 10px;'>
                <p style='font-weight: bold; color: #940000;'>*** ALL THE BEST ***</p>
            </div>
        </div>";

        $paperID = DB::table('exam_papers')->insertGetId([
            'examID' => $examID,
            'weekly_test_schedule_id' => $scheduleID,
            'test_week' => 1,
            'class_subjectID' => $subject->class_subjectID,
            'teacherID' => $subject->teacherID ?? $staffID,
            'question_content' => $questionContent,
            'upload_type' => 'question',
            'status' => 'approved',
            'approval_comment' => 'Automatically generated and approved for full curriculum coverage.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // 6. Fill Results for all students
        $students = DB::table('students')->where('subclassID', $subclass->subclassID)->get();
        foreach ($students as $student) {
            $marks = rand(45, 95);
            $grade = 'F';
            if ($marks >= 75) $grade = 'A';
            elseif ($marks >= 65) $grade = 'B';
            elseif ($marks >= 45) $grade = 'C';
            elseif ($marks >= 30) $grade = 'D';

            DB::table('results')->insert([
                'studentID' => $student->studentID,
                'examID' => $examID,
                'subclassID' => $subclass->subclassID,
                'class_subjectID' => $subject->class_subjectID,
                'marks' => $marks,
                'grade' => $grade,
                'remark' => 'Pass',
                'status' => 'approved',
                'test_week' => 1,
                'test_date' => Carbon::now()->startOfWeek()->addDays($dayIndex % 5)->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
        $dayIndex++;
    }
}
echo "Full Weekly Test setup completed successfully for schoolID 5!\n";
