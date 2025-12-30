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
    color: #940000 !important;
}

/* Badilisha rangi ya maandishi (links) */
#left-panel .nav-link,
#left-panel a.nav-link,
#left-panel li a,
#left-panel .navbar-nav li a,
#left-panel .navbar-nav > li > a {
    color: #940000 !important;
    font-weight: 600;
    background-color: transparent !important;
    font-family: Arial, sans-serif !important;
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
    background-color: #f8f9fa !important; /* kijivu chepesi */
    color: #940000 !important;
}

/* Active link */
#left-panel .nav-link.active,
#left-panel a.nav-link.active,
#left-panel li.active > a,
#left-panel .navbar-nav > li.active > a,
#left-panel .nav-link.menu-active,
#left-panel a.nav-link.menu-active,
#left-panel li.menu-active > a {
    background-color: #940000 !important;
    color: #ffffff !important;
    border-radius: 4px;
}

/* Active link icons - white when active */
#left-panel .nav-link.active i,
#left-panel a.nav-link.active i,
#left-panel li.active > a i,
#left-panel .nav-link.menu-active i,
#left-panel a.nav-link.menu-active i {
    color: #ffffff !important;
}

/* Rangi ya jina la "Teacher" na maandishi ya ndani ya sidebar */
#left-panel p,
#left-panel .navbar-brand,
#left-panel .navbar-brand:hover {
    color: #940000 !important;
}

/* Rangi ya navbar brand */
#left-panel .navbar-header .navbar-brand {
    color: #940000 !important;
}

/* Rangi ya toggle button */
#left-panel .navbar-toggler,
#left-panel .navbar-toggler i {
    color: #940000 !important;
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
    color: #940000 !important;
}

/* Exception for icons - keep them #940000 */
#left-panel i,
#left-panel .fa {
    color: #940000 !important;
}

/* Overflow scroll kwa sidebar links container - scrollbar hidden */
.sidebar-links-container {
    overflow-y: auto !important;
    overflow-x: hidden !important;
    max-height: calc(100vh - 200px) !important;
    scrollbar-width: none !important; /* Firefox */
    -ms-overflow-style: none !important; /* IE and Edge */
    width: 100% !important;
}

.sidebar-links-container::-webkit-scrollbar {
    display: none !important; /* Chrome, Safari, Opera */
}

.sidebar-links-container ul {
    width: 100% !important;
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
            <a class="navbar-brand" href="#">Shule Link</a>
            <a class="navbar-brand hidden" href="#">SL</a>
        </div>

        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <!-- Profile with School Logo -->
                <li class="text-center my-3">
                    @php
                        $schoolLogo = isset($school_details) && $school_details && $school_details->school_logo 
                            ? asset($school_details->school_logo) 
                            : asset('images/admin.jpg');
                        $schoolName = isset($school_details) && $school_details && $school_details->school_name 
                            ? $school_details->school_name 
                            : 'Shule Link';
                    @endphp
                    <img src="{{ $schoolLogo }}" alt="School Logo" class="rounded-circle" width="80" height="80" style="object-fit: cover; border: 3px solid #940000;">
                    <p class="mt-2 mb-0 font-weight-bold" style="font-family: Arial, sans-serif; color: #940000;">{{ $schoolName }}</p>
                    <p class="mb-0 small" style="font-family: Arial, sans-serif; color: #940000;">Admin</p>
                </li>
                <!-- Sidebar Links -->
                <li class="sidebar-links-container">
                    <ul style="list-style: none; padding: 0; margin: 0; font-family: Arial, sans-serif;">
                        <li><a href="{{ route('AdminDashboard') }}" class="nav-link"><i class="fa fa-building"></i> Dashboard</a></li>
                        <li><a href="{{ route('school') }}" class="nav-link"><i class="fa fa-building"></i> School Management</a></li>
                        <li><a href="{{ route('manageTeachers') }}" class="nav-link"><i class="fa fa-users"></i> Teachers Management</a></li>
                        <li><a href="{{ route('manage_student') }}" class="nav-link"><i class="fa fa-user"></i> Students Management</a></li>
                        <li><a href="{{ route('manage_parents') }}" class="nav-link"><i class="fa fa-user-plus"></i> Parents Management</a></li>
                        <li><a href="{{ route('manage_fees') }}" class="nav-link"><i class="fa fa-money"></i> Fees Management</a></li>
                        <li><a href="{{ route('manage_library') }}" class="nav-link"><i class="fa fa-book"></i> Library Management</a></li>
                        <li><a href="{{ route('manageClasses') }}" class="nav-link"><i class="fa fa-columns"></i> Classes Management</a></li>
                        <li><a href="{{ route('manageSubjects') }}" class="nav-link"><i class="fa fa-bookmark"></i> Subjects Management</a></li>
                        <li><a href="{{ route('manage_accomodation') }}" class="nav-link"><i class="fa fa-bed"></i> Accommodation Management</a></li>
                        <li><a href="{{ route('sms_notification') }}" class="nav-link"><i class="fa fa-bell"></i> SMS Information Management</a></li>
                        <li><a href="{{ route('manageResults') }}" class="nav-link"><i class="fa fa-trophy"></i> Results Management</a></li>
                        <li><a href="{{ route('manageExamination') }}" class="nav-link"><i class="fa fa-pencil-square-o"></i> Examination Management</a></li>
                        <li><a href="{{ route('manageAttendance') }}" class="nav-link"><i class="fa fa-check-square-o"></i> Attendance Management</a></li>
                        <li><a href="{{ route('taskManagement') }}" class="nav-link"><i class="fa fa-tasks"></i> Task Management</a></li>
                        <li><a href="{{ route('fingerprint_device_settings') }}" class="nav-link"><i class="fa fa-fingerprint"></i> Fingerprint Device Settings</a></li>
                        <li><a href="{{ route('timeTable') }}" class="nav-link"><i class="fa fa-clock-o"></i> Time Tables Management</a></li>
                        <li><a href="{{ route('admin.calendar') }}" class="nav-link"><i class="fa fa-calendar"></i> Calendar Management</a></li>
                        <li><a href="#" class="nav-link"><i class="fa fa-lightbulb-o"></i> Suggestions Management</a></li>
                        <li><a href="#" class="nav-link"><i class="fa fa-exclamation-triangle"></i> Incidence Management</a></li>
                        <li><a href="#" class="nav-link"><i class="fa fa-line-chart"></i> Performance Management</a></li>
                        <li><a href="#" class="nav-link"><i class="fa fa-car"></i> Combies Management</a></li>
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
// Add active state to menu items on click
document.addEventListener('DOMContentLoaded', function() {
    // Get all sidebar menu links
    const menuLinks = document.querySelectorAll('#left-panel .nav-link');
    
    // Remove active class from all links
    function removeActiveClass() {
        menuLinks.forEach(link => {
            link.classList.remove('menu-active');
        });
    }
    
    // Add click event listener to each link
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
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
    
    menuLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref && linkHref !== '#') {
            // Check if current URL matches the link
            if (currentUrl.includes(linkHref) || currentPath === linkHref) {
                link.classList.add('menu-active');
            }
        }
    });
});
</script>
