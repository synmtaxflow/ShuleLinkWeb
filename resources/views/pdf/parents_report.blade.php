<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Parents Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #940000;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #940000;
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #940000;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #940000;
            font-size: 11px;
        }
        .table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 10px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        .parent-info {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .parent-info strong {
            color: #940000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>JAMUHURI YA MUUNGANO WA TANZANIA</h1>
        <h2>TAWALA ZA MIKOA NA SERIKALI ZA MITAA</h2>
        <h2>TAMISEMI {{ strtoupper($schoolName) }}</h2>
        <h2>WAZAZI WA DARASA {{ strtoupper($subclassName) }}</h2>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Jina la Mzazi</th>
                <th>Namba ya Simu</th>
                <th>Barua Pepe</th>
                <th>Kazi</th>
                <th>Namba ya Utambulisho</th>
                <th>Anwani</th>
                <th>Wanafunzi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parents as $index => $parent)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $parent->first_name }} {{ $parent->middle_name ?? '' }} {{ $parent->last_name }}</td>
                    <td>{{ $parent->phone ?? 'N/A' }}</td>
                    <td>{{ $parent->email ?? 'N/A' }}</td>
                    <td>{{ $parent->occupation ?? 'N/A' }}</td>
                    <td>{{ $parent->national_id ?? 'N/A' }}</td>
                    <td>{{ $parent->address ?? 'N/A' }}</td>
                    <td>
                        @if($parent->students && $parent->students->count() > 0)
                            @foreach($parent->students as $student)
                                {{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_number }})@if(!$loop->last), @endif
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">Hakuna wazazi walioorodheshwa</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Powered by: EmCa Technologies LTD</p>
        <p>Generated on: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>







