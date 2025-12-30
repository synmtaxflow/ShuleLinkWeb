@include('includes.parent_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
    }

    body {
        background-color: #f8f9fa;
    }

    /* Statistics Cards - Smaller and Cleaner */
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
        border-top: 3px solid #e9ecef;
        height: 100%;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-top-color: var(--primary-color);
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #495057;
        font-size: 22px;
        margin-bottom: 12px;
    }

    .stat-card:hover .stat-icon {
        background: var(--primary-color);
        color: white;
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        color: #212529;
        margin: 8px 0;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
        margin: 0;
    }

    /* Parent Details Card */
    .parent-details-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 25px;
    }

    .parent-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .parent-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    .parent-photo-preview {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid #e9ecef;
    }

    .parent-photo-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    .parent-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .info-item i {
        color: #6c757d;
        margin-right: 10px;
        margin-top: 3px;
        font-size: 18px;
        width: 20px;
    }

    .info-item-content {
        flex: 1;
    }

    .info-item-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }

    .info-item-value {
        font-size: 0.95rem;
        color: #212529;
        font-weight: 500;
    }

    /* Chart Cards */
    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        height: 100%;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .chart-title i {
        margin-right: 8px;
        color: var(--primary-color);
        font-size: 1.2rem;
    }

    /* Section Headers */
    .section-header {
        font-size: 1.4rem;
        font-weight: 600;
        color: #212529;
        margin: 25px 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    /* Table Cards */
    .table-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .table-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .table-card-title i {
        margin-right: 8px;
        color: var(--primary-color);
    }

    /* Notification Cards */
    .notification-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #e9ecef;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .notification-card:hover {
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        transform: translateX(5px);
    }

    .notification-card.border-success {
        border-left-color: #28a745;
    }

    .notification-card.border-danger {
        border-left-color: #dc3545;
    }

    .notification-card.border-warning {
        border-left-color: #ffc107;
    }

    .notification-card.border-info {
        border-left-color: #17a2b8;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-right: 12px;
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }

    .notification-message {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .notification-date {
        font-size: 0.75rem;
        color: #adb5bd;
    }

    /* Student Card */
    .student-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border-left: 3px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .student-card:hover {
        border-left-color: var(--primary-color);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    .student-photo {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 15px;
    }

    /* Badge Styles */
    .badge-custom {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Additional Info Cards */
    .info-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border-left: 3px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .info-card:hover {
        border-left-color: var(--primary-color);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    .info-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.5rem;
        }
        
        .parent-title {
            font-size: 1.25rem;
        }
        
        .parent-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid" style="padding: 20px;">
    
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
                <i class="bi bi-house-door"></i> Parent Dashboard
            </h2>
        </div>
    </div>

    <!-- Parent Details Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="parent-details-card">
                <div class="parent-header">
                    <div class="d-flex align-items-center">
                        <div class="parent-photo-preview me-3">
                            @if($parent->photo)
                                <img src="{{ asset('userImages/' . $parent->photo) }}" alt="Parent Photo">
                            @else
                                <i class="bi bi-person" style="font-size: 40px; color: #6c757d;"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="parent-title">{{ $parent->first_name }} {{ $parent->middle_name ?? '' }} {{ $parent->last_name }}</h3>
                            <small class="text-muted">Parent ID: {{ $parent->parentID }}</small>
                        </div>
                    </div>
                </div>

                <div class="parent-info-grid">
                    @if($parent->phone)
                    <div class="info-item">
                        <i class="bi bi-telephone"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Phone</div>
                            <div class="info-item-value">{{ $parent->phone }}</div>
                        </div>
                    </div>
                    @endif
                    @if($parent->email)
                    <div class="info-item">
                        <i class="bi bi-envelope"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Email</div>
                            <div class="info-item-value">{{ $parent->email }}</div>
                        </div>
                    </div>
                    @endif
                    @if($parent->occupation)
                    <div class="info-item">
                        <i class="bi bi-briefcase"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Occupation</div>
                            <div class="info-item-value">{{ $parent->occupation }}</div>
                        </div>
                    </div>
                    @endif
                    @if($parent->address)
                    <div class="info-item">
                        <i class="bi bi-geo-alt"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Address</div>
                            <div class="info-item-value">{{ $parent->address }}</div>
                        </div>
                    </div>
                    @endif
                    @if($school)
                    <div class="info-item">
                        <i class="bi bi-building"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">School</div>
                            <div class="info-item-value">{{ $school->school_name }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-number">{{ number_format($totalStudents) }}</div>
                <div class="stat-label">Total Children</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="stat-number">{{ number_format($activeStudents) }}</div>
                <div class="stat-label">Active Students</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-trophy"></i>
                </div>
                <div class="stat-number">{{ $recentResults->count() }}</div>
                <div class="stat-label">Recent Results</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-number">{{ $upcomingExams->count() }}</div>
                <div class="stat-label">Upcoming Exams</div>
            </div>
        </div>
    </div>

    <!-- Notifications Section -->
    @if($notifications->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-bell"></i> Recent Notifications
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="table-card">
                @foreach($notifications as $notification)
                <div class="notification-card border-{{ $notification['color'] }}" onclick="window.location.href='{{ $notification['link'] }}'">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon bg-{{ $notification['color'] }} bg-opacity-10 text-{{ $notification['color'] }}">
                            <i class="bi {{ $notification['icon'] }}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">{{ $notification['title'] }}</div>
                            <div class="notification-message">{{ $notification['message'] }}</div>
                            <div class="notification-date">{{ $notification['date']->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Students Section -->
    @if($students->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-people"></i> My Children
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        @foreach($students as $student)
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="student-card">
                <div class="d-flex align-items-center">
                    @if($student->photo)
                        <img src="{{ asset('userImages/' . $student->photo) }}" alt="Student" class="student-photo">
                    @else
                        <div class="student-photo bg-light d-flex align-items-center justify-content-center">
                            <i class="bi bi-person" style="font-size: 30px; color: #6c757d;"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h5 class="mb-1">{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</h5>
                        <p class="mb-1 text-muted small">
                            <i class="bi bi-book"></i> 
                            @if($student->subclass && $student->subclass->class)
                                {{ $student->subclass->class->class_name }} {{ $student->subclass->subclass_name }}
                            @else
                                N/A
                            @endif
                        </p>
                        <p class="mb-1 text-muted small">
                            <i class="bi bi-hash"></i> {{ $student->admission_number }}
                        </p>
                        <div class="d-flex align-items-center mt-2">
                            <span class="badge badge-custom bg-{{ $student->status == 'Active' ? 'success' : 'secondary' }}">
                                {{ $student->status }}
                            </span>
                            @if(isset($attendanceStats[$student->studentID]))
                                <span class="badge badge-custom bg-info ms-2">
                                    Attendance: {{ $attendanceStats[$student->studentID]['percentage'] }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Gender Distribution Chart -->
    @if($totalStudents > 0)
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-bar-chart"></i> Children Gender Distribution
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="chart-card">
                <h4 class="chart-title">
                    <i class="bi bi-graph-up"></i> Gender Distribution
                </h4>
                <canvas id="genderChart" height="250"></canvas>
                <div class="row mt-3 text-center">
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label">Male</div>
                            <div class="info-value">{{ number_format($maleStudents) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label">Female</div>
                            <div class="info-value">{{ number_format($femaleStudents) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-12 mb-3">
            <div class="chart-card">
                <h4 class="chart-title">
                    <i class="bi bi-pie-chart"></i> Status Distribution
                </h4>
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Results -->
    @if($recentResults->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-trophy"></i> Recent Results
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="table-card-header">
                    <h5 class="table-card-title">
                        <i class="bi bi-list-check"></i> Latest Results
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Exam</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentResults as $result)
                            <tr>
                                <td>
                                    <strong>{{ $result->student->first_name }} {{ $result->student->last_name }}</strong>
                                </td>
                                <td>{{ $result->examination->exam_name ?? 'N/A' }}</td>
                                <td>
                                    @if($result->classSubject && $result->classSubject->subject)
                                        {{ $result->classSubject->subject->subject_name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $result->marks ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $result->grade ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $result->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Attendance -->
    @if($recentAttendance->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-calendar-check"></i> Recent Attendance (Last 7 Days)
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="table-card-header">
                    <h5 class="table-card-title">
                        <i class="bi bi-list-check"></i> Attendance Records
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAttendance as $attendance)
                            <tr>
                                <td>
                                    <strong>{{ $attendance->student->first_name }} {{ $attendance->student->last_name }}</strong>
                                </td>
                                <td>
                                    @if($attendance->subclass && $attendance->subclass->class)
                                        {{ $attendance->subclass->class->class_name }} {{ $attendance->subclass->subclass_name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $attendance->attendance_date->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'Present' => 'success',
                                            'Absent' => 'danger',
                                            'Late' => 'warning',
                                            'Excused' => 'info'
                                        ];
                                        $color = $statusColors[$attendance->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $attendance->status }}</span>
                                </td>
                                <td>{{ $attendance->remark ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Upcoming Exams -->
    @if($upcomingExams->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-calendar-event"></i> Upcoming Exams (Next 30 Days)
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="table-card-header">
                    <h5 class="table-card-title">
                        <i class="bi bi-list-check"></i> Exam Schedule
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Date</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingExams as $exam)
                            <tr>
                                <td>
                                    <strong>{{ $exam->examination->exam_name ?? 'N/A' }}</strong>
                                </td>
                                <td>{{ $exam->subject->subject_name ?? 'N/A' }}</td>
                                <td>
                                    @if($exam->subclass && $exam->subclass->class)
                                        {{ $exam->subclass->class->class_name }} {{ $exam->subclass->subclass_name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $exam->exam_date->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Active Book Borrows -->
    @if($activeBookBorrows->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-book"></i> Library Books
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="table-card-header">
                    <h5 class="table-card-title">
                        <i class="bi bi-list-check"></i> Active Book Borrows
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Book</th>
                                <th>Borrow Date</th>
                                <th>Expected Return</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeBookBorrows as $borrow)
                            <tr>
                                <td>
                                    <strong>{{ $borrow->student->first_name }} {{ $borrow->student->last_name }}</strong>
                                </td>
                                <td>{{ $borrow->book->book_title ?? 'N/A' }}</td>
                                <td>{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td>
                                    @if($borrow->expected_return_date)
                                        {{ $borrow->expected_return_date->format('M d, Y') }}
                                        @if($borrow->expected_return_date < now())
                                            <span class="badge bg-danger ms-2">Overdue</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $borrow->status }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Bootstrap 5 JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    @if($totalStudents > 0)
    // Gender Distribution Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    const genderChart = new Chart(genderCtx, {
        type: 'line',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                label: 'Children',
                data: [{{ $maleStudents }}, {{ $femaleStudents }}],
                borderColor: '#940000',
                backgroundColor: 'rgba(148, 0, 0, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: '#940000',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = {{ $maleStudents }} + {{ $femaleStudents }};
                            const percentage = total > 0 ? Math.round(context.parsed.y / total * 100) : 0;
                            return context.label + ': ' + context.parsed.y + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Transferred', 'Graduated'],
            datasets: [{
                data: [
                    {{ $students->where('status', 'Active')->count() }},
                    {{ $students->where('status', 'Inactive')->count() }},
                    {{ $students->where('status', 'Transferred')->count() }},
                    {{ $students->where('status', 'Graduated')->count() }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#6c757d',
                    '#ffc107',
                    '#17a2b8'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    @endif
</script>

@include('includes.footer')
