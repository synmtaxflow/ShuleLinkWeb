<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lesson Plan - {{ $lessonPlan->subject }}</title>
    <style>
        @font-face {
            font-family: 'Century Gothic';
            src: url('{{ public_path('fonts/century-gothic.ttf') }}') format('truetype');
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
        }
        
        .swahili-text {
            font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 80px;
            max-height: 80px;
            margin-bottom: 10px;
        }
        
        .school-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #212529;
        }
        
        .lesson-plan-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        
        .school-type {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        
        table td, table th {
            border: 1px solid #212529;
            padding: 4px 5px;
            text-align: left;
        }
        
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .nested-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .nested-table td, .nested-table th {
            border: 1px solid #212529;
            padding: 3px 4px;
            text-align: center;
            font-size: 9px;
        }
        
        .nested-table th {
            background-color: #f5f5f5;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #940000;
            padding: 5px;
            border-top: 1px solid #ddd;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        
        .dotted-line {
            border-bottom: 2px dotted #212529;
            min-height: 25px;
            padding: 5px 0;
            margin: 5px 0;
        }
        
        .signature-section {
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .signature-label {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .signature-box {
            border: 2px solid #212529;
            min-height: 80px;
            padding: 5px;
            margin-bottom: 10px;
        }
        
        .signature-image {
            max-width: 100%;
            max-height: 80px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($schoolLogoPath && file_exists($schoolLogoPath))
            <img src="{{ $schoolLogoPath }}" alt="School Logo" class="logo">
        @endif
        @if($school && $school->school_name)
            <div class="school-name">{{ strtoupper($school->school_name) }}</div>
        @endif
        <div class="lesson-plan-title">LESSON PLAN</div>
        <div class="school-type">{{ $schoolType }}</div>
    </div>
    
    <table>
        <tr>
            <th>SUBJECT:</th>
            <td class="swahili-text">{{ $lessonPlan->subject ?? 'N/A' }}</td>
            <th>CLASS:</th>
            <td class="swahili-text">{{ $lessonPlan->class_name ?? 'N/A' }}</td>
            <th>YEAR:</th>
            <td>{{ $lessonPlan->year ?? date('Y') }}</td>
        </tr>
        <tr>
            <th>TEACHER'S NAME</th>
            <td colspan="2" class="swahili-text">{{ $teacherName }}</td>
            <td colspan="3">
                <table class="nested-table">
                    <tr>
                        <th colspan="3">NUMBER OF PUPILS</th>
                    </tr>
                    <tr>
                        <th colspan="3">REGISTERED</th>
                        <th colspan="3">PRESENT</th>
                    </tr>
                    <tr>
                        <th>GIRLS</th>
                        <th>BOYS</th>
                        <th>TOTAL</th>
                        <th>GIRLS</th>
                        <th>BOYS</th>
                        <th>TOTAL</th>
                    </tr>
                    <tr>
                        <td>{{ $lessonPlan->registered_girls ?? 0 }}</td>
                        <td>{{ $lessonPlan->registered_boys ?? 0 }}</td>
                        <td>{{ $lessonPlan->registered_total ?? 0 }}</td>
                        <td>{{ $lessonPlan->present_girls ?? 0 }}</td>
                        <td>{{ $lessonPlan->present_boys ?? 0 }}</td>
                        <td>{{ $lessonPlan->present_total ?? 0 }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th>TIME</th>
            <td>{{ \Carbon\Carbon::parse($lessonPlan->lesson_time_start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($lessonPlan->lesson_time_end)->format('h:i A') }}</td>
            <th>DATE</th>
            <td colspan="3">{{ \Carbon\Carbon::parse($lessonPlan->lesson_date)->format('d/m/Y') }}</td>
        </tr>
    </table>
    
    <table>
        <tr>
            <th>MAIN COMPETENCE</th>
            <td class="swahili-text">{{ $lessonPlan->main_competence ?? '' }}</td>
        </tr>
        <tr>
            <th>SPECIFIC COMPETENCE</th>
            <td class="swahili-text">{{ $lessonPlan->specific_competence ?? '' }}</td>
        </tr>
        <tr>
            <th>MAIN ACTIVITY</th>
            <td class="swahili-text">{{ $lessonPlan->main_activity ?? '' }}</td>
        </tr>
        <tr>
            <th>SPECIFIC ACTIVITY</th>
            <td class="swahili-text">{{ $lessonPlan->specific_activity ?? '' }}</td>
        </tr>
        <tr>
            <th>TEACHING & LEARNING RESOURCES</th>
            <td class="swahili-text">{{ $lessonPlan->teaching_learning_resources ?? '' }}</td>
        </tr>
        <tr>
            <th>REFERENCES</th>
            <td class="swahili-text">{{ $lessonPlan->references ?? '' }}</td>
        </tr>
    </table>
    
    <div class="section-title">LESSON DEVELOPMENT</div>
    <table>
        <thead>
            <tr>
                <th>STAGE</th>
                <th>TIME</th>
                <th>TEACHING ACTIVITIES</th>
                <th>LEARNING ACTIVITIES</th>
                <th>ASSESSMENT CRITERIA</th>
            </tr>
        </thead>
        <tbody>
            @php
                $stages = is_array($lessonPlan->lesson_stages) ? $lessonPlan->lesson_stages : json_decode($lessonPlan->lesson_stages, true) ?? [];
                $stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];
            @endphp
            @foreach($stageNames as $stageName)
                @php
                    $stage = collect($stages)->firstWhere('stage', $stageName) ?? [];
                @endphp
                <tr>
                    <td>{{ $stageName }}</td>
                    <td>{{ $stage['time'] ?? '' }}</td>
                    <td class="swahili-text">{{ $stage['teaching_activities'] ?? '' }}</td>
                    <td class="swahili-text">{{ $stage['learning_activities'] ?? '' }}</td>
                    <td class="swahili-text">{{ $stage['assessment_criteria'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="section-title">Remarks</div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;" class="swahili-text">{{ $lessonPlan->remarks ?? '' }}</p>
    </div>
    
    <div class="section-title">Reflection</div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;" class="swahili-text">{{ $lessonPlan->reflection ?? '' }}</p>
    </div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;"></p>
    </div>
    
    <div class="section-title">Evaluation</div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;" class="swahili-text">{{ $lessonPlan->evaluation ?? '' }}</p>
    </div>
    <div class="dotted-line">
        <p style="margin: 0; padding: 3px 0; font-size: 10px;"></p>
    </div>
    
    <div class="signature-section">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: none; padding: 5px;">
                    <div class="signature-label">Subject Teacher's Signature:</div>
                    <div class="signature-box">
                        @if($lessonPlan->teacher_signature)
                            <img src="{{ $lessonPlan->teacher_signature }}" class="signature-image" alt="Teacher Signature">
                        @else
                            <div style="height: 80px;"></div>
                        @endif
                    </div>
                </td>
                <td style="width: 50%; border: none; padding: 5px;">
                    <div class="signature-label">Academic/Supervisor's Signature:</div>
                    <div class="signature-box">
                        @if($lessonPlan->supervisor_signature)
                            <img src="{{ $lessonPlan->supervisor_signature }}" class="signature-image" alt="Supervisor Signature">
                        @else
                            <div style="height: 80px;"></div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        Powered by EMCATECHONOLOGY
    </div>
</body>
</html>

