<!DOCTYPE html>
<html>
<head>
    <title>Teacher Attendance Report</title>
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
            border-bottom: 3px solid #940000;
            padding-bottom: 15px;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .logo-section {
            display: table-cell;
            width: 15%;
            vertical-align: top;
        }
        .logo-section img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        .school-info {
            display: table-cell;
            width: 85%;
            vertical-align: top;
            padding-left: 15px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #940000;
            margin: 0;
            padding: 0;
            text-transform: uppercase;
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #940000;
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 5px solid #940000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #940000;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #940000;
        }
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #940000;
            padding: 10px 0;
            border-top: 2px solid #940000;
            background-color: #fff;
        }
        .present-dates {
            font-size: 8px;
            color: #666;
            max-width: 200px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                @if($schoolLogo && file_exists($schoolLogo))
                    @php
                        $logoData = base64_encode(file_get_contents($schoolLogo));
                        $logoExt = pathinfo($schoolLogo, PATHINFO_EXTENSION);
                        $logoSrc = 'data:image/' . $logoExt . ';base64,' . $logoData;
                    @endphp
                    <img src="{{ $logoSrc }}" alt="School Logo">
                @endif
            </div>
            <div class="school-info">
                <h1 class="school-name">{{ $school->school_name ?? 'School Name' }}</h1>
            </div>
        </div>
    </div>

    <div class="report-title">
        {{ $reportTitle }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Teacher Name</th>
                <th style="width: 15%;">Position</th>
                <th style="width: 10%;">Days Present</th>
                <th style="width: 10%;">Days Absent</th>
                <th style="width: 10%;">Working Days</th>
                <th style="width: 10%;">Attendance Rate (%)</th>
                <th style="width: 15%;">Present Dates</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teachers as $index => $teacher)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td><strong>{{ $teacher['name'] }}</strong></td>
                    <td>{{ $teacher['position'] }}</td>
                    <td style="text-align: center; color: #28a745; font-weight: bold;">{{ $teacher['days_present'] }}</td>
                    <td style="text-align: center; color: #dc3545; font-weight: bold;">{{ $teacher['days_absent'] }}</td>
                    <td style="text-align: center;">{{ $teacher['working_days'] }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $teacher['attendance_rate'] }}%</td>
                    <td class="present-dates">
                        @if(count($teacher['present_dates']) > 0)
                            {{ implode(', ', array_slice($teacher['present_dates'], 0, 5)) }}
                            @if(count($teacher['present_dates']) > 5)
                                <br>... and {{ count($teacher['present_dates']) - 5 }} more
                            @endif
                        @else
                            <span style="color: #999;">No attendance</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #999;">
                        No teacher attendance records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p style="margin: 0; font-weight: bold;">shuleLink powered by emcaTechnology</p>
        <p style="margin: 5px 0 0 0;">Generated on {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>

