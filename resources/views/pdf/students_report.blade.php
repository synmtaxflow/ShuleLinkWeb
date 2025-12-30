<!DOCTYPE html>
<html>
<head>
    <title>Students Report</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
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
        .school-details {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
            line-height: 1.6;
        }
        .report-title {
            text-align: center;
            font-size: 14px;
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
        tr:hover {
            background-color: #f5f5f5;
        }
        .photo-cell {
            text-align: center;
            width: 50px;
        }
        .photo-cell img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #940000;
        }
        .no-photo {
            width: 40px;
            height: 40px;
            background-color: #940000;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin: 0 auto;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .status-active {
            background-color: #28a745;
            color: white;
        }
        .status-transferred {
            background-color: #ffc107;
            color: #333;
        }
        .status-inactive {
            background-color: #6c757d;
            color: white;
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
        .page-break {
            page-break-after: always;
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
                <h1 class="school-name">{{ $schoolName }}</h1>
                <div class="school-details">
                    <div><strong>Email:</strong> {{ $schoolEmail }}</div>
                    <div><strong>Phone:</strong> {{ $schoolPhone }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="report-title">
        WANAFUNZI WA KIDATO CHA {{ strtoupper($subclassName) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 8%;">Picha</th>
                <th style="width: 12%;">Namba ya Usajili</th>
                <th style="width: 20%;">Jina Kamili</th>
                <th style="width: 8%;">Jinsia</th>
                <th style="width: 12%;">Tarehe ya Kuzaliwa</th>
                <th style="width: 15%;">Mzazi/Mlezi</th>
                <th style="width: 10%;">Hali</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td class="photo-cell">
                        @if($student->photo && file_exists(public_path('userImages/' . $student->photo)))
                            @php
                                $imagePath = public_path('userImages/' . $student->photo);
                                $imageData = base64_encode(file_get_contents($imagePath));
                                $imageSrc = 'data:image/' . pathinfo($imagePath, PATHINFO_EXTENSION) . ';base64,' . $imageData;
                            @endphp
                            <img src="{{ $imageSrc }}" alt="Photo">
                        @else
                            <div class="no-photo">{{ strtoupper(substr($student->first_name, 0, 1)) }}</div>
                        @endif
                    </td>
                    <td>{{ $student->admission_number }}</td>
                    <td>{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</td>
                    <td>{{ $student->gender }}</td>
                    <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @if($student->parent)
                            {{ $student->parent->first_name }} {{ $student->parent->last_name }}
                        @else
                            <span style="color: #999;">Hajakabidhiwa</span>
                        @endif
                    </td>
                    <td>
                        @if($student->status === 'Active')
                            <span class="status-badge status-active">Active</span>
                        @elseif($student->status === 'Transferred')
                            <span class="status-badge status-transferred">Transferred</span>
                        @else
                            <span class="status-badge status-inactive">{{ $student->status }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #999;">
                        Hakuna wanafunzi walioorodheshwa kwenye darasa hili.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p style="margin: 0; font-weight: bold;">shuleLink powered by emcaTechnology</p>
    </div>
</body>
</html>

