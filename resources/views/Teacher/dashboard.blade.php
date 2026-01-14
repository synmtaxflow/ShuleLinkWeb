@include('includes.teacher_nav')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    .dashboard-widget-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .dashboard-widget-row::after {
        content: "";
        display: table;
        clear: both;
    }
    .dashboard-widget {
        float: left;
        width: 100%;
    }
    @media (min-width: 768px) {
        .dashboard-widget {
            width: 50%;
        }
    }
    @media (min-width: 992px) {
        .dashboard-widget {
            width: 25%;
        }
    }
</style>
<div class="container-fluid mt-3">
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@php
    // Check if there's a session happening now
    $currentSession = null;
    $sessionNotification = null;
    if (isset($teacherNotifications) && $teacherNotifications->count() > 0) {
        $sessionNotifications = $teacherNotifications->filter(function($n) {
            return isset($n['type']) && $n['type'] === 'session_time';
        });
        if ($sessionNotifications->count() > 0) {
            $sessionNotification = $sessionNotifications->first();
        }
    }
@endphp

@if($sessionNotification)
<div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-left: 4px solid #ffc107;">
    <h5 class="alert-heading">
        <i class="fa fa-clock-o"></i> Session Time Alert!
    </h5>
    <p class="mb-2"><strong>{{ $sessionNotification['title'] }}</strong></p>
    <p class="mb-0">{{ $sessionNotification['message'] }}</p>
    <hr>
    <p class="mb-0">
        <a href="{{ $sessionNotification['link'] ?? route('teacher.mySessions') }}" class="btn btn-sm btn-warning">
            <i class="fa fa-arrow-right"></i> Go to Sessions
        </a>
    </p>
</div>
@endif


<!-- Dashboard Statistics Section -->
@if(isset($dashboardStats))
<!-- First Row: 4 Widgets -->
<div class="row mb-4">
    <!-- Subjects Count -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #940000 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Subjects Teaching</h6>
                        <h3 class="mb-0" style="color: #940000; font-weight: bold;">{{ $dashboardStats['subjects_count'] ?? 0 }}</h3>
                    </div>
                    <div class="text-primary-custom" style="font-size: 2.5rem; opacity: 0.3;">
                        <i class="fa fa-book"></i>
                    </div>
                </div>
                <a href="{{ route('teacherSubjects') }}" class="btn btn-sm btn-outline-primary-custom mt-3 w-100">
                    <i class="fa fa-eye"></i> View Subjects
                </a>
            </div>
        </div>
    </div>

    <!-- Classes Count (Subclasses with Sessions) -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #17a2b8 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Classes Teaching</h6>
                        <h3 class="mb-0" style="color: #17a2b8; font-weight: bold;">{{ $dashboardStats['classes_count'] ?? 0 }}</h3>
                        <small class="text-muted">Subclasses with sessions</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #17a2b8;">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
                @if (isset($hasAssignedClass) && $hasAssignedClass)
                <a href="{{ route('AdmitedClasses') }}" class="btn btn-sm btn-outline-info mt-3 w-100">
                    <i class="fa fa-eye"></i> View Classes
                </a>
                @else
                <button class="btn btn-sm btn-outline-secondary mt-3 w-100" disabled>
                    <i class="fa fa-lock"></i> No Classes Assigned
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Class Teacher Subclasses Count -->
    @if(isset($coordinator) && $coordinator->count() > 0)
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #e83e8c !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Class Teacher</h6>
                        <h3 class="mb-0" style="color: #e83e8c; font-weight: bold;">{{ $dashboardStats['class_teacher_subclasses_count'] ?? 0 }}</h3>
                        <small class="text-muted">Subclasses Managing</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #e83e8c;">
                        <i class="fa fa-user-tie"></i>
                    </div>
                </div>
                <a href="{{ route('AdmitedClasses') }}" class="btn btn-sm mt-3 w-100" style="background-color: #e83e8c; border-color: #e83e8c; color: white; border-radius: 0;">
                    <i class="fa fa-eye"></i> View Managed Classes
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Sessions Per Week -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Sessions Per Week</h6>
                        <h3 class="mb-0" style="color: #28a745; font-weight: bold;">{{ $dashboardStats['sessions_per_week'] ?? 0 }}</h3>
                        <small class="text-muted">Monday - Friday</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #28a745;">
                        <i class="fa fa-calendar-week"></i>
                    </div>
                </div>
                <a href="{{ route('teacher.mySessions') }}" class="btn btn-sm btn-outline-success mt-3 w-100">
                    <i class="fa fa-clock-o"></i> View Sessions
                </a>
            </div>
        </div>
    </div>
</div>

    <!-- Coordinator Main Classes Count -->
    @if(isset($dashboardStats['coordinator_classes_count']) && $dashboardStats['coordinator_classes_count'] > 0)
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #20c997 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Coordinator</h6>
                        <h3 class="mb-0" style="color: #20c997; font-weight: bold;">{{ $dashboardStats['coordinator_classes_count'] ?? 0 }}</h3>
                        <small class="text-muted">Main Classes Managing</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #20c997;">
                        <i class="fa fa-user-graduate"></i>
                    </div>
                </div>
                <a href="{{ route('AdmitedClasses', ['coordinator' => 'true']) }}" class="btn btn-sm mt-3 w-100" style="background-color: #20c997; border-color: #20c997; color: white; border-radius: 0;">
                    <i class="fa fa-cog"></i> Manage Classes
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Sessions Per Year -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Sessions Per Year</h6>
                        <h3 class="mb-0" style="color: #ffc107; font-weight: bold;">{{ $dashboardStats['sessions_per_year'] ?? 0 }}</h3>
                        <small class="text-muted">Excluding holidays</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #ffc107;">
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
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6f42c1 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Sessions Entered</h6>
                        <h3 class="mb-0" style="color: #6f42c1; font-weight: bold;">{{ $dashboardStats['approved_sessions_count'] ?? 0 }}</h3>
                        <small class="text-muted">With approved tasks</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #6f42c1;">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
                <a href="{{ route('teacher.mySessions') }}" class="btn btn-sm btn-outline-secondary mt-3 w-100">
                    <i class="fa fa-list"></i> View More
                </a>
            </div>
        </div>
    </div>

    <!-- Scheme of Work -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #fd7e14 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Scheme of Work</h6>
                        <h3 class="mb-0" style="color: #fd7e14; font-weight: bold;">{{ $dashboardStats['scheme_of_work_count'] ?? 0 }}</h3>
                        <small class="text-muted">For current year</small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #fd7e14;">
                        <i class="fa fa-book-open"></i>
                    </div>
                </div>
                <a href="{{ route('teacher.schemeOfWork') }}" class="btn btn-sm mt-3 w-100" style="background-color: #fd7e14; border-color: #fd7e14; color: white; border-radius: 0;">
                    <i class="fa fa-eye"></i> View Scheme of Work
                </a>
            </div>
        </div>
    </div>

    <!-- Lesson Plans -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #007bff !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">Lesson Plans</h6>
                        <h3 class="mb-0" style="color: #007bff; font-weight: bold;">{{ $dashboardStats['lesson_plans_count'] ?? 0 }}</h3>
                        <small class="text-muted">
                            {{ $dashboardStats['lesson_plans_sent_count'] ?? 0 }} sent to admin
                        </small>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.3; color: #007bff;">
                        <i class="fa fa-file-text"></i>
                    </div>
                </div>
                <a href="{{ route('teacher.lessonPlans') }}" class="btn btn-sm mt-3 w-100" style="background-color: #007bff; border-color: #007bff; color: white; border-radius: 0;">
                    <i class="fa fa-eye"></i> View Lesson Plans
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Exam Approval Widgets -->
@if((isset($pendingApprovals) && count($pendingApprovals) > 0) || 
    (isset($specialRoleApprovals) && count($specialRoleApprovals) > 0) || 
    (isset($waitingApprovals) && count($waitingApprovals) > 0))
<div class="row mb-4">
    <div class="col-12 mb-3">
        <h5 class="text-primary-custom" style="font-weight: 600;">
            <i class="fa fa-check-circle"></i> Exam Approvals Pending
        </h5>
        <hr style="border-top: 2px solid #940000;">
    </div>
    
    <!-- Pending Approvals (Regular Roles) -->
@if(isset($pendingApprovals) && count($pendingApprovals) > 0)
        @foreach($pendingApprovals as $approval)
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }} !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div style="flex: 1;">
                                <h6 class="mb-1" style="font-weight: 600; color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }};">
                                    <i class="fa fa-{{ $approval->status === 'rejected' ? 'times-circle' : 'exclamation-triangle' }}"></i> 
                                    {{ ($approval->examination && $approval->examination->exam_name) ? $approval->examination->exam_name : 'N/A' }}
                                </h6>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Role:</strong> {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}
                                </p>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Step:</strong> {{ $approval->approval_order }}
                                </p>
                        @if($approval->status === 'rejected')
                                    <p class="mb-1" style="color: #dc3545; font-size: 0.85rem; font-weight: 600;">
                                        <strong>Status:</strong> Rejected
                                    </p>
                            @if($approval->rejection_reason)
                                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                            <strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($approval->rejection_reason, 50) }}
                                        </p>
                            @endif
                        @else
                                    <p class="mb-0" style="color: #ffc107; font-size: 0.85rem; font-weight: 600;">
                                        <strong>Status:</strong> Pending Your Approval
                                    </p>
                        @endif
                            </div>
                        </div>
                        @if($approval->examination && $approval->examination->examID)
                        <a href="{{ route('approve_result', $approval->examination->examID) }}" class="btn btn-sm mt-2 w-100" style="background-color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }}; border-color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }}; color: white; border-radius: 0;">
                            <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Review & Approve' }}
                            </a>
                        @else
                        <button class="btn btn-sm mt-2 w-100" style="background-color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }}; border-color: {{ $approval->status === 'rejected' ? '#dc3545' : '#ffc107' }}; color: white; border-radius: 0;" disabled>
                            <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Review & Approve' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
@endif

    <!-- Special Role Approvals (Class Teacher & Coordinator) -->
@if(isset($specialRoleApprovals) && count($specialRoleApprovals) > 0)
        @foreach($specialRoleApprovals as $specialApproval)
            @php
                $approval = $specialApproval['approval'];
                $type = $specialApproval['type'];
                $exam = $specialApproval['exam'];
                $borderColor = $approval->status === 'rejected' ? '#dc3545' : ($type === 'class_teacher' ? '#ffc107' : '#17a2b8');
            @endphp
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $borderColor }} !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div style="flex: 1;">
                                <h6 class="mb-1" style="font-weight: 600; color: {{ $borderColor }};">
                                    <i class="fa fa-{{ $approval->status === 'rejected' ? 'times-circle' : ($type === 'class_teacher' ? 'users' : 'diagram-3') }}"></i> 
                                    {{ $exam->exam_name ?? 'N/A' }}
                                </h6>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </p>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Step:</strong> {{ $approval->approval_order }}
                                </p>
                        @if($approval->status === 'rejected')
                                    <p class="mb-1" style="color: #dc3545; font-size: 0.85rem; font-weight: 600;">
                                        <strong>Status:</strong> Rejected
                                    </p>
                            @if($approval->rejection_reason)
                                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                            <strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($approval->rejection_reason, 50) }}
                                        </p>
                            @endif
                        @else
                                    <p class="mb-0" style="color: {{ $borderColor }}; font-size: 0.85rem; font-weight: 600;">
                                        <strong>Status:</strong> Pending Your Approval
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if($exam && $exam->examID)
                        <a href="{{ route('approve_result', $exam->examID) }}" class="btn btn-sm mt-2 w-100" style="background-color: {{ $borderColor }}; border-color: {{ $borderColor }}; color: white; border-radius: 0;">
                            <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Go to Approve' }}
                        </a>
                        @else
                        <button class="btn btn-sm mt-2 w-100" style="background-color: {{ $borderColor }}; border-color: {{ $borderColor }}; color: white; border-radius: 0;" disabled>
                            <i class="fa fa-eye"></i> {{ $approval->status === 'rejected' ? 'View & Review' : 'Go to Approve' }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
@endif

    <!-- Waiting Approvals -->
@if(isset($waitingApprovals) && count($waitingApprovals) > 0)
        @foreach($waitingApprovals as $waitingApproval)
            @php
                $approval = $waitingApproval['approval'];
                $currentApprover = $waitingApproval['current_approver'] ?? null;
                $pendingCount = $waitingApproval['pending_count'] ?? 0;
            @endphp
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div style="flex: 1;">
                                <h6 class="mb-1" style="font-weight: 600; color: #6c757d;">
                                    <i class="fa fa-clock-o"></i> {{ ($approval->examination && $approval->examination->exam_name) ? $approval->examination->exam_name : 'N/A' }}
                                </h6>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Your Role:</strong> {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}
                                </p>
                                <p class="mb-1 text-muted" style="font-size: 0.85rem;">
                                    <strong>Step:</strong> {{ $approval->approval_order }}
                                </p>
                                <p class="mb-1" style="color: #6c757d; font-size: 0.85rem; font-weight: 600;">
                                    <strong>Status:</strong> Waiting for {{ $pendingCount }} previous approval(s)
                                </p>
                        @if($currentApprover)
                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                        <strong>Current:</strong> 
                                        @if($currentApprover->role ?? null)
                                    {{ $currentApprover->role->name ?? $currentApprover->role->role_name ?? 'N/A' }}
                                @else
                                            Step {{ $currentApprover->approval_order ?? 'N/A' }}
                                        @endif
                                        <span class="badge badge-{{ ($currentApprover->status ?? 'pending') === 'rejected' ? 'danger' : 'warning' }}" style="border-radius: 0;">
                                            {{ ucfirst($currentApprover->status ?? 'pending') }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if($approval->examination && $approval->examination->examID)
                        <a href="{{ route('approve_result', $approval->examination->examID) }}" class="btn btn-sm mt-2 w-100" style="background-color: #6c757d; border-color: #6c757d; color: white; border-radius: 0;">
                            <i class="fa fa-diagram-3"></i> View Approval Chain
                            </a>
                        @else
                        <button class="btn btn-sm mt-2 w-100" style="background-color: #6c757d; border-color: #6c757d; color: white; border-radius: 0;" disabled>
                            <i class="fa fa-diagram-3"></i> View Approval Chain
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    </div>
@endif

<!-- Management Permissions Widgets -->
@if(isset($managementPermissions) && count($managementPermissions) > 0)
<div class="row mb-4">
    <div class="col-12 mb-3">
        <h5 class="text-primary-custom" style="font-weight: 600;">
            <i class="fa fa-cogs"></i> Management Access
        </h5>
        <hr style="border-top: 2px solid #940000;">
    </div>
    @foreach($managementPermissions as $permission)
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $permission['color'] }} !important;">
            <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600;">{{ $permission['name'] }}</h6>
                            <p class="mb-0" style="color: {{ $permission['color'] }}; font-weight: bold; font-size: 1.1rem;">Access Granted</p>
                        </div>
                        <div style="font-size: 2.5rem; opacity: 0.3; color: {{ $permission['color'] }};">
                            <i class="fa {{ $permission['icon'] }}"></i>
                        </div>
                    </div>
                    <a href="{{ route($permission['route']) }}" class="btn btn-sm mt-3 w-100" style="background-color: {{ $permission['color'] }}; border-color: {{ $permission['color'] }}; color: white; border-radius: 0;">
                        <i class="fa fa-arrow-right"></i> Manage
                    </a>
                </div>
            </div>
        </div>
    @endforeach
    </div>
@endif

<!-- Teaching Subjects Display -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #940000 0%, #b30000 100%);">
                <h5 class="mb-0">
                    <i class="fa fa-book"></i> Teaching Subjects
                </h5>
            </div>
            <div class="card-body">
                @if(isset($dashboardStats['teaching_subjects']) && $dashboardStats['teaching_subjects']->count() > 0)
                    <div class="row">
                        @foreach($dashboardStats['teaching_subjects'] as $subject)
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="alert alert-info mb-0" style="border-left: 4px solid #17a2b8;">
                                    <strong>
                                        <i class="fa fa-book"></i> {{ $subject['subject_name'] }}
                                    </strong>
                                    @if($subject['subject_code'])
                                        <br><small class="text-muted">Code: {{ $subject['subject_code'] }}</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('teacherSubjects') }}" class="btn btn-primary-custom">
                            <i class="fa fa-eye"></i> View More Subjects
                        </a>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fa fa-exclamation-triangle"></i> No subjects assigned yet.
                </div>
                @endif
            </div>
        </div>
    </div>
                    </div>
@else
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Loading dashboard statistics...
        </div>
        </div>
    </div>
    @endif

<!-- Dashboard Graphs Section -->
@if(isset($graphData))
<div class="row mb-4">
    <!-- Graph 1: Sessions per week by day -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #940000 0%, #b30000 100%);">
                <h5 class="mb-0">
                    <i class="fa fa-calendar-week"></i> Sessions Per Week by Day
                </h5>
                <small>Based on approved tasks</small>
            </div>
            <div class="card-body">
                <canvas id="sessionsByDayChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Graph 2: Subject Performance -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #940000 0%, #b30000 100%);">
                <h5 class="mb-0">
                    <i class="fa fa-line-chart"></i> Subject Performance (Pass/Fail Rates)
                </h5>
                <small>Performance across all classes</small>
            </div>
            <div class="card-body">
                <canvas id="subjectPerformanceChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Graph 3: Classes with Most Sessions -->
    <div class="col-lg-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #940000 0%, #b30000 100%);">
                <h5 class="mb-0">
                    <i class="fa fa-users"></i> Classes with Most Sessions
                </h5>
                <small>Based on approved tasks</small>
            </div>
            <div class="card-body">
                <canvas id="classesSessionsChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endif
</div>

<!-- Class Teacher Approvals Modal -->
<div class="modal fade" id="classTeacherApprovalsModal" tabindex="-1" role="dialog" aria-labelledby="classTeacherApprovalsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="classTeacherApprovalsModalLabel">
                    <i class="bi bi-people"></i> Class Teacher Approvals Status
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="classTeacherApprovalsContent">
                    <div class="text-center">
                        <div class="spinner-border text-warning" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading class teacher approvals...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize graphs
    @if(isset($graphData))
    
    // Graph 1: Sessions per week by day
    @if(isset($graphData['sessions_by_day']) && !empty($graphData['sessions_by_day']))
    const sessionsByDayCtx = document.getElementById('sessionsByDayChart');
    if (sessionsByDayCtx) {
        new Chart(sessionsByDayCtx, {
            type: 'bar',
            data: {
                labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                datasets: [{
                    label: 'Number of Sessions',
                    data: [
                        {{ $graphData['sessions_by_day']['Monday'] ?? 0 }},
                        {{ $graphData['sessions_by_day']['Tuesday'] ?? 0 }},
                        {{ $graphData['sessions_by_day']['Wednesday'] ?? 0 }},
                        {{ $graphData['sessions_by_day']['Thursday'] ?? 0 }},
                        {{ $graphData['sessions_by_day']['Friday'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(148, 0, 0, 0.8)',
                    borderColor: 'rgba(148, 0, 0, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    @endif
    
    // Graph 2: Subject Performance
    @if(isset($graphData['subject_performance']) && !empty($graphData['subject_performance']))
    const subjectPerformanceCtx = document.getElementById('subjectPerformanceChart');
    if (subjectPerformanceCtx) {
        const subjectData = @json($graphData['subject_performance']);
        new Chart(subjectPerformanceCtx, {
            type: 'bar',
            data: {
                labels: subjectData.map(s => s.subject_name),
                datasets: [{
                    label: 'Pass Rate (%)',
                    data: subjectData.map(s => s.pass_rate),
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }, {
                    label: 'Fail Rate (%)',
                    data: subjectData.map(s => s.fail_rate),
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgba(220, 53, 69, 1)',
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
                        display: true
                    }
                }
            }
        });
    }
    @endif
    
    // Graph 3: Classes with Most Sessions
    @if(isset($graphData['classes_sessions']) && !empty($graphData['classes_sessions']))
    const classesSessionsCtx = document.getElementById('classesSessionsChart');
    if (classesSessionsCtx) {
        const classesData = @json($graphData['classes_sessions']);
        const classNames = Object.keys(classesData);
        const sessionCounts = Object.values(classesData);
        
        new Chart(classesSessionsCtx, {
            type: 'bar',
            data: {
                labels: classNames,
                datasets: [{
                    label: 'Number of Sessions',
                    data: sessionCounts,
                    backgroundColor: 'rgba(23, 162, 184, 0.8)',
                    borderColor: 'rgba(23, 162, 184, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    @endif
    
    @endif
    
    // Handle class teacher approvals button click
    $('.view-class-teacher-approvals').on('click', function() {
        const examID = $(this).data('exam-id');
        $('#classTeacherApprovalsModal').modal('show');
        
        // Load class teacher approvals
        $.ajax({
            url: '{{ route("get_class_teacher_approvals", ":examID") }}'.replace(':examID', examID),
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    let html = `<h6 class="mb-3"><strong>${response.exam_name}</strong></h6>`;
                    html += '<table class="table table-bordered table-hover">';
                    html += '<thead class="thead-light">';
                    html += '<tr>';
                    html += '<th>Class</th>';
                    html += '<th>Subclass</th>';
                    html += '<th>Class Teacher</th>';
                    html += '<th>Phone</th>';
                    html += '<th>Status</th>';
                    html += '<th>Action</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    response.details.forEach(function(detail) {
                        const statusBadge = detail.status === 'approved' 
                            ? '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Approved</span>'
                            : detail.status === 'rejected'
                            ? '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> Rejected</span>'
                            : '<span class="badge badge-warning"><i class="bi bi-clock"></i> Pending</span>';
                        
                        const teacherName = detail.class_teacher ? detail.class_teacher.name : 'Not Assigned';
                        const teacherPhone = detail.class_teacher ? (detail.class_teacher.phone || 'N/A') : 'N/A';
                        
                        html += '<tr>';
                        html += `<td>${detail.class_name}</td>`;
                        html += `<td>${detail.subclass_name}</td>`;
                        html += `<td>${teacherName}</td>`;
                        html += `<td>${teacherPhone}</td>`;
                        html += `<td>${statusBadge}</td>`;
                        html += '<td>';
                        if (detail.status === 'pending' && teacherPhone && teacherPhone !== 'N/A') {
                            html += `<button class="btn btn-sm btn-primary send-reminder-sms" data-phone="${teacherPhone}" data-subclass="${detail.subclass_name}">`;
                            html += '<i class="bi bi-send"></i> Send Reminder';
                            html += '</button>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    
                    $('#classTeacherApprovalsContent').html(html);
                    
                    // Handle send reminder SMS for class teachers
                    $('.send-reminder-sms').on('click', function() {
                        const phone = $(this).data('phone');
                        const subclass = $(this).data('subclass');
                        
                        Swal.fire({
                            title: 'Send Reminder SMS',
                            html: `
                                <p>Send reminder to <strong>${subclass}</strong> class teacher?</p>
                                <p><strong>Phone:</strong> ${phone}</p>
                                <textarea id="reminder-message" class="form-control mt-3" rows="3" placeholder="Enter reminder message (optional)">Please approve the results for ${subclass} class. Thank you.</textarea>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Send SMS',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#28a745',
                            preConfirm: () => {
                                return {
                                    phone: phone,
                                    message: document.getElementById('reminder-message').value || `Please approve the results for ${subclass} class. Thank you.`
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Send SMS via AJAX
                                $.ajax({
                                    url: '{{ route("send_message_to_teachers") }}',
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    data: {
                                        phone_numbers: [result.value.phone],
                                        message: result.value.message
                                    },
                                    success: function(response) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: 'Reminder SMS sent successfully.',
                                            icon: 'success',
                                            confirmButtonColor: '#940000'
                                        });
                                    },
                                    error: function(xhr) {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: 'Failed to send SMS. Please try again.',
                                            icon: 'error',
                                            confirmButtonColor: '#940000'
                                        });
                                    }
                                });
                            }
                        });
                    });
                } else {
                    $('#classTeacherApprovalsContent').html('<div class="alert alert-danger">Failed to load class teacher approvals.</div>');
                }
            },
            error: function(xhr) {
                $('#classTeacherApprovalsContent').html('<div class="alert alert-danger">Error loading class teacher approvals. Please try again.</div>');
            }
        });
    });
});
</script>

@include('includes.footer')
