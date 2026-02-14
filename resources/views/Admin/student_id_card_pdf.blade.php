<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Import Century Gothic-like font */
        @import url('https://fonts.googleapis.com/css2?family=Questrial&display=swap');

        @page {
            size: 85.6mm 54mm;
            margin: 0;
            padding: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }

        .page-break {
            page-break-after: always;
        }

        .card-page {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            background-color: #fff;
            box-sizing: border-box;
        }

        /* Footer Stripe */
        .footer-stripe {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 8px; /* Slightly thinner for elegance */
            background-color: {{ $primaryColor ?? '#940000' }};
            z-index: 10;
        }

        /* Content Layout */
        .header-section {
            background-color: {{ $primaryColor ?? '#940000' }};
            color: {{ $secondaryColor ?? '#ffffff' }};
            height: 42px; /* Slightly reduced */
            padding: 4px 10px;
            width: 100%;
        }

        /* Table Resets */
        table { width: 100%; border-collapse: collapse; border-spacing: 0; }
        td { padding: 0; margin: 0; vertical-align: top; }

        .logo-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            padding: 2px;
            object-fit: contain;
        }

        .school-info {
            padding-left: 8px;
            vertical-align: middle;
        }

        .school-name {
            font-size: 9pt; /* Standardized Title */
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }

        .card-label {
            font-size: 7pt; /* Subtitle */
            font-weight: normal;
            opacity: 0.9;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }

        .body-section {
            padding: 8px 10px;
        }

        .photo-img {
            width: 22mm;
            height: 27mm;
            border: 1px solid #ccc;
            object-fit: cover;
            display: block;
        }

        .details-col {
            padding-left: 10px;
        }

        .student-name {
            font-size: 10pt; /* Name standout */
            font-weight: bold;
            text-transform: uppercase;
            color: #2f2f2f;
            margin-bottom: 4px;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }

        /* Standardized Info Text Size */
        .info-row {
            font-size: 8pt; /* Uniform Size */
            line-height: 1.35;
            color: #333;
            margin-bottom: 1px;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }

        .label {
            font-weight: bold;
            color: {{ $primaryColor ?? '#940000' }};
            display: inline-block;
            width: 45px;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }

        .value {
            text-transform: uppercase;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }

        /* Back layout */
        .back-content {
            padding: 15px 12px;
        }

        .important-title {
            color: {{ $primaryColor ?? '#940000' }};
            font-weight: bold;
            font-size: 9pt; /* Matching Header */
            border-bottom: 2px solid {{ $primaryColor ?? '#940000' }};
            padding-bottom: 2px;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }

        .back-row {
            font-size: 8pt; /* Matching Front Info */
            margin-bottom: 3px;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }
        
        .disclaimer {
            margin-top: 10px;
            font-size: 7pt; /* Small print */
            color: #555;
            line-height: 1.25;
            text-transform: uppercase;
            font-family: 'Questrial', 'Century Gothic', sans-serif !important;
        }
    </style>
</head>
<body>
@foreach($students as $batchIndex => $student)
    <!-- Front Page -->
    <div class="card-page">
        <div class="header-section">
            <table style="height: 100%;">
                <tr>
                    <td style="width: 38px; vertical-align: middle;">
                        @php
                            $logoPath = 'images/shuleXpert.jpg';
                            if ($student->school) {
                                if (!empty($student->school->logo)) {
                                    $logoPath = 'schoolLogos/' . $student->school->logo;
                                } elseif (!empty($student->school->school_logo)) {
                                    $logoPath = $student->school->school_logo;
                                }
                            }
                            $logoFullPath = public_path($logoPath);
                            if (!file_exists($logoFullPath)) {
                                 $logoFullPath = public_path('images/shuleXpert.jpg');
                            }
                        @endphp
                        <img src="{{ $logoFullPath }}" class="logo-img">
                    </td>
                    <td class="school-info">
                        <div class="school-name">{{ Str::limit($student->school->school_name ?? 'SHULELINK ACADEMY', 35) }}</div>
                        <div class="card-label">STUDENT IDENTITY CARD</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="body-section">
            <table>
                <tr>
                    <td style="width: 24mm;">
                        @php
                            $placeholder = strtolower($student->gender ?? 'male') === 'female' ? 'images/female.png' : 'images/male.png';
                            $photoPath = ($student->photo && file_exists(public_path('userImages/' . $student->photo))) 
                                ? 'userImages/' . $student->photo 
                                : $placeholder;
                        @endphp
                        <img src="{{ public_path($photoPath) }}" class="photo-img">
                    </td>
                    <td class="details-col">
                        <div class="student-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                        <!-- Nested table for perfect alignment -->
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 45px;" class="info-row label">ID:</td>
                                <td class="info-row value">{{ $student->admission_number ?? $student->studentID }}</td>
                            </tr>
                            <tr>
                                <td class="info-row label">CLASS:</td>
                                <td class="info-row value">{{ $student->subclass->class->class_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="info-row label">GENDER:</td>
                                <td class="info-row value">{{ ucfirst($student->gender) }}</td>
                            </tr>
                            <tr>
                                <td class="info-row label">DOB:</td>
                                <td class="info-row value">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer-stripe"></div>
    </div><div class="page-break"></div><!-- Back Page -->
    <div class="card-page">
        <div class="back-content">
            <div class="important-title">IMPORTANT INFORMATION</div>
            
            <table style="width: 100%;">
                <tr>
                    <td class="back-row label" style="width:60px; color:#2f2f2f;">PARENT:</td>
                    <td class="back-row value">{{ $student->parent->first_name ?? '' }} {{ $student->parent->last_name ?? '' }}</td>
                </tr>
                <tr>
                    <td class="back-row label" style="color:#2f2f2f;">CONTACT:</td>
                    <td class="back-row value">{{ $student->parent->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="back-row label" style="color:#2f2f2f;">ADDRESS:</td>
                    <td class="back-row value">{{ $student->address ?? 'N/A' }}</td>
                </tr>
            </table>

            <div class="disclaimer">
                THIS CARD IS THE PROPERTY OF <strong>{{ $student->school->school_name ?? 'SHULELINK' }}</strong>. IF FOUND, PLEASE RETURN IT TO THE NEAREST SCHOOL OFFICE OR CALL <strong>{{ $student->school->phone ?? 'THE OFFICE' }}</strong>.
            </div>
        </div>
        <div class="footer-stripe"></div>
    </div>
    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
