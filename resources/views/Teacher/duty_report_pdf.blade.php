<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Duty Report - {{ $report->report_date->format('d/m/Y') }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #940000; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: bold; margin-top: 10px; background: #f8f9fa; padding: 5px; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .label { font-weight: bold; color: #555; width: 15%; }
        .value { border-bottom: 1px dotted #ccc; width: 35%; }

        table.attendance { width: 100%; border-collapse: collapse; margin-bottom: 20px; text-align: center; }
        table.attendance th, table.attendance td { border: 1px solid #ddd; padding: 4px; }
        table.attendance th { background-color: #f2f2f2; font-size: 9px; }
        .class-name { text-align: left; font-weight: bold; background: #fafafa; }

        .observations-grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .observations-grid td { vertical-align: top; padding: 10px; border: 1px solid #eee; width: 50%; }
        .obs-title { font-weight: bold; color: #940000; margin-bottom: 5px; border-bottom: 1px solid #eee; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }
        .status-stamp { position: absolute; top: 100px; right: 50px; border: 3px solid #ccc; padding: 10px; font-size: 20px; font-weight: bold; color: #ccc; transform: rotate(-15deg); text-transform: uppercase; border-radius: 5px; opacity: 0.3; }
        .status-approved { border-color: green; color: green; }
        .status-sent { border-color: blue; color: blue; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'School Report' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Phone: {{ $school->phone ?? 'N/A' }} | Email: {{ $school->email ?? 'N/A' }}</div>
        <div class="report-title">DAILY ATTENDANCE & DUTY REPORT</div>
    </div>

    @if($report->status == 'Approved')
        <div class="status-stamp status-approved">APPROVED</div>
    @elseif($report->status == 'Sent')
        <div class="status-stamp status-sent">SENT</div>
    @endif

    <table class="info-table">
        <tr>
            <td class="label">Teacher:</td>
            <td class="value">{{ $teacher->first_name }} {{ $teacher->last_name }}</td>
            <td class="label">Date:</td>
            <td class="value">{{ $report->report_date->format('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td class="value">{{ $report->status }}</td>
            <td class="label">Attendance:</td>
            <td class="value">{{ $report->attendance_percentage }}%</td>
        </tr>
    </table>

    <table class="attendance">
        <thead>
            <tr>
                <th rowspan="2">CLASS</th>
                <th colspan="3">REGISTERED</th>
                <th colspan="3">PRESENT</th>
                <th colspan="3">ABSENT</th>
                <th colspan="3">NEW COMERS</th>
                <th colspan="3">PERMISSION</th>
                <th colspan="3">SICK</th>
            </tr>
            <tr>
                @for($i=0; $i<6; $i++)
                    <th>B</th><th>G</th><th>T</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @php $attData = $report->attendance_data; @endphp
            @foreach($classes as $cls)
                @php $c = $attData[$cls->classID] ?? null; @endphp
                <tr>
                    <td class="class-name">{{ $cls->class_name }}</td>
                    @if($c)
                        <td>{{ $c['reg_b'] }}</td><td>{{ $c['reg_g'] }}</td><td>{{ $c['reg_b'] + $c['reg_g'] }}</td>
                        <td>{{ $c['pres_b'] }}</td><td>{{ $c['pres_g'] }}</td><td>{{ $c['pres_b'] + $c['pres_g'] }}</td>
                        <td>{{ $c['abs_b'] }}</td><td>{{ $c['abs_g'] }}</td><td>{{ $c['abs_b'] + $c['abs_g'] }}</td>
                        <td>{{ $c['new_b'] }}</td><td>{{ $c['new_g'] }}</td><td>{{ $c['new_b'] + $c['new_g'] }}</td>
                        <td>{{ $c['perm_b'] }}</td><td>{{ $c['perm_g'] }}</td><td>{{ $c['perm_b'] + $c['perm_g'] }}</td>
                        <td>{{ $c['sick_b'] ?? 0 }}</td><td>{{ $c['sick_g'] ?? 0 }}</td><td>{{ ($c['sick_b'] ?? 0) + ($c['sick_g'] ?? 0) }}</td>
                    @else
                        @for($i=0; $i<18; $i++) <td>-</td> @endfor
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="observations-grid">
        <tr>
            <td>
                <div class="obs-title">1. School Environment</div>
                <div>{{ $report->school_environment ?: 'N/A' }}</div>
            </td>
            <td>
                <div class="obs-title">2. Pupil's Cleanliness</div>
                <div>{{ $report->pupils_cleanliness ?: 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="obs-title">3. Staff Attendance</div>
                <div style="white-space: pre-line;">{{ $report->teachers_attendance ?: 'N/A' }}</div>
            </td>
            <td>
                <div class="obs-title">4. Timetable Follow-up</div>
                <div>{{ $report->timetable_status ?: 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="obs-title">5. Outside Activities</div>
                <div>{{ $report->outside_activities ?: 'N/A' }}</div>
            </td>
            <td>
                <div class="obs-title">6. Special Events</div>
                <div>{{ $report->special_events ?: 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="obs-title">7. Teacher's General Comments</div>
                <div style="white-space: pre-line;">{{ $report->teacher_comments ?: 'N/A' }}</div>
            </td>
        </tr>
    </table>

    @if($report->admin_comments)
    <div style="margin-top: 20px; padding: 10px; border: 1px solid #ddd; background: #fffdf0;">
        <div class="obs-title">Admin / Headmaster Comments</div>
        <div style="font-style: italic;">"{{ $report->admin_comments }}"</div>
    </div>
    @endif

    <div style="margin-top: 30px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div style="font-weight: bold;">Teacher's Signature:</div>
                    <div style="margin-top: 10px; border-bottom: 1px solid #333; width: 150px; height: 15px;"></div>
                    <div style="font-size: 9px; margin-top: 5px;">{{ $teacher->first_name }} {{ $teacher->last_name }}</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div style="font-weight: bold;">Admin / Headmaster's Signature:</div>
                    @if($report->signature_image)
                        <div style="margin-top: 5px;">
                            <img src="{{ $report->signature_image }}" style="max-height: 50px; max-width: 150px;">
                        </div>
                        <div style="border-bottom: 1px solid #333; width: 150px; display: inline-block;"></div>
                        <div style="font-size: 9px; font-weight: bold;">{{ $report->signed_by }}</div>
                        <div style="font-size: 8px;">Signed on {{ $report->signed_at->format('d/m/Y H:i') }}</div>
                    @elseif($report->signed_by)
                        <div style="margin-top: 10px; font-family: 'Courier', monospace; font-size: 14px; font-style: italic; color: #000080;">
                            {{ $report->signed_by }}
                        </div>
                        <div style="margin-top: 2px; border-bottom: 1px solid #333; width: 150px; height: 1px; display: inline-block;"></div>
                        <div style="font-size: 8px; margin-top: 2px;">Signed on {{ $report->signed_at->format('d/m/Y H:i') }}</div>
                    @else
                        <div style="margin-top: 10px; border-bottom: 1px solid #333; width: 150px; height: 15px; display: inline-block;"></div>
                        <div style="font-size: 9px; margin-top: 5px;">Headmaster / Administrator</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generated by ShuleLink Support System | {{ date('d/m/Y H:i') }} | Teacher: {{ $teacher->first_name }} {{ $teacher->last_name }}
    </div>
</body>
</html>
