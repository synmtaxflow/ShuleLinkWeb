<!DOCTYPE html>
<html>
<head>
    <title>Filtered Payments List</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 4px solid #940000;
            padding-bottom: 15px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #940000;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .report-title {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background: #940000;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
        }
        .filters {
            margin: 10px 0;
            padding: 8px;
            background: #f8f9fa;
            border-left: 3px solid #940000;
            font-size: 9px;
        }
        .student-section {
            margin: 20px 0;
            page-break-inside: avoid;
        }
        .student-header {
            background: #940000;
            color: white;
            padding: 8px;
            font-weight: bold;
            font-size: 11px;
        }
        .student-info {
            background: #f8f9fa;
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8px;
        }
        table th {
            background: #495057;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }
        table td {
            padding: 5px 4px;
            border: 1px solid #dee2e6;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #212529;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        .badge-secondary {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
        <div style="font-size: 9px; color: #666;">
            {{ $school->registration_number ?? '' }} | 
            {{ $school->phone ?? '' }} | 
            {{ $school->email ?? '' }}
        </div>
    </div>

    <div class="report-title">
        FILTERED PAYMENTS REPORT - {{ $year }}
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filters Applied:</strong>
        @if($filters['class']) Class: {{ $filters['class'] }} | @endif
        @if($filters['subclass']) Subclass: {{ $filters['subclass'] }} | @endif
        @if($filters['status']) Status: {{ $filters['status'] }} | @endif
        @if($filters['fee_type']) Fee Type: {{ $filters['fee_type'] }} | @endif
        @if($filters['payment_status']) Payment Status: {{ $filters['payment_status'] }} @endif
    </div>
    @endif

    @foreach($students as $index => $studentData)
    <div class="student-section">
        <div class="student-header">
            {{ $index + 1 }}. {{ $studentData['student']['first_name'] }} {{ $studentData['student']['middle_name'] }} {{ $studentData['student']['last_name'] }}
            @if($studentData['student']['status'] === 'Graduated')
                <span class="badge badge-secondary">Graduated</span>
            @endif
        </div>
        
        <div class="student-info">
            <strong>Admission Number:</strong> {{ $studentData['student']['admission_number'] ?? 'N/A' }} | 
            <strong>Class:</strong> {{ $studentData['student']['class'] }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Fee Type</th>
                    <th style="width: 15%;" class="text-right">Required Amount</th>
                    <th style="width: 12%;" class="text-right">Debt</th>
                    <th style="width: 15%;" class="text-right">Total Required</th>
                    <th style="width: 15%;" class="text-right">Amount Paid</th>
                    <th style="width: 15%;" class="text-right">Balance</th>
                    <th style="width: 8%;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>School Fee</strong></td>
                    <td class="text-right">{{ number_format($studentData['tuitionBaseRequired'], 0) }}</td>
                    <td class="text-right" style="color: #ffc107;">{{ number_format($studentData['tuitionDebt'], 0) }}</td>
                    <td class="text-right"><strong>{{ number_format($studentData['tuitionRequired'], 0) }}</strong></td>
                    <td class="text-right" style="color: #28a745;">{{ number_format($studentData['tuitionPaid'], 0) }}</td>
                    <td class="text-right" style="color: #dc3545;">{{ number_format($studentData['tuitionBalance'], 0) }}</td>
                    <td class="text-center">
                        @if($studentData['tuitionBalance'] <= 0 && $studentData['tuitionPaid'] > 0)
                            <span class="badge badge-success">Paid</span>
                        @elseif($studentData['tuitionPaid'] > 0)
                            <span class="badge badge-info">Incomplete</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Other Contribution</strong></td>
                    <td class="text-right">{{ number_format($studentData['otherBaseRequired'], 0) }}</td>
                    <td class="text-right" style="color: #ffc107;">{{ number_format($studentData['otherDebt'], 0) }}</td>
                    <td class="text-right"><strong>{{ number_format($studentData['otherRequired'], 0) }}</strong></td>
                    <td class="text-right" style="color: #28a745;">{{ number_format($studentData['otherPaid'], 0) }}</td>
                    <td class="text-right" style="color: #dc3545;">{{ number_format($studentData['otherBalance'], 0) }}</td>
                    <td class="text-center">
                        @if($studentData['otherBalance'] <= 0 && $studentData['otherPaid'] > 0)
                            <span class="badge badge-success">Paid</span>
                        @elseif($studentData['otherPaid'] > 0)
                            <span class="badge badge-info">Incomplete</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                </tr>
                <tr style="background: #940000; color: white; font-weight: bold;">
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right">{{ number_format($studentData['tuitionBaseRequired'] + $studentData['otherBaseRequired'], 0) }}</td>
                    <td class="text-right">{{ number_format($studentData['tuitionDebt'] + $studentData['otherDebt'], 0) }}</td>
                    <td class="text-right">{{ number_format($studentData['totalRequired'], 0) }}</td>
                    <td class="text-right">{{ number_format($studentData['totalPaid'], 0) }}</td>
                    <td class="text-right">{{ number_format($studentData['totalBalance'], 0) }}</td>
                    <td class="text-center">
                        @if($studentData['totalBalance'] <= 0 && $studentData['totalPaid'] > 0)
                            <span class="badge badge-success">Paid</span>
                        @elseif($studentData['totalPaid'] > 0)
                            <span class="badge badge-info">Incomplete</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach

    <div style="margin-top: 30px; padding-top: 15px; border-top: 2px solid #dee2e6; text-align: center; font-size: 8px; color: #6c757d;">
        Generated on: {{ date('d/m/Y H:i:s') }} | Powered by EmCa Technology
    </div>
</body>
</html>


