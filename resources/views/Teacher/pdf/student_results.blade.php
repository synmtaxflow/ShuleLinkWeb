<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Results - {{ $student->admission_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 100px;
            max-height: 100px;
            margin-bottom: 10px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            text-decoration: underline;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-info td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .student-info td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f5f5f5;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .results-table th,
        .results-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .results-table th {
            background-color: #940000;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .results-table td {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .summary td:first-child {
            font-weight: bold;
            width: 40%;
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($school->school_logo && file_exists(public_path('logos/' . $school->school_logo)))
            <img src="{{ public_path('logos/' . $school->school_logo) }}" alt="School Logo" class="logo">
        @endif
        <div class="school-name">{{ $schoolName }}</div>
        <div class="title">STUDENT RESULTS</div>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td>Student Name:</td>
                <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</td>
            </tr>
            <tr>
                <td>Admission Number:</td>
                <td>{{ $student->admission_number }}</td>
            </tr>
            <tr>
                <td>Class:</td>
                <td>{{ $student->subclass->subclass_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Examination:</td>
                <td>{{ $exam->exam_name }} ({{ $exam->year }})</td>
            </tr>
        </table>
    </div>

    <table class="results-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Code</th>
                <th>Marks</th>
                <th>{{ $schoolType === 'Primary' ? 'Division' : 'Grade' }}</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $index => $subject)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $subject['subject_name'] }}</td>
                <td>{{ $subject['subject_code'] ?? 'N/A' }}</td>
                <td>{{ $subject['marks'] !== null ? $subject['marks'] : '-' }}</td>
                <td>
                    @if($schoolType === 'Primary' || ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])))
                        {{ $subject['division'] ?? '-' }}
                    @else
                        {{ $subject['grade'] ?? '-' }}
                    @endif
                </td>
                <td>{{ $subject['remark'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Headmaster Signature after results table -->
    <div style="margin-top: 20px; text-align: right; padding-right: 20px;">
        <div style="border-top: 2px solid #0000FF; width: 150px; margin-left: auto; margin-bottom: 5px;"></div>
        <div style="color: #0000FF; font-weight: bold; font-size: 12px; text-align: right;">
            Headmaster's Sign
        </div>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Marks:</td>
                <td>{{ $total_marks }}</td>
            </tr>
            <tr>
                <td>Average:</td>
                <td>{{ number_format($average_marks, 2) }}</td>
            </tr>
            <tr>
                <td>Number of Subjects:</td>
                <td>{{ $subject_count }}</td>
            </tr>
            <tr>
                <td>Total {{ $schoolType === 'Primary' ? 'Division' : 'Grade' }}:</td>
                <td>
                    @if($schoolType === 'Primary' || ($schoolType === 'Secondary' && in_array(strtolower($className ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])))
                        {{ $total_division ?? 'N/A' }}
                    @else
                        {{ $total_grade ?? 'N/A' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>Subclass Position:</td>
                <td>{{ $subclass_position ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Class Position:</td>
                <td>{{ $class_position ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Generated on {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>

