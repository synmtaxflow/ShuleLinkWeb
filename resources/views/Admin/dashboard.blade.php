@include('includes.Admin_nav')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: white;
    }
    .btn-outline-primary-custom {
        border-color: #940000;
        color: #940000;
    }
    .btn-outline-primary-custom:hover {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }
    div, .card, .alert, .btn {
        border-radius: 0 !important;
    }
    .dashboard-hero {
        background: #fff7f7;
        border: 1px solid #f0dada;
        padding: 18px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .dashboard-hero .hero-title {
        font-size: 1.4rem;
        margin-bottom: 6px;
    }
    .dashboard-hero .hero-subtitle {
        color: #6c757d;
        margin-bottom: 0;
    }
    .dashboard-hero .hero-actions .btn {
        margin-left: 8px;
    }
    .dashboard-card {
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        height: 100%;
    }
    .dashboard-card .card-title {
        font-weight: 600;
        color: #940000;
        margin-bottom: 8px;
    }
    .dashboard-card .stat {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2f2f2f;
        margin-bottom: 6px;
    }
    .dashboard-card .stat-note {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .dashboard-card .card-icon {
        font-size: 2rem;
        color: rgba(148, 0, 0, 0.35);
    }
    .dashboard-card .card-actions {
        margin-top: 14px;
    }
</style>

<div class="container-fluid mt-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>There were some errors in your form:</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

<!-- Dashboard Statistics Section -->
@if(isset($dashboardStats))
<div class="dashboard-hero">
                    <div>
        <div class="text-muted" style="font-size: 0.9rem;">Today · {{ \Carbon\Carbon::now()->format('l, d M Y') }}</div>
        <div class="hero-title">Hello, Administrator</div>
        <p class="hero-subtitle">Quick access to the modules that keep your school running.</p>
    </div>
    <div class="hero-actions">
        <a href="{{ route('manageTeachers') }}" class="btn btn-primary-custom btn-sm">
            <i class="fa fa-users"></i> Manage Teachers
        </a>
        <a href="{{ route('manage_student') }}" class="btn btn-outline-primary-custom btn-sm">
            <i class="fa fa-user"></i> Manage Students
        </a>
    </div>
</div>

@php
    $cards = [
        ['title' => 'Subjects', 'count' => $dashboardStats['subjects_count'] ?? 0, 'icon' => 'fa-book', 'link' => route('manageSubjects'), 'link_label' => 'View Subjects'],
        ['title' => 'Classes', 'count' => $dashboardStats['classes_count'] ?? 0, 'icon' => 'fa-users', 'link' => route('manageClasses'), 'link_label' => 'View Classes'],
        ['title' => 'Students', 'count' => $dashboardStats['students_count'] ?? 0, 'icon' => 'fa-user', 'link' => route('manage_student'), 'link_label' => 'View Students'],
        ['title' => 'Parents', 'count' => $dashboardStats['parents_count'] ?? 0, 'icon' => 'fa-user-plus', 'link' => route('manage_parents'), 'link_label' => 'View Parents'],
        ['title' => 'Teachers', 'count' => $dashboardStats['teachers_count'] ?? 0, 'icon' => 'fa-users', 'link' => route('manageTeachers'), 'link_label' => 'View Teachers'],
        ['title' => 'Examinations', 'count' => $dashboardStats['examinations_count'] ?? 0, 'icon' => 'fa-pencil-square-o', 'link' => route('manageExamination'), 'link_label' => 'View Examinations'],
        ['title' => 'Fees Records', 'count' => $dashboardStats['fees_count'] ?? 0, 'icon' => 'fa-money', 'link' => route('manage_fees'), 'link_label' => 'View Fees'],
        ['title' => 'Sessions Per Week', 'count' => $dashboardStats['sessions_per_week'] ?? 0, 'icon' => 'fa-calendar', 'note' => 'Monday - Friday', 'link' => route('timeTable'), 'link_label' => 'View Sessions'],
        ['title' => 'Sessions Per Year', 'count' => $dashboardStats['sessions_per_year'] ?? 0, 'icon' => 'fa-calendar', 'note' => 'Excluding holidays', 'link' => null, 'link_label' => 'Annual Count'],
        ['title' => 'Sessions Entered', 'count' => $dashboardStats['approved_sessions_count'] ?? 0, 'icon' => 'fa-check-circle', 'note' => 'With approved tasks', 'link' => route('taskManagement'), 'link_label' => 'View More'],
        ['title' => 'School', 'count' => 'Info', 'icon' => 'fa-building', 'link' => route('school'), 'link_label' => 'Manage School'],
        ['title' => 'Attendance', 'count' => 'Manage', 'icon' => 'fa-check-square-o', 'link' => route('manageAttendance'), 'link_label' => 'View Attendance'],
    ];
@endphp

<div class="row">
    @foreach($cards as $card)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card dashboard-card">
            <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                    <div>
                            <div class="card-title">{{ $card['title'] }}</div>
                            <div class="stat">{{ $card['count'] }}</div>
                            @if(!empty($card['note']))
                                <div class="stat-note">{{ $card['note'] }}</div>
                            @endif
                    </div>
                        <div class="card-icon">
                            <i class="fa {{ $card['icon'] }}"></i>
    </div>
                    </div>
                    <div class="card-actions">
                        @if(!empty($card['link']))
                            <a href="{{ $card['link'] }}" class="btn btn-sm btn-outline-primary-custom w-100">
                                <i class="fa fa-eye"></i> {{ $card['link_label'] }}
                            </a>
                        @else
                            <button class="btn btn-sm btn-outline-primary-custom w-100" disabled>
                                <i class="fa fa-info-circle"></i> {{ $card['link_label'] }}
                </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
</div>
@include('includes.footer')
