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
        }
        .summary-table th {
            background: #940000;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .summary-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
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
            font-size: 10px;
        }
        .details-table th {
            background: #495057;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        .details-table td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
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
                <div class="info-value">{{ $student->subclass->subclass_name ?? 'N/A' }}</div>
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
                    <th style="width: 40%;">Fee Type</th>
                    <th style="width: 20%;" class="text-right">Amount Required</th>
                    <th style="width: 20%;" class="text-right">Amount Paid</th>
                    <th style="width: 20%;" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Tuition Fees</strong></td>
                    <td class="amount">TZS {{ number_format($tuitionRequired, 2) }}</td>
                    <td class="amount" style="color: #28a745;">TZS {{ number_format($tuitionPaid, 2) }}</td>
                    <td class="amount" style="color: #dc3545;">TZS {{ number_format($tuitionBalance, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Other Fees</strong></td>
                    <td class="amount">TZS {{ number_format($otherRequired, 2) }}</td>
                    <td class="amount" style="color: #28a745;">TZS {{ number_format($otherPaid, 2) }}</td>
                    <td class="amount" style="color: #dc3545;">TZS {{ number_format($otherBalance, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td class="amount">TZS {{ number_format($totalRequired, 2) }}</td>
                    <td class="amount">TZS {{ number_format($totalPaid, 2) }}</td>
                    <td class="amount">TZS {{ number_format($totalBalance, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tuition Fees Details -->
    @if($tuitionPayments->count() > 0)
    <div class="details-section">
        <div class="details-title">TUITION FEES DETAILS</div>
        @foreach($tuitionPayments as $payment)
            <table class="details-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Control Number</th>
                        <th style="width: 20%;">Amount Required</th>
                        <th style="width: 20%;">Amount Paid</th>
                        <th style="width: 20%;">Balance</th>
                        <th style="width: 25%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>{{ $payment->control_number ?? 'N/A' }}</strong></td>
                        <td class="text-right">TZS {{ number_format($payment->amount_required, 2) }}</td>
                        <td class="text-right" style="color: #28a745;">TZS {{ number_format($payment->amount_paid, 2) }}</td>
                        <td class="text-right" style="color: #dc3545;">TZS {{ number_format($payment->balance, 2) }}</td>
                        <td>
                            @if($payment->payment_status == 'Paid')
                                <span class="badge badge-success">Paid</span>
                            @elseif($payment->payment_status == 'Pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($payment->payment_status == 'Incomplete Payment' || $payment->payment_status == 'Partial')
                                <span class="badge badge-info">Incomplete</span>
                            @else
                                <span class="badge badge-danger">{{ $payment->payment_status }}</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            
            @if($payment->fee && $payment->fee->installments && $payment->fee->installments->count() > 0)
                <div style="margin-top: 10px; margin-bottom: 15px;">
                    <strong style="color: #495057; font-size: 10px;">Installments:</strong>
                    <table class="installment-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">#</th>
                                <th style="width: 30%;">Installment Name</th>
                                <th style="width: 20%;">Type</th>
                                <th style="width: 20%;" class="text-right">Amount</th>
                                <th style="width: 20%;">Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->fee->installments as $index => $installment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $installment->installment_name }}</td>
                                    <td>{{ $installment->installment_type }}</td>
                                    <td class="text-right">TZS {{ number_format($installment->amount, 2) }}</td>
                                    <td>{{ $installment->due_date ? \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
    </div>
    @endif

    <!-- Other Fees Details -->
    @if($otherFeePayments->count() > 0)
    <div class="details-section">
        <div class="details-title">OTHER FEES DETAILS</div>
        @foreach($otherFeePayments as $payment)
            <table class="details-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Control Number</th>
                        <th style="width: 20%;">Amount Required</th>
                        <th style="width: 20%;">Amount Paid</th>
                        <th style="width: 20%;">Balance</th>
                        <th style="width: 25%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>{{ $payment->control_number ?? 'N/A' }}</strong></td>
                        <td class="text-right">TZS {{ number_format($payment->amount_required, 2) }}</td>
                        <td class="text-right" style="color: #28a745;">TZS {{ number_format($payment->amount_paid, 2) }}</td>
                        <td class="text-right" style="color: #dc3545;">TZS {{ number_format($payment->balance, 2) }}</td>
                        <td>
                            @if($payment->payment_status == 'Paid')
                                <span class="badge badge-success">Paid</span>
                            @elseif($payment->payment_status == 'Pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($payment->payment_status == 'Incomplete Payment' || $payment->payment_status == 'Partial')
                                <span class="badge badge-info">Incomplete</span>
                            @else
                                <span class="badge badge-danger">{{ $payment->payment_status }}</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            
            @if($payment->fee && $payment->fee->otherFeeDetails && $payment->fee->otherFeeDetails->count() > 0)
                <div style="margin-top: 10px; margin-bottom: 15px;">
                    <strong style="color: #495057; font-size: 10px;">Fee Details:</strong>
                    <table class="other-fee-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">#</th>
                                <th style="width: 40%;">Fee Detail Name</th>
                                <th style="width: 25%;" class="text-right">Amount</th>
                                <th style="width: 25%;">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->fee->otherFeeDetails as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->fee_detail_name }}</td>
                                    <td class="text-right">TZS {{ number_format($detail->amount, 2) }}</td>
                                    <td>{{ $detail->description ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
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
            Powered by EmCa Technology
        </div>
    </div>
</body>
</html>

