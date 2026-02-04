<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin</title>
    <meta name="description" content="Sufee Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="apple-icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    <link rel="stylesheet" href="{{ asset('vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/themify-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/selectFX/css/cs-skin-elastic.css') }}">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<!-- Bootstrap Bundle JS (includes Popper) -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

    <style>
/* Badilisha rangi ya background ya sidebar */
#left-panel,
#left-panel .navbar,
#left-panel .navbar-default,
#left-panel .main-menu,
#left-panel .navbar-nav,
#left-panel ul {
    background-color: #ffffff !important; /* nyeupe */
    color: #2f2f2f !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Badilisha rangi ya maandishi (links) */
#left-panel .nav-link,
#left-panel a.nav-link,
#left-panel li a,
#left-panel .navbar-nav li a,
#left-panel .navbar-nav > li > a {
    color: #2f2f2f !important;
    font-weight: 600;
    background-color: transparent !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Rangi ya icon ndani ya links */
#left-panel .nav-link i,
#left-panel a.nav-link i,
#left-panel li a i,
#left-panel .navbar-nav li a i,
#left-panel .fa {
    color: #940000 !important;
}

/* Hover effect ya link */
#left-panel .nav-link:hover,
#left-panel a.nav-link:hover,
#left-panel li a:hover,
#left-panel .navbar-nav li a:hover,
#left-panel .navbar-nav > li > a:hover,
#left-panel li:hover {
    background-color: #f5f5f5 !important; /* kijivu chepesi */
    color: #2f2f2f !important;
}

/* Active link - higher specificity */
#left-panel .nav-link.active,
#left-panel a.nav-link.active,
#left-panel li.active > a,
#left-panel .navbar-nav > li.active > a,
#left-panel .nav-link.menu-active,
#left-panel a.nav-link.menu-active,
#left-panel li.menu-active > a,
#left-panel .sidebar-links-container .nav-link.menu-active,
#left-panel .sidebar-links-container a.nav-link.menu-active,
#left-panel .submenu .nav-link.menu-active,
#left-panel .submenu a.nav-link.menu-active {
    background-color: rgba(148, 0, 0, 0.08) !important;
    color: #2f2f2f !important;
    border-radius: 4px !important;
    padding: 8px 15px !important;
    margin: 2px 0 !important;
}

/* Active link icons - white when active */
#left-panel .nav-link.active i,
#left-panel a.nav-link.active i,
#left-panel li.active > a i,
#left-panel .nav-link.menu-active i,
#left-panel a.nav-link.menu-active i,
#left-panel .sidebar-links-container .nav-link.menu-active i,
#left-panel .submenu .nav-link.menu-active i {
    color: #666666 !important;
}

/* Active dropdown toggle */
#left-panel .dropdown-toggle.menu-active {
    background-color: rgba(148, 0, 0, 0.08) !important;
    color: #2f2f2f !important;
    border-radius: 4px !important;
}

#left-panel .dropdown-toggle.menu-active i {
    color: #666666 !important;
}

/* Rangi ya jina la "Teacher" na maandishi ya ndani ya sidebar */
#left-panel p,
#left-panel .navbar-brand,
#left-panel .navbar-brand:hover {
    color: #2f2f2f !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Rangi ya navbar brand */
#left-panel .navbar-header .navbar-brand {
    color: #2f2f2f !important;
    font-weight: 700 !important;
}

/* Rangi ya toggle button */
#left-panel .navbar-toggler,
#left-panel .navbar-toggler i {
    color: #2f2f2f !important;
}

/* Background ya list items */
#left-panel .navbar-nav li {
    background-color: transparent !important;
}

/* Border na dividers */
#left-panel .navbar-nav li {
    border-bottom: 1px solid #f0f0f0 !important;
}

/* Ensure all text in sidebar is #940000 */
#left-panel * {
    color: #2f2f2f !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Exception for icons - keep FontAwesome font-family and #940000 color */
#left-panel i,
#left-panel .fa,
#left-panel [class*="fa-"],
#left-panel [class^="fa-"] {
    color: #666666 !important;
    font-family: 'FontAwesome' !important;
}

/* Overflow scroll kwa sidebar links container - with visible scrollbar */
.sidebar-links-container {
    overflow-y: hidden !important;
    overflow-x: hidden !important;
    max-height: calc(100vh - 200px) !important;
    width: 100% !important;
    /* Hide scrollbar until hover */
    scrollbar-width: none !important; /* Firefox */
    -ms-overflow-style: none !important; /* IE and Edge */
}

/* Custom scrollbar styling for WebKit browsers (Chrome, Safari, Opera) */
.sidebar-links-container::-webkit-scrollbar {
    width: 0 !important;
    display: none !important;
}

.sidebar-links-container:hover {
    overflow-y: auto !important;
    scrollbar-width: thin !important; /* Firefox */
    scrollbar-color: #cfcfcf #f0f0f0 !important; /* Firefox */
    -ms-overflow-style: scrollbar !important; /* IE and Edge */
}

.sidebar-links-container:hover::-webkit-scrollbar {
    width: 8px !important;
    display: block !important;
}

.sidebar-links-container::-webkit-scrollbar-track {
    background: #f0f0f0 !important;
    border-radius: 4px !important;
}

.sidebar-links-container::-webkit-scrollbar-thumb {
    background: #cfcfcf !important;
    border-radius: 4px !important;
}

.sidebar-links-container::-webkit-scrollbar-thumb:hover {
    background: #bdbdbd !important;
}

.sidebar-links-container ul {
    width: 100% !important;
}

/* Ensure sidebar itself can scroll if needed */
#left-panel {
    overflow-y: auto !important;
    overflow-x: hidden !important;
    max-height: 100vh !important;
}

#left-panel::-webkit-scrollbar {
    width: 8px !important;
}

#left-panel::-webkit-scrollbar-track {
    background: #f0f0f0 !important;
}

#left-panel::-webkit-scrollbar-thumb {
    background: #cfcfcf !important;
    border-radius: 4px !important;
}

#left-panel::-webkit-scrollbar-thumb:hover {
    background: #bdbdbd !important;
}

/* Dropdown menu items styling */
.dropdown-nav-item {
    position: relative;
}

.dropdown-nav-item .dropdown-toggle {
    cursor: pointer;
    position: relative;
}

.dropdown-nav-item .dropdown-toggle i.fa-chevron-down {
    transition: transform 0.3s ease;
    font-size: 0.75rem;
    margin-top: 3px;
}

.dropdown-nav-item .dropdown-toggle[aria-expanded="true"] i.fa-chevron-down {
    transform: rotate(180deg);
}

.dropdown-nav-item .submenu {
    background-color: #f8f9fa !important;
    border-left: 2px solid #e0e0e0;
    margin-left: 10px;
}

.dropdown-nav-item .submenu li {
    border-bottom: none !important;
}

.dropdown-nav-item .submenu li a {
    padding-left: 15px !important;
    font-size: 0.9rem;
    color: #2f2f2f !important;
}

.dropdown-nav-item .submenu li a:hover {
    background-color: #f2f2f2 !important;
    padding-left: 20px !important;
}

.dropdown-nav-item .submenu li a i {
    margin-right: 8px;
    font-size: 0.85rem;
}

/* Header styling */
#header {
    background-color: #ffffff !important;
    border-bottom: 2px solid #e0e0e0 !important;
}

#header .header-menu {
    background-color: #ffffff !important;
}

#header .menutoggle,
#header .search-trigger,
#header .btn-secondary {
    color: #2f2f2f !important;
}

#header .user-avatar {
    border: 2px solid #e0e0e0 !important;
}

#header .dropdown-toggle {
    color: #2f2f2f !important;
}

#header .dropdown-menu {
    border: 1px solid #e0e0e0 !important;
}

#header .dropdown-menu a {
    color: #2f2f2f !important;
}

#header .dropdown-menu a:hover {
    background-color: #f5f5f5 !important;
    color: #2f2f2f !important;
}

/* Brand styling */
.brand-title {
    color: #940000 !important;
    font-weight: 700 !important;
    font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
}

/* Sidebar profile block */
.sidebar-profile {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(148, 0, 0, 0.08);
    border: 1px solid rgba(148, 0, 0, 0.35);
    border-radius: 8px;
}
.sidebar-profile img.profile-image {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    background: rgba(148, 0, 0, 0.08);
    border: 2px solid rgba(148, 0, 0, 0.35);
}
.sidebar-profile .profile-name {
    font-weight: 700;
    color: #2f2f2f !important;
}
.sidebar-profile .profile-role {
    font-size: 0.8rem;
    color: #666666 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

</head>
<body>
    <!-- Left Panel -->

  <!-- Left Panel -->
<aside id="left-panel" class="left-panel">
    <nav class="navbar navbar-expand-sm navbar-default">

        <div class="navbar-header">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand hidden" href="#">SL</a>
        </div>

        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <!-- Profile -->
                <li class="text-center mt-3 mb-2">
                    <div class="sidebar-profile">
                        <img src="{{ asset('images/shuleXpert.jpg') }}" alt="Admin" class="profile-image">
                        <div class="profile-meta text-left">
                            <div class="profile-role">User Type</div>
                            <div class="profile-name">Administrator</div>
                        </div>
                    </div>
                </li>
                <!-- Sidebar Links -->
                <li class="sidebar-links-container">
                    <ul style="list-style: none; padding: 0; margin: 0; font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
                        <li><a href="{{ route('AdminDashboard') }}" class="nav-link"><i class="fa fa-building"></i> Dashboard</a></li>

                        <!-- User Management -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#userManagement" aria-expanded="false">
                                <i class="fa fa-users"></i> User Management <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="userManagement" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('school') }}" class="nav-link"><i class="fa fa-building"></i> School</a></li>
                                <li><a href="{{ route('manageTeachers') }}" class="nav-link"><i class="fa fa-users"></i> Teachers And Staff</a></li>
                                <li><a href="{{ route('manage_student') }}" class="nav-link"><i class="fa fa-user"></i> Students</a></li>
                                <li><a href="{{ route('admin.school_visitors') }}" class="nav-link"><i class="fa fa-id-badge"></i> School Visitors</a></li>
                            </ul>
                        </li>

                        <!-- Academic Management -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#academicManagement" aria-expanded="false">
                                <i class="fa fa-graduation-cap"></i> Academic Management <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="academicManagement" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('manageClasses') }}" class="nav-link"><i class="fa fa-columns"></i> Classes</a></li>
                                <li><a href="{{ route('manageSubjects') }}" class="nav-link"><i class="fa fa-bookmark"></i> Subjects</a></li>
                                <li><a href="{{ route('manage_fees') }}" class="nav-link"><i class="fa fa-money"></i> Fees</a></li>
                                <li><a href="{{ route('manage_library') }}" class="nav-link"><i class="fa fa-book"></i> Library</a></li>
                                <li><a href="{{ route('manageResults') }}" class="nav-link"><i class="fa fa-trophy"></i> Results</a></li>
                                <li><a href="{{ route('manageExamination') }}" class="nav-link"><i class="fa fa-pencil-square-o"></i> Examinations</a></li>
                                <li><a href="{{ route('manageAttendance') }}" class="nav-link"><i class="fa fa-check-square-o"></i> Attendance</a></li>
                            </ul>
                        </li>

                        <!-- Planning & Scheduling -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#planningScheduling" aria-expanded="false">
                                <i class="fa fa-calendar"></i> Planning & Scheduling <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="planningScheduling" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('timeTable') }}" class="nav-link"><i class="fa fa-clock-o"></i> Time Tables</a></li>
                                <li><a href="{{ route('admin.calendar') }}" class="nav-link"><i class="fa fa-calendar"></i> Calendar</a></li>
                                <li><a href="{{ route('admin.schemeOfWork') }}" class="nav-link"><i class="fa fa-book"></i> Scheme of Work</a></li>
                                <li><a href="{{ route('admin.lessonPlans') }}" class="nav-link"><i class="fa fa-file-text"></i> Lesson Plans</a></li>
                                <li><a href="{{ route('admin.academicYears') }}" class="nav-link"><i class="fa fa-calendar-check-o"></i> Academic Years</a></li>
                            </ul>
                        </li>

                        <!-- Services -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#services" aria-expanded="false">
                                <i class="fa fa-cogs"></i> Services <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="services" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('taskManagement') }}" class="nav-link"><i class="fa fa-tasks"></i> Tasks</a></li>
                                <li><a href="{{ route('fingerprint_device_settings') }}" class="nav-link"><i class="fa fa-id-card"></i> Fingerprint</a></li>
                                <li><a href="{{ route('manage_accomodation') }}" class="nav-link"><i class="fa fa-bed"></i> Hostel</a></li>
                                <li><a href="{{ route('sms_notification') }}" class="nav-link"><i class="fa fa-bell"></i> SMS Information</a></li>
                            </ul>
                        </li>

                        <!-- HR Operations -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#hrOperations" aria-expanded="false">
                                <i class="fa fa-briefcase"></i> HR Operations <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="hrOperations" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.hr.leave') ? route('admin.hr.leave') : '#' }}" class="nav-link">
                                        <i class="fa fa-calendar"></i> Leave
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.hr.permission') ? route('admin.hr.permission') : '#' }}" class="nav-link">
                                        <i class="fa fa-check-square-o"></i> Permission
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.hr.payroll') ? route('admin.hr.payroll') : '#' }}" class="nav-link">
                                        <i class="fa fa-money"></i> Payroll
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Reports & Analytics -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#reportsAnalytics" aria-expanded="false">
                                <i class="fa fa-bar-chart"></i> Reports & Analytics <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="reportsAnalytics" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                @php
                                    $schoolID = Session::get('schoolID');
                                    $unreadSuggestions = 0;
                                    $unreadIncidents = 0;
                                    if ($schoolID) {
                                        $unreadSuggestions = \App\Models\TeacherFeedback::where('schoolID', $schoolID)
                                            ->where('type', 'suggestion')
                                            ->where('is_read_by_admin', false)
                                            ->count();
                                        $unreadIncidents = \App\Models\TeacherFeedback::where('schoolID', $schoolID)
                                            ->where('type', 'incident')
                                            ->where('is_read_by_admin', false)
                                            ->count();
                                    }
                                @endphp
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.suggestions') ? route('admin.suggestions') : '#' }}" class="nav-link">
                                        <i class="fa fa-lightbulb-o"></i> Suggestions
                                        @if($unreadSuggestions > 0)
                                            <span class="badge badge-danger ml-1">{{ $unreadSuggestions }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.incidents') ? route('admin.incidents') : '#' }}" class="nav-link">
                                        <i class="fa fa-exclamation-triangle"></i> Incidents
                                        @if($unreadIncidents > 0)
                                            <span class="badge badge-danger ml-1">{{ $unreadIncidents }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.performance') ? route('admin.performance') : '#' }}" class="nav-link">
                                        <i class="fa fa-line-chart"></i> Performance
                                    </a>
                                </li>
                                <li><a href="{{ route('admin.printing_unit') }}" class="nav-link"><i class="fa fa-print"></i> Printing Unit</a></li>
                            </ul>
                        </li>

                        <!-- Revenue and Expenses -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#revenueExpenses" aria-expanded="false">
                                <i class="fa fa-money"></i> Revenue and Expenses <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="revenueExpenses" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('manage_revenue') }}" class="nav-link"><i class="fa fa-arrow-up"></i> Manage Revenue</a></li>
                                <li><a href="{{ route('manage_expenses') }}" class="nav-link"><i class="fa fa-arrow-down"></i> Manage Expenses</a></li>
                            </ul>
                        </li>

                        <!-- School Resources -->
                        <li class="dropdown-nav-item">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#schoolResources" aria-expanded="false">
                                <i class="fa fa-cubes"></i> School Resources <i class="fa fa-chevron-down float-right"></i>
                            </a>
                            <ul id="schoolResources" class="collapse submenu" style="list-style: none; padding-left: 20px; margin: 0;">
                                <li><a href="{{ route('manage_incoming_resources') }}" class="nav-link"><i class="fa fa-arrow-circle-down"></i> Incoming Resources</a></li>
                                <li><a href="{{ route('manage_outgoing_resources') }}" class="nav-link"><i class="fa fa-arrow-circle-up"></i> Outgoing Resources</a></li>
                                <li><a href="{{ route('manage_damaged_lost_items') }}" class="nav-link"><i class="fa fa-exclamation-triangle"></i> Damaged / Lost Items</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>
</aside>

    <!-- Left Panel -->

    <!-- Right Panel -->

    <div id="right-panel" class="right-panel">

        <!-- Header-->
        <header id="header" class="header">

            <div class="header-menu">

                <div class="col-sm-7">
                    <a id="menuToggle" class="menutoggle pull-left"><i class="fa fa fa-tasks"></i></a>
                    <div class="header-left">
                        <button class="search-trigger"><i class="fa fa-search"></i></button>
                        <div class="form-inline">
                            <form class="search-form">
                                <input class="form-control mr-sm-2" type="text" placeholder="Search ..." aria-label="Search">
                                <button class="search-close" type="submit"><i class="fa fa-close"></i></button>
                            </form>
                        </div>

                        <div class="dropdown for-notification">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="notification" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell"></i>
                                <span class="count bg-danger">5</span>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="notification">
                                <p class="red">You have 3 Notification</p>
                                <a class="dropdown-item media bg-flat-color-1" href="#">
                                <i class="fa fa-check"></i>
                                <p>Server #1 overloaded.</p>
                            </a>
                                <a class="dropdown-item media bg-flat-color-4" href="#">
                                <i class="fa fa-info"></i>
                                <p>Server #2 overloaded.</p>
                            </a>
                                <a class="dropdown-item media bg-flat-color-5" href="#">
                                <i class="fa fa-warning"></i>
                                <p>Server #3 overloaded.</p>
                            </a>
                            </div>
                        </div>

                        <div class="dropdown for-message">
                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                id="message"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti-email"></i>
                                <span class="count bg-primary">9</span>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="message">
                                <p class="red">You have 4 Mails</p>
                                <a class="dropdown-item media bg-flat-color-1" href="#">
                                <span class="photo media-left"><img alt="avatar" src="{{ asset('images/avatar/1.jpg') }}"></span>
                                <span class="message media-body">
                                    <span class="name float-left">Jonathan Smith</span>
                                    <span class="time float-right">Just now</span>
                                        <p>Hello, this is an example msg</p>
                                </span>
                            </a>
                                <a class="dropdown-item media bg-flat-color-4" href="#">
                                <span class="photo media-left"><img alt="avatar" src="{{ asset('images/avatar/2.jpg') }}"></span>
                                <span class="message media-body">
                                    <span class="name float-left">Jack Sanders</span>
                                    <span class="time float-right">5 minutes ago</span>
                                        <p>Lorem ipsum dolor sit amet, consectetur</p>
                                </span>
                            </a>
                                <a class="dropdown-item media bg-flat-color-5" href="#">
                                <span class="photo media-left"><img alt="avatar" src="{{ asset('images/avatar/3.jpg') }}"></span>
                                <span class="message media-body">
                                    <span class="name float-left">Cheryl Wheeler</span>
                                    <span class="time float-right">10 minutes ago</span>
                                        <p>Hello, this is an example msg</p>
                                </span>
                            </a>
                                <a class="dropdown-item media bg-flat-color-3" href="#">
                                <span class="photo media-left"><img alt="avatar" src="{{ asset('images/avatar/4.jpg') }}"></span>
                                <span class="message media-body">
                                    <span class="name float-left">Rachel Santos</span>
                                    <span class="time float-right">15 minutes ago</span>
                                        <p>Lorem ipsum dolor sit amet, consectetur</p>
                                </span>
                            </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-5">
                    <div class="user-area dropdown float-right">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="user-avatar rounded-circle" src="{{ asset('images/admin.jpg') }}" alt="User Avatar">
                        </a>

                        <div class="user-menu dropdown-menu">
                            <a class="nav-link" href="#"><i class="fa fa-user"></i> My Profile</a>

                            <a class="nav-link" href="#"><i class="fa fa-user"></i> Notifications <span class="count">13</span></a>

                            <a class="nav-link" href="#"><i class="fa fa-cog"></i> Settings</a>

                            <a class="nav-link" href="{{ route('logout') }}"><i class="fa fa-power-off"></i> Logout</a>
                        </div>
                    </div>

                    <div class="language-select dropdown" id="language-select">
                        <a class="dropdown-toggle" href="#" data-toggle="dropdown"  id="language" aria-haspopup="true" aria-expanded="true">
                        <span class="flag-icon flag-icon-tz"></span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="language">
                            <div class="dropdown-item">
                                <span class="flag-icon flag-icon-fr"></span>
                            </div>
                            <div class="dropdown-item">
                                <i class="flag-icon flag-icon-es"></i>
                            </div>
                            <div class="dropdown-item">
                                <i class="flag-icon flag-icon-us"></i>
                            </div>
                            <div class="dropdown-item">
                                <i class="flag-icon flag-icon-it"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </header><!-- /header -->
        <!-- Header-->

<script>
// Ensure $ inside this block refers to jQuery (pass window.jQuery into IIFE)
(function(){
// Function to initialize menu and dropdowns
function initializeMenuDropdowns() {
    // Wait for jQuery to be available and for Bootstrap collapse plugin
    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn === 'undefined' || typeof window.jQuery.fn.collapse === 'undefined') {
        setTimeout(initializeMenuDropdowns, 100);
        return;
    }

    // Use a local $ bound to window.jQuery to avoid conflicts
    var $ = window.jQuery;

    // Get all sidebar menu links
    const menuLinks = document.querySelectorAll('#left-panel .nav-link');

    // Remove active class from all links
    function removeActiveClass() {
        menuLinks.forEach(link => {
            link.classList.remove('menu-active');
        });
    }

    // Reset all dropdowns to closed state first
    function resetAllDropdowns() {
        document.querySelectorAll('.dropdown-nav-item .collapse').forEach(collapse => {
            const $collapse = $(collapse);
            if ($collapse.hasClass('show') && typeof $collapse.collapse === 'function') {
                $collapse.collapse('hide');
            }
            const toggle = collapse.previousElementSibling;
            if (toggle && toggle.classList.contains('dropdown-toggle')) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Initialize all collapse elements and close them by default
    document.querySelectorAll('.dropdown-nav-item .collapse').forEach(collapse => {
        // Initialize collapse if not already initialized and if collapse function is available
        const $collapse = $(collapse);
        if (!$collapse.data('bs.collapse') && typeof $collapse.collapse === 'function') {
            $collapse.collapse({
                toggle: false
            });
        }
        // Ensure all dropdowns are closed on page load
        if ($collapse.hasClass('show')) {
            if (typeof $collapse.collapse === 'function') {
                $collapse.collapse('hide');
            }
        }
        // Set aria-expanded to false
        const toggle = collapse.previousElementSibling;
        if (toggle && toggle.classList.contains('dropdown-toggle')) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Remove all existing event listeners by cloning and replacing
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        const newToggle = toggle.cloneNode(true);
        toggle.parentNode.replaceChild(newToggle, toggle);
    });

    // Add click event listener to each link
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Handle dropdown toggle
            if (this.classList.contains('dropdown-toggle')) {
                e.preventDefault();
                e.stopPropagation();

                const targetId = this.getAttribute('data-target');
                const target = document.querySelector(targetId);

                if (!target) return;

                const $target = $(target);
                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                // Toggle current dropdown
                if (isExpanded) {
                    // Close current dropdown
                    if (typeof $target.collapse === 'function') {
                        $target.collapse('hide');
                    }
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    // Close all other dropdowns first
                    document.querySelectorAll('.dropdown-nav-item .collapse').forEach(collapse => {
                        const $collapse = $(collapse);
                        if (collapse.id !== targetId.replace('#', '') && $collapse.hasClass('show') && typeof $collapse.collapse === 'function') {
                            $collapse.collapse('hide');
                            const otherToggle = collapse.previousElementSibling;
                            if (otherToggle && otherToggle.classList.contains('dropdown-toggle')) {
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });

                    // Open current dropdown after a small delay to ensure others are closed
                    setTimeout(() => {
                        if (typeof $target.collapse === 'function') {
                            $target.collapse('show');
                        }
                        this.setAttribute('aria-expanded', 'true');
                    }, 50);
                }

                return false;
            }

            // Don't prevent default if it's not a hash link
            if (this.getAttribute('href') !== '#') {
                // Remove active class from all links
                removeActiveClass();
                // Add active class to clicked link
                this.classList.add('menu-active');
            }
        });
    });

    // Set active based on current URL on page load
    const currentUrl = window.location.href;
    const currentPath = window.location.pathname;

    // Function to check if URL matches
    function urlMatches(linkHref, currentUrl, currentPath) {
        if (!linkHref || linkHref === '#') return false;

        // Remove query strings and fragments for comparison
        let linkPath = linkHref.split('?')[0].split('#')[0].replace(/\/$/, ''); // Remove trailing slash
        let currentPathClean = currentPath.split('?')[0].split('#')[0].replace(/\/$/, '');
        let currentUrlClean = currentUrl.split('?')[0].split('#')[0].replace(/\/$/, '');

        // Normalize paths
        linkPath = linkPath.toLowerCase();
        currentPathClean = currentPathClean.toLowerCase();
        currentUrlClean = currentUrlClean.toLowerCase();

        // Check exact match
        if (currentPathClean === linkPath || currentUrlClean === linkPath) {
            return true;
        }

        // Check if current URL/path ends with link path (for nested routes)
        if (currentPathClean.endsWith(linkPath) || currentUrlClean.endsWith(linkPath)) {
            return true;
        }

        // Check if current URL/path contains link path (for routes with parameters)
        if (linkPath && (currentPathClean.includes(linkPath) || currentUrlClean.includes(linkPath))) {
            return true;
        }

        return false;
    }

    // First reset all dropdowns - close ALL dropdowns
    resetAllDropdowns();

    // Force close all dropdowns again after a delay
    setTimeout(() => {
        document.querySelectorAll('.dropdown-nav-item .collapse').forEach(collapse => {
            const $collapse = $(collapse);
            if (typeof $collapse.collapse === 'function') {
                $collapse.removeClass('show').collapse('hide');
            }
            const toggle = collapse.previousElementSibling;
            if (toggle && toggle.classList.contains('dropdown-toggle')) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }, 100);

    // Then set active link and expand ONLY the parent dropdown of active link
    setTimeout(() => {
        let activeLinkFound = false;

        menuLinks.forEach(link => {
            const linkHref = link.getAttribute('href');
            if (urlMatches(linkHref, currentUrl, currentPath)) {
                link.classList.add('menu-active');
                activeLinkFound = true;

                // If link is in a submenu, expand the parent dropdown and make it active
                const submenu = link.closest('.submenu');
                if (submenu) {
                    const $submenu = $(submenu);
                    // Find the parent dropdown toggle (it should be before the submenu)
                    const parentLi = submenu.closest('li.dropdown-nav-item');
                    if (parentLi) {
                        const dropdownToggle = parentLi.querySelector('.dropdown-toggle');
                        if (dropdownToggle) {
                            // Show the dropdown
                            if (typeof $submenu.collapse === 'function') {
                                $submenu.collapse('show');
                            }
                            dropdownToggle.setAttribute('aria-expanded', 'true');

                            // Add active class to parent toggle
                            dropdownToggle.classList.add('menu-active');
                        }
                    } else {
                        // Fallback to previous method
                        const dropdownToggle = submenu.previousElementSibling;
                        if (dropdownToggle && dropdownToggle.classList.contains('dropdown-toggle')) {
                            if (typeof $submenu.collapse === 'function') {
                                $submenu.collapse('show');
                            }
                            dropdownToggle.setAttribute('aria-expanded', 'true');
                            dropdownToggle.classList.add('menu-active');
                        }
                    }
                }
            }
        });

        // If no active link found, check if we need to highlight parent menu
        // BUT ONLY expand ONE dropdown - the one containing the active link
        if (!activeLinkFound) {
            // Check if any route path matches partially (for nested routes)
            menuLinks.forEach(link => {
                const linkHref = link.getAttribute('href');
                if (linkHref && linkHref !== '#') {
                    const linkPath = linkHref.split('?')[0].split('#')[0];
                    // Only match if path is significant (not just "/")
                    if (linkPath && linkPath !== '/' && linkPath.length > 1) {
                        if (currentPath.includes(linkPath) || currentUrl.includes(linkPath)) {
                            link.classList.add('menu-active');
                            activeLinkFound = true;

                            // Expand ONLY the parent dropdown of this active link
                            const submenu = link.closest('.submenu');
                            if (submenu) {
                                const $submenu = $(submenu);
                                const parentLi = submenu.closest('li.dropdown-nav-item');
                                if (parentLi) {
                                    const dropdownToggle = parentLi.querySelector('.dropdown-toggle');
                                    if (dropdownToggle) {
                                        // Close all other dropdowns first
                                        document.querySelectorAll('.dropdown-nav-item .collapse').forEach(c => {
                                            if (c !== submenu) {
                                                const $c = $(c);
                                                if (typeof $c.collapse === 'function') {
                                                    $c.removeClass('show').collapse('hide');
                                                }
                                            }
                                        });
                                        // Then open only this one
                                        if (typeof $submenu.collapse === 'function') {
                                            $submenu.collapse('show');
                                        }
                                        dropdownToggle.setAttribute('aria-expanded', 'true');
                                        dropdownToggle.classList.add('menu-active');
                                    }
                                } else {
                                    const dropdownToggle = submenu.previousElementSibling;
                                    if (dropdownToggle && dropdownToggle.classList.contains('dropdown-toggle')) {
                                        // Close all other dropdowns first
                                        document.querySelectorAll('.dropdown-nav-item .collapse').forEach(c => {
                                            if (c !== submenu) {
                                                const $c = $(c);
                                                if (typeof $c.collapse === 'function') {
                                                    $c.removeClass('show').collapse('hide');
                                                }
                                            }
                                        });
                                        // Then open only this one
                                        if (typeof $submenu.collapse === 'function') {
                                            $submenu.collapse('show');
                                        }
                                        dropdownToggle.setAttribute('aria-expanded', 'true');
                                        dropdownToggle.classList.add('menu-active');
                                    }
                                }
                            }
                            // Stop after finding first match
                            return false;
                        }
                    }
                }
            });
        }

        // Also check for parent dropdowns that might contain active children
        if (activeLinkFound) {
            // Ensure all parent dropdowns of active links are open and highlighted
            document.querySelectorAll('#left-panel .nav-link.menu-active').forEach(activeLink => {
                const submenu = activeLink.closest('.submenu');
                if (submenu) {
                    const parentLi = submenu.closest('li.dropdown-nav-item');
                    if (parentLi) {
                        const dropdownToggle = parentLi.querySelector('.dropdown-toggle');
                        if (dropdownToggle && !dropdownToggle.classList.contains('menu-active')) {
                            dropdownToggle.classList.add('menu-active');
                        }
                    }
                }
            });
        }
    }, 300);

    // Initialize Bootstrap collapse events for dropdowns
    $('.dropdown-nav-item .collapse').off('show.bs.collapse hide.bs.collapse').on('show.bs.collapse', function() {
        const toggle = $(this).prev('.dropdown-toggle');
        if (toggle.length) {
            toggle.attr('aria-expanded', 'true');
        }
    }).on('hide.bs.collapse', function() {
        const toggle = $(this).prev('.dropdown-toggle');
        if (toggle.length) {
            toggle.attr('aria-expanded', 'false');
        }
    });

    // Also listen for when links are clicked that navigate to new pages
    menuLinks.forEach(link => {
        if (link.getAttribute('href') && link.getAttribute('href') !== '#') {
            link.addEventListener('click', function() {
                // Close all dropdowns when navigating
                setTimeout(() => {
                    resetAllDropdowns();
                }, 100);
            });
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeMenuDropdowns);

// Also re-initialize when page is shown (for back/forward navigation)
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        initializeMenuDropdowns();
    }
});

// Re-initialize after a short delay to ensure everything is loaded
setTimeout(initializeMenuDropdowns, 500);

})(window.jQuery);
</script>
