<!DOCTYPE html>
<html>
<head>
    <title>Student Fees Payment Invoice</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 4px solid #940000;
            padding-bottom: 20px;
        }
        .header-content {
            display: table;
            width: 100%;
            margin-bottom: 15px;
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
            font-size: 22px;
            font-weight: bold;
            color: #940000;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .school-details {
            font-size: 10px;
            color: #666;
            margin: 3px 0;
        }
        .invoice-title {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: linear-gradient(135deg, #940000 0%, #b30000 100%);
            color: white;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .student-info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #940000;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            display: table-cell;
            width: 70%;
            color: #212529;
        }
        .summary-section {
            margin: 25px 0;
        }
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #940000;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #940000;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        .summary-table th {
            background: #940000;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            word-wrap: break-word;
        }
        .summary-table td {
            padding: 8px 4px;
            border-bottom: 1px solid #dee2e6;
            word-wrap: break-word;
            font-size: 9px;
        }
        .summary-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .summary-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .total-row {
            background: #940000 !important;
            color: white !important;
            font-weight: bold;
            font-size: 12px;
        }
        .total-row td {
            padding: 15px 12px;
            border: none;
        }
        .details-section {
            margin: 25px 0;
        }
        .details-title {
            font-size: 13px;
            font-weight: bold;
            color: #940000;
            margin-bottom: 12px;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid #940000;
            border-radius: 4px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8px;
            table-layout: fixed;
        }
        .details-table th {
            background: #495057;
            color: white;
            padding: 6px 3px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            word-wrap: break-word;
        }
        .details-table td {
            padding: 6px 3px;
            border: 1px solid #dee2e6;
            word-wrap: break-word;
            font-size: 8px;
        }
        .details-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .installment-table, .other-fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        .installment-table th, .other-fee-table th {
            background: #6c757d;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }
        .installment-table td, .other-fee-table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }
        .footer-note {
            margin-top: 15px;
            padding: 10px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            font-size: 10px;
            color: #856404;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .mb-3 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Header with Logo and School Info -->
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                @if($schoolLogo && file_exists($schoolLogo))
                    <img src="{{ $schoolLogo }}" alt="School Logo">
                @else
                    <div style="width: 100px; height: 100px; background: #f8f9fa; border: 2px solid #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 12px; text-align: center;">
                        No Logo
                    </div>
                @endif
            </div>
            <div class="school-info">
                <div class="school-name">{{ $school->school_name }}</div>
                @if($school->registration_number)
                    <div class="school-details"><strong>Registration No:</strong> {{ $school->registration_number }}</div>
                @endif
                @if($school->address)
                    <div class="school-details"><strong>Address:</strong> {{ $school->address }}</div>
                @endif
                @if($school->phone)
                    <div class="school-details"><strong>Phone:</strong> {{ $school->phone }}</div>
                @endif
                @if($school->email)
                    <div class="school-details"><strong>Email:</strong> {{ $school->email }}</div>
                @endif
            </div>
        </div>
        
        <!-- Invoice Title -->
        <div class="invoice-title">
            STUDENT FEES PAYMENT INVOICE ({{ $year }})
        </div>
    </div>

    <!-- Student Information -->
    <div class="student-info-section">
        <div class="info-row">
            <div class="info-label">Student Name:</div>
            <div class="info-value">{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Admission Number:</div>
            <div class="info-value">{{ $student->admission_number ?? 'N/A' }}</div>
        </div>
        @if($student->subclass)
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">
                    @if($student->subclass->class && $student->subclass->subclass_name)
                        {{ $student->subclass->class->class_name }} {{ $student->subclass->subclass_name }}
                    @elseif($student->subclass->subclass_name)
                        {{ $student->subclass->subclass_name }}
                    @elseif($student->subclass->class)
                        {{ $student->subclass->class->class_name }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
        @endif
        @if($student->parent)
            <div class="info-row">
                <div class="info-label">Parent/Guardian:</div>
                <div class="info-value">{{ $student->parent->first_name }} {{ $student->parent->last_name ?? '' }}</div>
            </div>
            @if($student->parent->phone)
                <div class="info-row">
                    <div class="info-label">Parent Phone:</div>
                    <div class="info-value">{{ $student->parent->phone }}</div>
                </div>
            @endif
        @endif
    </div>

    <!-- Payment Summary -->
    <div class="summary-section">
        <div class="summary-title">PAYMENT SUMMARY</div>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Description</th>
                    <th style="width: 20%;" class="text-right">Total Required</th>
                    <th style="width: 20%;" class="text-right">Amount Paid</th>
                    <th style="width: 20%;" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="total-row">
                    <td><strong>OVERALL TOTAL</strong></td>
                    <td class="amount">TZS {{ number_format($totalRequired, 0) }}</td>
                    <td class="amount">TZS {{ number_format($totalPaid, 0) }}</td>
                    <td class="amount">TZS {{ number_format($totalBalance, 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Fees Breakdown -->
    <div class="details-section">
        <div class="details-title">FEES BREAKDOWN</div>
        @if($feeBreakdown->count() > 0)
            <table class="details-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Fee Name</th>
                        <th style="width: 15%;" class="text-center">Required?</th>
                        <th style="width: 15%;" class="text-right">Total Amount</th>
                        <th style="width: 15%;" class="text-right">Amount Paid</th>
                        <th style="width: 15%;" class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeBreakdown as $fee)
                        <tr>
                            <td><strong>{{ $fee->fee_name }}</strong></td>
                            <td class="text-center">
                                @if($fee->is_required)
                                    <span class="badge badge-danger">NDIYO</span>
                                @else
                                    <span class="badge badge-secondary">HAPANA</span>
                                @endif
                            </td>
                            <td class="text-right">TZS {{ number_format($fee->fee_total_amount, 0) }}</td>
                            <td class="text-right" style="color: #28a745;">TZS {{ number_format($fee->amount_paid, 0) }}</td>
                            <td class="text-right" style="color: #dc3545;">TZS {{ number_format($fee->balance, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center text-muted">Hakuna maelezo ya ada yaliyopatikana.</p>
        @endif
    </div>

    @if($previousYearDebt['school_fee_balance'] > 0 || $previousYearDebt['other_contribution_balance'] > 0)
    <div class="details-section">
        <div class="details-title">PREVIOUS YEAR DEBT</div>
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 70%;">Description</th>
                    <th style="width: 30%;" class="text-right">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @if($previousYearDebt['school_fee_balance'] > 0)
                <tr>
                    <td>Outstanding School Fees</td>
                    <td class="text-right">{{ number_format($previousYearDebt['school_fee_balance'], 0) }}</td>
                </tr>
                @endif
                @if($previousYearDebt['other_contribution_balance'] > 0)
                <tr>
                    <td>Outstanding Other Contributions</td>
                    <td class="text-right">{{ number_format($previousYearDebt['other_contribution_balance'], 0) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div class="footer-note">
            <strong>Note:</strong> This is an official invoice for fees payment. Please keep this document for your records.
        </div>
        <div style="margin-top: 15px;">
            Generated on: {{ \Carbon\Carbon::now()->format('d F Y, h:i A') }}
        </div>
        <div style="margin-top: 5px; color: #940000; font-weight: bold;">
            Powered by: EmCa Technologies LTD
        </div>
    </div>
</body>
</html>

