<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>View Scheme of Work</title>
    
    <link rel="stylesheet" href="{{ asset('vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    <style>
        * {
            font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
        }
        
        .fa, .fa:before, i.fa, [class*="fa-"]:before, [class^="fa-"]:before {
            font-family: 'FontAwesome' !important;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .header-section {
            background: linear-gradient(135deg, #940000 0%, #b30000 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .content-wrapper {
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn-primary-custom {
            background-color: #940000;
            border-color: #940000;
            color: #ffffff;
        }
        
        .btn-primary-custom:hover {
            background-color: #b30000;
            border-color: #b30000;
            color: #ffffff;
        }
        
        .scheme-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .scheme-table th,
        .scheme-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .scheme-table th {
            background-color: #940000;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .month-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            width: 30px;
            background-color: #940000;
            color: white;
            font-weight: bold;
        }
        
        .month-cell {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            width: 30px;
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .holiday-row {
            background-color: #ffe6e6 !important;
        }
        
        .holiday-cell {
            background-color: #ffcccc;
            text-align: center;
            font-weight: bold;
            color: #cc0000;
            padding: 15px !important;
        }
        
        .row-number {
            width: 40px;
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .done-tick {
            color: #28a745;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .objectives-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .info-section {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0">
                        <i class="fa fa-eye"></i> View Scheme of Work
                    </h3>
                    <p class="mb-0 mt-2">
                        Subject: <strong>{{ $scheme->classSubject->subject->subject_name ?? 'N/A' }}</strong>
                        @if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class)
                            | Class: <strong>{{ $scheme->classSubject->subclass->class->class_name }} {{ $scheme->classSubject->subclass->subclass_name }}</strong>
                        @elseif($scheme->classSubject->class)
                            | Class: <strong>{{ $scheme->classSubject->class->class_name }}</strong>
                        @endif
                        | Year: <strong>{{ $scheme->year }}</strong>
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="d-inline-block mr-2">
                        <select id="languageSelector" class="form-control form-control-sm" style="width: auto; display: inline-block;" onchange="changeLanguage(this.value)">
                            <option value="en">English</option>
                            <option value="sw">Swahili</option>
                        </select>
                    </div>
                    <a href="{{ route('teacher.schemeOfWork') }}" class="btn btn-light">
                        <i class="fa fa-arrow-left"></i> Go Back
                    </a>
                    <button type="button" class="btn btn-danger ml-2" onclick="downloadPDF()">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-success ml-2" onclick="downloadExcel()">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="content-wrapper">
            <!-- School and Teacher Info -->
            <div class="info-section" id="infoSection">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong data-translate="nameOfSchool">NAME OF SCHOOL:</strong> {{ $school->school_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong data-translate="teacherName">TEACHER'S NAME:</strong> {{ $scheme->createdBy->first_name ?? '' }} {{ $scheme->createdBy->last_name ?? '' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong data-translate="subject">SUBJECT:</strong> {{ strtoupper($scheme->classSubject->subject->subject_name ?? 'N/A') }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong data-translate="year">YEAR:</strong> {{ $scheme->year }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong data-translate="class">CLASS:</strong> 
                            @if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class)
                                {{ strtoupper($scheme->classSubject->subclass->class->class_name . ' ' . $scheme->classSubject->subclass->subclass_name) }}
                            @elseif($scheme->classSubject->class)
                                {{ strtoupper($scheme->classSubject->class->class_name) }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Learning Objectives Section -->
            @if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0)
            <div class="objectives-section" id="objectivesSection">
                @php
                    $schoolType = $school->school_type ?? 'Primary';
                    if($schoolType === 'Primary') {
                        $objectivesTitle = "Objectives of Primary Education";
                    } else {
                        $objectivesTitle = "Learning Objectives";
                    }
                @endphp
                <h5 class="mb-3">
                    <i class="fa fa-list"></i> <span data-translate="objectivesTitle">{{ $objectivesTitle }}</span>
                </h5>
                @php
                    $schoolType = $school->school_type ?? 'Primary';
                    $subjectName = $scheme->classSubject->subject->subject_name ?? '';
                    $mainClassName = '';
                    if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class) {
                        $mainClassName = $scheme->classSubject->subclass->class->class_name;
                    } elseif($scheme->classSubject->class) {
                        $mainClassName = $scheme->classSubject->class->class_name;
                    }
                    
                    // Determine objectives intro text
                    if($schoolType === 'Primary') {
                        // Check if main class starts with "Standard 3" or similar (Standard III, Standard IV, Standard V, Standard VI, Standard 7)
                        $mainClassNameLower = strtolower(trim($mainClassName));
                        if(preg_match('/^standard\s*(3|iii|iv|v|vi|4|5|6|7)/i', $mainClassNameLower)) {
                            // Standard 3-7 - use fixed format
                            $objectivesIntro = "The objectives of Primary Education Standard III – VI are to:";
                        } else {
                            // Other primary classes (Baby class, Standard 1, Standard 2) - use fixed format for lower classes
                            $objectivesIntro = "The objectives of Primary Education Standard Baby class – standard2 are to:";
                        }
                    } else {
                        // Secondary school - just "Learning Objectives" (no intro text)
                        $objectivesIntro = "";
                    }
                @endphp
                @if(!empty($objectivesIntro))
                <p class="mb-3"><strong data-translate="objectivesIntro">{{ $objectivesIntro }}</strong></p>
                @endif
                <ol id="objectivesList">
                    @foreach($scheme->learningObjectives as $objective)
                        <li style="margin-bottom: 10px;" data-objective-id="{{ $objective->objective_id }}">{{ $objective->objective_text }}</li>
                    @endforeach
                </ol>
            </div>
            @endif

            <!-- Scheme of Work Table -->
            <div class="mt-4">
                <h5 class="mb-3">
                    <i class="fa fa-table"></i> <span data-translate="schemeOfWorkTable">Scheme of Work Table</span>
                </h5>

                <div style="overflow-x: auto;">
                    <table class="scheme-table" id="schemeTable">
                        <thead>
                            <tr>
                                <th class="row-number">#</th>
                                <th data-translate="mainCompetence">Main Competence</th>
                                <th data-translate="specificCompetences">Specific Competences</th>
                                <th data-translate="learningActivities">Learning Activities</th>
                                <th data-translate="specificActivities">Specific Activities</th>
                                <th class="month-header" data-translate="month">Month</th>
                                <th data-translate="week">Week</th>
                                <th data-translate="numberOfPeriods">Number of Periods</th>
                                <th data-translate="teachingMethods">Teaching and Learning Methods</th>
                                <th data-translate="teachingResources">Teaching and Learning Resources</th>
                                <th data-translate="assessmentTools">Assessment Tools</th>
                                <th data-translate="references">References</th>
                                <th data-translate="remarks">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $rowCount = 1;
                                $itemsByMonth = $scheme->items->groupBy('month');
                                
                                // Define month order
                                $monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 
                                             'July', 'August', 'September', 'October', 'November', 'December'];
                                
                                // Sort months by order
                                $sortedMonths = collect($monthOrder)->filter(function($month) use ($itemsByMonth) {
                                    return $itemsByMonth->has($month);
                                })->merge($itemsByMonth->keys()->diff($monthOrder))->toArray();
                            @endphp
                            
                            @foreach($sortedMonths as $month)
                                @if($itemsByMonth->has($month))
                                    @php
                                        $monthItems = $itemsByMonth->get($month);
                                        $monthRowspan = $monthItems->count();
                                        $isFirstRow = true;
                                    @endphp
                                    
                                    @foreach($monthItems as $item)
                                        <tr data-month="{{ $month }}">
                                            <td class="row-number">{{ $rowCount }}</td>
                                            <td>{{ $item->main_competence ?? '' }}</td>
                                            <td>{{ $item->specific_competences ?? '' }}</td>
                                            <td>{{ $item->learning_activities ?? '' }}</td>
                                            <td>{{ $item->specific_activities ?? '' }}</td>
                                            @if($isFirstRow)
                                                <td class="month-cell" rowspan="{{ $monthRowspan }}">{{ $month }}</td>
                                                @php $isFirstRow = false; @endphp
                                            @endif
                                            <td>{{ $item->week ?? '' }}</td>
                                            <td>{{ $item->number_of_periods ?? '' }}</td>
                                            <td>{{ $item->teaching_methods ?? '' }}</td>
                                            <td>{{ $item->teaching_resources ?? '' }}</td>
                                            <td>{{ $item->assessment_tools ?? '' }}</td>
                                            <td>{{ $item->references ?? '' }}</td>
                                            <td style="text-align: center;">
                                                @if(!empty($item->remarks) && strtolower(trim($item->remarks)) === 'done')
                                                    <span class="done-tick">✓</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @php $rowCount++; @endphp
                                    @endforeach
                                    
                                    {{-- Add separator row after each month (except last) --}}
                                    @if(!$loop->last)
                                        <tr class="month-separator">
                                            <td colspan="13" style="background-color: #e0e0e0; height: 2px; padding: 0; border: none;"></td>
                                        </tr>
                                    @endif
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- jsPDF Library for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.31/dist/jspdf.plugin.autotable.min.js"></script>
    
    <!-- SheetJS for Excel export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    
    <script>
        // Ensure jsPDF is available globally
        if (typeof window.jspdf !== 'undefined' && !window.jsPDF) {
            window.jsPDF = window.jspdf.jsPDF;
        }
        
        // Language translations
        const translations = {
            en: {
                schemeOfWork: 'SCHEME OF WORK',
                subject: 'SUBJECT:',
                year: 'YEAR:',
                class: 'CLASS:',
                teacherName: 'TEACHER\'S NAME:',
                objectivesTitle: 'Objectives of Primary Education The objectives of Primary Education',
                areTo: 'are to:',
                schemeOfWorkTable: 'Scheme of Work Table',
                poweredBy: 'Powered by EMCA Technology',
                mainCompetence: 'Main Competence',
                specificCompetences: 'Specific Competences',
                learningActivities: 'Learning Activities',
                specificActivities: 'Specific Activities',
                month: 'Month',
                week: 'Week',
                numberOfPeriods: 'Number of Periods',
                teachingMethods: 'Teaching and Learning Methods',
                teachingResources: 'Teaching and Learning Resources',
                assessmentTools: 'Assessment Tools',
                references: 'References',
                remarks: 'Remarks',
                months: {
                    'January': 'January',
                    'February': 'February',
                    'March': 'March',
                    'April': 'April',
                    'May': 'May',
                    'June': 'June',
                    'July': 'July',
                    'August': 'August',
                    'September': 'September',
                    'October': 'October',
                    'November': 'November',
                    'December': 'December'
                }
            },
            sw: {
                schemeOfWork: 'RATIBA YA KAZI',
                subject: 'SOMO:',
                year: 'MWAKA:',
                class: 'DARASA:',
                teacherName: 'JINA LA MWALIMU:',
                objectivesTitle: 'Malengo ya Elimu ya Msingi Malengo ya Elimu ya Msingi',
                areTo: 'ni:',
                schemeOfWorkTable: 'Jedwali la Ratiba ya Kazi',
                poweredBy: 'Inaendeshwa na EMCA Technology',
                mainCompetence: 'Uwezo Mkuu',
                specificCompetences: 'Uwezo Maalum',
                learningActivities: 'Shughuli za Kujifunza',
                specificActivities: 'Shughuli Maalum',
                month: 'Mwezi',
                week: 'Wiki',
                numberOfPeriods: 'Idadi ya Vipindi',
                teachingMethods: 'Mbinu za Kufundisha na Kujifunza',
                teachingResources: 'Rasilimali za Kufundisha na Kujifunza',
                assessmentTools: 'Zana za Tathmini',
                references: 'Marejeo',
                remarks: 'Maelezo',
                months: {
                    'January': 'Januari',
                    'February': 'Februari',
                    'March': 'Machi',
                    'April': 'Aprili',
                    'May': 'Mei',
                    'June': 'Juni',
                    'July': 'Julai',
                    'August': 'Agosti',
                    'September': 'Septemba',
                    'October': 'Oktoba',
                    'November': 'Novemba',
                    'December': 'Desemba'
                }
            }
        };
        
        // Get current language
        function getCurrentLanguage() {
            const selector = document.getElementById('languageSelector');
            return selector ? selector.value : 'en';
        }
        
        // Get translation
        function t(key) {
            const lang = getCurrentLanguage();
            return translations[lang][key] || translations.en[key] || key;
        }
        
        // Function to translate month name
        function translateMonth(monthName) {
            const lang = getCurrentLanguage();
            const monthTranslations = translations[lang].months || translations.en.months;
            return monthTranslations[monthName] || monthName;
        }
        
        // Change language function with AJAX and Google Translate
        function changeLanguage(lang) {
            console.log('Changing language to:', lang);
            
            // Update all static text on the page
            document.querySelectorAll('[data-translate]').forEach(function(element) {
                const key = element.getAttribute('data-translate');
                const translation = t(key);
                if (element.tagName === 'STRONG' || element.tagName === 'SPAN') {
                    element.textContent = translation;
                } else {
                    element.textContent = translation;
                }
            });
            
            // Update month names in table
            document.querySelectorAll('.month-cell').forEach(function(cell) {
                const monthName = cell.textContent.trim();
                if (monthName) {
                    cell.textContent = translateMonth(monthName);
                }
            });
            
            // Translate dynamic content using MyMemory Translation API (free alternative to Google Translate)
            if (lang === 'sw') {
                translateDynamicContent(lang);
            } else {
                restoreOriginalContent();
            }
        }
        
        // Translate dynamic content using MyMemory Translation API
        function translateDynamicContent(targetLang) {
            if (targetLang === 'en') {
                restoreOriginalContent();
                return;
            }
            
            // Translate objectives
            const objectives = document.querySelectorAll('#objectivesList li');
            objectives.forEach(function(li) {
                const originalText = li.getAttribute('data-original-text') || li.textContent.trim();
                if (!li.getAttribute('data-original-text')) {
                    li.setAttribute('data-original-text', originalText);
                }
                
                // Use MyMemory API (free, no API key needed)
                const apiUrl = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(originalText)}&langpair=en|sw`;
                
                fetch(apiUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.responseData && data.responseData.translatedText) {
                            // Preserve number prefix if exists
                            const match = originalText.match(/^(\d+\.)\s*(.+)$/);
                            if (match) {
                                li.textContent = match[1] + ' ' + data.responseData.translatedText;
                            } else {
                                li.textContent = data.responseData.translatedText;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Translation error:', error);
                    });
            });
            
            // Translate table content (main competence, specific competences, etc.)
            const tableRows = document.querySelectorAll('#schemeTable tbody tr:not(.month-separator)');
            tableRows.forEach(function(row) {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 5) {
                    // Main Competence (index 1)
                    if (cells[1]) translateCell(cells[1], targetLang);
                    // Specific Competences (index 2)
                    if (cells[2]) translateCell(cells[2], targetLang);
                    // Learning Activities (index 3)
                    if (cells[3]) translateCell(cells[3], targetLang);
                    // Specific Activities (index 4)
                    if (cells[4]) translateCell(cells[4], targetLang);
                    // Teaching Methods (index 8)
                    if (cells[8]) translateCell(cells[8], targetLang);
                    // Teaching Resources (index 9)
                    if (cells[9]) translateCell(cells[9], targetLang);
                    // Assessment Tools (index 10)
                    if (cells[10]) translateCell(cells[10], targetLang);
                    // References (index 11)
                    if (cells[11]) translateCell(cells[11], targetLang);
                }
            });
        }
        
        // Translate individual cell
        function translateCell(cell, targetLang) {
            const originalText = cell.getAttribute('data-original-text') || cell.textContent.trim();
            if (!originalText || originalText === '') return;
            
            if (!cell.getAttribute('data-original-text')) {
                cell.setAttribute('data-original-text', originalText);
            }
            
            if (targetLang === 'en') {
                // Restore original
                cell.textContent = originalText;
                return;
            }
            
            // Use MyMemory API (free, no API key needed)
            const apiUrl = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(originalText)}&langpair=en|sw`;
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.responseData && data.responseData.translatedText) {
                        cell.textContent = data.responseData.translatedText;
                    }
                })
                .catch(error => {
                    console.error('Translation error:', error);
                });
        }
        
        // Restore original content
        function restoreOriginalContent() {
            document.querySelectorAll('[data-original-text]').forEach(function(element) {
                const original = element.getAttribute('data-original-text');
                if (original) {
                    element.textContent = original;
                }
            });
        }
        
        // Make functions globally accessible
        function downloadPDF() {
            console.log('PDF download clicked');
            
            // Check if jsPDF is available
            var jsPDFLib = window.jspdf;
            var JSPDF = null;
            
            if (jsPDFLib && jsPDFLib.jsPDF) {
                JSPDF = jsPDFLib.jsPDF;
                console.log('jsPDF found via window.jspdf');
            } else if (typeof window.jsPDF !== 'undefined') {
                JSPDF = window.jsPDF;
                console.log('jsPDF found via window.jsPDF');
            } else {
                console.error('jsPDF not found');
                alert('PDF library not loaded. Please refresh the page.');
                return;
            }
            
            if (!JSPDF) {
                alert('PDF library not loaded. Please refresh the page.');
                return;
            }
            
            // Function to generate PDF (will be called after logo loads)
            function generatePDF(logoDataUrl) {
                try {
                    // Use landscape orientation for all pages
                    const doc = new JSPDF('landscape', 'mm', 'a4');
                    const pageWidth = doc.internal.pageSize.getWidth();
                    const pageHeight = doc.internal.pageSize.getHeight();
                    let yPos = 20;
                    
                    // ========== COVER PAGE - Objectives ==========
                    
                    // School Logo (top center)
                    @if($school && $school->school_logo)
                        if (logoDataUrl) {
                            const logoWidth = 30;
                            const logoHeight = 30;
                            const logoX = (pageWidth - logoWidth) / 2;
                            try {
                                doc.addImage(logoDataUrl, 'PNG', logoX, yPos, logoWidth, logoHeight);
                            } catch(e) {
                                console.log('Could not add logo image:', e);
                            }
                        }
                        yPos += 35;
                    @else
                        yPos += 10;
                    @endif
                
                // School Name (in CAPS, centered)
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.setFont('helvetica', 'bold');
                doc.text('{{ strtoupper(addslashes($school->school_name ?? "N/A")) }}', pageWidth / 2, yPos, { align: 'center' });
                yPos += 10;
                
                // SCHEME OF WORK Title
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.setFont('helvetica', 'bold');
                doc.text(t('schemeOfWork'), pageWidth / 2, yPos, { align: 'center' });
                yPos += 15;
                
                // Info Table (full width) - 2 rows structure
                const tableStartY = yPos;
                const tableMargin = 10;
                const tableWidth = pageWidth - (tableMargin * 2);
                const rowHeight = 8;
                const cellPadding = 3;
                
                // Table border (2 rows)
                doc.setDrawColor(0, 0, 0);
                doc.setLineWidth(0.5);
                doc.rect(tableMargin, tableStartY, tableWidth, rowHeight * 2);
                
                // Horizontal line (middle)
                doc.line(tableMargin, tableStartY + rowHeight, tableMargin + tableWidth, tableStartY + rowHeight);
                
                // Vertical lines (3 columns) - only for row 1
                doc.line(tableMargin + tableWidth / 3, tableStartY, tableMargin + tableWidth / 3, tableStartY + rowHeight);
                doc.line(tableMargin + (tableWidth / 3) * 2, tableStartY, tableMargin + (tableWidth / 3) * 2, tableStartY + rowHeight);
                
                // Fill table content
                doc.setFontSize(12);
                doc.setTextColor(0, 0, 0);
                doc.setFont('helvetica', 'bold');
                
                // Row 1: Subject, Year, Class (3 columns)
                doc.text(t('subject'), tableMargin + cellPadding, tableStartY + rowHeight - 3);
                doc.setFont('helvetica', 'normal');
                const subjectName = '{{ strtoupper(addslashes($scheme->classSubject->subject->subject_name ?? "N/A")) }}';
                const subjectNameWidth = doc.getTextWidth(t('subject')) + 5;
                doc.text(subjectName, tableMargin + subjectNameWidth, tableStartY + rowHeight - 3);
                
                doc.setFont('helvetica', 'bold');
                doc.text(t('year'), tableMargin + tableWidth / 3 + cellPadding, tableStartY + rowHeight - 3);
                doc.setFont('helvetica', 'normal');
                const yearValue = '{{ $scheme->year }}';
                const yearLabelWidth = doc.getTextWidth(t('year')) + 5;
                doc.text(yearValue, tableMargin + tableWidth / 3 + yearLabelWidth, tableStartY + rowHeight - 3);
                
                doc.setFont('helvetica', 'bold');
                doc.text(t('class'), tableMargin + (tableWidth / 3) * 2 + cellPadding, tableStartY + rowHeight - 3);
                doc.setFont('helvetica', 'normal');
                const className = '{{ strtoupper(addslashes(($scheme->classSubject->subclass && $scheme->classSubject->subclass->class ? $scheme->classSubject->subclass->class->class_name . " " . $scheme->classSubject->subclass->subclass_name : ($scheme->classSubject->class ? $scheme->classSubject->class->class_name : "N/A")))) }}';
                const classLabelWidth = doc.getTextWidth(t('class')) + 5;
                doc.text(className, tableMargin + (tableWidth / 3) * 2 + classLabelWidth, tableStartY + rowHeight - 3);
                
                // Row 2: Teacher Name (spanning full width - colspan)
                doc.setFont('helvetica', 'bold');
                doc.text(t('teacherName'), tableMargin + cellPadding, tableStartY + rowHeight * 2 - 3);
                doc.setFont('helvetica', 'normal');
                const teacherName = '{{ addslashes(($scheme->createdBy->first_name ?? "") . " " . ($scheme->createdBy->last_name ?? "")) }}';
                const teacherNameLabelWidth = doc.getTextWidth(t('teacherName')) + 5;
                const teacherNameLines = doc.splitTextToSize(teacherName, tableWidth - teacherNameLabelWidth - 10);
                doc.text(teacherNameLines, tableMargin + teacherNameLabelWidth, tableStartY + rowHeight * 2 - 3);
                
                yPos = tableStartY + rowHeight * 2 + 15;
                
                // Learning Objectives Section
                @if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0)
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                @php
                    $schoolType = $school->school_type ?? 'Primary';
                    $subjectName = $scheme->classSubject->subject->subject_name ?? '';
                    $mainClassName = '';
                    if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class) {
                        $mainClassName = $scheme->classSubject->subclass->class->class_name;
                    } elseif($scheme->classSubject->class) {
                        $mainClassName = $scheme->classSubject->class->class_name;
                    }
                    
                    // Determine objectives title text
                    if($schoolType === 'Primary') {
                        // Check if main class starts with "Standard 3" or similar (Standard III, Standard IV, Standard V, Standard VI, Standard 7)
                        $mainClassNameLower = strtolower(trim($mainClassName));
                        if(preg_match('/^standard\s*(3|iii|iv|v|vi|4|5|6|7)/i', $mainClassNameLower)) {
                            // Standard 3-7 - use fixed format
                            $objectivesTitleText = "Objectives of Primary Education The objectives of Primary Education Standard III – VI are to:";
                        } else {
                            // Other primary classes (Baby class, Standard 1, Standard 2) - use fixed format for lower classes
                            $objectivesTitleText = "Objectives of Primary Education The objectives of Primary Education Standard Baby class – standard2 are to:";
                        }
                    } else {
                        // Secondary school - just "Learning Objectives"
                        $objectivesTitleText = "Learning Objectives";
                    }
                @endphp
                const objectivesTitle = '{{ addslashes($objectivesTitleText) }}';
                const titleLines = doc.splitTextToSize(objectivesTitle, pageWidth - 20);
                doc.text(titleLines, pageWidth / 2, yPos, { align: 'center' });
                yPos += titleLines.length * 5 + 8;
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(12);
                doc.setTextColor(0, 0, 0);
                
                @foreach($scheme->learningObjectives as $objective)
                    const objectiveText{{ $loop->index }} = doc.splitTextToSize('{{ $loop->iteration }}.\t{{ addslashes($objective->objective_text) }}', pageWidth - 25);
                    doc.text(objectiveText{{ $loop->index }}, 15, yPos);
                    yPos += objectiveText{{ $loop->index }}.length * 5;
                    if (yPos > pageHeight - 20) {
                        // Add footer before new page
                        doc.setFontSize(12);
                        doc.setTextColor(148, 0, 0); // #940000
                        doc.setFont('helvetica', 'italic');
                        doc.text(t('poweredBy'), pageWidth / 2, pageHeight - 5, { align: 'center' });
                        doc.addPage();
                        yPos = 15;
                    }
                @endforeach
                
                // Add footer on last page of objectives
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0); // #940000
                doc.setFont('helvetica', 'italic');
                doc.text(t('poweredBy'), pageWidth / 2, pageHeight - 5, { align: 'center' });
                @endif
                
                // ========== NEW PAGE - Table ==========
                // Already in landscape mode, no need to addPage with landscape
                doc.addPage();
                const landscapePageWidth = doc.internal.pageSize.getWidth();
                const landscapePageHeight = doc.internal.pageSize.getHeight();
                yPos = 10;
                
                // Table header title
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.setFont('helvetica', 'bold');
                doc.text(t('schemeOfWorkTable'), landscapePageWidth / 2, yPos, { align: 'center' });
                yPos += 10;
                
                // Prepare table data with proper structure (ordered by months) with rowspan for month
                const tableData = [];
                const monthSeparators = [];
                const monthRowspans = {}; // Store rowspan info for each month
                @php
                    $rowNum = 1;
                    $itemsByMonth = $scheme->items->groupBy('month');
                    
                    // Define month order
                    $monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 
                                 'July', 'August', 'September', 'October', 'November', 'December'];
                    
                    // Sort months by order
                    $sortedMonths = collect($monthOrder)->filter(function($month) use ($itemsByMonth) {
                        return $itemsByMonth->has($month);
                    })->merge($itemsByMonth->keys()->diff($monthOrder))->toArray();
                @endphp
                
                @foreach($sortedMonths as $monthIndex => $month)
                    @if($itemsByMonth->has($month))
                        @php
                            $monthItems = $itemsByMonth->get($month);
                            $monthRowspan = $monthItems->count();
                            $isFirstRow = true;
                            $firstRowIndex = null;
                        @endphp
                        
                        @foreach($monthItems as $item)
                            @php
                                $isFirst = $isFirstRow;
                                if($isFirstRow) {
                                    $isFirstRow = false;
                                }
                            @endphp
                            const row{{ $rowNum }} = [
                                '{{ $rowNum }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->main_competence ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->specific_competences ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->learning_activities ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->specific_activities ?? "")) }}',
                                @if($isFirst)
                                    translateMonth('{{ addslashes($month) }}')
                                @else
                                    null
                                @endif,
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->week ?? "")) }}',
                                '{{ $item->number_of_periods ?? "" }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->teaching_methods ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->teaching_resources ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->assessment_tools ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->references ?? "")) }}',
                                '{{ strtolower(trim($item->remarks ?? "")) === "done" ? "done" : addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->remarks ?? "")) }}'
                            ];
                            tableData.push(row{{ $rowNum }});
                            @if($isFirst)
                                monthRowspans[tableData.length - 1] = {{ $monthRowspan }};
                            @endif
                            @php $rowNum++; @endphp
                        @endforeach
                        
                        {{-- Add separator after month (except last) --}}
                        @if(!$loop->last)
                            monthSeparators.push(tableData.length);
                        @endif
                    @endif
                @endforeach
                
                // Add table using autoTable with better formatting
                if (typeof doc.autoTable !== 'undefined') {
                    // Calculate column widths based on landscape page width
                    const totalWidth = landscapePageWidth - 10;
                    const columnWidths = {
                        0: 8,   // #
                        1: 28,  // Main Competence
                        2: 28,  // Specific Competences
                        3: 28,  // Learning Activities
                        4: 28,  // Specific Activities
                        5: 18,  // Month
                        6: 18,  // Week
                        7: 15,  // Number of Periods
                        8: 32,  // Teaching Methods
                        9: 28,  // Teaching Resources
                        10: 28, // Assessment Tools
                        11: 32, // References
                        12: 18  // Remarks
                    };
                    
                    doc.autoTable({
                        head: [['#', t('mainCompetence'), t('specificCompetences'), t('learningActivities'), t('specificActivities'), 
                               t('month'), t('week'), t('numberOfPeriods'), t('teachingMethods'), 
                               t('teachingResources'), t('assessmentTools'), t('references'), t('remarks')]],
                        body: tableData,
                        startY: yPos,
                        theme: 'grid',
                        headStyles: { 
                            fillColor: [148, 0, 0], 
                            textColor: [255, 255, 255], 
                            fontStyle: 'bold',
                            fontSize: 12,
                            font: 'helvetica',
                            halign: 'center'
                        },
                        bodyStyles: { 
                            fontSize: 12,
                            font: 'helvetica',
                            textColor: [0, 0, 0],
                            cellPadding: 2
                        },
                        styles: { 
                            fontSize: 12,
                            font: 'helvetica',
                            cellPadding: 2,
                            overflow: 'linebreak',
                            lineWidth: 0.1
                        },
                        columnStyles: {
                            0: columnWidths[0],
                            1: columnWidths[1],
                            2: columnWidths[2],
                            3: columnWidths[3],
                            4: columnWidths[4],
                            5: { 
                                cellWidth: 20,
                                valign: 'middle',
                                halign: 'center'
                            },
                            6: columnWidths[6],
                            7: columnWidths[7],
                            8: columnWidths[8],
                            9: columnWidths[9],
                            10: columnWidths[10],
                            11: columnWidths[11],
                            12: columnWidths[12]
                        },
                        margin: { left: 5, right: 5, top: 5 },
                        tableWidth: totalWidth,
                        showHead: 'everyPage',
                        didParseCell: function(data) {
                            // Only apply rowspan to body cells (TD), not header (TH)
                            if (data.table.section === 'body' && data.column.index === 5) { // Month column (index 5) in body only
                                const rowIndex = data.row.index;
                                
                                // Check if this row should have month rowspan
                                if (monthRowspans[rowIndex] !== undefined && monthRowspans[rowIndex] > 0) {
                                    // This is the first row of a month group - apply rowspan with shadow/background
                                    data.cell.rowSpan = monthRowspans[rowIndex];
                                    data.cell.styles.valign = 'middle';
                                    data.cell.styles.halign = 'center';
                                    data.cell.styles.fontSize = 12;
                                    data.cell.styles.fontStyle = 'bold';
                                    data.cell.styles.font = 'helvetica';
                                    // Add background color (shadow effect) - light gray like in blade (#f0f0f0)
                                    data.cell.styles.fillColor = [240, 240, 240];
                                    data.cell.styles.textColor = [0, 0, 0];
                                    // Keep the month text visible and centered
                                } else if (data.cell.text[0] === null || data.cell.text[0] === '' || data.cell.text[0] === undefined) {
                                    // This is a subsequent row in the month group - remove the cell (it's part of rowspan)
                                    // Set rowspan to 0 to skip rendering this cell
                                    data.cell.rowSpan = 0;
                                    data.cell.styles.fontSize = 0;
                                    data.cell.styles.cellPadding = 0;
                                    data.cell.styles.lineWidth = 0;
                                    data.cell.styles.fillColor = [255, 255, 255];
                                }
                            }
                            
                            // Style remarks column (index 12) - show "done" in green, bold
                            if (data.table.section === 'body' && data.column.index === 12) { // Remarks column
                                const remarkText = (data.cell.text[0] || '').toLowerCase().trim();
                                if (remarkText === 'done') {
                                    // This is a done remark - make it green and bold
                                    data.cell.styles.textColor = [0, 128, 0]; // Green
                                    data.cell.styles.fontStyle = 'bold';
                                    data.cell.styles.fontSize = 12;
                                    data.cell.styles.font = 'helvetica';
                                    data.cell.styles.halign = 'center';
                                    data.cell.styles.valign = 'middle';
                                } else if (remarkText === '') {
                                    // Empty remark - keep it empty
                                    data.cell.styles.textColor = [0, 0, 0];
                                    data.cell.styles.halign = 'center';
                                }
                            }
                            
                            // Add separator line after month groups (only in body)
                            if (data.table.section === 'body' && monthSeparators.includes(data.row.index)) {
                                data.cell.styles.fillColor = [224, 224, 224];
                                data.cell.styles.lineWidth = 0.5;
                            }
                        },
                        didDrawCell: function(data) {
                            // Draw month text vertically (like in blade with writing-mode: vertical-rl)
                            if (data.table.section === 'body' && data.column.index === 5) { // Month column
                                const rowIndex = data.row.index;
                                
                                // Check if this is the first row of a month group with rowspan
                                if (monthRowspans[rowIndex] !== undefined && monthRowspans[rowIndex] > 0) {
                                    // Get the original month text from tableData
                                    const monthText = tableData[rowIndex][5]; // Month is at index 5
                                    
                                    if (monthText && monthText !== '' && monthText !== null) {
                                        // Calculate center position of the cell (considering rowspan)
                                        const cellCenterX = data.cell.x + (data.cell.width / 2);
                                        const totalCellHeight = data.cell.height * monthRowspans[rowIndex];
                                        const cellCenterY = data.cell.y + (totalCellHeight / 2);
                                        
                                        // Set font for month
                                        doc.setFontSize(12);
                                        doc.setFont('helvetica', 'bold');
                                        doc.setTextColor(0, 0, 0);
                                        
                                        // Draw month text vertically (rotated 90 degrees)
                                        // Split text into characters and draw vertically
                                        const chars = monthText.split('');
                                        const charHeight = 3.5; // Height per character
                                        const totalTextHeight = (chars.length - 1) * charHeight;
                                        const startY = cellCenterY - (totalTextHeight / 2);
                                        
                                        chars.forEach(function(char, index) {
                                            const charY = startY + (index * charHeight);
                                            // Rotate text 90 degrees (vertical) - angle in degrees
                                            doc.text(char, cellCenterX, charY, {
                                                angle: 90,
                                                align: 'center'
                                            });
                                        });
                                        
                                        // Clear the original text so it doesn't draw horizontally
                                        data.cell.text = [''];
                                    }
                                }
                            }
                        },
                        didDrawPage: function(data) {
                            // Add footer - Powered by EMCA Technology (moved up to avoid cutoff when printing)
                            doc.setFontSize(12);
                            doc.setTextColor(148, 0, 0); // #940000
                            doc.setFont('helvetica', 'italic');
                            doc.text(t('poweredBy'), landscapePageWidth / 2, landscapePageHeight - 8, { align: 'center' });
                            
                            // Add page number (below footer)
                            doc.setFontSize(12);
                            doc.setTextColor(100, 100, 100);
                            doc.text('Page ' + doc.internal.getNumberOfPages(), landscapePageWidth / 2, landscapePageHeight - 5, { align: 'center' });
                        }
                    });
                    
                    // Add separator lines after each month group
                    monthSeparators.forEach(function(separatorIndex) {
                        if (separatorIndex < tableData.length) {
                            // Draw line after the row
                            const lastY = doc.lastAutoTable.finalY;
                            doc.setDrawColor(200, 200, 200);
                            doc.setLineWidth(0.5);
                            doc.line(5, lastY + 1, landscapePageWidth - 5, lastY + 1);
                        }
                    });
                } else {
                    alert('PDF table plugin not loaded. Please refresh the page.');
                    return;
                }
                
                    // Save PDF
                    const filename = 'Scheme_of_Work_{{ str_replace(" ", "_", $scheme->classSubject->subject->subject_name ?? "Subject") }}_{{ $scheme->year }}.pdf';
                    doc.save(filename);
                    
                } catch (error) {
                    console.error('PDF Export Error:', error);
                    alert('Failed to export PDF: ' + error.message);
                }
            }
            
            // Load logo if exists, then generate PDF
            @if($school && $school->school_logo)
                console.log('Loading logo:', '{{ asset($school->school_logo) }}');
                const logoImg = new Image();
                
                // Try to set crossOrigin, but handle errors gracefully
                try {
                    // Only set crossOrigin if not on same origin (to avoid CORS issues)
                    const logoUrl = '{{ asset($school->school_logo) }}';
                    if (logoUrl.startsWith('http://') || logoUrl.startsWith('https://')) {
                        logoImg.crossOrigin = 'anonymous';
                    }
                } catch(e) {
                    console.log('Could not set crossOrigin:', e);
                }
                
                // Set timeout to prevent hanging
                const logoTimeout = setTimeout(function() {
                    console.log('Logo loading timeout, continuing without logo');
                    generatePDF(null);
                }, 5000);
                
                logoImg.onload = function() {
                    clearTimeout(logoTimeout);
                    console.log('Logo loaded successfully');
                    try {
                        // Convert to data URL
                        const canvas = document.createElement('canvas');
                        canvas.width = logoImg.width;
                        canvas.height = logoImg.height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(logoImg, 0, 0);
                        const logoDataUrl = canvas.toDataURL('image/png');
                        console.log('Logo converted to data URL, generating PDF...');
                        generatePDF(logoDataUrl);
                    } catch(e) {
                        console.error('Error converting logo:', e);
                        console.log('Continuing without logo');
                        generatePDF(null);
                    }
                };
                
                logoImg.onerror = function(error) {
                    clearTimeout(logoTimeout);
                    console.log('Logo failed to load, continuing without logo', error);
                    // If logo fails to load, continue without it
                    generatePDF(null);
                };
                
                logoImg.src = '{{ asset($school->school_logo) }}';
            @else
                console.log('No logo, generating PDF without logo');
                generatePDF(null);
            @endif
        }
        
        function downloadExcel() {
            console.log('Excel download clicked');
            
            // Check if XLSX is available
            if (typeof XLSX === 'undefined') {
                console.error('XLSX library not found');
                alert('Excel library not loaded. Please refresh the page.');
                return;
            }
            
            console.log('XLSX library found, starting export...');
            
            try {
                const wb = XLSX.utils.book_new();
                const data = [];
                
                // Get current language for Excel
                const currentLang = getCurrentLanguage();
                const t_excel = translations[currentLang] || translations.en;
                
                // Header rows
                data.push(['{{ strtoupper(addslashes($school->school_name ?? "N/A")) }}']);
                data.push([t_excel.schemeOfWork]);
                data.push([]); // Empty row
                
                // Info table
                data.push([t_excel.subject, '{{ strtoupper(addslashes($scheme->classSubject->subject->subject_name ?? "N/A")) }}', '', t_excel.year, '{{ $scheme->year }}', '', t_excel.class, '{{ strtoupper(addslashes(($scheme->classSubject->subclass && $scheme->classSubject->subclass->class ? $scheme->classSubject->subclass->class->class_name . " " . $scheme->classSubject->subclass->subclass_name : ($scheme->classSubject->class ? $scheme->classSubject->class->class_name : "N/A")))) }}']);
                data.push([t_excel.teacherName, '{{ addslashes(($scheme->createdBy->first_name ?? "") . " " . ($scheme->createdBy->last_name ?? "")) }}']);
                data.push([]); // Empty row
                
                // Learning Objectives
                @if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0)
                @php
                    $schoolType = $school->school_type ?? 'Primary';
                    $subjectName = $scheme->classSubject->subject->subject_name ?? '';
                    $mainClassName = '';
                    if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class) {
                        $mainClassName = $scheme->classSubject->subclass->class->class_name;
                    } elseif($scheme->classSubject->class) {
                        $mainClassName = $scheme->classSubject->class->class_name;
                    }
                    
                    // Determine objectives title text
                    if($schoolType === 'Primary') {
                        // Check if main class starts with "Standard 3" or similar (Standard III, Standard IV, Standard V, Standard VI, Standard 7)
                        $mainClassNameLower = strtolower(trim($mainClassName));
                        if(preg_match('/^standard\s*(3|iii|iv|v|vi|4|5|6|7)/i', $mainClassNameLower)) {
                            // Standard 3-7 - use fixed format
                            $objectivesTitleText = "Objectives of Primary Education The objectives of Primary Education Standard III – VI are to:";
                        } else {
                            // Other primary classes (Baby class, Standard 1, Standard 2) - use fixed format for lower classes
                            $objectivesTitleText = "Objectives of Primary Education The objectives of Primary Education Standard Baby class – standard2 are to:";
                        }
                    } else {
                        // Secondary school - just "Learning Objectives"
                        $objectivesTitleText = "Learning Objectives";
                    }
                @endphp
                data.push(['{{ addslashes($objectivesTitleText) }}']);
                @foreach($scheme->learningObjectives as $objective)
                    data.push(['{{ $loop->iteration }}.', '{{ addslashes($objective->objective_text) }}']);
                @endforeach
                data.push([]); // Empty row
                @endif
                
                // Table headers
                data.push(['#', t_excel.mainCompetence, t_excel.specificCompetences, t_excel.learningActivities, t_excel.specificActivities, 
                          t_excel.month, t_excel.week, t_excel.numberOfPeriods, t_excel.teachingMethods, 
                          t_excel.teachingResources, t_excel.assessmentTools, t_excel.references, t_excel.remarks]);
                
                // Table data (ordered by months)
                @php
                    $rowNum = 1;
                    $itemsByMonth = $scheme->items->groupBy('month');
                    
                    // Define month order
                    $monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 
                                 'July', 'August', 'September', 'October', 'November', 'December'];
                    
                    // Sort months by order
                    $sortedMonths = collect($monthOrder)->filter(function($month) use ($itemsByMonth) {
                        return $itemsByMonth->has($month);
                    })->merge($itemsByMonth->keys()->diff($monthOrder))->toArray();
                @endphp
                
                @foreach($sortedMonths as $month)
                    @if($itemsByMonth->has($month))
                        @php
                            $monthItems = $itemsByMonth->get($month);
                            $isFirstRow = true;
                        @endphp
                        
                        @foreach($monthItems as $item)
                            @php
                                $isFirst = $isFirstRow;
                                if($isFirstRow) {
                                    $isFirstRow = false;
                                }
                            @endphp
                            data.push([
                                {{ $rowNum }},
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->main_competence ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->specific_competences ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->learning_activities ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->specific_activities ?? "")) }}',
                                @if($isFirst)
                                    translateMonth('{{ addslashes($month) }}')
                                @else
                                    ''
                                @endif,
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->week ?? "")) }}',
                                '{{ $item->number_of_periods ?? "" }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->teaching_methods ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->teaching_resources ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->assessment_tools ?? "")) }}',
                                '{{ addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->references ?? "")) }}',
                                '{{ strtolower(trim($item->remarks ?? "")) === "done" ? "done" : addslashes(str_replace(["\r\n", "\r", "\n"], " ", $item->remarks ?? "")) }}'
                            ]);
                            @php $rowNum++; @endphp
                        @endforeach
                    @endif
                @endforeach
                
                // Create worksheet
                const ws = XLSX.utils.aoa_to_sheet(data);
                
                // Set column widths
                ws['!cols'] = [
                    { wch: 5 },   // #
                    { wch: 30 },  // Main Competence
                    { wch: 30 },  // Specific Competences
                    { wch: 30 },  // Learning Activities
                    { wch: 30 },  // Specific Activities
                    { wch: 15 },  // Month
                    { wch: 15 },  // Week
                    { wch: 12 },  // Number of Periods
                    { wch: 35 },  // Teaching Methods
                    { wch: 30 },  // Teaching Resources
                    { wch: 30 },  // Assessment Tools
                    { wch: 35 },  // References
                    { wch: 15 }   // Remarks
                ];
                
                // Style header row (row with table headers)
                @php
                    $headerRowIndex = 0;
                    if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0) {
                        $headerRowIndex = 3 + 1 + $scheme->learningObjectives->count() + 1; // School, Title, empty, info rows, objectives, empty
                    } else {
                        $headerRowIndex = 3 + 1; // School, Title, empty, info rows
                    }
                @endphp
                const headerRowIndex = {{ $headerRowIndex }};
                
                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, 'Scheme of Work');
                
                // Generate filename
                const filename = 'Scheme_of_Work_{{ str_replace(" ", "_", $scheme->classSubject->subject->subject_name ?? "Subject") }}_{{ $scheme->year }}.xlsx';
                
                // Save file
                console.log('Saving Excel file:', filename);
                XLSX.writeFile(wb, filename);
                console.log('Excel file saved successfully');
                
            } catch (error) {
                console.error('Excel Export Error:', error);
                console.error('Error stack:', error.stack);
                alert('Failed to export Excel: ' + error.message);
            }
        }
        
        // Wait for libraries to load
        window.addEventListener('load', function() {
            console.log('Page loaded');
            console.log('jsPDF available:', typeof window.jspdf !== 'undefined' || typeof window.jsPDF !== 'undefined');
            console.log('XLSX available:', typeof XLSX !== 'undefined');
            console.log('downloadPDF function:', typeof window.downloadPDF);
            console.log('downloadExcel function:', typeof window.downloadExcel);
        });
        
        // Functions are now globally accessible
    </script>
</body>
</html>

