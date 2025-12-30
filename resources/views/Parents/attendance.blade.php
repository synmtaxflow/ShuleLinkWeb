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

    /* Overview Cards */
    .overview-card {
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

    .overview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .overview-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .overview-title i {
        margin-right: 10px;
        color: var(--primary-color);
    }

    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 0;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        text-align: center;
        border: 1px solid #e9ecef;
        width: 100%;
        max-width: 100%;
    }

    .stat-card.bg-success {
        background: #28a745;
        color: white;
        border: none;
    }

    .stat-card.bg-danger {
        background: #dc3545;
        color: white;
        border: none;
    }

    .stat-card.bg-warning {
        background: #ffc107;
        color: #212529;
        border: none;
    }

    .stat-card.bg-info {
        background: #17a2b8;
        color: white;
        border: none;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin: 8px 0;
    }

    .stat-label {
        font-size: 0.9rem;
        font-weight: 500;
        margin: 0;
        opacity: 0.9;
    }

    /* View More Dropdown */
    .view-more-section {
        margin-top: 0;
        margin-bottom: 20px;
    }

    .view-more-btn {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        padding: 10px 20px;
        border-radius: 0;
        font-weight: 500;
        width: 100%;
        transition: all 0.2s ease;
    }

    .view-more-btn:hover {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
        color: white;
    }

    .view-more-content {
        display: none;
        margin-top: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 0;
        border: 1px solid #dee2e6;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .view-more-content.show {
        display: block;
    }

    /* Overview Section - Hide when view more is open */
    .overview-section.hidden {
        display: none !important;
    }

    /* Daily Records Table */
    .daily-record-item {
        background: white;
        border-radius: 0;
        padding: 12px 15px;
        margin-bottom: 10px;
        border-left: 4px solid #e9ecef;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
        width: 100%;
        max-width: 100%;
        word-wrap: break-word;
    }

    @media (min-width: 769px) {
        .daily-record-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }
    }

    .daily-record-item.border-success {
        border-left-color: #28a745;
    }

    .daily-record-item.border-danger {
        border-left-color: #dc3545;
    }

    .daily-record-item.border-warning {
        border-left-color: #ffc107;
    }

    .daily-record-item.border-info {
        border-left-color: #17a2b8;
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

    /* Summary Card */
    .summary-card {
        background: white;
        border-radius: 0;
        padding: 20px;
        margin-top: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
        width: 100%;
        max-width: 100%;
    }

    /* Chart Card */
    .chart-card {
        background: white;
        border-radius: 0;
        padding: 20px;
        margin-top: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-weight: 500;
        color: #6c757d;
    }

    .summary-value {
        font-weight: 600;
        color: #212529;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 10px !important;
            max-width: 100%;
            overflow-x: hidden;
        }

        .row {
            margin-left: 0;
            margin-right: 0;
        }

        .row > * {
            padding-left: 8px;
            padding-right: 8px;
        }

        .stat-card {
            padding: 10px;
            margin-bottom: 8px;
            width: 100%;
        }

        .stat-number {
            font-size: 1.3rem;
            margin: 4px 0;
        }

        .stat-label {
            font-size: 0.65rem;
        }

        .overview-card {
            padding: 12px;
            width: 100%;
            max-width: 100%;
        }

        .summary-card {
            padding: 12px;
            width: 100%;
        }

        .chart-card {
            padding: 12px;
            width: 100%;
        }

        .chart-card > div {
            height: 200px !important;
            max-width: 100%;
        }

        .filter-card {
            padding: 12px;
            width: 100%;
        }

        .form-label {
            font-size: 0.8rem;
            margin-bottom: 5px;
        }

        .form-control, .form-select {
            font-size: 0.85rem;
            padding: 6px 10px;
        }

        .section-header {
            font-size: 1.1rem;
            margin: 15px 0 10px 0;
        }

        .overview-title {
            font-size: 1rem;
        }

        .daily-record-item {
            padding: 8px 10px;
            font-size: 0.8rem;
            width: 100%;
        }

        .btn-filter, .btn-reset {
            padding: 8px 16px;
            font-size: 0.85rem;
            width: 100%;
            margin-bottom: 8px;
        }

        .view-more-btn {
            font-size: 0.85rem;
            padding: 8px 16px;
        }

        .view-more-content {
            padding: 12px;
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

        .stat-card {
            padding: 8px;
            margin-bottom: 6px;
        }

        .stat-number {
            font-size: 1.1rem;
            margin: 3px 0;
        }

        .stat-label {
            font-size: 0.6rem;
        }

        .chart-card > div {
            height: 160px !important;
        }

        .overview-card {
            padding: 10px;
        }

        .summary-card {
            padding: 10px;
        }

        .filter-card {
            padding: 10px;
        }

        .section-header {
            font-size: 1rem;
            margin: 10px 0 8px 0;
        }

        .overview-title {
            font-size: 0.95rem;
        }

        .summary-item {
            padding: 6px 0;
            font-size: 0.8rem;
        }

        .filter-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .overview-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .daily-record-item .d-flex {
            flex-direction: column !important;
        }

        .daily-record-item .mb-2 {
            margin-bottom: 8px !important;
        }
    }

    /* Prevent horizontal scroll */
    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }

    .row {
        max-width: 100%;
    }

    [class*="col-"] {
        max-width: 100%;
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
                <i class="bi bi-calendar-check"></i> Attendance Records
            </h2>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="filter-header">
                    <h3 class="filter-title">
                        <i class="bi bi-funnel"></i> Filter Attendance
                    </h3>
                </div>
                <form method="GET" action="{{ route('parentAttendance') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6 col-12">
                            <label class="form-label">
                                <i class="bi bi-person"></i> Select Student
                            </label>
                            <select name="student" class="form-select" id="studentFilter">
                                <option value="">All Students</option>
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
                        <div class="col-md-2 col-sm-6 col-12">
                            <label class="form-label">
                                <i class="bi bi-calendar"></i> Search Type
                            </label>
                            <select name="search_type" class="form-select" id="searchType">
                                <option value="month" {{ $searchType == 'month' ? 'selected' : '' }}>By Month</option>
                                <option value="year" {{ $searchType == 'year' ? 'selected' : '' }}>By Year</option>
                                <option value="date" {{ $searchType == 'date' ? 'selected' : '' }}>By Date</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 col-12" id="yearField">
                            <label class="form-label">
                                <i class="bi bi-calendar-year"></i> Year
                            </label>
                            <select name="year" class="form-select" id="yearFilter">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $yearFilter == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 col-12" id="monthField">
                            <label class="form-label">
                                <i class="bi bi-calendar-month"></i> Month
                            </label>
                            <select name="month" class="form-select" id="monthFilter">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $monthFilter == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12" id="dateField" style="display: none;">
                            <label class="form-label">
                                <i class="bi bi-calendar-date"></i> Date
                            </label>
                            <input type="date" name="date" class="form-control" id="dateFilter" value="{{ $dateFilter }}">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-filter">
                                    <i class="bi bi-search me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('parentAttendance') }}" class="btn btn-reset">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Overview Section -->
    @if($overviewData && $studentFilter)
    <div class="row mb-4">
        <div class="col-12">
            <div class="overview-card">
                <div class="overview-header">
                    <h3 class="overview-title">
                        <i class="bi bi-bar-chart"></i> Attendance Overview
                    </h3>
                </div>

                <!-- View More Button - At the top -->
                @if($searchType != 'date' && $dailyRecords->count() > 0)
                <div class="view-more-section" style="margin-top: 0; margin-bottom: 20px;">
                    <button type="button" class="btn view-more-btn" onclick="toggleViewMore()">
                        <i class="bi bi-chevron-down me-1"></i> View More Details
                    </button>
                </div>
                @endif

                <!-- Overview Section - Hide when view more is open -->
                <div class="overview-section" id="overviewSection">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 col-6 mb-3">
                            <div class="stat-card bg-success">
                                <div class="stat-number">{{ $overviewData['present'] }}</div>
                                <div class="stat-label">Present</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-6 mb-3">
                            <div class="stat-card bg-danger">
                                <div class="stat-number">{{ $overviewData['absent'] }}</div>
                                <div class="stat-label">Absent</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-6 mb-3">
                            <div class="stat-card bg-warning">
                                <div class="stat-number">{{ $overviewData['late'] }}</div>
                                <div class="stat-label">Late</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-6 mb-3">
                            <div class="stat-card bg-info">
                                <div class="stat-number">{{ $overviewData['excused'] }}</div>
                                <div class="stat-label">Excused</div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="summary-card">
                        <h5 class="mb-3"><i class="bi bi-info-circle"></i> Summary</h5>
                        <div class="summary-item">
                            <span class="summary-label">Total Days:</span>
                            <span class="summary-value">{{ $overviewData['total_days'] }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Present Days:</span>
                            <span class="summary-value">{{ $overviewData['present'] }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Absent Days:</span>
                            <span class="summary-value">{{ $overviewData['absent'] }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Attendance Rate:</span>
                            <span class="summary-value">{{ $overviewData['attendance_rate'] }}%</span>
                        </div>
                    </div>

                    <!-- Bar Chart -->
                    @if($searchType != 'date')
                    <div class="chart-card">
                        <h5 class="mb-3"><i class="bi bi-bar-chart"></i> Attendance Statistics</h5>
                        <div style="position: relative; height: 250px; max-height: 250px;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- View More Content - Daily Records Below Overview -->
                @if($searchType != 'date' && $dailyRecords->count() > 0)
                <div class="view-more-content" id="viewMoreContent">
                    <h6 class="mb-3"><i class="bi bi-calendar3"></i> Daily Records</h6>
                    @foreach($dailyRecords->groupBy('attendance_date') as $date => $records)
                        @php
                            $dateObj = \Carbon\Carbon::parse($date);
                            $status = $records->first()->status;
                            $statusColors = [
                                'Present' => 'success',
                                'Absent' => 'danger',
                                'Late' => 'warning',
                                'Excused' => 'info'
                            ];
                            $color = $statusColors[$status] ?? 'secondary';
                        @endphp
                        <div class="daily-record-item border-{{ $color }}">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                                <div class="mb-2 mb-sm-0">
                                    <strong>{{ $dateObj->format('l, F d, Y') }}</strong>
                                    <span class="badge bg-{{ $color }} ms-2">{{ $status }}</span>
                                </div>
                                @if($records->first()->remark)
                                    <small class="text-muted text-break">{{ $records->first()->remark }}</small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @elseif($searchType == 'date' && $dailyRecords->count() > 0)
                    @php
                        $record = $dailyRecords->first();
                        $statusColors = [
                            'Present' => 'success',
                            'Absent' => 'danger',
                            'Late' => 'warning',
                            'Excused' => 'info'
                        ];
                        $color = $statusColors[$record->status] ?? 'secondary';
                    @endphp
                    <div class="alert alert-{{ $color }} mt-3">
                        <h5><i class="bi bi-info-circle"></i> Status: <strong>{{ $record->status }}</strong></h5>
                        @if($record->remark)
                            <p class="mb-0"><strong>Remark:</strong> {{ $record->remark }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    @elseif($studentFilter && !$overviewData)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No attendance records found for the selected filters.
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Bootstrap 5 JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<script>
    let attendanceChart = null;

    // Toggle view more content
    function toggleViewMore() {
        const content = document.getElementById('viewMoreContent');
        const overviewSection = document.getElementById('overviewSection');
        const btn = document.querySelector('.view-more-btn');
        
        if (content.classList.contains('show')) {
            content.classList.remove('show');
            if (overviewSection) {
                overviewSection.classList.remove('hidden');
            }
            btn.innerHTML = '<i class="bi bi-chevron-down me-1"></i> View More Details';
        } else {
            content.classList.add('show');
            if (overviewSection) {
                overviewSection.classList.add('hidden');
            }
            btn.innerHTML = '<i class="bi bi-chevron-up me-1"></i> Hide Details';
        }
    }

    // Initialize attendance chart
    @if($overviewData && $studentFilter && $searchType != 'date')
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart');
        if (ctx) {
            const chartCtx = ctx.getContext('2d');
            
            const present = {{ $overviewData['present'] }};
            const absent = {{ $overviewData['absent'] }};
            const late = {{ $overviewData['late'] }};
            const excused = {{ $overviewData['excused'] }};
            const totalDays = {{ $overviewData['total_days'] }};

            attendanceChart = new Chart(chartCtx, {
                type: 'bar',
                data: {
                    labels: ['Present', 'Absent', 'Late', 'Excused'],
                    datasets: [{
                        label: 'Days',
                        data: [present, absent, late, excused],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'],
                        borderColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed.y || 0;
                                    const percentage = totalDays > 0 ? ((value / totalDays) * 100).toFixed(2) : 0;
                                    return label + ': ' + value + ' days (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    @endif

    // Show/hide fields based on search type
    document.addEventListener('DOMContentLoaded', function() {
        const searchType = document.getElementById('searchType');
        const yearField = document.getElementById('yearField');
        const monthField = document.getElementById('monthField');
        const dateField = document.getElementById('dateField');

        function toggleFields() {
            const type = searchType.value;
            
            if (type === 'date') {
                yearField.style.display = 'none';
                monthField.style.display = 'none';
                dateField.style.display = 'block';
            } else if (type === 'year') {
                yearField.style.display = 'block';
                monthField.style.display = 'none';
                dateField.style.display = 'none';
            } else {
                yearField.style.display = 'block';
                monthField.style.display = 'block';
                dateField.style.display = 'none';
            }
        }

        searchType.addEventListener('change', toggleFields);
        toggleFields(); // Initialize on page load
    });
</script>

@include('includes.footer')

