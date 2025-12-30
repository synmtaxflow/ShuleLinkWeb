<!DOCTYPE html>
<html>
<head>
    <title>Results Report</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 4px solid #940000;
            padding-bottom: 15px;
        }
        .header-content {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .logo-section {
            display: table-cell;
            width: 20%;
            vertical-align: top;
            padding-right: 15px;
        }
        .logo-section img {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 5px;
        }
        .school-info {
            display: table-cell;
            width: 80%;
            vertical-align: top;
        }
        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #940000;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .school-details {
            font-size: 9px;
            color: #666;
            margin: 2px 0;
        }
        .report-title {
            text-align: center;
            margin: 15px 0;
            padding: 12px;
            background: linear-gradient(135deg, #940000 0%, #b30000 100%);
            color: white;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .filter-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 9px;
        }
        .filter-info span {
            margin-right: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        table thead {
            background-color: #940000;
            color: white;
        }
        table th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table td {
            padding: 6px 5px;
            border: 1px solid #ddd;
        }
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .student-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .student-header {
            background-color: #940000;
            color: white;
            padding: 8px;
            font-weight: bold;
            border-radius: 5px 5px 0 0;
        }
        .student-info {
            background: #f8f9fa;
            padding: 8px;
            border: 1px solid #ddd;
            border-top: none;
            font-size: 9px;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
        }
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 2px solid #940000;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .position-info {
            background: #e7f3ff;
            padding: 10px;
            border-left: 4px solid #940000;
            margin-bottom: 15px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                @if($school && $school->school_logo)
                    <img src="{{ public_path($school->school_logo) }}" alt="School Logo">
                @endif
            </div>
            <div class="school-info">
                <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                @if($school)
                    <div class="school-details">{{ $school->address ?? '' }}</div>
                    <div class="school-details">Phone: {{ $school->phone ?? 'N/A' }} | Email: {{ $school->email ?? 'N/A' }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="report-title">
        @if($option === 'single' && $students->count() > 0)
            @php
                $student = $students->first();
                $result = $resultsData[$student->studentID] ?? null;
            @endphp
            {{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }} - {{ $title ?? ($filters['type'] === 'exam' ? 'Exam Results' : 'Term Report') }}
            @if($result && isset($result['position']))
                | Position: {{ $result['position'] }} in Main Class
            @endif
        @else
            {{ $title ?? ($filters['type'] === 'exam' ? 'Exam Results' : 'Term Report') }}
        @endif
    </div>

    <div class="filter-info">
        <span><strong>Term:</strong> {{ $filters['term'] ? ucfirst(str_replace('_', ' ', $filters['term'])) : 'All Terms' }}</span>
        <span><strong>Year:</strong> {{ $filters['year'] }}</span>
        <span><strong>Type:</strong> {{ $filters['type'] === 'exam' ? 'Exam Results' : 'Term Report' }}</span>
        @if($filters['examID'])
            @php
                $exam = \App\Models\Examination::find($filters['examID']);
            @endphp
            <span><strong>Exam:</strong> {{ $exam->exam_name ?? 'N/A' }}</span>
        @endif
        @if($filters['class'])
            @php
                $class = \App\Models\ClassModel::find($filters['class']);
            @endphp
            <span><strong>Class:</strong> {{ $class->class_name ?? 'N/A' }}</span>
        @endif
        @if($filters['subclass'])
            @php
                $subclass = \App\Models\Subclass::find($filters['subclass']);
            @endphp
            <span><strong>Subclass:</strong> {{ $subclass->subclass_name ?? 'N/A' }}</span>
        @endif
        @if($filters['grade'])
            <span><strong>Grade:</strong> {{ $filters['grade'] }}</span>
        @endif
        @if($filters['gender'])
            <span><strong>Gender:</strong> {{ $filters['gender'] }}</span>
        @endif
    </div>

    @if($option === 'single' && $students->count() > 0)
        @php
            $student = $students->first();
            $result = $resultsData[$student->studentID] ?? null;
        @endphp
        @if($result)
            <div class="position-info">
                <strong>Student Information:</strong><br>
                Name: {{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }} | 
                Admission: {{ $student->admission_number ?? 'N/A' }} | 
                @if($student->subclass && $student->subclass->class)
                    Class: {{ $student->subclass->class->class_name }} | 
                    Subclass: {{ $student->subclass->subclass_name }}
                @elseif($student->oldSubclass && $student->oldSubclass->class)
                    Class: {{ $student->oldSubclass->class->class_name }} (History) | 
                    Subclass: {{ $student->oldSubclass->subclass_name }} (History)
                @endif
                @if(isset($result['position']))
                    | Position: {{ $result['position'] }} in Main Class
                @endif
            </div>

            @if($filters['type'] === 'report')
                <table>
                    <thead>
                        <tr>
                            <th>Total Marks</th>
                            <th>Average</th>
                            <th>Grade</th>
                            <th>Division</th>
                            <th>Position</th>
                            <th>Exams Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format($result['total_marks'], 2) }}</td>
                            <td><strong>{{ number_format($result['average_marks'], 2) }}</strong></td>
                            <td><span class="badge badge-info">{{ $result['grade'] ?? 'N/A' }}</span></td>
                            <td><span class="badge badge-warning">{{ $result['division'] ?? 'N/A' }}</span></td>
                            <td><span class="badge badge-success">{{ $result['position'] ?? 'N/A' }}</span></td>
                            <td>{{ $result['exam_count'] ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                @foreach($result as $examResult)
                    <div class="student-section">
                        <div class="student-header">
                            {{ $examResult['exam']->exam_name ?? 'N/A' }} - {{ $examResult['exam']->start_date ?? 'N/A' }}
                        </div>
                        <div class="student-info">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Marks</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examResult['subjects'] as $subject)
                                        <tr>
                                            <td>{{ $subject['subject_name'] }}</td>
                                            <td>{{ $subject['marks'] ?? 'N/A' }}</td>
                                            <td>{{ $subject['grade'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div style="margin-top: 10px;">
                                <strong>Total Marks:</strong> {{ number_format($examResult['total_marks'], 2) }} | 
                                <strong>Average:</strong> {{ number_format($examResult['average_marks'], 2) }} | 
                                <strong>Grade:</strong> {{ $examResult['grade'] ?? 'N/A' }} | 
                                <strong>Division:</strong> {{ $examResult['division'] ?? 'N/A' }}
                            </div>
                            
                            <!-- Headmaster Signature after exam result -->
                            <div style="margin-top: 15px; text-align: right; padding-right: 20px;">
                                <div style="border-top: 2px solid #0000FF; width: 150px; margin-left: auto; margin-bottom: 5px;"></div>
                                <div style="color: #0000FF; font-weight: bold; font-size: 12px; text-align: right;">
                                    Headmaster's Sign
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif
    @else
        @if($option === 'class' || $option === 'subclass')
            @php
                $groupedStudents = $students->groupBy(function($student) {
                    if ($student->subclass && $student->subclass->class) {
                        return $student->subclass->class->class_name;
                    } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                        return $student->oldSubclass->class->class_name;
                    }
                    return 'Unknown';
                });
            @endphp
            @foreach($groupedStudents as $className => $classStudents)
                <h3 style="color: #940000; margin-top: 20px; margin-bottom: 10px;">{{ $className }}</h3>
                @if($option === 'subclass')
                    @php
                        $subclassGroups = $classStudents->groupBy(function($student) {
                            if ($student->subclass) {
                                return $student->subclass->subclass_name;
                            } elseif ($student->oldSubclass) {
                                return $student->oldSubclass->subclass_name;
                            }
                            return 'Unknown';
                        });
                    @endphp
                    @foreach($subclassGroups as $subclassName => $subclassStudents)
                        <h4 style="color: #666; margin-top: 15px; margin-bottom: 8px;">{{ $subclassName }}</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <th>Admission No.</th>
                                    @if($filters['type'] === 'report')
                                        <th>Total Marks</th>
                                        <th>Average</th>
                                        <th>Grade</th>
                                        <th>Division</th>
                                        <th>Position</th>
                                    @else
                                        <th>Exam</th>
                                        <th>Total Marks</th>
                                        <th>Average</th>
                                        <th>Grade</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subclassStudents as $index => $student)
                                    @if(isset($resultsData[$student->studentID]))
                                        @php
                                            $result = $resultsData[$student->studentID];
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</td>
                                            <td>{{ $student->admission_number ?? 'N/A' }}</td>
                                            @if($filters['type'] === 'report')
                                                <td>{{ number_format($result['total_marks'], 2) }}</td>
                                                <td><strong>{{ number_format($result['average_marks'], 2) }}</strong></td>
                                                <td><span class="badge badge-info">{{ $result['grade'] ?? 'N/A' }}</span></td>
                                                <td><span class="badge badge-warning">{{ $result['division'] ?? 'N/A' }}</span></td>
                                                <td><span class="badge badge-success">{{ $result['position'] ?? 'N/A' }}</span></td>
                                            @else
                                                @php
                                                    $firstExam = is_array($result) && !empty($result) ? $result[0] : $result;
                                                @endphp
                                                <td>{{ $firstExam['exam']->exam_name ?? 'N/A' }}</td>
                                                <td>{{ number_format($firstExam['total_marks'] ?? 0, 2) }}</td>
                                                <td><strong>{{ number_format($firstExam['average_marks'] ?? 0, 2) }}</strong></td>
                                                <td><span class="badge badge-info">{{ $firstExam['grade'] ?? 'N/A' }}</span></td>
                                            @endif
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        
                        <!-- Headmaster Signature after subclass table -->
                        <div style="margin-top: 15px; text-align: right; padding-right: 20px;">
                            <div style="border-top: 2px solid #0000FF; width: 150px; margin-left: auto; margin-bottom: 5px;"></div>
                            <div style="color: #0000FF; font-weight: bold; font-size: 12px; text-align: right;">
                                Headmaster's Sign
                            </div>
                        </div>
                    @endforeach
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Admission No.</th>
                                <th>Subclass</th>
                                @if($filters['type'] === 'report')
                                    <th>Total Marks</th>
                                    <th>Average</th>
                                    <th>Grade</th>
                                    <th>Division</th>
                                    <th>Position</th>
                                @else
                                    <th>Exam</th>
                                    <th>Total Marks</th>
                                    <th>Average</th>
                                    <th>Grade</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classStudents as $index => $student)
                                @if(isset($resultsData[$student->studentID]))
                                    @php
                                        $result = $resultsData[$student->studentID];
                                        $subclassName = '';
                                        if ($student->subclass) {
                                            $subclassName = $student->subclass->subclass_name;
                                        } elseif ($student->oldSubclass) {
                                            $subclassName = $student->oldSubclass->subclass_name . ' (History)';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</td>
                                        <td>{{ $student->admission_number ?? 'N/A' }}</td>
                                        <td>{{ $subclassName }}</td>
                                        @if($filters['type'] === 'report')
                                            <td>{{ number_format($result['total_marks'], 2) }}</td>
                                            <td><strong>{{ number_format($result['average_marks'], 2) }}</strong></td>
                                            <td><span class="badge badge-info">{{ $result['grade'] ?? 'N/A' }}</span></td>
                                            <td><span class="badge badge-warning">{{ $result['division'] ?? 'N/A' }}</span></td>
                                            <td><span class="badge badge-success">{{ $result['position'] ?? 'N/A' }}</span></td>
                                        @else
                                            @php
                                                $firstExam = is_array($result) && !empty($result) ? $result[0] : $result;
                                            @endphp
                                            <td>{{ $firstExam['exam']->exam_name ?? 'N/A' }}</td>
                                            <td>{{ number_format($firstExam['total_marks'] ?? 0, 2) }}</td>
                                            <td><strong>{{ number_format($firstExam['average_marks'] ?? 0, 2) }}</strong></td>
                                            <td><span class="badge badge-info">{{ $firstExam['grade'] ?? 'N/A' }}</span></td>
                                        @endif
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    
                    <!-- Headmaster Signature after class table -->
                    <div style="margin-top: 15px; text-align: right; padding-right: 20px;">
                        <div style="border-top: 2px solid #0000FF; width: 150px; margin-left: auto; margin-bottom: 5px;"></div>
                        <div style="color: #0000FF; font-weight: bold; font-size: 12px; text-align: right;">
                            Headmaster's Sign
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission No.</th>
                        <th>Class</th>
                        <th>Subclass</th>
                        @if($filters['type'] === 'report')
                            <th>Total Marks</th>
                            <th>Average</th>
                            <th>Grade</th>
                            <th>Division</th>
                            <th>Position</th>
                        @else
                            <th>Exam</th>
                            <th>Total Marks</th>
                            <th>Average</th>
                            <th>Grade</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                        @if(isset($resultsData[$student->studentID]))
                            @php
                                $result = $resultsData[$student->studentID];
                                $className = '';
                                $subclassName = '';
                                if ($student->subclass && $student->subclass->class) {
                                    $className = $student->subclass->class->class_name;
                                    $subclassName = $student->subclass->subclass_name;
                                } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                                    $className = $student->oldSubclass->class->class_name . ' (History)';
                                    $subclassName = $student->oldSubclass->subclass_name . ' (History)';
                                }
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</td>
                                <td>{{ $student->admission_number ?? 'N/A' }}</td>
                                <td>{{ $className }}</td>
                                <td>{{ $subclassName }}</td>
                                @if($filters['type'] === 'report')
                                    <td>{{ number_format($result['total_marks'], 2) }}</td>
                                    <td><strong>{{ number_format($result['average_marks'], 2) }}</strong></td>
                                    <td><span class="badge badge-info">{{ $result['grade'] ?? 'N/A' }}</span></td>
                                    <td><span class="badge badge-warning">{{ $result['division'] ?? 'N/A' }}</span></td>
                                    <td><span class="badge badge-success">{{ $result['position'] ?? 'N/A' }}</span></td>
                                @else
                                    @php
                                        $firstExam = is_array($result) && !empty($result) ? $result[0] : $result;
                                    @endphp
                                    <td>{{ $firstExam['exam']->exam_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($firstExam['total_marks'] ?? 0, 2) }}</td>
                                    <td><strong>{{ number_format($firstExam['average_marks'] ?? 0, 2) }}</strong></td>
                                    <td><span class="badge badge-info">{{ $firstExam['grade'] ?? 'N/A' }}</span></td>
                                @endif
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            
            <!-- Headmaster Signature after all students table -->
            <div style="margin-top: 20px; text-align: right; padding-right: 20px;">
                <div style="border-top: 2px solid #0000FF; width: 150px; margin-left: auto; margin-bottom: 5px;"></div>
                <div style="color: #0000FF; font-weight: bold; font-size: 12px; text-align: right;">
                    Headmaster's Sign
                </div>
            </div>
        @endif
    @endif

    <div class="footer">
        Generated on {{ date('d/m/Y H:i:s') }} | {{ $school->school_name ?? 'School' }}
    </div>
</body>
</html>



