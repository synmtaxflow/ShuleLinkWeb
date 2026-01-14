<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk Lesson Plans</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .lesson-plan-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .school-type {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        table, th, td {
            border: 1px solid #212529;
        }
        
        th {
            background-color: #f5f5f5;
            padding: 5px;
            text-align: left;
            font-weight: bold;
        }
        
        td {
            padding: 4px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .no-page-break {
            page-break-after: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="lesson-plan-title">LESSON PLANS</div>
        <div class="school-type">{{ $schoolType }}</div>
    </div>
    
    @foreach($lessonPlans as $index => $lessonPlan)
        @php
            $teacherName = 'N/A';
            if ($lessonPlan->teacher) {
                $teacherName = trim(($lessonPlan->teacher->first_name ?? '') . ' ' . ($lessonPlan->teacher->last_name ?? ''));
            }
            
            $dateObj = \Carbon\Carbon::parse($lessonPlan->lesson_date);
            $formattedDate = $dateObj->format('d/m/Y');
            
            $formatTime = function($timeStr) {
                if (!$timeStr) return 'N/A';
                $parts = explode(':', $timeStr);
                $hours = (int)$parts[0];
                $minutes = $parts[1];
                $ampm = $hours >= 12 ? 'PM' : 'AM';
                $displayHours = $hours % 12 ?: 12;
                return $displayHours . ':' . $minutes . ' ' . $ampm;
            };
            
            $startTime = $formatTime($lessonPlan->lesson_time_start);
            $endTime = $formatTime($lessonPlan->lesson_time_end);
            
            $stages = is_array($lessonPlan->lesson_stages) ? $lessonPlan->lesson_stages : json_decode($lessonPlan->lesson_stages, true) ?? [];
        @endphp
        
        <div class="{{ $index > 0 ? 'page-break' : '' }}">
            <table>
                <tr>
                    <th>SUBJECT:</th>
                    <td>{{ $lessonPlan->subject ?? 'N/A' }}</td>
                    <th>CLASS:</th>
                    <td>{{ $lessonPlan->class_name ?? 'N/A' }}</td>
                    <th>YEAR:</th>
                    <td>{{ $lessonPlan->year }}</td>
                </tr>
                <tr>
                    <th>TEACHER'S NAME</th>
                    <td colspan="2">{{ $teacherName }}</td>
                    <td colspan="3">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <th colspan="3" style="text-align: center;">NUMBER OF PUPILS</th>
                            </tr>
                            <tr>
                                <th colspan="3" style="text-align: center;">REGISTERED</th>
                                <th colspan="3" style="text-align: center;">PRESENT</th>
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
                    <td>{{ $startTime }} - {{ $endTime }}</td>
                    <th>DATE</th>
                    <td colspan="3">{{ $formattedDate }}</td>
                </tr>
            </table>
            
            <table>
                <tr>
                    <th>MAIN COMPETENCE</th>
                    <td>{{ $lessonPlan->main_competence ?? '' }}</td>
                </tr>
                <tr>
                    <th>SPECIFIC COMPETENCE</th>
                    <td>{{ $lessonPlan->specific_competence ?? '' }}</td>
                </tr>
                <tr>
                    <th>MAIN ACTIVITY</th>
                    <td>{{ $lessonPlan->main_activity ?? '' }}</td>
                </tr>
                <tr>
                    <th>SPECIFIC ACTIVITY</th>
                    <td>{{ $lessonPlan->specific_activity ?? '' }}</td>
                </tr>
                <tr>
                    <th>TEACHING & LEARNING RESOURCES</th>
                    <td>{{ $lessonPlan->teaching_learning_resources ?? '' }}</td>
                </tr>
                <tr>
                    <th>REFERENCES</th>
                    <td>{{ $lessonPlan->references ?? '' }}</td>
                </tr>
            </table>
            
            <h5 style="margin-top: 15px; margin-bottom: 10px;">LESSON DEVELOPMENT</h5>
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
                        $stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];
                    @endphp
                    @foreach($stageNames as $stageName)
                        @php
                            $stage = collect($stages)->firstWhere('stage', $stageName) ?? [];
                        @endphp
                        <tr>
                            <td>{{ $stageName }}</td>
                            <td>{{ $stage['time'] ?? '' }}</td>
                            <td>{{ $stage['teaching_activities'] ?? '' }}</td>
                            <td>{{ $stage['learning_activities'] ?? '' }}</td>
                            <td>{{ $stage['assessment_criteria'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="margin-top: 15px;">
                <label><strong>Remarks:</strong></label>
                <div style="border-bottom: 2px dotted #212529; min-height: 20px; padding: 3px 0;">
                    {{ $lessonPlan->remarks ?? '' }}
                </div>
            </div>
            
            <div style="margin-top: 10px;">
                <label><strong>Reflection:</strong></label>
                <div style="border-bottom: 2px dotted #212529; min-height: 20px; padding: 3px 0;">
                    {{ $lessonPlan->reflection ?? '' }}
                </div>
            </div>
            
            <div style="margin-top: 10px;">
                <label><strong>Evaluation:</strong></label>
                <div style="border-bottom: 2px dotted #212529; min-height: 20px; padding: 3px 0;">
                    {{ $lessonPlan->evaluation ?? '' }}
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <div style="display: inline-block; width: 48%; vertical-align: top;">
                    <label><strong>Subject Teacher's Signature:</strong></label>
                    @if($lessonPlan->teacher_signature)
                        <div style="border: 1px solid #212529; min-height: 80px; margin-top: 5px;">
                            <img src="{{ $lessonPlan->teacher_signature }}" style="max-width: 100%; max-height: 80px;">
                        </div>
                    @else
                        <div style="border: 1px solid #212529; min-height: 80px; margin-top: 5px;"></div>
                    @endif
                </div>
                <div style="display: inline-block; width: 48%; vertical-align: top; margin-left: 4%;">
                    <label><strong>Academic/Supervisor's Signature:</strong></label>
                    @if($lessonPlan->supervisor_signature)
                        <div style="border: 1px solid #212529; min-height: 80px; margin-top: 5px;">
                            <img src="{{ $lessonPlan->supervisor_signature }}" style="max-width: 100%; max-height: 80px;">
                        </div>
                    @else
                        <div style="border: 1px solid #212529; min-height: 80px; margin-top: 5px;"></div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
