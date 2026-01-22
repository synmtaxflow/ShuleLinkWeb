<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWN1YhP4P9RZ6HlfD/yEw9Q0mWvH/V+VjFzlY5Vq6y1wq3h+v1rIYh0dA6ZPZbUq" crossorigin="anonymous">
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
#left-panel .navbar-nav > li.active > a {
    background-color: #f8f9fa !important;
    color: #940000 !important;
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
            <a class="navbar-brand" href="#">ShuleXpert</a>
            <a class="navbar-brand hidden" href="#">SL</a>
        </div>

        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <!-- Profile -->
                <li class="text-center my-3">
                    @php
                        $parentID = Session::get('parentID');
                        $schoolID = Session::get('schoolID');
                        $parent = $parentID ? \App\Models\ParentModel::where('parentID', $parentID)
                            ->where('schoolID', $schoolID)
                            ->first() : null;
                        $parentName = $parent ? ($parent->first_name . ' ' . ($parent->last_name ?? '')) : 'Parent';
                        $parentPhoto = $parent && $parent->photo ? asset('userImages/' . $parent->photo) : asset('images/admin.jpg');
                    @endphp
                    <img src="{{ $parentPhoto }}" alt="{{ $parentName }}" class="rounded-circle" width="80" height="80" style="object-fit: cover; border: 2px solid #940000;">
                    <p class="mt-2 mb-0 font-weight-bold">{{ $parentName }}</p>
                </li>
                <!-- Sidebar Links -->
                <li class="sidebar-links-container">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li><a href="{{ route('parentDashboard') }}" class="nav-link"><i class="fa fa-building"></i> <span data-translate="dashboard">Dashboard</span></a></li>
                        <li><a href="{{ route('parentResults') }}" class="nav-link"><i class="fa fa-trophy"></i> <span data-translate="results">Results</span></a></li>
                        <li><a href="{{ route('parentAttendance') }}" class="nav-link"><i class="fa fa-calendar-check-o"></i> <span data-translate="attendance">Attendance</span></a></li>
                        <li><a href="{{ route('parentSubjects') }}" class="nav-link"><i class="fa fa-book"></i> <span data-translate="subjects">Subjects</span></a></li>
                        <li><a href="{{ route('parentPayments') }}" class="nav-link"><i class="fa fa-credit-card"></i> <span data-translate="payments">Payments</span></a></li>
                        <li><a href="{{ route('parentFeesSummary') }}" class="nav-link"><i class="fa fa-list-alt"></i> <span data-translate="fees_summary">Fees Summary</span></a></li>
                        <li><a href="{{ route('parent.permissions') }}" class="nav-link"><i class="fa fa-check-square-o"></i> <span data-translate="permissions">Permission</span></a></li>
                    </ul>
                </li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>
</aside>

    <!-- Left Panel -->

    <!-- Right Panel -->
    
    <!-- Language Translation Script -->
    <script>
    (function() {
        // Language translations
        const translations = {
            'sw': {
                'dashboard': 'Dashboard',
                'results': 'Matokeo',
                'attendance': 'Ushiriki',
                'subjects': 'Masomo',
                'payments': 'Malipo',
                'fees_summary': 'Muhtasari wa Ada',
                'permissions': 'Ruhusa',
                'my_profile': 'Wasifu Wangu',
                'notifications': 'Arifa',
                'settings': 'Mipangilio',
                'logout': 'Toka'
            },
            'en': {
                'dashboard': 'Dashboard',
                'results': 'Results',
                'attendance': 'Attendance',
                'subjects': 'Subjects',
                'payments': 'Payments',
                'fees_summary': 'Fees Summary',
                'permissions': 'Permission',
                'my_profile': 'My Profile',
                'notifications': 'Notifications',
                'settings': 'Settings',
                'logout': 'Logout'
            }
        };

        // Get current language from session or default to Swahili
        let currentLang = '{{ Session::get("locale", "sw") }}';

        // Function to update translations
        function updateTranslations() {
            const lang = currentLang;
            document.querySelectorAll('[data-translate]').forEach(function(element) {
                const key = element.getAttribute('data-translate');
                if (translations[lang] && translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });
        }

        // Language switcher
        document.addEventListener('DOMContentLoaded', function() {
            // Update translations on page load
            updateTranslations();

            // Handle language selection
            document.querySelectorAll('.language-option').forEach(function(option) {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    const lang = this.getAttribute('data-lang');
                    
                    // Update language via AJAX
                    fetch('/change-language', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ locale: lang })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            currentLang = lang;
                            updateTranslations();
                            
                            // Update language selector display
                            const langText = lang === 'en' ? 'English' : 'Kiswahili';
                            const langFlag = lang === 'en' ? 'us' : 'tz';
                            const selector = document.querySelector('#language');
                            if (selector) {
                                selector.innerHTML = '<span class="flag-icon flag-icon-' + langFlag + '" style="margin-right: 5px;"></span><span>' + langText + '</span>';
                            }
                            
                            // Reload page to apply translations to all pages
                            setTimeout(function() {
                                window.location.reload();
                            }, 100);
                        }
                    })
                    .catch(error => {
                        console.error('Error changing language:', error);
                    });
                });
            });
        });
    })();
    </script>

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
                        @php
                            $parentID = Session::get('parentID');
                            $schoolID = Session::get('schoolID');
                            $parent = $parentID ? \App\Models\ParentModel::where('parentID', $parentID)
                                ->where('schoolID', $schoolID)
                                ->first() : null;
                            $parentName = $parent ? ($parent->first_name . ' ' . ($parent->last_name ?? '')) : 'Parent';
                            $parentPhoto = $parent && $parent->photo ? asset('userImages/' . $parent->photo) : asset('images/admin.jpg');
                        @endphp
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="user-avatar rounded-circle" src="{{ $parentPhoto }}" alt="{{ $parentName }}" style="width: 40px; height: 40px; object-fit: cover;">
                        </a>

                        <div class="user-menu dropdown-menu">
                            <a class="nav-link" href="#"><i class="fa fa-user"></i> <span data-translate="my_profile">My Profile</span></a>

                            <a class="nav-link" href="#"><i class="fa fa-user"></i> <span data-translate="notifications">Notifications</span> <span class="count">13</span></a>

                            <a class="nav-link" href="#"><i class="fa fa-cog"></i> <span data-translate="settings">Settings</span></a>

                            <a class="nav-link" href="{{ route('logout') }}"><i class="fa fa-power-off"></i> <span data-translate="logout">Logout</span></a>
                        </div>
                    </div>

                    <div class="language-select dropdown float-right mr-2" id="language-select">
                        @php
                            $currentLang = Session::get('locale', 'sw');
                            $langText = $currentLang === 'en' ? 'English' : 'Kiswahili';
                            $langFlag = $currentLang === 'en' ? 'us' : 'tz';
                        @endphp
                        <a class="dropdown-toggle d-flex align-items-center" href="#" data-toggle="dropdown" id="language" aria-haspopup="true" aria-expanded="true" style="color: #940000; text-decoration: none; padding: 8px 12px;">
                            <span class="flag-icon flag-icon-{{ $langFlag }}" style="margin-right: 5px;"></span>
                            <span>{{ $langText }}</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="language">
                            <a class="dropdown-item language-option d-flex align-items-center" href="#" data-lang="sw" style="cursor: pointer;">
                                <span class="flag-icon flag-icon-tz" style="margin-right: 8px;"></span>
                                <span>Kiswahili</span>
                            </a>
                            <a class="dropdown-item language-option d-flex align-items-center" href="#" data-lang="en" style="cursor: pointer;">
                                <span class="flag-icon flag-icon-us" style="margin-right: 8px;"></span>
                                <span>English</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </header><!-- /header -->
        <!-- Header-->
        
    <!-- Language Translation Script - Global -->
    <script>
    // Make translation system global so it works on all pages
    window.ParentTranslationSystem = (function() {
        // Language translations
        const translations = {
            'sw': {
                'dashboard': 'Dashboard',
                'results': 'Matokeo',
                'attendance': 'Ushiriki',
                'subjects': 'Masomo',
                'payments': 'Malipo',
                'fees_summary': 'Muhtasari wa Ada',
                'my_profile': 'Wasifu Wangu',
                'notifications': 'Arifa',
                'settings': 'Mipangilio',
                'logout': 'Toka'
            },
            'en': {
                'dashboard': 'Dashboard',
                'results': 'Results',
                'attendance': 'Attendance',
                'subjects': 'Subjects',
                'payments': 'Payments',
                'fees_summary': 'Fees Summary',
                'my_profile': 'My Profile',
                'notifications': 'Notifications',
                'settings': 'Settings',
                'logout': 'Logout'
            }
        };

        // Get current language from session or default to Swahili
        let currentLang = '{{ Session::get("locale", "sw") }}';

        // Function to update translations
        function updateTranslations() {
            const lang = currentLang;
            document.querySelectorAll('[data-translate]').forEach(function(element) {
                const key = element.getAttribute('data-translate');
                if (translations[lang] && translations[lang][key]) {
                    // Preserve HTML content if exists
                    const html = element.innerHTML;
                    const hasHTML = html !== element.textContent;
                    
                    if (hasHTML) {
                        // Replace only text content, keep HTML structure
                        const textNodes = [];
                        const walker = document.createTreeWalker(
                            element,
                            NodeFilter.SHOW_TEXT,
                            null,
                            false
                        );
                        let node;
                        while (node = walker.nextNode()) {
                            textNodes.push(node);
                        }
                        // Update first text node only
                        if (textNodes.length > 0) {
                            textNodes[0].textContent = translations[lang][key];
                        }
                    } else {
                        element.textContent = translations[lang][key];
                    }
                }
            });
        }

        // Initialize language switcher
        function initLanguageSwitcher() {
            // Update translations on page load
            updateTranslations();

            // Handle language selection - use event delegation for dynamically added elements
            document.addEventListener('click', function(e) {
                if (e.target.closest('.language-option')) {
                    e.preventDefault();
                    const option = e.target.closest('.language-option');
                    const lang = option.getAttribute('data-lang');
                    
                    // Update language via AJAX
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfToken) {
                        console.error('CSRF token not found');
                        return;
                    }
                    
                    fetch('/change-language', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                        },
                        body: JSON.stringify({ locale: lang })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            currentLang = lang;
                            
                            // Update language selector display immediately
                            const langText = lang === 'en' ? 'English' : 'Kiswahili';
                            const langFlag = lang === 'en' ? 'us' : 'tz';
                            const selector = document.querySelector('#language');
                            if (selector) {
                                selector.innerHTML = '<span class="flag-icon flag-icon-' + langFlag + '" style="margin-right: 5px;"></span><span>' + langText + '</span>';
                            }
                            
                            // Update translations immediately
                            updateTranslations();
                            
                            // Reload page to apply translations to all pages (including server-side content)
                            setTimeout(function() {
                                window.location.reload();
                            }, 100);
                        } else {
                            console.error('Failed to change language:', data.message || 'Unknown error');
                            alert('Failed to change language. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error changing language:', error);
                        alert('Error changing language. Please try again.');
                    });
                }
            });
        }

        // Initialize when DOM is ready - with delay to ensure all scripts are loaded
        function initializeTranslationSystem() {
            // Wait a bit for all scripts to load
            setTimeout(function() {
                initLanguageSwitcher();
            }, 100);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeTranslationSystem);
        } else {
            initializeTranslationSystem();
        }

        // Also initialize after a short delay to catch late-loading pages
        setTimeout(function() {
            if (typeof window.ParentTranslationSystem === 'undefined') {
                initLanguageSwitcher();
            }
        }, 500);

        // Expose update function for external use
        return {
            updateTranslations: updateTranslations,
            getCurrentLang: function() { return currentLang; },
            setCurrentLang: function(lang) { currentLang = lang; updateTranslations(); }
        };
    })();
    
    // Make sure translations are applied even if script loads late
    if (window.ParentTranslationSystem) {
        window.ParentTranslationSystem.updateTranslations();
    }
    </script>
