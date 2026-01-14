<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Online Application - ShuleXpert School Management System</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@600;700&family=Open+Sans&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        /* Splash Screen */
        #splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #940000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        #splash-screen.hide {
            opacity: 0;
            pointer-events: none;
        }

        .splash-content {
            text-align: center;
            color: #ffffff;
        }

        .splash-icon {
            font-size: 80px;
            color: #ffffff;
            margin-bottom: 30px;
            animation: bounce 1.5s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            25% {
                transform: translateY(-15px) rotate(5deg);
            }
            50% {
                transform: translateY(-25px) rotate(0deg);
            }
            75% {
                transform: translateY(-15px) rotate(-5deg);
            }
        }

        .splash-text {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #ffffff;
            line-height: 1.3;
        }

        .splash-powered {
            font-size: 16px;
            color: #ffffff;
            margin-top: 20px;
            opacity: 0.9;
        }

        .splash-loader {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <!-- Splash Screen -->
    <div id="splash-screen">
        <div class="splash-content">
            <div class="splash-icon">
                <i class="fa fa-graduation-cap"></i>
            </div>
            <div class="splash-text">Welcome to ShuleXpert<br>Online Application System</div>
            <div class="splash-loader"></div>
            <div class="splash-powered">Powered by: EmCa Technologies LTD</div>
        </div>
    </div>

    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>
    <!-- Spinner End -->

    @include('includes.web_nav')

    <!-- Page Header Start -->
    <div class="container-fluid page-header py-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center py-4">
            <h1 class="display-3 animated slideInDown">Online Application</h1>
            <nav aria-label="breadcrumb animated slideInDown">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Online Application</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Application Content Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <!-- Search Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="GET" action="{{ route('online_application') }}" class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <input type="text" name="search" class="form-control form-control-lg" 
                                        placeholder="Search by school name, region, or district..." 
                                        value="{{ $search }}">
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                @if($search)
                                    <a href="{{ route('online_application') }}" class="btn btn-secondary btn-lg">
                                        <i class="fa fa-times"></i> Clear
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schools List -->
            <div class="row">
                @if($schools->count() > 0)
                    @foreach($schools as $school)
                        @if($school['hasAnySpaces'])
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm border-0" style="border-top: 4px solid #940000 !important;">
                                    <div class="card-body">
                                        <!-- School Logo and Name -->
                                        <div class="text-center mb-3">
                                            @if($school['schoolLogo'])
                                                <img src="{{ asset($school['schoolLogo']) }}" 
                                                    alt="{{ $school['schoolName'] }}" 
                                                    class="img-fluid rounded-circle mb-2" 
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fa fa-school fa-2x text-primary"></i>
                                                </div>
                                            @endif
                                            <h5 class="mb-1">{{ $school['schoolName'] }}</h5>
                                            <p class="text-muted small mb-0">{{ $school['schoolType'] }} - {{ $school['region'] }}</p>
                                        </div>

                                        <!-- School Statistics -->
                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <div class="border-end">
                                                    <h6 class="mb-0 text-primary">{{ $school['classesCount'] }}</h6>
                                                    <small class="text-muted">Classes</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border-end">
                                                    <h6 class="mb-0 text-primary">{{ $school['subclassesCount'] }}</h6>
                                                    <small class="text-muted">Subclasses</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <h6 class="mb-0 text-primary">{{ $school['activeStudentsCount'] }}</h6>
                                                <small class="text-muted">Students</small>
                                            </div>
                                        </div>

                                        <!-- Available Classes -->
                                        <div class="mb-3">
                                            <h6 class="mb-2">Available Classes:</h6>
                                            @foreach($school['classesWithSpaces'] as $class)
                                                @if($class['hasSpaces'])
                                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                                        <div>
                                                            <strong>{{ $class['className'] }}</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $class['availableSpaces'] }} space(s) available
                                                            </small>
                                                        </div>
                                                        <button class="btn btn-sm btn-primary" 
                                                            onclick="applyToClass({{ $school['schoolID'] }}, {{ $class['classID'] }})">
                                                            Apply Now
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                                        <div>
                                                            <strong>{{ $class['className'] }}</strong>
                                                            <br>
                                                            <small class="text-danger">No space for application</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <!-- View More Button -->
                                        <div class="text-center">
                                            <button class="btn btn-outline-primary" 
                                                onclick="viewSchoolDetails({{ $school['schoolID'] }})">
                                                <i class="fa fa-eye"></i> View More
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if($schools->where('hasAnySpaces', true)->count() === 0)
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fa fa-info-circle fa-2x mb-3"></i>
                                <h5>No schools with available spaces found</h5>
                                <p class="mb-0">Please try a different search or check back later.</p>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="fa fa-exclamation-triangle fa-2x mb-3"></i>
                            <h5>No schools found</h5>
                            <p class="mb-0">Please try a different search term.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Application Content End -->

    <!-- School Details Modal -->
    <div class="modal fade" id="schoolDetailsModal" tabindex="-1" aria-labelledby="schoolDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%); color: #ffffff;">
                    <h5 class="modal-title" id="schoolDetailsModalLabel">School Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="schoolDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading school details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function applyToClass(schoolID, classID) {
            // Navigate to application form
            window.location.href = '{{ route("online_application.apply") }}?school=' + schoolID + '&class=' + classID;
        }

        function viewSchoolDetails(schoolID) {
            $('#schoolDetailsModal').modal('show');
            
            // Reset content
            $('#schoolDetailsContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading school details...</p>
                </div>
            `);
            
            // Load school details via AJAX
            $.ajax({
                url: '{{ route("online_application.school_details", ":schoolID") }}'.replace(':schoolID', schoolID),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let html = `
                            <div class="school-details">
                                <!-- School Header -->
                                <div class="text-center mb-4">
                                    ${response.school.schoolLogo ? 
                                        `<img src="{{ asset('') }}${response.school.schoolLogo}" alt="${response.school.schoolName}" 
                                            class="img-fluid rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">` 
                                        : ''}
                                    <h4>${response.school.schoolName}</h4>
                                    <p class="text-muted mb-0">${response.school.schoolType} - ${response.school.ownership}</p>
                                    <p class="text-muted small">${response.school.region}, ${response.school.district}</p>
                                    ${response.school.address ? `<p class="text-muted small">${response.school.address}</p>` : ''}
                                    ${response.school.phone ? `<p class="text-muted small"><i class="fa fa-phone"></i> ${response.school.phone}</p>` : ''}
                                    ${response.school.email ? `<p class="text-muted small"><i class="fa fa-envelope"></i> ${response.school.email}</p>` : ''}
                                    ${response.school.establishedYear ? `<p class="text-muted small">Established: ${response.school.establishedYear}</p>` : ''}
                                </div>

                                <!-- Classes Details -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2 mb-3" style="color: #940000;">
                                        <i class="fa fa-columns"></i> Classes (${response.classes.length})
                                    </h5>
                                    <div class="row">
                                        ${response.classes.map(function(cls) {
                                            return `
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border">
                                                        <div class="card-body">
                                                            <h6 class="card-title" style="color: #940000;">${cls.className}</h6>
                                                            <p class="mb-2"><strong>Subclasses:</strong> ${cls.subclassesCount}</p>
                                                            <div class="small">
                                                                ${cls.subclasses.map(function(sub) {
                                                                    return `<div class="mb-1">
                                                                        <i class="fa fa-circle" style="font-size: 6px; color: #940000;"></i> 
                                                                        ${sub.subclassName}: ${sub.studentCount} students
                                                                    </div>`;
                                                                }).join('')}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                        }).join('')}
                                    </div>
                                </div>

                                <!-- School Subjects -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2 mb-3" style="color: #940000;">
                                        <i class="fa fa-book"></i> School Subjects (${response.schoolSubjects.length})
                                    </h5>
                                    <div class="row">
                                        ${response.schoolSubjects.map(function(subj) {
                                            return `
                                                <div class="col-md-4 mb-2">
                                                    <div class="badge bg-light text-dark p-2 w-100 text-start">
                                                        <strong>${subj.subject_name}</strong>
                                                        ${subj.subject_code ? `<br><small>Code: ${subj.subject_code}</small>` : ''}
                                                    </div>
                                                </div>
                                            `;
                                        }).join('')}
                                    </div>
                                </div>

                                <!-- Class Subjects -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2 mb-3" style="color: #940000;">
                                        <i class="fa fa-bookmark"></i> Class Subjects
                                    </h5>
                                    ${Object.keys(response.classSubjects).map(function(classID) {
                                        const classData = response.classes.find(c => c.classID == classID);
                                        const className = classData ? classData.className : 'Unknown Class';
                                        const subjects = response.classSubjects[classID];
                                        
                                        return `
                                            <div class="mb-3">
                                                <h6 style="color: #940000;">${className}</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Subject</th>
                                                                <th>Code</th>
                                                                <th>Teacher</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            ${subjects.map(function(subj) {
                                                                return `
                                                                    <tr>
                                                                        <td>${subj.subjectName}</td>
                                                                        <td>${subj.subjectCode || '-'}</td>
                                                                        <td>${subj.teacherName}</td>
                                                                    </tr>
                                                                `;
                                                            }).join('')}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        `;
                                    }).join('')}
                                </div>

                                <!-- Teachers -->
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2 mb-3" style="color: #940000;">
                                        <i class="fa fa-users"></i> Teachers (${response.teachers.length})
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Roles</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${response.teachers.map(function(teacher) {
                                                    return `
                                                        <tr>
                                                            <td>${teacher.fullName}</td>
                                                            <td>${teacher.position || '-'}</td>
                                                            <td>
                                                                ${teacher.roles.length > 0 
                                                                    ? teacher.roles.map(function(role) {
                                                                        return `<span class="badge bg-primary me-1">${role}</span>`;
                                                                    }).join('')
                                                                    : '<span class="text-muted">No roles assigned</span>'
                                                                }
                                                            </td>
                                                        </tr>
                                                    `;
                                                }).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#schoolDetailsContent').html(html);
                    }
                },
                error: function(xhr) {
                    $('#schoolDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle"></i> 
                            Error loading school details. Please try again.
                        </div>
                    `);
                }
            });
        }
    </script>

    <!-- Hide Spinner Script (must be before jQuery) -->
    <script>
        // Hide spinner immediately when DOM is ready
        (function() {
            function hideSpinner() {
                var spinner = document.getElementById('spinner');
                if (spinner) {
                    spinner.classList.remove('show');
                    spinner.style.display = 'none';
                }
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', hideSpinner);
            } else {
                hideSpinner();
            }
            
            window.addEventListener('load', hideSpinner);
        })();
    </script>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <!-- Splash Screen Script -->
    <script>
        // Hide splash screen after 1 second
        window.addEventListener('load', function() {
            setTimeout(function() {
                var splash = document.getElementById('splash-screen');
                if (splash) {
                    splash.classList.add('hide');
                    setTimeout(function() {
                        splash.style.display = 'none';
                    }, 500);
                }
            }, 1000);
        });
    </script>
</body>

</html>

