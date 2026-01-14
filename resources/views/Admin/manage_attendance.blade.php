@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
    }

    body {
        background-color: #f8f9fa;
    }

    .search-card {
        background: white;
        border-radius: 4px;
        padding: 25px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }

    .search-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .search-title i {
        margin-right: 10px;
        color: var(--primary-color);
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 6px;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .form-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 4px;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15);
    }

    .btn-search {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        padding: 10px 24px;
        border-radius: 4px;
        font-weight: 500;
    }

    .btn-search:hover {
        background: #b30000;
        border-color: #b30000;
        color: white;
    }

    .stats-card {
        background: white;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
        border-radius: 4px;
        background: #f8f9fa;
        margin-bottom: 10px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #212529;
        margin: 5px 0;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }

    .chart-card {
        background: white;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 15px;
    }

    .data-table-card {
        background: white;
        border-radius: 4px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }

    .table {
        font-size: 0.9rem;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    .badge-present {
        background-color: #10b981;
        color: white;
    }

    .badge-absent {
        background-color: #ef4444;
        color: white;
    }

    .badge-late {
        background-color: #f59e0b;
        color: white;
    }

    .badge-excused {
        background-color: #6366f1;
        color: white;
    }

    .btn-view {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .btn-view:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .btn-print {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
    }

    .btn-print:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }

    .section-header {
        font-size: 1.4rem;
        font-weight: 600;
        color: #212529;
        margin: 25px 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .hidden {
        display: none !important;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="container-fluid" style="padding: 20px;">
    
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="section-header">
                <i class="bi bi-calendar-check"></i> Attendance Management
            </h2>
        </div>
    </div>

    <!-- Tabs for System vs Fingerprint Attendance -->
    <ul class="nav nav-tabs mb-3 no-print" id="attendanceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="system-attendance-tab" type="button" onclick="showAttendanceTab('system')">
                <i class="bi bi-grid"></i> System Attendance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="fingerprint-attendance-tab" type="button" onclick="showAttendanceTab('fingerprint')">
                <i class="bi bi-fingerprint"></i> Fingerprint Attendance
            </button>
        </li>
        @if(!isset($isCoordinatorView) || !$isCoordinatorView)
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="exam-attendance-tab" type="button" onclick="showAttendanceTab('exam')">
                <i class="bi bi-calendar-check"></i> Exam Attendance
            </button>
        </li>
        @endif
    </ul>

    <!-- System Attendance Section (existing logic) -->
    <div id="systemAttendanceSection">
        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card" style="border: 1px solid #e9ecef; border-radius: 4px;">
                    <div class="card-header" style="background-color: #940000; color: white; padding: 15px 20px;">
                        <h5 class="mb-0">
                            <i class="bi bi-search me-2"></i>Search Attendance
                        </h5>
                    </div>
                    <div class="card-body" style="padding: 25px;">
                        <form id="searchAttendanceForm">
                            <div class="row g-3 align-items-end">
                                @if(isset($isCoordinatorView) && $isCoordinatorView && $selectedClass)
                                <!-- Coordinator View: Locked Main Class -->
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Main Class</label>
                                    <input type="text" class="form-control" value="{{ $selectedClass->class_name }}" readonly style="background-color: #e9ecef !important; color: #6c757d !important; cursor: not-allowed; opacity: 0.7;">
                                    <input type="hidden" name="classID" id="lockedClassID" value="{{ $classIDParam }}">
                                    <small class="form-text text-muted"><i class="bi bi-lock-fill"></i> Locked - Coordinator assigned class</small>
                                </div>
                                <!-- Coordinator View: Subclass Filter -->
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Subclass</label>
                                    <select class="form-select" id="subclassFilter" name="subclassID">
                                        <option value="">All Subclasses</option>
                                        @foreach($coordinatorSubclasses as $subclass)
                                            <option value="{{ $subclass->subclassID }}">{{ $subclass->display_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select subclass to filter</small>
                                </div>
                                @endif
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Search Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="search_type" name="search_type" required>
                                        <option value="">Select Type</option>
                                        <option value="date">By Date</option>
                                        <option value="month">By Month</option>
                                        <option value="year">By Year</option>
                                    </select>
                                    <small class="form-text text-muted">Choose how to search attendance</small>
                                </div>
                                <div class="col-md-3" id="date_field" style="display: none;">
                                    <label class="form-label mb-1">Date</label>
                                    <input type="date" class="form-control" id="date" name="date">
                                    <small class="form-text text-muted">Select specific date</small>
                                </div>
                                <div class="col-md-2" id="year_field" style="display: none;">
                                    <label class="form-label mb-1">Year</label>
                                    <select class="form-select" id="year" name="year">
                                        @for($y = date('Y'); $y >= 2020; $y--)
                                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                    <small class="form-text text-muted">Select year</small>
                                </div>
                                <div class="col-md-2" id="month_field" style="display: none;">
                                    <label class="form-label mb-1">Month</label>
                                    <select class="form-select" id="month" name="month">
                                        <option value="01">January</option>
                                        <option value="02">February</option>
                                        <option value="03">March</option>
                                        <option value="04">April</option>
                                        <option value="05">May</option>
                                        <option value="06">June</option>
                                        <option value="07">July</option>
                                        <option value="08">August</option>
                                        <option value="09">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <small class="form-text text-muted">Select month</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1 d-block">&nbsp;</label>
                                    <button type="submit" class="btn w-100" style="background-color: #940000; color: white; border-color: #940000;">
                                        <i class="bi bi-search me-1"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" style="display: none;">
            
            <!-- Attendance by Class with Students -->
            <div class="row mb-4" id="attendanceByClassSection">
                <div class="col-12">
                    <div class="data-table-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">
                                <i class="bi bi-people"></i> Attendance by Class
                            </h4>
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" id="searchClasses">
                                    <option value="">All Classes</option>
                                </select>
                            </div>
                        </div>
                        <div id="attendanceByClassContent">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fingerprint Attendance Section (external API) -->
    <div id="fingerprintAttendanceSection" style="display: none;">
        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card" style="border: 1px solid #e9ecef; border-radius: 4px;">
                    <div class="card-header" style="background-color: #940000; color: white; padding: 15px 20px;">
                        <h5 class="mb-0">
                            <i class="bi bi-search me-2"></i>Search Fingerprint Attendance
                        </h5>
                    </div>
                    <div class="card-body" style="padding: 25px;">
                        <form id="searchFingerprintAttendanceForm">
                            <div class="row g-3 align-items-end">
                                @if(isset($isCoordinatorView) && $isCoordinatorView && $selectedClass)
                                <!-- Coordinator View: Locked Main Class -->
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Main Class</label>
                                    <input type="text" class="form-control" value="{{ $selectedClass->class_name }}" readonly style="background-color: #e9ecef !important; color: #6c757d !important; cursor: not-allowed; opacity: 0.7;">
                                    <input type="hidden" name="classID" id="fingerprintLockedClassID" value="{{ $classIDParam }}">
                                    <small class="form-text text-muted"><i class="bi bi-lock-fill"></i> Locked - Coordinator assigned class</small>
                                </div>
                                <!-- Coordinator View: Subclass Filter -->
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Subclass</label>
                                    <select class="form-select" id="fingerprintSubclassFilter" name="subclassID">
                                        <option value="">All Subclasses</option>
                                        @foreach($coordinatorSubclasses as $subclass)
                                            <option value="{{ $subclass->subclassID }}">{{ $subclass->display_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select subclass to filter</small>
                                </div>
                                @endif
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Search Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="fingerprint_search_type" name="search_type" required>
                                        <option value="">Select Type</option>
                                        <option value="date">By Date</option>
                                        <option value="month">By Month</option>
                                        <option value="year">By Year</option>
                                    </select>
                                    <small class="form-text text-muted">Choose how to search attendance</small>
                                </div>
                                <div class="col-md-3" id="fingerprint_date_field" style="display: none;">
                                    <label class="form-label mb-1">Date</label>
                                    <input type="date" class="form-control" id="fingerprint_date" name="date">
                                    <small class="form-text text-muted">Select specific date</small>
                                </div>
                                <div class="col-md-2" id="fingerprint_year_field" style="display: none;">
                                    <label class="form-label mb-1">Year</label>
                                    <select class="form-select" id="fingerprint_year" name="year">
                                        @for($y = date('Y'); $y >= 2020; $y--)
                                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                    <small class="form-text text-muted">Select year</small>
                                </div>
                                <div class="col-md-2" id="fingerprint_month_field" style="display: none;">
                                    <label class="form-label mb-1">Month</label>
                                    <select class="form-select" id="fingerprint_month" name="month">
                                        <option value="01">January</option>
                                        <option value="02">February</option>
                                        <option value="03">March</option>
                                        <option value="04">April</option>
                                        <option value="05">May</option>
                                        <option value="06">June</option>
                                        <option value="07">July</option>
                                        <option value="08">August</option>
                                        <option value="09">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <small class="form-text text-muted">Select month</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1 d-block">&nbsp;</label>
                                    <button type="submit" class="btn w-100" style="background-color: #940000; color: white; border-color: #940000;">
                                        <i class="bi bi-search me-1"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="fingerprintResultsSection" style="display: none;">
            <!-- Attendance by Class with Students -->
            <div class="row mb-4" id="fingerprintAttendanceByClassSection">
                <div class="col-12">
                    <div class="data-table-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">
                                <i class="bi bi-people"></i> Fingerprint Attendance by Class
                            </h4>
                            <div class="d-flex gap-3 align-items-center flex-wrap">
                                <div style="min-width: 250px; flex: 0 0 auto;">
                                    <select class="form-select form-select-sm" id="searchFingerprintClasses" style="width: 100%;">
                                        <option value="">All Classes</option>
                                    </select>
                                </div>
                                <div class="btn-group" role="group" style="flex: 0 0 auto;">
                                    <button type="button" class="btn btn-success btn-sm" id="exportFingerprintExcelBtn" title="Export to Excel" style="display: inline-block;">
                                        <i class="bi bi-file-earmark-excel"></i> Excel
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" id="exportFingerprintPdfBtn" title="Export to PDF" style="display: inline-block;">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="fingerprintAttendanceByClassContent">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Attendance Section -->
    <div id="examAttendanceSection" style="display: none;">
        <!-- Filters Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card" style="border: 1px solid #e9ecef; border-radius: 4px;">
                    <div class="card-header" style="background-color: #940000; color: white; padding: 15px 20px;">
                        <h5 class="mb-0">
                            <i class="bi bi-funnel me-2"></i>Filter Exam Attendance
                        </h5>
                    </div>
                    <div class="card-body" style="padding: 25px;">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label mb-1">Year <span class="text-danger">*</span></label>
                                <select class="form-select" id="examFilterYear">
                                    <option value="">Select Year</option>
                                    @php
                                        $currentYear = date('Y');
                                        for($y = $currentYear; $y >= 2020; $y--) {
                                            echo "<option value=\"{$y}\">{$y}</option>";
                                        }
                                    @endphp
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Term</label>
                                <select class="form-select" id="examFilterTerm" disabled>
                                    <option value="">Select Term</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Exam Name</label>
                                <select class="form-select" id="examFilterExam" disabled>
                                    <option value="">Select Exam</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Main Class</label>
                                <select class="form-select" id="examFilterMainClass">
                                    <option value="">All Classes</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Subclass</label>
                                <select class="form-select" id="examFilterSubclass" disabled>
                                    <option value="">All Subclasses</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Subject</label>
                                <select class="form-select" id="examFilterSubject">
                                    <option value="">All Subjects</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Statistics -->
        <div id="examAttendanceStats" style="display: none;">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white text-center clickable-stat-exam" data-filter="present" style="cursor: pointer; transition: all 0.2s;">
                        <div class="card-body">
                            <h6 class="mb-0">Total Present</h6>
                            <h4 class="mb-0" id="examTotalPresent">0</h4>
                            <small class="d-block mt-1">Click to view</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white text-center clickable-stat-exam" data-filter="absent" style="cursor: pointer; transition: all 0.2s;">
                        <div class="card-body">
                            <h6 class="mb-0">Total Absent</h6>
                            <h4 class="mb-0" id="examTotalAbsent">0</h4>
                            <small class="d-block mt-1">Click to view</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body">
                            <h6 class="mb-0">Total Students</h6>
                            <h4 class="mb-0" id="examTotalStudents">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div id="examStudentsTableContainer" style="display: none;">
            <div class="card" style="border: 1px solid #e9ecef; border-radius: 4px;">
                <div class="card-header" style="background-color: #940000; color: white; padding: 15px 20px;">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>Students Attendance
                    </h5>
                </div>
                <div class="card-body" style="padding: 25px;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="examStudentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Subjects Taken</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="examStudentsTableBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Select filters to view attendance</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #e9ecef; padding: 20px 25px;">
                <h5 class="modal-title" id="studentDetailsModalLabel" style="font-weight: 600; font-size: 1.25rem;">
                    <i class="bi bi-person me-2"></i>Student Attendance Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentDetailsContent" style="padding: 25px;">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SheetJS for Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
    let studentChart = null;

    // Toggle between System Attendance, Fingerprint Attendance, and Exam Attendance tabs
    function showAttendanceTab(tab) {
        console.log('Switching to tab:', tab);
        
        const systemTab = document.getElementById('system-attendance-tab');
        const fingerprintTab = document.getElementById('fingerprint-attendance-tab');
        const examTab = document.getElementById('exam-attendance-tab');
        const systemSection = document.getElementById('systemAttendanceSection');
        const fingerprintSection = document.getElementById('fingerprintAttendanceSection');
        const examSection = document.getElementById('examAttendanceSection');

        // Check if required elements exist
        if (!systemTab || !fingerprintTab || !systemSection || !fingerprintSection) {
            console.error('Required elements not found:', {
                systemTab: !!systemTab,
                fingerprintTab: !!fingerprintTab,
                systemSection: !!systemSection,
                fingerprintSection: !!fingerprintSection
            });
            return;
        }

        // Remove active class from all tabs
        if (systemTab) systemTab.classList.remove('active');
        if (fingerprintTab) fingerprintTab.classList.remove('active');
        if (examTab) examTab.classList.remove('active');
        
        // Hide all sections
        if (systemSection) systemSection.style.display = 'none';
        if (fingerprintSection) fingerprintSection.style.display = 'none';
        if (examSection) examSection.style.display = 'none';

        if (tab === 'fingerprint') {
            console.log('Showing fingerprint section');
            if (fingerprintTab) fingerprintTab.classList.add('active');
            if (fingerprintSection) {
            fingerprintSection.style.display = 'block';
                console.log('Fingerprint section displayed');
            }
        } else if (tab === 'exam') {
            console.log('Showing exam section');
            if (examTab) examTab.classList.add('active');
            if (examSection) examSection.style.display = 'block';
        } else {
            console.log('Showing system section');
            if (systemTab) systemTab.classList.add('active');
            if (systemSection) systemSection.style.display = 'block';
        }
    }

    // Handle search type change for System Attendance
    document.getElementById('search_type').addEventListener('change', function() {
        const searchType = this.value;
        
        // Hide all fields
        document.getElementById('date_field').style.display = 'none';
        document.getElementById('year_field').style.display = 'none';
        document.getElementById('month_field').style.display = 'none';

        // Show relevant fields
        switch(searchType) {
            case 'date':
                document.getElementById('date_field').style.display = 'block';
                break;
            case 'year':
                document.getElementById('year_field').style.display = 'block';
                break;
            case 'month':
                document.getElementById('year_field').style.display = 'block';
                document.getElementById('month_field').style.display = 'block';
                break;
        }
    });

    // Handle search type change for Fingerprint Attendance
    const fingerprintSearchType = document.getElementById('fingerprint_search_type');
    if (fingerprintSearchType) {
        fingerprintSearchType.addEventListener('change', function() {
            const searchType = this.value;
            
            // Hide all fields
            document.getElementById('fingerprint_date_field').style.display = 'none';
            document.getElementById('fingerprint_year_field').style.display = 'none';
            document.getElementById('fingerprint_month_field').style.display = 'none';

            // Show relevant fields
            switch(searchType) {
                case 'date':
                    document.getElementById('fingerprint_date_field').style.display = 'block';
                    break;
                case 'year':
                    document.getElementById('fingerprint_year_field').style.display = 'block';
                    break;
                case 'month':
                    document.getElementById('fingerprint_year_field').style.display = 'block';
                    document.getElementById('fingerprint_month_field').style.display = 'block';
                    break;
            }
        });
    }

    // Search form submission
    document.getElementById('searchAttendanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // For coordinator view, add classID and coordinator flag
        @if(isset($isCoordinatorView) && $isCoordinatorView && $classIDParam)
            const lockedClassID = document.getElementById('lockedClassID');
            if (lockedClassID) {
                formData.append('classID', lockedClassID.value);
                formData.append('coordinator', 'true');
            }
        @endif
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Searching...';

        fetch('{{ route("search_attendance") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAttendanceResults(data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'An error occurred',
                    confirmButtonColor: '#940000'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while searching',
                confirmButtonColor: '#940000'
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    function displayAttendanceResults(data) {
        // Show results section
        document.getElementById('resultsSection').style.display = 'block';

        // Display attendance by class if available
        if (data.attendance_by_class && data.attendance_by_class.length > 0) {
            displayAttendanceByClass(data.attendance_by_class);
        } else {
            document.getElementById('attendanceByClassContent').innerHTML = '<div class="alert alert-info">No attendance data found.</div>';
        }

        // Scroll to results
        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
    }

    // Search form submission for Fingerprint Attendance
    const searchFingerprintAttendanceForm = document.getElementById('searchFingerprintAttendanceForm');
    if (searchFingerprintAttendanceForm) {
        searchFingerprintAttendanceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // For coordinator view, add classID and coordinator flag
            @if(isset($isCoordinatorView) && $isCoordinatorView && $classIDParam)
                const fingerprintLockedClassID = document.getElementById('fingerprintLockedClassID');
                if (fingerprintLockedClassID) {
                    formData.append('classID', fingerprintLockedClassID.value);
                    formData.append('coordinator', 'true');
                }
            @endif
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Searching...';

            fetch('{{ route("search_fingerprint_attendance") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayFingerprintAttendanceResults(data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An error occurred',
                        confirmButtonColor: '#940000'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while searching',
                    confirmButtonColor: '#940000'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    function displayFingerprintAttendanceResults(data) {
        // Show results section
        document.getElementById('fingerprintResultsSection').style.display = 'block';

        // Display attendance by class if available
        if (data.attendance_by_class && data.attendance_by_class.length > 0) {
            displayFingerprintAttendanceByClass(data.attendance_by_class);
        } else {
            document.getElementById('fingerprintAttendanceByClassContent').innerHTML = '<div class="alert alert-info">No fingerprint attendance data found.</div>';
        }

        // Scroll to results
        document.getElementById('fingerprintResultsSection').scrollIntoView({ behavior: 'smooth' });
    }

    // Store current fingerprint attendance data for export
    let currentFingerprintAttendanceData = null;
    let currentFingerprintSearchType = null;
    let currentFingerprintSearchMonth = null;
    let currentFingerprintSearchYear = null;
    let currentFingerprintSearchDate = null;

    function displayFingerprintAttendanceByClass(attendanceByClass) {
        const content = document.getElementById('fingerprintAttendanceByClassContent');
        const searchType = document.getElementById('fingerprint_search_type').value;
        const month = document.getElementById('fingerprint_month') ? document.getElementById('fingerprint_month').value : '';
        const year = document.getElementById('fingerprint_year') ? document.getElementById('fingerprint_year').value : '';
        const date = document.getElementById('fingerprint_date') ? document.getElementById('fingerprint_date').value : '';
        
        // Store data for export
        currentFingerprintAttendanceData = attendanceByClass;
        currentFingerprintSearchType = searchType;
        currentFingerprintSearchMonth = (searchType === 'month' && year && month) ? `${year}-${month}` : null;
        currentFingerprintSearchYear = (searchType === 'year' && year) ? year : null;
        currentFingerprintSearchDate = (searchType === 'date' && date) ? date : null;
        
        // Show/hide export buttons based on search type (show for all types now)
        document.getElementById('exportFingerprintExcelBtn').style.display = 'inline-block';
        document.getElementById('exportFingerprintPdfBtn').style.display = 'inline-block';
        
        // Populate class dropdown
        const classDropdown = document.getElementById('searchFingerprintClasses');
        if (classDropdown) {
            classDropdown.innerHTML = '<option value="">All Classes</option>';
            attendanceByClass.forEach((classData) => {
                const option = document.createElement('option');
                option.value = classData.subclassID;
                const displayName = classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name;
                option.textContent = displayName;
                classDropdown.appendChild(option);
            });
            
            // Add realtime filtering event listener
            classDropdown.addEventListener('change', function() {
                const selectedClassID = this.value;
                filterFingerprintClasses(selectedClassID);
            });
        }
        
        let html = '';
        
        attendanceByClass.forEach((classData) => {
            if (!classData.has_attendance) {
                // No attendance collected - get specific period message
                let periodMessage = '';
                if (searchType === 'month') {
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    const month = document.getElementById('fingerprint_month') ? document.getElementById('fingerprint_month').value : '';
                    const year = document.getElementById('fingerprint_year') ? document.getElementById('fingerprint_year').value : '';
                    if (month && year) {
                        const monthName = monthNames[parseInt(month) - 1];
                        periodMessage = `in ${monthName} ${year}`;
                    } else {
                        periodMessage = 'for the selected period';
                    }
                } else if (searchType === 'year') {
                    const year = document.getElementById('fingerprint_year') ? document.getElementById('fingerprint_year').value : '';
                    if (year) {
                        periodMessage = `in ${year}`;
                    } else {
                        periodMessage = 'for the selected period';
                    }
                } else if (searchType === 'date') {
                    const date = document.getElementById('fingerprint_date') ? document.getElementById('fingerprint_date').value : '';
                    if (date) {
                        const dateObj = new Date(date);
                        periodMessage = `on ${dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`;
                    } else {
                        periodMessage = 'for the selected date';
                    }
                } else {
                    periodMessage = 'for the selected period';
                }
                
                html += `
                    <div class="card mb-4 border-secondary" data-subclass-id="${classData.subclassID}">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-folder"></i> ${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> No fingerprint attendance collected ${periodMessage} for this class.
                            </div>
                        </div>
                    </div>
                `;
                return;
            }

            // Has attendance - show statistics
            html += `
                <div class="card mb-4" style="border-color: #940000; border-width: 2px;" data-subclass-id="${classData.subclassID}">
                    <div class="card-header text-white" style="background-color: #940000;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-folder"></i> ${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}
                            </h5>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-success export-class-excel-btn" data-subclass-id="${classData.subclassID}" data-subclass-name="${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}" title="Export to Excel">
                                        <i class="bi bi-file-earmark-excel"></i> Excel
                                    </button>
                                    <button class="btn btn-sm btn-danger export-class-pdf-btn" data-subclass-id="${classData.subclassID}" data-subclass-name="${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}" title="Export to PDF">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </button>
                                </div>
                                <button class="btn btn-sm btn-light toggle-subclass-btn" data-subclass-id="${classData.subclassID}">
                                    <i class="bi bi-chevron-down"></i> View Students
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
            `;

            // Show widgets based on search type
            if (searchType === 'month' || searchType === 'year') {
                // Show percentage widgets for month/year
                html += `
                            <div class="col-md-6">
                                <div class="card bg-success">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.present_percentage}%</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Present (${classData.present} days)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-danger">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.absent_percentage}%</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Absent (${classData.absent} days)</p>
                                    </div>
                                </div>
                            </div>
                `;
            } else {
                // Show count widgets for date
                html += `
                            <div class="col-md-6">
                                <div class="card bg-success">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.present || 0}</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Present</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-danger">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.absent || 0}</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Absent</p>
                                    </div>
                                </div>
                            </div>
                `;
            }

            html += `
                        </div>
                        
                        <div class="subclass-students" id="fingerprint-subclass-students-${classData.subclassID}" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="fingerprintStudentsTable_${classData.subclassID}">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Admission No.</th>
                                            ${searchType === 'date' ? `
                                            <th>Check In Time</th>
                                            <th>Check Out Time</th>
                                            ` : `
                                            <th>Gender</th>
                                            <th>Days Present</th>
                                            <th>Days Absent</th>
                                            <th>Total Days</th>
                                            `}
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;
            
            // Calculate working days for this class
            let workingDays = 0;
            if (searchType === 'month' && year && month) {
                const startDate = new Date(parseInt(year), parseInt(month) - 1, 1);
                let endDate = new Date(parseInt(year), parseInt(month), 0);
                if (endDate > new Date()) endDate = new Date();
                let current = new Date(startDate);
                while (current <= endDate) {
                    if (current.getDay() !== 0 && current.getDay() !== 6) {
                        workingDays++;
                    }
                    current.setDate(current.getDate() + 1);
                }
            } else if (searchType === 'year' && year) {
                const startDate = new Date(parseInt(year), 0, 1);
                let endDate = new Date(parseInt(year), 11, 31);
                if (endDate > new Date()) endDate = new Date();
                let current = new Date(startDate);
                while (current <= endDate) {
                    if (current.getDay() !== 0 && current.getDay() !== 6) {
                        workingDays++;
                    }
                    current.setDate(current.getDate() + 1);
                }
            } else {
                workingDays = 1; // For date search
            }
            
            if (classData.students && classData.students.length > 0) {
                classData.students.forEach((student, studentIndex) => {
                    if (searchType === 'date') {
                        // For date view, show check in/out times
                        const checkInBadge = student.check_in_time 
                            ? '<span class="badge bg-success">' + student.check_in_time + '</span>' 
                            : '<span class="text-muted">--</span>';
                        const checkOutBadge = student.check_out_time 
                            ? '<span class="badge bg-primary">' + student.check_out_time + '</span>' 
                            : '<span class="text-muted">--</span>';
                        
                        html += `
                            <tr>
                                <td>${studentIndex + 1}</td>
                                <td>${student.first_name} ${student.middle_name || ''} ${student.last_name}</td>
                                <td>${student.admission_number || 'N/A'}</td>
                                <td>${checkInBadge}</td>
                                <td>${checkOutBadge}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-fingerprint-student-btn" 
                                            data-student-id="${student.studentID}" 
                                            data-student-name="${student.first_name} ${student.middle_name || ''} ${student.last_name}"
                                            data-subclass-id="${classData.subclassID}"
                                            title="View More Details">
                                        <i class="bi bi-eye"></i> View More
                                    </button>
                                </td>
                            </tr>
                        `;
                    } else {
                        // For month/year view, show stats
                        html += `
                            <tr>
                                <td>${studentIndex + 1}</td>
                                <td>${student.first_name} ${student.middle_name || ''} ${student.last_name}</td>
                                <td>${student.admission_number || 'N/A'}</td>
                                <td>${student.gender || 'N/A'}</td>
                                <td><span class="badge bg-success">${student.days_present || 0}</span></td>
                                <td><span class="badge bg-danger">${student.days_absent || 0}</span></td>
                                <td>${workingDays}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-fingerprint-student-btn" 
                                            data-student-id="${student.studentID}" 
                                            data-student-name="${student.first_name} ${student.middle_name || ''} ${student.last_name}"
                                            data-subclass-id="${classData.subclassID}"
                                            title="View More Details">
                                        <i class="bi bi-eye"></i> View More
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                });
            }

            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        content.innerHTML = html;

        // Add toggle functionality for subclass students
        document.querySelectorAll('.toggle-subclass-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subclassID = this.getAttribute('data-subclass-id');
                const studentsDiv = document.getElementById(`fingerprint-subclass-students-${subclassID}`);
                const icon = this.querySelector('i');
                
                if (studentsDiv.style.display === 'none') {
                    studentsDiv.style.display = 'block';
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-up');
                } else {
                    studentsDiv.style.display = 'none';
                    icon.classList.remove('bi-chevron-up');
                    icon.classList.add('bi-chevron-down');
                }
            });
        });

        // Add export functionality for each class
        document.querySelectorAll('.export-class-excel-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subclassId = this.getAttribute('data-subclass-id');
                const subclassName = this.getAttribute('data-subclass-name');
                exportClassFingerprintAttendanceToExcel(subclassId, subclassName);
            });
        });

        document.querySelectorAll('.export-class-pdf-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subclassId = this.getAttribute('data-subclass-id');
                const subclassName = this.getAttribute('data-subclass-name');
                exportClassFingerprintAttendanceToPdf(subclassId, subclassName);
            });
        });

        // Add event listeners for View More buttons
        document.querySelectorAll('.view-fingerprint-student-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = this.getAttribute('data-student-id');
                const studentName = this.getAttribute('data-student-name');
                const searchType = document.getElementById('fingerprint_search_type').value;
                const month = document.getElementById('fingerprint_month') ? document.getElementById('fingerprint_month').value : '';
                const year = document.getElementById('fingerprint_year') ? document.getElementById('fingerprint_year').value : '';
                const date = document.getElementById('fingerprint_date') ? document.getElementById('fingerprint_date').value : '';
                
                viewFingerprintStudentDetails(studentId, studentName, searchType, month, year, date);
            });
        });
        
        // Initialize realtime filtering for class dropdown
        initializeFingerprintClassFilter();
    }
    
    // Function to filter fingerprint classes in realtime
    function filterFingerprintClasses(selectedClassID) {
        const container = document.getElementById('fingerprintAttendanceByClassContent');
        if (!container) {
            console.warn('Fingerprint attendance container not found');
            return;
        }
        
        // Get all cards - both with and without border-secondary
        const allCards = container.querySelectorAll('.card.mb-4[data-subclass-id]');
        
        console.log('Filtering classes. Selected:', selectedClassID, 'Total cards:', allCards.length);
        
        if (selectedClassID === '' || selectedClassID === null || selectedClassID === undefined) {
            // Show all classes
            allCards.forEach(function(card) {
                card.style.display = '';
            });
        } else {
            // Show only selected class - hide all others
            let foundMatch = false;
            allCards.forEach(function(card) {
                const subclassID = card.getAttribute('data-subclass-id');
                console.log('Card subclassID:', subclassID, 'Selected:', selectedClassID, 'Match:', subclassID == selectedClassID);
                if (subclassID && String(subclassID) === String(selectedClassID)) {
                    card.style.display = '';
                    foundMatch = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // If no match found, show all cards (fallback)
            if (!foundMatch) {
                console.warn('No matching class found for ID:', selectedClassID);
                // Show all cards as fallback
                allCards.forEach(function(card) {
                    card.style.display = '';
                });
            }
        }
    }
    
    // Initialize fingerprint class filter
    function initializeFingerprintClassFilter() {
        const classDropdown = document.getElementById('searchFingerprintClasses');
        if (classDropdown) {
            // Remove any existing event listeners by cloning
            const newDropdown = classDropdown.cloneNode(true);
            classDropdown.parentNode.replaceChild(newDropdown, classDropdown);
            
            // Add change event listener for realtime filtering
            newDropdown.addEventListener('change', function(e) {
                const selectedClassID = this.value;
                console.log('Class dropdown changed:', selectedClassID);
                filterFingerprintClasses(selectedClassID);
            });
            
            // Also add direct event listener as backup
            $(newDropdown).off('change').on('change', function() {
                const selectedClassID = $(this).val();
                console.log('jQuery change event:', selectedClassID);
                filterFingerprintClasses(selectedClassID);
            });
        } else {
            console.warn('searchFingerprintClasses dropdown not found');
        }
    }

    // View Fingerprint Student Details
    function viewFingerprintStudentDetails(studentID, studentName, searchType, month, year, date) {
        const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
        const modalContent = document.getElementById('studentDetailsContent');
        const modalTitle = document.getElementById('studentDetailsModalLabel');
        
        modalTitle.innerHTML = '<i class="bi bi-person me-2"></i>Fingerprint Attendance Details - ' + studentName;
        
        modalContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Loading attendance details...</p>
            </div>
        `;
        
        modal.show();
        
        // Build query parameters
        let url = `{{ url('student_fingerprint_attendance_details') }}/${studentID}?search_type=${searchType}`;
        if (month && year) {
            url += `&month=${month}&year=${year}`;
        } else if (year) {
            url += `&year=${year}`;
        } else if (date) {
            url += `&date=${date}`;
        }
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFingerprintStudentDetails(data);
            } else {
                modalContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Failed to load attendance details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error(error);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> An error occurred while loading attendance details
                </div>
            `;
        });
    }

    // Store current student data for export
    let currentStudentFingerprintData = null;

    function displayFingerprintStudentDetails(data) {
        const modalContent = document.getElementById('studentDetailsContent');
        const student = data.student;
        const stats = data.statistics;
        const records = data.attendance_records;
        const searchPeriod = data.search_period;
        const searchType = data.search_type;
        
        // Store data for export
        currentStudentFingerprintData = {
            student: student,
            stats: stats,
            records: records,
            searchPeriod: searchPeriod,
            searchType: searchType
        };
        
        let html = `
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Student Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> ${student.full_name}</p>
                                    <p><strong>Admission Number:</strong> ${student.admission_number || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Period:</strong> ${searchPeriod}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${stats.days_present}</h3>
                            <p class="mb-0">Days Present</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${stats.days_absent}</h3>
                            <p class="mb-0">Days Absent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${stats.total_days}</h3>
                            <p class="mb-0">Total Days</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${stats.total_records}</h3>
                            <p class="mb-0">Total Records</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Attendance Records</h6>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-success" id="exportStudentFingerprintExcelBtn" title="Export to Excel">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                                <button class="btn btn-sm btn-danger" id="exportStudentFingerprintPdfBtn" title="Export to PDF">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Check In Time</th>
                                            <th>Check Out Time</th>
                                            <th>Verify Mode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
        `;
        
        if (records && records.length > 0) {
            records.forEach((record, index) => {
                const checkInBadge = record.check_in_time 
                    ? '<span class="badge bg-success">' + record.check_in_time + '</span>' 
                    : '<span class="text-muted">--</span>';
                const checkOutBadge = record.check_out_time 
                    ? '<span class="badge bg-primary">' + record.check_out_time + '</span>' 
                    : '<span class="text-muted">--</span>';
                
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${record.attendance_date_formatted}</td>
                        <td>${checkInBadge}</td>
                        <td>${checkOutBadge}</td>
                        <td>${record.verify_mode || 'N/A'}</td>
                    </tr>
                `;
            });
        } else {
            html += `
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        <i class="bi bi-info-circle"></i> No attendance records found for this period
                    </td>
                </tr>
            `;
        }
        
        html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        modalContent.innerHTML = html;
        
        // Add event listeners for export buttons
        const excelBtn = document.getElementById('exportStudentFingerprintExcelBtn');
        const pdfBtn = document.getElementById('exportStudentFingerprintPdfBtn');
        
        if (excelBtn) {
            // Remove existing listeners
            const newExcelBtn = excelBtn.cloneNode(true);
            excelBtn.parentNode.replaceChild(newExcelBtn, excelBtn);
            newExcelBtn.addEventListener('click', function() {
                exportStudentFingerprintDetailsToExcel();
            });
        }
        
        if (pdfBtn) {
            // Remove existing listeners
            const newPdfBtn = pdfBtn.cloneNode(true);
            pdfBtn.parentNode.replaceChild(newPdfBtn, pdfBtn);
            newPdfBtn.addEventListener('click', function() {
                exportStudentFingerprintDetailsToPdf();
            });
        }
    }

    // Export Student Fingerprint Details to Excel
    function exportStudentFingerprintDetailsToExcel() {
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Excel export library not loaded', 'error');
            return;
        }

        if (!currentStudentFingerprintData) {
            Swal.fire('Error', 'No data to export.', 'error');
            return;
        }

        const student = currentStudentFingerprintData.student;
        const stats = currentStudentFingerprintData.stats;
        const records = currentStudentFingerprintData.records;
        const searchPeriod = currentStudentFingerprintData.searchPeriod;
        const schoolName = '{{ $school_details->school_name ?? "School" }}';
        
        const reportTitle = 'FINGERPRINT ATTENDANCE DETAILS - ' + student.full_name.toUpperCase() + ' - ' + searchPeriod.toUpperCase();

        // Create workbook
        const wb = XLSX.utils.book_new();
        const wsData = [];

        // Header rows
        wsData.push([schoolName]);
        wsData.push([reportTitle]);
        wsData.push([]);
        wsData.push(['Student Name:', student.full_name]);
        wsData.push(['Admission Number:', student.admission_number || 'N/A']);
        wsData.push(['Period:', searchPeriod]);
        wsData.push(['Days Present:', stats.days_present]);
        wsData.push(['Days Absent:', stats.days_absent]);
        wsData.push(['Total Days:', stats.total_days]);
        wsData.push([]);
        wsData.push(['Date', 'Check In Time', 'Check Out Time', 'Verify Mode']);

        // Attendance records
        records.forEach(function(record) {
            wsData.push([
                record.attendance_date_formatted,
                record.check_in_time || '--',
                record.check_out_time || '--',
                record.verify_mode || 'N/A'
            ]);
        });

        const ws = XLSX.utils.aoa_to_sheet(wsData);
        if (!ws['!merges']) ws['!merges'] = [];
        ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 3}});
        ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 3}});

        XLSX.utils.book_append_sheet(wb, ws, 'Attendance Details');
        XLSX.writeFile(wb, 'Fingerprint_Attendance_' + student.full_name.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.xlsx');
    }

    // Export Student Fingerprint Details to PDF
    function exportStudentFingerprintDetailsToPdf() {
        if (typeof window.jspdf === 'undefined') {
            Swal.fire('Error', 'PDF export library not loaded', 'error');
            return;
        }

        if (!currentStudentFingerprintData) {
            Swal.fire('Error', 'No data to export.', 'error');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        const student = currentStudentFingerprintData.student;
        const stats = currentStudentFingerprintData.stats;
        const records = currentStudentFingerprintData.records;
        const searchPeriod = currentStudentFingerprintData.searchPeriod;
        const schoolName = '{{ $school_details->school_name ?? "School" }}';
        const schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';
        
        const reportTitle = 'FINGERPRINT ATTENDANCE DETAILS - ' + student.full_name.toUpperCase() + ' - ' + searchPeriod.toUpperCase();

        function drawHeaderAndTable(logoImg) {
            const pageWidth = doc.internal.pageSize.getWidth();
            const centerX = pageWidth / 2;

            // Logo on the left if available
            if (logoImg) {
                try {
                    doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                } catch (e) {
                    console.warn('Failed to add logo to PDF:', e);
                }
            }

            // School name and report title centered
            doc.setFontSize(16);
            doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
            doc.setFontSize(12);
            doc.text(reportTitle, centerX, 26, { align: 'center' });

            // Student info
            doc.setFontSize(10);
            doc.text('Student Name: ' + student.full_name, 14, 36);
            doc.text('Admission Number: ' + (student.admission_number || 'N/A'), 14, 42);
            doc.text('Period: ' + searchPeriod, 14, 48);
            doc.text('Days Present: ' + stats.days_present + ' | Days Absent: ' + stats.days_absent + ' | Total Days: ' + stats.total_days, 14, 54);

            // Prepare table data
            const tableData = [];
            records.forEach(function(record) {
                tableData.push([
                    record.attendance_date_formatted,
                    record.check_in_time || '--',
                    record.check_out_time || '--',
                    record.verify_mode || 'N/A'
                ]);
            });

            // Add table
            doc.autoTable({
                startY: 60,
                head: [['Date', 'Check In Time', 'Check Out Time', 'Verify Mode']],
                body: tableData,
                theme: 'striped',
                headStyles: { fillColor: [148, 0, 0] },
                didDrawPage: function (data) {
                    // Footer on each page
                    const pageHeight = doc.internal.pageSize.getHeight();
                    doc.setFontSize(9);
                    doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                }
            });

            // Save PDF
            doc.save('Fingerprint_Attendance_' + student.full_name.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf');
        }

        if (schoolLogoUrl) {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function() {
                drawHeaderAndTable(img);
            };
            img.onerror = function() {
                console.warn('Failed to load school logo image for PDF header.');
                drawHeaderAndTable(null);
            };
            img.src = schoolLogoUrl;
        } else {
            drawHeaderAndTable(null);
        }
    }

    // Load fingerprint attendance from external API and display all records
    function loadFingerprintAttendance(page = 1) {
        const container = document.getElementById('fingerprintAttendanceContent');

        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Loading attendance records from biometric system...</p>
            </div>
        `;

        fetch('{{ route("api.attendance.external_list") }}?page=' + page, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load external attendance');
            }

            const records = data.data || [];
            const pagination = data.pagination || null;

            if (records.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No attendance records found from the biometric system.
                    </div>
                `;
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm" id="fingerprintAttendanceTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Enroll ID (Fingerprint ID)</th>
                                <th>Attendance Date</th>
                                <th>Check In Time</th>
                                <th>Check Out Time</th>
                                <th>Status</th>
                                <th>Verify Mode</th>
                                <th>Device IP</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            records.forEach((rec, index) => {
                const user = rec.user || {};
                const status = rec.status === '1' ? 'Present' : (rec.status || '');

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${user.id ?? ''}</td>
                        <td>${user.name ?? ''}</td>
                        <td>${user.enroll_id ?? ''}</td>
                        <td>${rec.attendance_date ?? ''}</td>
                        <td>${rec.check_in_time ?? ''}</td>
                        <td>${rec.check_out_time ?? ''}</td>
                        <td>${status}</td>
                        <td>${rec.verify_mode ?? ''}</td>
                        <td>${rec.device_ip ?? ''}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            // Pagination info (simple display)
            if (pagination) {
                html += `
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">
                            Page ${pagination.current_page} of ${pagination.last_page}, Total: ${pagination.total}
                        </small>
                        <div>
                `;

                if (pagination.current_page > 1) {
                    html += `
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="loadFingerprintAttendance(${pagination.current_page - 1})">
                            <i class="bi bi-chevron-left"></i> Prev
                        </button>
                    `;
                }

                if (pagination.current_page < pagination.last_page) {
                    html += `
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadFingerprintAttendance(${pagination.current_page + 1})">
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    `;
                }

                html += `
                        </div>
                    </div>
                `;
            }

            container.innerHTML = html;

            // Initialize DataTable for fingerprint table
            if ($('#fingerprintAttendanceTable').length) {
                $('#fingerprintAttendanceTable').DataTable({
                    pageLength: 25,
                    order: [[4, 'desc'], [6, 'desc']],
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "No entries found",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        zeroRecords: "No matching records found"
                    },
                    responsive: true
                });
            }
        })
        .catch(error => {
            console.error(error);
            container.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message || 'Failed to load external attendance.'}
                </div>
            `;
        });
    }

    function displayAttendanceByClass(attendanceByClass) {
        const content = document.getElementById('attendanceByClassContent');
        const searchType = document.getElementById('search_type').value;
        
        let html = '';
        
        attendanceByClass.forEach((classData) => {
            if (!classData.has_attendance) {
                // No attendance collected - get specific period message
                let periodMessage = '';
                if (searchType === 'month') {
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    const month = document.getElementById('month') ? document.getElementById('month').value : '';
                    const year = document.getElementById('year') ? document.getElementById('year').value : '';
                    if (month && year) {
                        const monthName = monthNames[parseInt(month) - 1];
                        periodMessage = `in ${monthName} ${year}`;
                    } else {
                        periodMessage = getPeriodLabel(searchType);
                    }
                } else if (searchType === 'year') {
                    const year = document.getElementById('year') ? document.getElementById('year').value : '';
                    if (year) {
                        periodMessage = `in ${year}`;
                    } else {
                        periodMessage = getPeriodLabel(searchType);
                    }
                } else if (searchType === 'date') {
                    const date = document.getElementById('date') ? document.getElementById('date').value : '';
                    if (date) {
                        const dateObj = new Date(date);
                        periodMessage = `on ${dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`;
                    } else {
                        periodMessage = getPeriodLabel(searchType);
                    }
                } else {
                    periodMessage = getPeriodLabel(searchType);
                }
                
                html += `
                    <div class="card mb-4 border-secondary" data-subclass-id="${classData.subclassID}">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-folder"></i> ${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> No attendance collected ${periodMessage} for this class.
                            </div>
                        </div>
                    </div>
                `;
                return;
            }

            // Has attendance - show statistics
            html += `
                <div class="card mb-4" style="border-color: #940000; border-width: 2px;">
                    <div class="card-header text-white" style="background-color: #940000;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-folder"></i> ${classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name}
                            </h5>
                            <button class="btn btn-sm btn-light toggle-subclass-btn" data-subclass-id="${classData.subclassID}">
                                <i class="bi bi-chevron-down"></i> View Students
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
            `;

            // Show widgets based on search type
            if (searchType === 'month' || searchType === 'year') {
                // Show percentage widgets for month/year
                html += `
                            <div class="col-md-3">
                                <div class="card bg-success">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.present_percentage}%</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Present (${classData.present})</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.absent_percentage}%</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Absent (${classData.absent})</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning">
                                    <div class="card-body text-center" style="color: #000000;">
                                        <h4 style="color: #000000; font-weight: bold;">${classData.late_percentage}%</h4>
                                        <p class="mb-0" style="color: #000000; font-weight: 500;">Late (${classData.late})</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.excused_percentage}%</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Excused (${classData.excused})</p>
                                    </div>
                                </div>
                            </div>
                `;
            } else {
                // Show count widgets for date/week
                html += `
                            <div class="col-md-3">
                                <div class="card bg-success">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.present || 0}</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Present</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.absent || 0}</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Absent</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning">
                                    <div class="card-body text-center" style="color: #000000;">
                                        <h4 style="color: #000000; font-weight: bold;">${classData.late || 0}</h4>
                                        <p class="mb-0" style="color: #000000; font-weight: 500;">Late</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info">
                                    <div class="card-body text-center" style="color: #ffffff;">
                                        <h4 style="color: #ffffff; font-weight: bold;">${classData.excused || 0}</h4>
                                        <p class="mb-0" style="color: #ffffff; font-weight: 500;">Excused</p>
                                    </div>
                                </div>
                            </div>
                `;
            }

            html += `
                        </div>
                        
                        <!-- Bar Chart for Attendance Percentages -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header text-white" style="background-color: #940000;">
                                        <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Attendance Percentages</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="classChart_${classData.subclassID}" height="80"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="subclass-students" id="subclass-students-${classData.subclassID}" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="studentsTable_${classData.subclassID}">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Admission No.</th>
                                            <th>Gender</th>
                                            <th>Days Present</th>
                                            <th>Days Absent</th>
                                            <th>Days Late</th>
                                            <th>Days Excused</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;
            
            if (classData.students && classData.students.length > 0) {
                classData.students.forEach((student, studentIndex) => {
                    html += `
                        <tr>
                            <td>${studentIndex + 1}</td>
                            <td>${student.first_name} ${student.middle_name || ''} ${student.last_name}</td>
                            <td>${student.admission_number || 'N/A'}</td>
                            <td>${student.gender || 'N/A'}</td>
                            <td><span class="badge bg-success">${student.days_present || 0}</span></td>
                            <td><span class="badge bg-danger">${student.days_absent || 0}</span></td>
                            <td><span class="badge bg-warning">${student.days_late || 0}</span></td>
                            <td><span class="badge bg-info">${student.days_excused || 0}</span></td>
                            <td>
                                <button class="btn btn-sm btn-view" onclick="viewStudentDetails(${student.studentID}, '${searchType}')">
                                    <i class="bi bi-eye"></i> View More
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="9" class="text-center">No students found</td></tr>';
            }
            
            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        content.innerHTML = html;

        // Populate dropdown with all classes (including those without attendance)
        const classDropdown = $('#searchClasses');
        classDropdown.empty();
        classDropdown.append('<option value="">All Classes</option>');
        attendanceByClass.forEach((classData) => {
            const displayName = classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name;
            const option = $('<option></option>').val(classData.subclassID).text(displayName);
            classDropdown.append(option);
        });

        // Initialize DataTables and Charts for each class
        attendanceByClass.forEach((classData) => {
            if (classData.has_attendance) {
                // Initialize DataTable for students table
                if (classData.students && classData.students.length > 0) {
                    $(`#studentsTable_${classData.subclassID}`).DataTable({
                        pageLength: 10,
                        order: [[0, 'asc']],
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "No entries found",
                            infoFiltered: "(filtered from _MAX_ total entries)",
                            zeroRecords: "No matching records found"
                        },
                        responsive: true,
                        columnDefs: [
                            { orderable: false, targets: 8 } // Disable sorting on Actions column
                        ]
                    });
                }

                // Initialize Bar Chart for attendance percentages
                const chartCtx = document.getElementById(`classChart_${classData.subclassID}`);
                if (chartCtx) {
                    const ctx = chartCtx.getContext('2d');
                    
                    let labels = [];
                    let dataValues = [];
                    let backgroundColor = [];
                    
                    if (searchType === 'month' || searchType === 'year') {
                        // Show percentages for month/year
                        labels = ['Present', 'Absent', 'Late', 'Excused'];
                        dataValues = [
                            classData.present_percentage || 0,
                            classData.absent_percentage || 0,
                            classData.late_percentage || 0,
                            classData.excused_percentage || 0
                        ];
                        backgroundColor = ['#28a745', '#dc3545', '#ffc107', '#17a2b8'];
                    } else {
                        // Show counts for date
                        labels = ['Present', 'Absent', 'Late', 'Excused'];
                        const total = (classData.present || 0) + (classData.absent || 0) + (classData.late || 0) + (classData.excused || 0);
                        dataValues = [
                            total > 0 ? ((classData.present || 0) / total * 100).toFixed(2) : 0,
                            total > 0 ? ((classData.absent || 0) / total * 100).toFixed(2) : 0,
                            total > 0 ? ((classData.late || 0) / total * 100).toFixed(2) : 0,
                            total > 0 ? ((classData.excused || 0) / total * 100).toFixed(2) : 0
                        ];
                        backgroundColor = ['#28a745', '#dc3545', '#ffc107', '#17a2b8'];
                    }

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: searchType === 'month' || searchType === 'year' ? 'Percentage (%)' : 'Percentage (%)',
                                data: dataValues,
                                backgroundColor: backgroundColor,
                                borderColor: backgroundColor,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': ' + context.parsed.y + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });

        // Add toggle functionality
        $('.toggle-subclass-btn').on('click', function() {
            const subclassID = $(this).data('subclass-id');
            const classCard = $(this).closest('.card');
            const studentsDiv = $('#subclass-students-' + subclassID);
            const widgetsDiv = classCard.find('.row.mb-3').first();
            const chartDiv = classCard.find('.row.mb-3').last();
            const icon = $(this).find('i');

            if (studentsDiv.is(':visible')) {
                // Hide students, show widgets and chart
                studentsDiv.slideUp();
                widgetsDiv.slideDown();
                chartDiv.slideDown();
                icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
                $(this).html('<i class="bi bi-chevron-down"></i> View Students');
            } else {
                // Show students, hide widgets and chart
                widgetsDiv.slideUp();
                chartDiv.slideUp();
                studentsDiv.slideDown();
                icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
                $(this).html('<i class="bi bi-chevron-up"></i> Hide Students');
            }
        });

        // Add search functionality for classes (dropdown)
        $('#searchClasses').on('change', function() {
            const selectedClassID = $(this).val();
            const searchType = document.getElementById('search_type').value;
            const periodLabel = getPeriodLabel(searchType);
            
            $('.card.mb-4').each(function() {
                if (selectedClassID === '') {
                    // Show all classes
                    $(this).show();
                } else {
                    // Show only selected class - check both data attribute and toggle button
                    const subclassID = $(this).data('subclass-id') || $(this).find('.toggle-subclass-btn').data('subclass-id');
                    
                    if (subclassID == selectedClassID) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                }
            });
        });
    }

    function getPeriodLabel(searchType) {
        const labels = {
            'month': 'in this month',
            'year': 'in this year',
            'date': 'on this date'
        };
        return labels[searchType] || '';
    }


    function viewStudentDetails(studentID, searchType = 'month') {
        const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
        const content = document.getElementById('studentDetailsContent');
        
        const currentYear = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
        const currentMonth = document.getElementById('month') ? document.getElementById('month').value : String(new Date().getMonth() + 1).padStart(2, '0');
        const currentWeek = document.getElementById('week') ? document.getElementById('week').value : null;
        const currentDate = document.getElementById('date') ? document.getElementById('date').value : null;
        
        content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Loading...</p></div>';
        modal.show();

        // Build URL based on search type
        let url = `{{ url('student_attendance_details') }}/${studentID}?search_type=${searchType}&year=${currentYear}`;
        
        if (searchType === 'month') {
            url += `&month=${currentMonth}`;
        } else if (searchType === 'week' && currentWeek) {
            url += `&week=${currentWeek}`;
        } else if (searchType === 'date' && currentDate) {
            url += `&date=${currentDate}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayStudentDetails(data, searchType);
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Error loading student details</div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">Error loading student details</div>';
            });
    }

    function displayStudentDetails(data, searchType = 'month') {
        const content = document.getElementById('studentDetailsContent');
        const student = data.student;
        const stats = data.stats;
        const chartData = data.chart_data;

        const currentYear = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
        const currentMonth = document.getElementById('month') ? document.getElementById('month').value : String(new Date().getMonth() + 1).padStart(2, '0');
        const currentDate = document.getElementById('date') ? document.getElementById('date').value : null;

        // Determine period label for overview
        let overviewLabel = '';
        if (searchType === 'date' && currentDate) {
            const dateObj = new Date(currentDate);
            overviewLabel = `Student Attendance Overview in ${dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`;
        } else if (searchType === 'month' && currentYear && currentMonth) {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const monthName = monthNames[parseInt(currentMonth) - 1];
            overviewLabel = `Student Attendance Overview in ${monthName} ${currentYear}`;
        } else if (searchType === 'year' && currentYear) {
            overviewLabel = `Student Attendance Overview in ${currentYear}`;
        } else {
            overviewLabel = 'Student Attendance Overview';
        }

        // Widget cards like classManagement attendance overview
        content.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-12">
                    <h4 class="mb-2">${student.first_name} ${student.middle_name || ''} ${student.last_name}</h4>
                    <p class="text-muted mb-2">Admission No: ${student.admission_number || 'N/A'}</p>
                    <h5 class="text-primary mb-3">${overviewLabel}</h5>
                </div>
            </div>
            
            <!-- Statistics Widgets -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-success">
                        <div class="card-body text-center" style="color: #ffffff;">
                            <h4 style="color: #ffffff; font-weight: bold;">${stats.present || 0}</h4>
                            <p class="mb-0" style="color: #ffffff; font-weight: 500;">Present</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger">
                        <div class="card-body text-center" style="color: #ffffff;">
                            <h4 style="color: #ffffff; font-weight: bold;">${stats.absent || 0}</h4>
                            <p class="mb-0" style="color: #ffffff; font-weight: 500;">Absent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning">
                        <div class="card-body text-center" style="color: #000000;">
                            <h4 style="color: #000000; font-weight: bold;">${stats.late || 0}</h4>
                            <p class="mb-0" style="color: #000000; font-weight: 500;">Late</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info">
                        <div class="card-body text-center" style="color: #ffffff;">
                            <h4 style="color: #ffffff; font-weight: bold;">${stats.excused || 0}</h4>
                            <p class="mb-0" style="color: #ffffff; font-weight: 500;">Excused</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card mb-3">
                <div class="card-header text-white" style="background-color: #940000;">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body">
                    <p><strong>Total Days:</strong> ${stats.total_days || 0}</p>
                    <p><strong>Total Present:</strong> ${stats.present || 0}</p>
                    <p><strong>Total Absent:</strong> ${stats.absent || 0}</p>
                    <p><strong>Total Late:</strong> ${stats.late || 0}</p>
                    <p><strong>Total Excused:</strong> ${stats.excused || 0}</p>
                </div>
            </div>

            <!-- Charts -->
            ${searchType !== 'date' ? `
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header text-white" style="background-color: #940000;">
                            <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Attendance Percentages</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="studentChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Attendance History -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header text-white" style="background-color: #940000;">
                            <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Attendance History</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.attendances && data.attendances.length > 0 ? data.attendances.map(att => {
                                            const statusClass = {
                                                'Present': 'bg-success',
                                                'Absent': 'bg-danger',
                                                'Late': 'bg-warning',
                                                'Excused': 'bg-info'
                                            }[att.status] || 'bg-secondary';
                                            return `
                                                <tr>
                                                    <td>${new Date(att.attendance_date).toLocaleDateString()}</td>
                                                    <td><span class="badge ${statusClass}">${att.status}</span></td>
                                                    <td>${att.remark || '-'}</td>
                                                </tr>
                                            `;
                                        }).join('') : '<tr><td colspan="3" class="text-center">No attendance records found</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Display student chart (only for month and year, not for date)
        if (searchType !== 'date') {
            setTimeout(() => {
                displayStudentChart(data, searchType);
            }, 100);
        }
    }

    function displayStudentChart(data, searchType) {
        const ctx = document.getElementById('studentChart');
        if (!ctx) return;
        
        const chartCtx = ctx.getContext('2d');
        
        if (studentChart) {
            studentChart.destroy();
        }

        const stats = data.stats;
        const totalDays = stats.total_days || 0;

        // Calculate percentages
        let presentPercentage = 0;
        let absentPercentage = 0;
        let latePercentage = 0;
        let excusedPercentage = 0;

        if (totalDays > 0) {
            presentPercentage = ((stats.present || 0) / totalDays * 100).toFixed(2);
            absentPercentage = ((stats.absent || 0) / totalDays * 100).toFixed(2);
            latePercentage = ((stats.late || 0) / totalDays * 100).toFixed(2);
            excusedPercentage = ((stats.excused || 0) / totalDays * 100).toFixed(2);
        }

        const labels = ['Present', 'Absent', 'Late', 'Excused'];
        const dataValues = [
            parseFloat(presentPercentage),
            parseFloat(absentPercentage),
            parseFloat(latePercentage),
            parseFloat(excusedPercentage)
        ];

        studentChart = new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Attendance Percentage (%)',
                    data: dataValues,
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'],
                    borderColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Export Fingerprint Attendance to Excel
    function exportFingerprintAttendanceToExcel() {
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Excel export library not loaded', 'error');
            return;
        }

        if (!currentFingerprintAttendanceData || currentFingerprintAttendanceData.length === 0) {
            Swal.fire('Error', 'No data to export. Please search attendance first.', 'error');
            return;
        }

        // Get school name and build title
        var schoolName = '{{ $school_details->school_name ?? "School" }}';
        var reportTitle = '';
        if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0];
        } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + currentFingerprintSearchYear;
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            var dateObj = new Date(currentFingerprintSearchDate);
            var dateFormatted = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE ON ' + dateFormatted.toUpperCase();
        } else {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE';
        }

        // Calculate working days
        var startDate, endDate;
        if (currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
            endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchYear) {
            startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
            endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            // For date view, single day
            startDate = endDate = new Date(currentFingerprintSearchDate);
        } else {
            Swal.fire('Error', 'No valid search period selected.', 'error');
            return;
        }
        
        // For date view, export students with check in/out times
        if (currentFingerprintSearchType === 'date') {
            // Collect all students from all classes with check in/out times
            var studentList = [];
            currentFingerprintAttendanceData.forEach(function(classData) {
                if (classData.students && classData.students.length > 0) {
                    classData.students.forEach(function(student) {
                        var fullName = (student.first_name || '') + ' ' +
                                       (student.middle_name ? student.middle_name + ' ' : '') +
                                       (student.last_name || '');
                        fullName = fullName.trim() || 'N/A';
                        
                        studentList.push({
                            name: fullName,
                            admissionNumber: student.admission_number || 'N/A',
                            className: student.subclass_name || (classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name) || 'N/A',
                            checkInTime: student.check_in_time || '--',
                            checkOutTime: student.check_out_time || '--'
                        });
                    });
                }
            });
            
            studentList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });
            
            // Create workbook
            var wb = XLSX.utils.book_new();
            var wsData = [];
            
            // Header rows
            wsData.push([schoolName]);
            wsData.push([reportTitle]);
            wsData.push([]);
            wsData.push(['Student Name', 'Class', 'Admission Number', 'Check In Time', 'Check Out Time']);
            
            // Student data
            studentList.forEach(function(student) {
                wsData.push([student.name, student.className, student.admissionNumber, student.checkInTime, student.checkOutTime]);
            });
            
            var ws = XLSX.utils.aoa_to_sheet(wsData);
            if (!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 4}});
            ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 4}});
            
            XLSX.utils.book_append_sheet(wb, ws, 'Fingerprint Attendance');
            var fileName = 'Fingerprint_Attendance_' + currentFingerprintSearchDate.replace(/-/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.xlsx';
            XLSX.writeFile(wb, fileName);
            return;
        }

        var workingDays = 0;
        var current = new Date(startDate);
        while (current <= endDate) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        // Get selected class
        var selectedClassId = document.getElementById('searchFingerprintClasses') ? document.getElementById('searchFingerprintClasses').value : '';
        var selectedClassName = '';
        
        // Collect students from selected class only
        var studentMap = {};
        currentFingerprintAttendanceData.forEach(function(classData) {
            // If a class is selected, only export that class
            if (selectedClassId && classData.subclassID != selectedClassId) {
                return;
            }
            
            // Store selected class name for report title
            if (selectedClassId && classData.subclassID == selectedClassId) {
                selectedClassName = classData.subclass_name;
            }
            
            if (classData.students && classData.students.length > 0) {
                classData.students.forEach(function(student) {
                    var studentId = student.studentID || '';
                    if (!studentId) return;

                    var fullName = (student.first_name || '') + ' ' +
                                   (student.middle_name ? student.middle_name + ' ' : '') +
                                   (student.last_name || '');
                    fullName = fullName.trim() || 'N/A';

                    if (!studentMap[studentId]) {
                        studentMap[studentId] = {
                            name: fullName,
                            className: student.subclass_name || (classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name) || 'N/A',
                            daysPresent: 0,
                            daysAbsent: 0
                        };
                    }
                    studentMap[studentId].daysPresent = student.days_present || 0;
                    studentMap[studentId].daysAbsent = student.days_absent || 0;
                });
            }
        });
        
        // Update report title if class is selected
        if (selectedClassName) {
            reportTitle = reportTitle + ' - ' + selectedClassName.toUpperCase();
        }

        var studentList = Object.values(studentMap);
        studentList.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Create workbook
        var wb = XLSX.utils.book_new();
        var wsData = [];

        // Header rows
        wsData.push([schoolName]);
        wsData.push([reportTitle]);
        wsData.push([]);
        wsData.push(['Student Name', 'Class', 'Days Present', 'Days Absent', 'Total Days']);

        // Student data
        studentList.forEach(function(student) {
            wsData.push([student.name, student.className, student.daysPresent, student.daysAbsent, workingDays]);
        });

        var ws = XLSX.utils.aoa_to_sheet(wsData);
        if (!ws['!merges']) ws['!merges'] = [];
        ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 4}});
        ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 4}});

        XLSX.utils.book_append_sheet(wb, ws, 'Fingerprint Attendance');
        var fileName = 'Fingerprint_Attendance_';
        if (currentFingerprintSearchMonth) {
            fileName += currentFingerprintSearchMonth.replace(/-/g, '_');
        } else if (currentFingerprintSearchYear) {
            fileName += currentFingerprintSearchYear;
        } else if (currentFingerprintSearchDate) {
            fileName += currentFingerprintSearchDate.replace(/-/g, '_');
        } else {
            fileName += 'Report';
        }
        fileName += '_' + new Date().toISOString().split('T')[0] + '.xlsx';
        XLSX.writeFile(wb, fileName);
    }

    // Export Fingerprint Attendance to PDF
    function exportFingerprintAttendanceToPdf() {
        if (typeof window.jspdf === 'undefined') {
            Swal.fire('Error', 'PDF export library not loaded', 'error');
            return;
        }

        if (!currentFingerprintAttendanceData || currentFingerprintAttendanceData.length === 0) {
            Swal.fire('Error', 'No data to export. Please search attendance first.', 'error');
            return;
        }

        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('landscape');

        // Get school name and build title
        var schoolName = '{{ $school_details->school_name ?? "School" }}';
        var reportTitle = '';
        if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0];
        } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + currentFingerprintSearchYear;
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            var dateObj = new Date(currentFingerprintSearchDate);
            var dateFormatted = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE ON ' + dateFormatted.toUpperCase();
        } else {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE';
        }

        // Calculate working days
        var startDate, endDate;
        if (currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
            endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchYear) {
            startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
            endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            // For date view, single day
            startDate = endDate = new Date(currentFingerprintSearchDate);
        } else {
            Swal.fire('Error', 'No valid search period selected.', 'error');
            return;
        }
        
        // For date view, export students with check in/out times
        if (currentFingerprintSearchType === 'date') {
            // Collect all students from all classes with check in/out times
            var studentList = [];
            currentFingerprintAttendanceData.forEach(function(classData) {
                if (classData.students && classData.students.length > 0) {
                    classData.students.forEach(function(student) {
                        var fullName = (student.first_name || '') + ' ' +
                                       (student.middle_name ? student.middle_name + ' ' : '') +
                                       (student.last_name || '');
                        fullName = fullName.trim() || 'N/A';
                        
                        studentList.push({
                            name: fullName,
                            admissionNumber: student.admission_number || 'N/A',
                            className: student.subclass_name || (classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name) || 'N/A',
                            checkInTime: student.check_in_time || '--',
                            checkOutTime: student.check_out_time || '--'
                        });
                    });
                }
            });
            
            studentList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });
            
            // Add header with logo and centered titles
            var schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';
            
            function drawHeaderAndTable(logoImg) {
                var pageWidth = doc.internal.pageSize.getWidth();
                var centerX = pageWidth / 2;
                
                // Logo on the left if available
                if (logoImg) {
                    try {
                        doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                    } catch (e) {
                        console.warn('Failed to add logo to PDF:', e);
                    }
                }
                
                // School name and report title centered
                doc.setFontSize(16);
                doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
                doc.setFontSize(12);
                doc.text(reportTitle, centerX, 26, { align: 'center' });
                
                // Prepare table data
                var tableData = [];
                studentList.forEach(function(student) {
                    tableData.push([student.name, student.className, student.admissionNumber, student.checkInTime, student.checkOutTime]);
                });
                
                // Add table
                doc.autoTable({
                    startY: 36,
                    head: [['Student Name', 'Class', 'Admission Number', 'Check In Time', 'Check Out Time']],
                    body: tableData,
                    theme: 'striped',
                    headStyles: { fillColor: [148, 0, 0] },
                    didDrawPage: function (data) {
                        // Footer on each page
                        var pageHeight = doc.internal.pageSize.getHeight();
                        doc.setFontSize(9);
                        doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                    }
                });
                
                // Save PDF
                var fileName = 'Fingerprint_Attendance_' + currentFingerprintSearchDate.replace(/-/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf';
                doc.save(fileName);
            }
            
            if (schoolLogoUrl) {
                var img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    drawHeaderAndTable(img);
                };
                img.onerror = function() {
                    console.warn('Failed to load school logo image for PDF header.');
                    drawHeaderAndTable(null);
                };
                img.src = schoolLogoUrl;
            } else {
                drawHeaderAndTable(null);
            }
            return;
        }

        var workingDays = 0;
        var current = new Date(startDate);
        while (current <= endDate) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        // Get selected class
        var selectedClassId = document.getElementById('searchFingerprintClasses') ? document.getElementById('searchFingerprintClasses').value : '';
        var selectedClassName = '';
        
        // Collect students from selected class only
        var studentMap = {};
        currentFingerprintAttendanceData.forEach(function(classData) {
            // If a class is selected, only export that class
            if (selectedClassId && classData.subclassID != selectedClassId) {
                return;
            }
            
            // Store selected class name for report title
            if (selectedClassId && classData.subclassID == selectedClassId) {
                selectedClassName = classData.subclass_name;
            }
            
            if (classData.students && classData.students.length > 0) {
                classData.students.forEach(function(student) {
                    var studentId = student.studentID || '';
                    if (!studentId) return;

                    var fullName = (student.first_name || '') + ' ' +
                                   (student.middle_name ? student.middle_name + ' ' : '') +
                                   (student.last_name || '');
                    fullName = fullName.trim() || 'N/A';

                    if (!studentMap[studentId]) {
                        studentMap[studentId] = {
                            name: fullName,
                            className: student.subclass_name || (classData.display_name || (classData.class_name + ' ' + classData.subclass_name) || classData.subclass_name) || 'N/A',
                            daysPresent: 0,
                            daysAbsent: 0
                        };
                    }
                    studentMap[studentId].daysPresent = student.days_present || 0;
                    studentMap[studentId].daysAbsent = student.days_absent || 0;
                });
            }
        });
        
        // Update report title if class is selected
        if (selectedClassName) {
            reportTitle = reportTitle + ' - ' + selectedClassName.toUpperCase();
        }

        var studentList = Object.values(studentMap);
        studentList.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Add header with logo and centered titles
        var schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';

        function drawHeaderAndTable(logoImg) {
            var pageWidth = doc.internal.pageSize.getWidth();
            var centerX = pageWidth / 2;

            // Logo on the left if available
            if (logoImg) {
                try {
                    doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                } catch (e) {
                    console.warn('Failed to add logo to PDF:', e);
                }
            }

            // School name and report title centered
            doc.setFontSize(16);
            doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
            doc.setFontSize(12);
            doc.text(reportTitle, centerX, 26, { align: 'center' });

            // Prepare table data
            var tableData = [];
            studentList.forEach(function(student) {
                tableData.push([student.name, student.className, student.daysPresent, student.daysAbsent, workingDays]);
            });

            // Add table
            doc.autoTable({
                startY: 36,
                head: [['Student Name', 'Class', 'Days Present', 'Days Absent', 'Total Days']],
                body: tableData,
                theme: 'striped',
                headStyles: { fillColor: [148, 0, 0] },
                didDrawPage: function (data) {
                    // Footer on each page
                    var pageHeight = doc.internal.pageSize.getHeight();
                    doc.setFontSize(9);
                    doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                }
            });

            // Save PDF
            doc.save('Fingerprint_Attendance_' + (currentFingerprintSearchMonth || currentFingerprintSearchYear || 'Report') + '_' + new Date().toISOString().split('T')[0] + '.pdf');
        }

        if (schoolLogoUrl) {
            var img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function() {
                drawHeaderAndTable(img);
            };
            img.onerror = function() {
                console.warn('Failed to load school logo image for PDF header.');
                drawHeaderAndTable(null);
            };
            img.src = schoolLogoUrl;
        } else {
            drawHeaderAndTable(null);
        }
    }

    // Bind export buttons
    document.getElementById('exportFingerprintExcelBtn').addEventListener('click', function() {
        exportFingerprintAttendanceToExcel();
    });

    document.getElementById('exportFingerprintPdfBtn').addEventListener('click', function() {
        exportFingerprintAttendanceToPdf();
    });

    // Export specific class fingerprint attendance to Excel
    function exportClassFingerprintAttendanceToExcel(subclassId, subclassName) {
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Excel export library not loaded', 'error');
            return;
        }

        if (!currentFingerprintAttendanceData || currentFingerprintAttendanceData.length === 0) {
            Swal.fire('Error', 'No data to export. Please search attendance first.', 'error');
            return;
        }

        // Find the class data
        var classData = currentFingerprintAttendanceData.find(function(c) {
            return c.subclassID == subclassId;
        });

        if (!classData || !classData.students || classData.students.length === 0) {
            Swal.fire('Error', 'No students found for this class.', 'error');
            return;
        }

        // Get school name and build title
        var schoolName = '{{ $school_details->school_name ?? "School" }}';
        var reportTitle = '';
        if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0] + ' - ' + subclassName.toUpperCase();
        } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + currentFingerprintSearchYear + ' - ' + subclassName.toUpperCase();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            var dateObj = new Date(currentFingerprintSearchDate);
            var dateFormatted = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE ON ' + dateFormatted.toUpperCase() + ' - ' + subclassName.toUpperCase();
        } else {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE - ' + subclassName.toUpperCase();
        }

        // Calculate working days
        var startDate, endDate;
        if (currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
            endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchYear) {
            startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
            endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            // For date view, single day
            startDate = endDate = new Date(currentFingerprintSearchDate);
        } else {
            Swal.fire('Error', 'No valid search period selected.', 'error');
            return;
        }

        var workingDays = 0;
        var current = new Date(startDate);
        while (current <= endDate) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        // Prepare student list
        var studentList = [];
        if (currentFingerprintSearchType === 'date') {
            // For date view, include check in/out times
            studentList = classData.students.map(function(student) {
                var fullName = (student.first_name || '') + ' ' +
                               (student.middle_name ? student.middle_name + ' ' : '') +
                               (student.last_name || '');
                return {
                    name: fullName.trim() || 'N/A',
                    admissionNumber: student.admission_number || 'N/A',
                    checkInTime: student.check_in_time || '--',
                    checkOutTime: student.check_out_time || '--'
                };
            });
        } else {
            // For month/year view, include days present/absent
            studentList = classData.students.map(function(student) {
                var fullName = (student.first_name || '') + ' ' +
                               (student.middle_name ? student.middle_name + ' ' : '') +
                               (student.last_name || '');
                return {
                    name: fullName.trim() || 'N/A',
                    daysPresent: student.days_present || 0,
                    daysAbsent: student.days_absent || 0
                };
            });
        }

        studentList.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Create workbook
        var wb = XLSX.utils.book_new();
        var wsData = [];

        // Header rows
        wsData.push([schoolName]);
        wsData.push([reportTitle]);
        wsData.push([]);
        
        if (currentFingerprintSearchType === 'date') {
            wsData.push(['Student Name', 'Admission Number', 'Check In Time', 'Check Out Time']);
            // Student data
            studentList.forEach(function(student) {
                wsData.push([student.name, student.admissionNumber, student.checkInTime, student.checkOutTime]);
            });
        } else {
            wsData.push(['Student Name', 'Days Present', 'Days Absent', 'Total Days']);
            // Student data
            studentList.forEach(function(student) {
                wsData.push([student.name, student.daysPresent, student.daysAbsent, workingDays]);
            });
        }

        var ws = XLSX.utils.aoa_to_sheet(wsData);
        if (!ws['!merges']) ws['!merges'] = [];
        var colCount = currentFingerprintSearchType === 'date' ? 3 : 3;
        ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: colCount}});
        ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: colCount}});

        XLSX.utils.book_append_sheet(wb, ws, 'Fingerprint Attendance');
        var fileName = 'Fingerprint_Attendance_' + subclassName.replace(/\s+/g, '_') + '_';
        if (currentFingerprintSearchMonth) {
            fileName += currentFingerprintSearchMonth.replace(/-/g, '_');
        } else if (currentFingerprintSearchYear) {
            fileName += currentFingerprintSearchYear;
        } else if (currentFingerprintSearchDate) {
            fileName += currentFingerprintSearchDate.replace(/-/g, '_');
        } else {
            fileName += 'Report';
        }
        fileName += '_' + new Date().toISOString().split('T')[0] + '.xlsx';
        XLSX.writeFile(wb, fileName);
    }

    // Export specific class fingerprint attendance to PDF
    function exportClassFingerprintAttendanceToPdf(subclassId, subclassName) {
        if (typeof window.jspdf === 'undefined') {
            Swal.fire('Error', 'PDF export library not loaded', 'error');
            return;
        }

        if (!currentFingerprintAttendanceData || currentFingerprintAttendanceData.length === 0) {
            Swal.fire('Error', 'No data to export. Please search attendance first.', 'error');
            return;
        }

        // Find the class data
        var classData = currentFingerprintAttendanceData.find(function(c) {
            return c.subclassID == subclassId;
        });

        if (!classData || !classData.students || classData.students.length === 0) {
            Swal.fire('Error', 'No students found for this class.', 'error');
            return;
        }

        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('landscape');

        // Get school name and build title
        var schoolName = '{{ $school_details->school_name ?? "School" }}';
        var reportTitle = '';
        if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0] + ' - ' + subclassName.toUpperCase();
        } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE IN ' + currentFingerprintSearchYear + ' - ' + subclassName.toUpperCase();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            var dateObj = new Date(currentFingerprintSearchDate);
            var dateFormatted = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE ON ' + dateFormatted.toUpperCase() + ' - ' + subclassName.toUpperCase();
        } else {
            reportTitle = 'STUDENT FINGERPRINT ATTENDANCE - ' + subclassName.toUpperCase();
        }

        // Calculate working days
        var startDate, endDate;
        if (currentFingerprintSearchMonth) {
            var monthParts = currentFingerprintSearchMonth.split('-');
            startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
            endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchYear) {
            startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
            endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
            if (endDate > new Date()) endDate = new Date();
        } else if (currentFingerprintSearchType === 'date' && currentFingerprintSearchDate) {
            // For date view, single day
            startDate = endDate = new Date(currentFingerprintSearchDate);
        } else {
            Swal.fire('Error', 'No valid search period selected.', 'error');
            return;
        }

        var workingDays = 0;
        var current = new Date(startDate);
        while (current <= endDate) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        // Prepare student list
        var studentList = [];
        if (currentFingerprintSearchType === 'date') {
            // For date view, include check in/out times
            studentList = classData.students.map(function(student) {
                var fullName = (student.first_name || '') + ' ' +
                               (student.middle_name ? student.middle_name + ' ' : '') +
                               (student.last_name || '');
                return {
                    name: fullName.trim() || 'N/A',
                    admissionNumber: student.admission_number || 'N/A',
                    checkInTime: student.check_in_time || '--',
                    checkOutTime: student.check_out_time || '--'
                };
            });
        } else {
            // For month/year view, include days present/absent
            studentList = classData.students.map(function(student) {
                var fullName = (student.first_name || '') + ' ' +
                               (student.middle_name ? student.middle_name + ' ' : '') +
                               (student.last_name || '');
                return {
                    name: fullName.trim() || 'N/A',
                    daysPresent: student.days_present || 0,
                    daysAbsent: student.days_absent || 0
                };
            });
        }

        studentList.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Add header with logo and centered titles
        var schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';

        function drawHeaderAndTable(logoImg) {
            var pageWidth = doc.internal.pageSize.getWidth();
            var centerX = pageWidth / 2;

            // Logo on the left if available
            if (logoImg) {
                try {
                    doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                } catch (e) {
                    console.warn('Failed to add logo to PDF:', e);
                }
            }

            // School name and report title centered
            doc.setFontSize(16);
            doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
            doc.setFontSize(12);
            doc.text(reportTitle, centerX, 26, { align: 'center' });

            // Prepare table data
            var tableData = [];
            if (currentFingerprintSearchType === 'date') {
                studentList.forEach(function(student) {
                    tableData.push([student.name, student.admissionNumber, student.checkInTime, student.checkOutTime]);
                });
            } else {
                studentList.forEach(function(student) {
                    tableData.push([student.name, student.daysPresent, student.daysAbsent, workingDays]);
                });
            }

            // Add table
            var tableHeaders = currentFingerprintSearchType === 'date' 
                ? [['Student Name', 'Admission Number', 'Check In Time', 'Check Out Time']]
                : [['Student Name', 'Days Present', 'Days Absent', 'Total Days']];
            
            doc.autoTable({
                startY: 36,
                head: tableHeaders,
                body: tableData,
                theme: 'striped',
                headStyles: { fillColor: [148, 0, 0] },
                didDrawPage: function (data) {
                    // Footer on each page
                    var pageHeight = doc.internal.pageSize.getHeight();
                    doc.setFontSize(9);
                    doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                }
            });

            // Save PDF
            var fileName = 'Fingerprint_Attendance_' + subclassName.replace(/\s+/g, '_') + '_';
            if (currentFingerprintSearchMonth) {
                fileName += currentFingerprintSearchMonth.replace(/-/g, '_');
            } else if (currentFingerprintSearchYear) {
                fileName += currentFingerprintSearchYear;
            } else if (currentFingerprintSearchDate) {
                fileName += currentFingerprintSearchDate.replace(/-/g, '_');
            } else {
                fileName += 'Report';
            }
            fileName += '_' + new Date().toISOString().split('T')[0] + '.pdf';
            doc.save(fileName);
            doc.save('Fingerprint_Attendance_' + subclassName.replace(/\s+/g, '_') + '_' + (currentFingerprintSearchMonth || currentFingerprintSearchYear || 'Report') + '_' + new Date().toISOString().split('T')[0] + '.pdf');
        }

        if (schoolLogoUrl) {
            var img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function() {
                drawHeaderAndTable(img);
            };
            img.onerror = function() {
                console.warn('Failed to load school logo image for PDF header.');
                drawHeaderAndTable(null);
            };
            img.src = schoolLogoUrl;
        } else {
            drawHeaderAndTable(null);
        }
    }

    // ==================== EXAM ATTENDANCE SECTION ====================
    let examStudentsTable = null;
    let examAllStudentsData = [];

    // Load main classes when page loads
    $(document).ready(function() {
        loadExamMainClasses();
    });

    // Load main classes
    function loadExamMainClasses() {
        $.get('/admin/get-classes')
            .done(function(response) {
                if (response.success && response.classes) {
                    let options = '<option value="">All Classes</option>';
                    response.classes.forEach(function(cls) {
                        options += `<option value="${cls.classID}">${cls.class_name}</option>`;
                    });
                    $('#examFilterMainClass').html(options);
                }
            })
            .fail(function() {
                console.error('Error loading classes');
            });
    }

    // When year is selected, load terms from database and enable term
    $('#examFilterYear').on('change', function() {
        const year = $(this).val();
        if (year) {
            loadTermsForYear(year);
        } else {
            $('#examFilterTerm').prop('disabled', true).html('<option value="">Select Term</option>');
            $('#examFilterExam').prop('disabled', true).html('<option value="">Select Exam</option>');
            hideExamAttendanceData();
        }
    });

    // Load terms for year from database
    function loadTermsForYear(year) {
        $.get('/admin/get-terms-for-year', { year: year })
            .done(function(response) {
                if (response.success && response.terms) {
                    let options = '<option value="">Select Term</option>';
                    response.terms.forEach(function(term) {
                        // Format term: first_term -> First Term
                        const termDisplay = term.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                        options += `<option value="${term}">${termDisplay}</option>`;
                    });
                    $('#examFilterTerm').html(options).prop('disabled', false);
                } else {
                    $('#examFilterTerm').html('<option value="">No terms available</option>').prop('disabled', false);
                }
            })
            .fail(function() {
                $('#examFilterTerm').html('<option value="">Error loading terms</option>').prop('disabled', false);
            });
    }

    // When term is selected, load exams
    $('#examFilterTerm').on('change', function() {
        const year = $('#examFilterYear').val();
        const term = $(this).val();
        if (year && term) {
            $('#examFilterExam').prop('disabled', false);
            loadExamsForYearTerm(year, term);
        } else {
            $('#examFilterExam').prop('disabled', true).html('<option value="">Select Exam</option>');
            hideExamAttendanceData();
        }
    });

    // When main class is selected, load subclasses and auto-load data if exam is selected
    $('#examFilterMainClass').on('change', function() {
        const classID = $(this).val();
        if (classID) {
            $('#examFilterSubclass').prop('disabled', false);
            loadSubclassesForClass(classID);
        } else {
            $('#examFilterSubclass').prop('disabled', true).html('<option value="">All Subclasses</option>');
        }
        
        // Auto-load data if exam is already selected
        const examID = $('#examFilterExam').val();
        if (examID) {
            loadExamAttendanceData();
        }
    });

    // When exam is selected, load subjects and attendance
    $('#examFilterExam').on('change', function() {
        const examID = $(this).val();
        if (examID) {
            loadSubjectsForClass();
            loadExamAttendanceData();
        } else {
            $('#examFilterSubject').html('<option value="">All Subjects</option>');
            hideExamAttendanceData();
        }
    });

    // When class or subclass changes, reload subjects
    $('#examFilterMainClass, #examFilterSubclass').on('change', function() {
        const examID = $('#examFilterExam').val();
        if (examID) {
            loadSubjectsForClass();
        }
    });

    // When subject is selected, reload attendance
    $('#examFilterSubject').on('change', function() {
        const examID = $('#examFilterExam').val();
        if (examID) {
            loadExamAttendanceData();
        }
    });

    // Load subjects for selected class
    function loadSubjectsForClass() {
        const mainClassID = $('#examFilterMainClass').val();
        const subclassID = $('#examFilterSubclass').val();
        const examID = $('#examFilterExam').val();

        if (!examID) {
            $('#examFilterSubject').html('<option value="">All Subjects</option>');
            return;
        }

        $.get('/admin/get-subjects-for-class', {
            examID: examID,
            mainClassID: mainClassID || '',
            subclassID: subclassID || ''
        })
            .done(function(response) {
                if (response.success && response.subjects) {
                    let options = '<option value="">All Subjects</option>';
                    response.subjects.forEach(function(subject) {
                        options += `<option value="${subject.subjectID}">${subject.subject_name}${subject.subject_code ? ' (' + subject.subject_code + ')' : ''}</option>`;
                    });
                    $('#examFilterSubject').html(options);
                } else {
                    $('#examFilterSubject').html('<option value="">All Subjects</option>');
                }
            })
            .fail(function() {
                $('#examFilterSubject').html('<option value="">All Subjects</option>');
            });
    }

    // When subclass is selected, auto-load data if exam is selected
    $('#examFilterSubclass').on('change', function() {
        const examID = $('#examFilterExam').val();
        if (examID) {
            loadExamAttendanceData();
        }
    });

    // Load exams for year and term
    function loadExamsForYearTerm(year, term) {
        $.get('/admin/get-exams-for-year-term', {
            year: year,
            term: term
        })
        .done(function(response) {
            if (response.success && response.exams) {
                let options = '<option value="">Select Exam</option>';
                response.exams.forEach(function(exam) {
                    options += `<option value="${exam.examID}">${exam.exam_name}</option>`;
                });
                $('#examFilterExam').html(options);
            }
        })
        .fail(function() {
            $('#examFilterExam').html('<option value="">Error loading exams</option>');
        });
    }

    // Load subclasses for class
    function loadSubclassesForClass(classID) {
        $.get('/admin/get-subclasses-for-class', { classID: classID })
            .done(function(response) {
                if (response.success && response.subclasses) {
                    let options = '<option value="">All Subclasses</option>';
                    response.subclasses.forEach(function(sub) {
                        options += `<option value="${sub.subclassID}">${sub.subclass_name}</option>`;
                    });
                    $('#examFilterSubclass').html(options);
                }
            })
            .fail(function() {
                console.error('Error loading subclasses');
            });
    }

    // Load exam attendance data
    function loadExamAttendanceData() {
        const examID = $('#examFilterExam').val();
        const mainClassID = $('#examFilterMainClass').val();
        const subclassID = $('#examFilterSubclass').val();
        const subjectID = $('#examFilterSubject').val();

        if (!examID) {
            hideExamAttendanceData();
            return;
        }

        // Show loading
        $('#examStudentsTableBody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div></td></tr>');
        $('#examAttendanceStats').show();
        $('#examStudentsTableContainer').show();

        $.get('/admin/get-exam-attendance-data', {
            examID: examID,
            mainClassID: mainClassID || '',
            subclassID: subclassID || '',
            subjectID: subjectID || ''
        })
        .done(function(response) {
            if (response.success && response.data) {
                displayExamAttendanceData(response.data);
            } else {
                $('#examStudentsTableBody').html('<tr><td colspan="6" class="text-center text-muted">No attendance data available</td></tr>');
                hideExamAttendanceStats();
            }
        })
        .fail(function() {
            $('#examStudentsTableBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading attendance data</td></tr>');
            hideExamAttendanceStats();
        });
    }

    // Display exam attendance data
    function displayExamAttendanceData(data) {
        // Store all students data with subjects information
        examAllStudentsData = (data.students || []).map(function(student) {
            return {
                studentID: student.studentID,
                name: student.name || 'N/A',
                class_display: student.class_display || (student.class_name + ' - ' + student.subclass_name) || 'N/A',
                status: student.status || 'Absent',
                subjects_taken: student.subjects_taken || 0,
                total_subjects: student.total_subjects || 0,
                missed_subjects: student.missed_subjects || []
            };
        });

        // Calculate statistics
        let totalPresent = 0;
        let totalAbsent = 0;
        let totalStudents = examAllStudentsData.length;

        examAllStudentsData.forEach(function(student) {
            if (student.status === 'Present' || student.status === 'present') {
                totalPresent++;
            } else {
                totalAbsent++;
            }
        });

        // Update statistics widgets
        $('#examTotalPresent').text(totalPresent);
        $('#examTotalAbsent').text(totalAbsent);
        $('#examTotalStudents').text(totalStudents);

        // Show statistics
        $('#examAttendanceStats').show();

        // Display all students
        displayExamStudentsTable('all');
    }

    // Display students table
    function displayExamStudentsTable(filter) {
        // Destroy existing DataTable
        if (examStudentsTable) {
            examStudentsTable.destroy();
            examStudentsTable = null;
        }

        // Filter students
        let filteredStudents = examAllStudentsData;
        if (filter === 'present') {
            filteredStudents = examAllStudentsData.filter(s => s.status === 'Present' || s.status === 'present');
        } else if (filter === 'absent') {
            filteredStudents = examAllStudentsData.filter(s => {
                const status = (s.status || '').toLowerCase();
                return status !== 'present';
            });
        }

        const tbody = $('#examStudentsTableBody');
        tbody.empty();

        if (filteredStudents.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center text-muted">No students found</td></tr>');
            $('#examStudentsTableContainer').show();
            return;
        }

        filteredStudents.forEach(function(student, index) {
            // Check if student has taken any subjects
            const subjectsTaken = student.subjects_taken || 0;
            const totalSubjects = student.total_subjects || 0;
            const hasTakenAnySubject = subjectsTaken > 0;
            
            // Highlight students who haven't taken any subject (red background)
            const rowClass = !hasTakenAnySubject ? 'bg-danger text-white' : '';
            const statusBadge = hasTakenAnySubject 
                ? `<span class="badge bg-success">${subjectsTaken}/${totalSubjects} Subjects</span>` 
                : '<span class="badge bg-danger">0/' + totalSubjects + ' Subjects</span>';
            
            // View More button (only show if student has missed subjects)
            const viewMoreBtn = student.missed_subjects && student.missed_subjects.length > 0
                ? `<button class="btn btn-sm btn-info" onclick="viewStudentMissedSubjects(${student.studentID}, '${student.name.replace(/'/g, "\\'")}')" title="View missed subjects">
                    <i class="bi bi-eye"></i> View More
                   </button>`
                : '<span class="text-muted">-</span>';

            const row = `
                <tr class="${rowClass}">
                    <td>${index + 1}</td>
                    <td><strong>${student.name || 'N/A'}</strong></td>
                    <td>${student.class_display || 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${hasTakenAnySubject ? '<span class="badge bg-success">Present</span>' : '<span class="badge bg-danger">Absent</span>'}</td>
                    <td>${viewMoreBtn}</td>
                </tr>
            `;
            tbody.append(row);
        });

        // Initialize DataTable
        examStudentsTable = $('#examStudentsTable').DataTable({
            responsive: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            order: [[1, 'asc']]
        });

        $('#examStudentsTableContainer').show();
    }

    // Click handlers for statistics widgets
    $(document).on('click', '.clickable-stat-exam', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const filter = $(this).data('filter');
        const examID = $('#examFilterExam').val();
        
        if (!examID) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Select Exam First',
                    text: 'Please select an exam to view attendance data.',
                    icon: 'info',
                    confirmButtonColor: '#940000'
                });
            }
            return;
        }

        if (examAllStudentsData.length === 0) {
            // Load data first
            loadExamAttendanceData();
            // Wait for data to load, then filter
            setTimeout(function() {
                if (examAllStudentsData.length > 0) {
                    displayExamStudentsTable(filter);
                }
            }, 1000);
        } else {
            // Show loading
            $('#examStudentsTableBody').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div></td></tr>');
            $('#examStudentsTableContainer').show();
            
            // Scroll to table
            $('html, body').animate({
                scrollTop: $('#examStudentsTableContainer').offset().top - 100
            }, 500);
            
            // Display filtered data
            setTimeout(function() {
                displayExamStudentsTable(filter);
            }, 300);
        }
    });

    // Hide exam attendance data
    function hideExamAttendanceData() {
        $('#examAttendanceStats').hide();
        $('#examStudentsTableContainer').hide();
        if (examStudentsTable) {
            examStudentsTable.destroy();
            examStudentsTable = null;
        }
        examAllStudentsData = [];
    }

    function hideExamAttendanceStats() {
        $('#examAttendanceStats').hide();
    }

    // View student missed subjects
    function viewStudentMissedSubjects(studentID, studentName) {
        const examID = $('#examFilterExam').val();
        
        if (!examID) {
            Swal.fire({
                title: 'Error',
                text: 'Please select an exam first',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            return;
        }

        // Show loading
        $('#studentMissedSubjectsModalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div></div>');
        $('#studentMissedSubjectsModal').modal('show');
        $('#studentMissedSubjectsModalLabel').html(`<i class="bi bi-person"></i> ${studentName} - Missed Subjects`);

        // Load missed subjects
        $.get('/admin/get-student-missed-subjects', {
            studentID: studentID,
            examID: examID
        })
        .done(function(response) {
            if (response.success && response.missed_subjects) {
                let html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
                html += '<thead class="table-light"><tr><th>#</th><th>Subject Name</th><th>Subject Code</th><th>Status</th></tr></thead><tbody>';
                
                if (response.missed_subjects.length > 0) {
                    response.missed_subjects.forEach(function(subject, index) {
                        html += `
                            <tr class="bg-danger text-white">
                                <td>${index + 1}</td>
                                <td><strong>${subject.subject_name || 'N/A'}</strong></td>
                                <td>${subject.subject_code || 'N/A'}</td>
                                <td><span class="badge bg-danger">Not Taken</span></td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="4" class="text-center text-muted">No missed subjects</td></tr>';
                }
                
                html += '</tbody></table></div>';
                $('#studentMissedSubjectsModalBody').html(html);
            } else {
                $('#studentMissedSubjectsModalBody').html('<div class="alert alert-info">No missed subjects found</div>');
            }
        })
        .fail(function() {
            $('#studentMissedSubjectsModalBody').html('<div class="alert alert-danger">Error loading missed subjects</div>');
        });
    }
</script>

<!-- Student Missed Subjects Modal -->
<div class="modal fade" id="studentMissedSubjectsModal" tabindex="-1" aria-labelledby="studentMissedSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="studentMissedSubjectsModalLabel">
                    <i class="bi bi-person"></i> Missed Subjects
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentMissedSubjectsModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
