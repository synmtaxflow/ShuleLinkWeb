@include('includes.parent_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
    }

    body {
        background-color: #f8f9fa;
        overflow-x: hidden;
    }

    * {
        box-sizing: border-box;
    }

    /* Filter Card */
    .filter-card {
        background: white;
        border-radius: 0;
        padding: 25px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .filter-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .filter-title i {
        margin-right: 10px;
        color: var(--primary-color);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 10px;
        display: block;
    }

    .form-label i {
        margin-right: 6px;
        color: var(--primary-color);
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 0;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15);
    }

    .btn-filter {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        padding: 10px 24px;
        border-radius: 0;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-filter:hover {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
        color: white;
    }

    .btn-reset {
        background: #6c757d;
        border-color: #6c757d;
        color: white;
        padding: 10px 24px;
        border-radius: 0;
        font-weight: 500;
    }

    .btn-reset:hover {
        background: #5a6268;
        border-color: #5a6268;
        color: white;
    }

    /* Results Card */
    .results-card {
        background: white;
        border-radius: 0;
        padding: 25px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .results-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .results-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .results-title i {
        margin-right: 10px;
        color: var(--primary-color);
    }

    /* Table Styles */
    .table-responsive {
        border-radius: 0;
        overflow-x: hidden;
        overflow-y: hidden;
        width: 100%;
        max-width: 100%;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead {
        background: var(--primary-color);
        color: white;
    }

    .table thead th {
        border: none;
        padding: 12px 15px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    .table tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }

    .student-photo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-color);
    }

    .badge-grade {
        padding: 6px 12px;
        border-radius: 0;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .badge-grade.A {
        background-color: #28a745;
        color: white;
    }

    .badge-grade.B {
        background-color: #17a2b8;
        color: white;
    }

    .badge-grade.C {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-grade.D {
        background-color: #fd7e14;
        color: white;
    }

    .badge-grade.E, .badge-grade.F {
        background-color: #dc3545;
        color: white;
    }

    /* Exam Group Card */
    .exam-group-card {
        background: white;
        border-radius: 0;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid var(--primary-color);
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .exam-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }

    .exam-group-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    .exam-group-info {
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* No Results Message */
    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .no-results i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .no-results h4 {
        color: #495057;
        margin-bottom: 10px;
    }

    /* Section Header */
    .section-header {
        font-size: 1.4rem;
        font-weight: 600;
        color: #212529;
        margin: 25px 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    /* Statistics Cards */
    .stat-card {
        background: white;
        border-radius: 0;
        padding: 18px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        border-top: 3px solid #e9ecef;
        text-align: center;
        border: 1px solid #e9ecef;
    }

    .stat-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-top-color: var(--primary-color);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 8px 0;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
        margin: 0;
    }

    /* Mini Result Summary Widget */
    .mini-result-widget {
        background: white;
        border-radius: 0;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
        border-left: 4px solid var(--primary-color);
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .mini-result-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .mini-result-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    .mini-result-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mini-stat-item {
        text-align: center;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 0;
        border: 1px solid #e9ecef;
        min-width: 70px;
    }

    .mini-stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 0;
    }

    .mini-stat-label {
        font-size: 0.7rem;
        color: #6c757d;
        margin: 0;
    }

    .mini-grade-badge {
        font-size: 0.9rem;
        padding: 6px 12px;
        border-radius: 0;
        font-weight: 600;
    }


    /* Responsive */
    @media (max-width: 768px) {
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        .container-fluid {
            padding: 10px !important;
            max-width: 100%;
            overflow-x: hidden;
        }

        .row {
            margin-left: 0;
            margin-right: 0;
            max-width: 100%;
        }

        .row > * {
            padding-left: 8px;
            padding-right: 8px;
        }

        [class*="col-"] {
            max-width: 100%;
        }

        .filter-card {
            padding: 12px;
            width: 100%;
        }

        .filter-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .results-card {
            padding: 12px;
            width: 100%;
        }

        .results-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .exam-group-card {
            padding: 12px;
            width: 100%;
        }

        .table {
            font-size: 0.75rem;
            width: 100%;
            table-layout: fixed;
        }

        .table thead th,
        .table tbody td {
            padding: 6px 5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 0;
        }

        .table thead th {
            font-size: 0.7rem;
        }

        .table tbody td {
            font-size: 0.75rem;
        }

        .table-responsive {
            overflow-x: hidden;
        }

        .stat-card {
            padding: 12px;
            width: 100%;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .mini-result-widget {
            padding: 12px;
            width: 100%;
        }

        .mini-result-title {
            font-size: 0.85rem;
        }

        .mini-result-stats {
            gap: 8px;
        }

        .mini-stat-item {
            padding: 6px 8px;
            min-width: 60px;
            flex: 1 1 auto;
        }

        .mini-stat-value {
            font-size: 0.9rem;
        }

        .mini-stat-label {
            font-size: 0.65rem;
        }


        .btn-filter, .btn-reset {
            width: 100%;
            margin-bottom: 8px;
        }
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding: 8px !important;
        }

        .row > * {
            padding-left: 5px;
            padding-right: 5px;
        }

        .filter-card {
            padding: 10px;
        }

        .results-card {
            padding: 10px;
        }

        .exam-group-card {
            padding: 10px;
        }

        .stat-card {
            padding: 10px;
        }

        .stat-number {
            font-size: 1.3rem;
        }

        .stat-label {
            font-size: 0.7rem;
        }

        .table {
            font-size: 0.7rem;
            table-layout: fixed;
        }

        .table thead th,
        .table tbody td {
            padding: 5px 4px;
            font-size: 0.7rem;
        }

        .table thead th {
            font-size: 0.65rem;
        }

        .badge {
            font-size: 0.65rem !important;
            padding: 4px 6px !important;
        }

        .mini-result-widget {
            padding: 10px;
        }

        .mini-result-stats {
            gap: 6px;
        }

        .mini-stat-item {
            padding: 5px 6px;
            min-width: 50px;
        }

        .mini-stat-value {
            font-size: 0.85rem;
        }

        .mini-stat-label {
            font-size: 0.6rem;
        }
    }

    /* Prevent horizontal scroll */
    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }

    .row {
        max-width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    [class*="col-"] {
        max-width: 100%;
        padding-left: 8px;
        padding-right: 8px;
    }

    .exam-group-title {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .exam-group-info {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
</style>

<div class="container-fluid" style="padding: 20px; max-width: 100%; overflow-x: hidden;">
    
    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="section-header">
                <i class="bi bi-trophy"></i> Results
            </h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    @if($results->count() > 0)
    <div class="row mb-4">
        <div class="col-md-6 col-sm-6 col-6 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $students->count() }}</div>
                <div class="stat-label">Children</div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-6 mb-3">
            <div class="stat-card">
                <div class="stat-number">{{ $examinations->count() }}</div>
                <div class="stat-label">Examinations</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="filter-header">
                    <h3 class="filter-title">
                        <i class="bi bi-funnel"></i> Filter Results
                    </h3>
                </div>
                <form method="GET" action="{{ route('parentResults') }}" id="filterForm">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-12 mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar"></i> Select Year <span class="text-danger">*</span>
                            </label>
                            <select name="year" class="form-select" id="yearFilter" required>
                                <option value="">Select Year</option>
                                @if(isset($years) && count($years) > 0)
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ $yearFilter == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No years available</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12 mb-3">
                            <label class="form-label">
                                <i class="bi bi-person"></i> Select Student <span class="text-danger">*</span>
                            </label>
                            <select name="student" class="form-select" id="studentFilter" required>
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->studentID }}" {{ $studentFilter == $student->studentID ? 'selected' : '' }}>
                                        {{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}
                                        @if($student->subclass && $student->subclass->subclass_name)
                                            ({{ $student->subclass->subclass_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12 mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar-month"></i> Select Term <span class="text-danger">*</span>
                            </label>
                            <select name="term" class="form-select" id="termFilter" required>
                                <option value="">Select Term</option>
                                @if(isset($terms) && count($terms) > 0)
                                    @foreach($terms as $term)
                                        <option value="{{ $term }}" {{ $termFilter == $term ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $term)) }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No terms available</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12 mb-3">
                            <label class="form-label">
                                <i class="bi bi-file-text"></i> Select Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" class="form-select" id="typeFilter" required>
                                <option value="exam" {{ ($typeFilter ?? 'exam') == 'exam' ? 'selected' : '' }}>Exam</option>
                                <option value="report" {{ ($typeFilter ?? '') == 'report' ? 'selected' : '' }}>Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2" id="examSelectionRow" style="display: {{ ($typeFilter ?? 'exam') == 'exam' ? 'block' : 'none' }};">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                <i class="bi bi-file-text"></i> Select Examination <span class="text-danger">*</span>
                            </label>
                            <select name="exam" class="form-select" id="examFilter">
                                <option value="">Select Examination</option>
                                @if(isset($examinations) && count($examinations) > 0)
                                    @foreach($examinations as $exam)
                                        <option value="{{ $exam->examID }}" {{ $examFilter == $exam->examID ? 'selected' : '' }}>
                                            {{ $exam->exam_name }} ({{ $exam->year }} - {{ ucfirst(str_replace('_', ' ', $exam->term ?? '')) }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No examinations available. Please select Year, Student, and Term first.</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-filter" id="filterBtn">
                                <i class="bi bi-search me-1"></i> Filter Results
                            </button>
                            <button type="button" class="btn btn-reset ms-2" id="resetBtn">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Results Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="results-card">
                <div class="results-header">
                    <h3 class="results-title">
                        <i class="bi bi-list-check"></i> Results
                    </h3>
                    <span class="badge bg-primary" id="resultsCount" style="display: {{ ($results->count() > 0) ? 'inline-block' : 'none' }};">{{ $results->count() }} Result(s) Found</span>
                </div>

                <div id="resultsContainer">
                    @if(isset($resultsWithDivisions) && count($resultsWithDivisions) > 0)
                    @foreach($resultsWithDivisions as $examKey => $group)
                        @php
                            $isReportType = isset($group['type']) && $group['type'] === 'report';
                            
                            if ($isReportType) {
                                // Report type data
                                $overallAverage = $group['overall_average'] ?? 0;
                                $overallGrade = $group['overall_grade'] ?? null;
                                $overallDivision = $group['overall_division'] ?? null;
                                $examsData = $group['exams'] ?? [];
                                $subjects = $group['subjects'] ?? [];
                                $term = $group['term'] ?? '';
                                $year = $group['year'] ?? '';
                                
                                $className = strtolower($group['student']->subclass->class->class_name ?? '');
                                $isSecondaryWithDivision = ($school->school_type ?? 'Secondary') === 'Secondary' && in_array($className, ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six']);
                                $displayLabel = ($school->school_type ?? 'Secondary') === 'Primary' ? 'Division' : ($isSecondaryWithDivision ? 'Division' : 'Grade');
                                
                                $divisionClass = '';
                                if($overallDivision) {
                                    if(str_starts_with($overallDivision, 'I.')) {
                                        $divisionClass = 'bg-success text-white';
                                    } elseif(str_starts_with($overallDivision, 'II.')) {
                                        $divisionClass = 'bg-info text-white';
                                    } elseif(str_starts_with($overallDivision, 'III.')) {
                                        $divisionClass = 'bg-warning text-dark';
                                    } elseif(str_starts_with($overallDivision, 'IV.')) {
                                        $divisionClass = 'bg-danger text-white';
                                    } else {
                                        $divisionClass = 'bg-secondary text-white';
                                    }
                                } elseif($overallGrade) {
                                    if($overallGrade === 'A') {
                                        $divisionClass = 'bg-success text-white';
                                    } elseif($overallGrade === 'B') {
                                        $divisionClass = 'bg-info text-white';
                                    } elseif($overallGrade === 'C') {
                                        $divisionClass = 'bg-warning text-dark';
                                    } elseif($overallGrade === 'D') {
                                        $divisionClass = 'bg-danger text-white';
                                    } else {
                                        $divisionClass = 'bg-secondary text-white';
                                    }
                                }
                            } else {
                                // Exam type data (existing logic)
                                $totalDivision = $group['total_division'] ?? null;
                                $totalGrade = $group['total_grade'] ?? null;
                                $totalPoints = $group['total_points'] ?? 0;
                                $averageMarks = $group['average_marks'] ?? 0;
                                $totalMarks = collect($group['results'])->sum('marks');
                                $subjectCount = count($group['results']);
                                
                                $className = strtolower($group['student']->subclass->class->class_name ?? '');
                                $isSecondaryWithDivision = ($school->school_type ?? 'Secondary') === 'Secondary' && in_array($className, ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six']);
                                $displayLabel = ($school->school_type ?? 'Secondary') === 'Primary' ? 'Division' : ($isSecondaryWithDivision ? 'Division' : 'Grade');
                                
                                $divisionClass = '';
                                if($totalDivision) {
                                    if(str_starts_with($totalDivision, 'I.')) {
                                        $divisionClass = 'bg-success text-white';
                                    } elseif(str_starts_with($totalDivision, 'II.')) {
                                        $divisionClass = 'bg-info text-white';
                                    } elseif(str_starts_with($totalDivision, 'III.')) {
                                        $divisionClass = 'bg-warning text-dark';
                                    } elseif(str_starts_with($totalDivision, 'IV.')) {
                                        $divisionClass = 'bg-danger text-white';
                                    } else {
                                        $divisionClass = 'bg-secondary text-white';
                                    }
                                } elseif($totalGrade) {
                                    if($totalGrade === 'A') {
                                        $divisionClass = 'bg-success text-white';
                                    } elseif($totalGrade === 'B') {
                                        $divisionClass = 'bg-info text-white';
                                    } elseif($totalGrade === 'C') {
                                        $divisionClass = 'bg-warning text-dark';
                                    } elseif($totalGrade === 'D') {
                                        $divisionClass = 'bg-danger text-white';
                                    } else {
                                        $divisionClass = 'bg-secondary text-white';
                                    }
                                }
                            }
                        @endphp
                        
                        @if($isReportType)
                            <!-- Report Type Display (similar to result_management view more about student report) -->
                            <div class="exam-group-card">
                                <div class="exam-group-header">
                                    <div>
                                        <h4 class="exam-group-title">
                                            Term Report - {{ ucfirst(str_replace('_', ' ', $term)) }} {{ $year }} - {{ $group['student']->first_name }} {{ $group['student']->middle_name ?? '' }} {{ $group['student']->last_name }}
                                        </h4>
                                        <div class="exam-group-info">
                                            <i class="bi bi-calendar"></i> Year: {{ $year }} | 
                                            <i class="bi bi-calendar-month"></i> Term: {{ ucfirst(str_replace('_', ' ', $term)) }} | 
                                            <i class="bi bi-book"></i> Class: 
                                            @if($group['student']->subclass && $group['student']->subclass->class)
                                                {{ $group['student']->subclass->class->class_name }} {{ $group['student']->subclass->subclass_name }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Student Information Card -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><strong>Student Information</strong></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Student Name:</strong> {{ $group['student']->first_name }} {{ $group['student']->middle_name ?? '' }} {{ $group['student']->last_name }}</p>
                                                <p><strong>Admission Number:</strong> {{ $group['student']->admission_number ?? 'N/A' }}</p>
                                                <p><strong>Term:</strong> {{ ucfirst(str_replace('_', ' ', $term)) }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Year:</strong> {{ $year }}</p>
                                                <p><strong>Overall Average:</strong> <span class="badge badge-info">{{ number_format($overallAverage, 1) }}</span></p>
                                                <p><strong>{{ $displayLabel }}:</strong> 
                                                    @if($overallDivision || $overallGrade)
                                                        <span class="badge {{ $divisionClass }}">{{ $overallDivision ?? $overallGrade }}</span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Examinations Card -->
                                @if(count($examsData) > 0)
                                <div class="card mb-3">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h6 class="mb-0"><i class="bi bi-book"></i> Examinations</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Examination</th>
                                                        <th>Average</th>
                                                        <th>{{ $displayLabel }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($examsData as $examData)
                                                        <tr>
                                                            <td><strong>{{ $examData['exam']->exam_name ?? 'N/A' }}</strong></td>
                                                            <td>{{ number_format($examData['average'] ?? 0, 1) }}</td>
                                                            <td>
                                                                @if($examData['division'] || $examData['grade'])
                                                                    <span class="badge badge-info">{{ $examData['division'] ?? $examData['grade'] }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Subject Results Card -->
                                @if(count($subjects) > 0 && count($examsData) > 0)
                                <div class="card mb-3">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h6 class="mb-0"><i class="bi bi-list-check"></i> Subject Results</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-striped">
                                                <thead class="bg-primary-custom text-white">
                                                    <tr>
                                                        <th>Subject</th>
                                                        @foreach($examsData as $examData)
                                                            <th>{{ $examData['exam']->exam_name ?? 'N/A' }}</th>
                                                        @endforeach
                                                        <th>Average</th>
                                                        <th>{{ $displayLabel }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($subjects as $subject)
                                                        <tr>
                                                            <td><strong>{{ $subject['subject_name'] ?? 'N/A' }}</strong></td>
                                                            @foreach($examsData as $examData)
                                                                @php
                                                                    $examResult = collect($subject['exams'] ?? [])->firstWhere('exam_name', $examData['exam']->exam_name ?? '');
                                                                @endphp
                                                                <td>
                                                                    @if($examResult && $examResult['marks'] !== null && $examResult['marks'] !== '')
                                                                        {{ number_format($examResult['marks'], 0) }}-{{ $examResult['grade'] ?? 'N/A' }}
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                            <td><strong>{{ number_format($subject['average'] ?? 0, 1) }}</strong></td>
                                                            <td>
                                                                @if($subject['grade'])
                                                                    <span class="badge badge-info">{{ $subject['grade'] }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @else
                            <!-- Exam Type Display (existing logic) -->
                        <div class="exam-group-card">
                            <div class="exam-group-header">
                                <div>
                                    <h4 class="exam-group-title">
                                        {{ $group['exam']->exam_name ?? 'N/A' }} - {{ $group['student']->first_name }} {{ $group['student']->middle_name ?? '' }} {{ $group['student']->last_name }}
                                    </h4>
                                    <div class="exam-group-info">
                                        <i class="bi bi-calendar"></i> Year: {{ $group['exam']->year ?? 'N/A' }} | 
                                        <i class="bi bi-book"></i> Class: 
                                        @if($group['student']->subclass && $group['student']->subclass->class)
                                            {{ $group['student']->subclass->class->class_name }} {{ $group['student']->subclass->subclass_name }}
                                        @else
                                            N/A
                                        @endif
                                        @if(isset($group['position']) && $group['position'] && isset($group['total_students']) && $group['total_students'] > 0)
                                            | <i class="bi bi-trophy"></i> Position: <strong>{{ $group['position'] }}</strong> out of <strong>{{ $group['total_students'] }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Mini Result Summary Widget -->
                            <div class="mini-result-widget">
                                <div class="mini-result-header">
                                    <h5 class="mini-result-title">Quick Summary</h5>
                                </div>
                                <div class="mini-result-stats">
                                    <div class="mini-stat-item">
                                        <div class="mini-stat-value">{{ $subjectCount }}</div>
                                        <div class="mini-stat-label">Subjects</div>
                                    </div>
                                    <div class="mini-stat-item">
                                        <div class="mini-stat-value">{{ number_format($totalMarks, 0) }}</div>
                                        <div class="mini-stat-label">Total Marks</div>
                                    </div>
                                    <div class="mini-stat-item">
                                        <div class="mini-stat-value">{{ number_format($averageMarks, 1) }}</div>
                                        <div class="mini-stat-label">Average</div>
                                    </div>
                                    @if(isset($group['position']) && $group['position'] && isset($group['total_students']) && $group['total_students'] > 0)
                                    <div class="mini-stat-item">
                                        <div class="mini-stat-value" style="color: var(--primary-color);">
                                            {{ $group['position'] }}/{{ $group['total_students'] }}
                                        </div>
                                        <div class="mini-stat-label">Position</div>
                                    </div>
                                    @endif
                                    @if($totalDivision || $totalGrade)
                                    <div class="mini-stat-item">
                                        <span class="badge {{ $divisionClass }} mini-grade-badge">
                                            {{ $totalDivision ?? $totalGrade }}
                                            @if($isSecondaryWithDivision && $totalPoints > 0)
                                                ({{ $totalPoints }}pts)
                                            @endif
                                        </span>
                                        <div class="mini-stat-label">{{ $displayLabel }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 30%;">Subject</th>
                                            <th style="width: 15%;">Marks</th>
                                            <th style="width: 20%;">{{ ($school->school_type ?? 'Secondary') === 'Primary' ? 'Division' : (in_array(strtolower($group['student']->subclass->class->class_name ?? ''), ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six']) ? 'Grade' : 'Grade') }}</th>
                                            <th style="width: 30%;">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group['results'] as $index => $result)
                                            @php
                                                $subjectData = $group['subjects_data'][$index] ?? null;
                                                $displayGradeOrDivision = '';
                                                $gradeClass = '';
                                                
                                                if($subjectData) {
                                                    if(($school->school_type ?? 'Secondary') === 'Primary') {
                                                        $displayGradeOrDivision = $subjectData['division'] ?? '-';
                                                        if($displayGradeOrDivision === 'Division One') {
                                                            $gradeClass = 'bg-success text-white';
                                                        } elseif($displayGradeOrDivision === 'Division Two') {
                                                            $gradeClass = 'bg-info text-white';
                                                        } elseif($displayGradeOrDivision === 'Division Three') {
                                                            $gradeClass = 'bg-warning text-dark';
                                                        } elseif($displayGradeOrDivision === 'Division Four') {
                                                            $gradeClass = 'bg-danger text-white';
                                                        } else {
                                                            $gradeClass = 'bg-secondary text-white';
                                                        }
                                                    } else {
                                                        $className = strtolower($group['student']->subclass->class->class_name ?? '');
                                                        if(in_array($className, ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'])) {
                                                            $displayGradeOrDivision = $subjectData['grade'] ?? '-';
                                                            if($displayGradeOrDivision === 'A') {
                                                                $gradeClass = 'bg-success text-white';
                                                            } elseif($displayGradeOrDivision === 'B') {
                                                                $gradeClass = 'bg-info text-white';
                                                            } elseif($displayGradeOrDivision === 'C') {
                                                                $gradeClass = 'bg-warning text-dark';
                                                            } elseif($displayGradeOrDivision === 'D') {
                                                                $gradeClass = 'bg-danger text-white';
                                                            } elseif(in_array($displayGradeOrDivision, ['E', 'F', 'S/F'])) {
                                                                $gradeClass = 'bg-secondary text-white';
                                                            }
                                                        } else {
                                                            $displayGradeOrDivision = $subjectData['grade'] ?? '-';
                                                            if($displayGradeOrDivision === 'A') {
                                                                $gradeClass = 'bg-success text-white';
                                                            } elseif($displayGradeOrDivision === 'B') {
                                                                $gradeClass = 'bg-info text-white';
                                                            } elseif($displayGradeOrDivision === 'C') {
                                                                $gradeClass = 'bg-warning text-dark';
                                                            } elseif($displayGradeOrDivision === 'D') {
                                                                $gradeClass = 'bg-danger text-white';
                                                            } else {
                                                                $gradeClass = 'bg-secondary text-white';
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $displayGradeOrDivision = $result->grade ?? '-';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>
                                                        @if($result->classSubject && $result->classSubject->subject)
                                                            {{ $result->classSubject->subject->subject_name }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        {{ $result->marks ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($displayGradeOrDivision && $displayGradeOrDivision !== '-')
                                                        <span class="badge {{ $gradeClass }}">
                                                            {{ $displayGradeOrDivision }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $result->remark ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                        <tr style="background-color: #f8f9fa; font-weight: 600;">
                                            <td colspan="2" class="text-end">
                                                <strong>Total {{ $displayLabel }}:</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    {{ number_format($totalMarks, 0) }} / Avg: {{ number_format($averageMarks, 1) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($totalDivision || $totalGrade)
                                                    <span class="badge {{ $divisionClass }}">
                                                        {{ $totalDivision ?? $totalGrade }}
                                                        @if($isSecondaryWithDivision && $totalPoints > 0)
                                                            ({{ $totalPoints }}pts)
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else
                    <div class="no-results">
                        <i class="bi bi-inbox"></i>
                        <h4>No Results Found</h4>
                        <p>There are no approved results available for your children at the moment.</p>
                        @if($studentFilter || $yearFilter || $examFilter)
                            <p class="text-muted">Try adjusting your filters to see more results.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 5 JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<script>
    // Setup CSRF token for AJAX
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }
</script>

<script>
    // Handle type filter change and exam selection
    document.addEventListener('DOMContentLoaded', function() {
        const typeFilter = document.getElementById('typeFilter');
        const examSelectionRow = document.getElementById('examSelectionRow');
        const examFilter = document.getElementById('examFilter');
        const yearFilter = document.getElementById('yearFilter');
        const termFilter = document.getElementById('termFilter');

        // Show/hide exam selection based on type
        if (typeFilter) {
            typeFilter.addEventListener('change', function() {
                if (this.value === 'exam') {
                    examSelectionRow.style.display = 'block';
                    examFilter.setAttribute('required', 'required');
                } else {
                    examSelectionRow.style.display = 'none';
                    examFilter.removeAttribute('required');
                    examFilter.value = '';
                }
            });

            // Trigger on page load
            if (typeFilter.value === 'exam') {
                examSelectionRow.style.display = 'block';
                examFilter.setAttribute('required', 'required');
            } else {
                examSelectionRow.style.display = 'none';
                examFilter.removeAttribute('required');
            }
        }

        // Filter button click handler
        const filterBtn = document.getElementById('filterBtn');
        const resetBtn = document.getElementById('resetBtn');
        const studentFilter = document.getElementById('studentFilter');

        if (filterBtn) {
            filterBtn.addEventListener('click', function() {
                const year = yearFilter ? yearFilter.value : '';
                const student = studentFilter ? studentFilter.value : '';
                const term = termFilter ? termFilter.value : '';
                const type = typeFilter ? typeFilter.value : 'exam';
                const exam = examFilter ? examFilter.value : '';

                // Validate required fields
                if (!year || !student || !term || !type) {
                    alert('Please fill in all required fields (Year, Student, Term, and Type).');
                    return;
                }

                if (type === 'exam' && !exam) {
                    alert('Please select an examination.');
                    return;
                }

                // Show loading
                $('#resultsContainer').html(`
                    <div class="text-center" style="padding: 40px;">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading results...</p>
                    </div>
                `);
                $('#resultsCount').hide();

                // Make AJAX request
                $.ajax({
                    url: '{{ route("parentResults") }}',
                    method: 'GET',
                    data: {
                        year: year,
                        student: student,
                        term: term,
                        type: type,
                        exam: exam
                    },
                    success: function(response) {
                        // Extract results HTML from response
                        const $response = $(response);
                        const resultsHtml = $response.find('#resultsContainer').html();
                        
                        if (resultsHtml) {
                            $('#resultsContainer').html(resultsHtml);
                            
                            // Update results count
                            const resultsCount = $response.find('#resultsContainer .exam-group-card').length;
                            if (resultsCount > 0) {
                                $('#resultsCount').text(resultsCount + ' Result(s) Found').show();
                            } else {
                                $('#resultsCount').hide();
                            }
                        } else {
                            $('#resultsContainer').html(`
                                <div class="no-results">
                                    <i class="bi bi-inbox"></i>
                                    <h4>No Results Found</h4>
                                    <p>There are no approved results available for the selected filters.</p>
                                    <p class="text-muted">Try adjusting your filters to see more results.</p>
                                </div>
                            `);
                            $('#resultsCount').hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#resultsContainer').html(`
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> 
                                An error occurred while loading results. Please try again.
                            </div>
                        `);
                        $('#resultsCount').hide();
                        console.error('Error loading results:', error);
                    }
                });
            });
        }

        // Reset button click handler
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                window.location.href = '{{ route("parentResults") }}';
            });
        }
    });
</script>

@include('includes.footer')

