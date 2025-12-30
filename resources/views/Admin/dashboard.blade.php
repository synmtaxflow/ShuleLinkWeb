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
<div class="row mb-4">
    <!-- Subjects Count -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #940000 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Subjects</h6>
                        <h3 class="mb-0" style="color: #940000; font-weight: bold;">{{ $dashboardStats['subjects_count'] ?? 0 }}</h3>
                    </div>
                    <div class="text-primary-custom" style="font-size: 2.5rem; opacity: 0.3;">
                        <i class="fa fa-book"></i>
                    </div>
                </div>
                <a href="{{ route('manageSubjects') }}" class="btn btn-sm btn-outline-primary-custom mt-3 w-100">
                    <i class="fa fa-eye"></i> View Subjects
                </a>
            </div>
        </div>
    </div>

    <!-- Classes Count -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #17a2b8 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Classes</h6>
                        <h3 class="mb-0" style="color: #17a2b8; font-weight: bold;">{{ $dashboardStats['classes_count'] ?? 0 }}</h3>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #17a2b8;">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
                <a href="{{ route('manageClasses') }}" class="btn btn-sm btn-outline-info mt-3 w-100">
                    <i class="fa fa-eye"></i> View Classes
                </a>
            </div>
        </div>
    </div>

    <!-- Students Count -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Students</h6>
                        <h3 class="mb-0" style="color: #28a745; font-weight: bold;">{{ $dashboardStats['students_count'] ?? 0 }}</h3>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #28a745;">
                        <i class="fa fa-user"></i>
                    </div>
                </div>
                <a href="{{ route('manage_student') }}" class="btn btn-sm btn-outline-success mt-3 w-100">
                    <i class="fa fa-eye"></i> View Students
                </a>
            </div>
        </div>
    </div>

    <!-- Parents Count -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Parents</h6>
                        <h3 class="mb-0" style="color: #ffc107; font-weight: bold;">{{ $dashboardStats['parents_count'] ?? 0 }}</h3>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #ffc107;">
                        <i class="fa fa-user-plus"></i>
                    </div>
                </div>
                <a href="{{ route('manage_parents') }}" class="btn btn-sm btn-outline-warning mt-3 w-100">
                    <i class="fa fa-eye"></i> View Parents
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Second Row -->
<div class="row mb-4">
    <!-- Teachers Count -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6f42c1 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Teachers</h6>
                        <h3 class="mb-0" style="color: #6f42c1; font-weight: bold;">{{ $dashboardStats['teachers_count'] ?? 0 }}</h3>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #6f42c1;">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
                <a href="{{ route('manageTeachers') }}" class="btn btn-sm btn-outline-secondary mt-3 w-100">
                    <i class="fa fa-eye"></i> View Teachers
                </a>
            </div>
        </div>
    </div>

    <!-- Examinations Count -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Examinations</h6>
                        <h3 class="mb-0" style="color: #dc3545; font-weight: bold;">{{ $dashboardStats['examinations_count'] ?? 0 }}</h3>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #dc3545;">
                        <i class="fa fa-pencil-square-o"></i>
                    </div>
                </div>
                <a href="{{ route('manageExamination') }}" class="btn btn-sm btn-outline-danger mt-3 w-100">
                    <i class="fa fa-eye"></i> View Examinations
                </a>
            </div>
        </div>
    </div>

    <!-- Fees Count -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #20c997 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Fees Records</h6>
                        <h3 class="mb-0" style="color: #20c997; font-weight: bold;">{{ $dashboardStats['fees_count'] ?? 0 }}</h3>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #20c997;">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
                <a href="{{ route('manage_fees') }}" class="btn btn-sm btn-outline-success mt-3 w-100">
                    <i class="fa fa-eye"></i> View Fees
                </a>
            </div>
        </div>
    </div>

    <!-- Sessions Per Week -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #007bff !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Sessions Per Week</h6>
                        <h3 class="mb-0" style="color: #007bff; font-weight: bold;">{{ $dashboardStats['sessions_per_week'] ?? 0 }}</h3>
                        <small class="text-muted">Monday - Friday</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #007bff;">
                        <i class="fa fa-calendar-week"></i>
                    </div>
                </div>
                <a href="{{ route('timeTable') }}" class="btn btn-sm btn-outline-primary mt-3 w-100">
                    <i class="fa fa-clock-o"></i> View Sessions
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Third Row -->
<div class="row mb-4">
    <!-- Sessions Per Year -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #fd7e14 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Sessions Per Year</h6>
                        <h3 class="mb-0" style="color: #fd7e14; font-weight: bold;">{{ $dashboardStats['sessions_per_year'] ?? 0 }}</h3>
                        <small class="text-muted">Excluding holidays</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #fd7e14;">
                        <i class="fa fa-calendar"></i>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-warning mt-3 w-100" disabled>
                    <i class="fa fa-info-circle"></i> Annual Count
                </button>
            </div>
        </div>
    </div>

    <!-- Sessions Entered -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6610f2 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Sessions Entered</h6>
                        <h3 class="mb-0" style="color: #6610f2; font-weight: bold;">{{ $dashboardStats['approved_sessions_count'] ?? 0 }}</h3>
                        <small class="text-muted">With approved tasks</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #6610f2;">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
                <a href="{{ route('taskManagement') }}" class="btn btn-sm btn-outline-secondary mt-3 w-100">
                    <i class="fa fa-list"></i> View More
                </a>
            </div>
        </div>
    </div>

    <!-- School Management -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #940000 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">School</h6>
                        <h3 class="mb-0" style="color: #940000; font-weight: bold;">Info</h3>
                    </div>
                    <div class="text-primary-custom" style="font-size: 2.5rem; opacity: 0.3;">
                        <i class="fa fa-building"></i>
                    </div>
                </div>
                <a href="{{ route('school') }}" class="btn btn-sm btn-outline-primary-custom mt-3 w-100">
                    <i class="fa fa-eye"></i> Manage School
                </a>
            </div>
        </div>
    </div>

    <!-- Attendance Management -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #17a2b8 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Attendance</h6>
                        <h3 class="mb-0" style="color: #17a2b8; font-weight: bold;">Manage</h3>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #17a2b8;">
                        <i class="fa fa-check-square-o"></i>
                    </div>
                </div>
                <a href="{{ route('manageAttendance') }}" class="btn btn-sm btn-outline-info mt-3 w-100">
                    <i class="fa fa-eye"></i> View Attendance
                </a>
            </div>
        </div>
    </div>
</div>
@endif
</div>
@include('includes.footer')
