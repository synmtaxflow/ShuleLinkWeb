<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ShuleLink - School Management System</title>
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

    <!-- Custom Color Override -->
    <style>
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            /* Carousel Section */
            .carousel-text h1.display-1 {
                font-size: 2rem !important;
                line-height: 1.2 !important;
            }
            .carousel-text p.fs-5 {
                font-size: 1rem !important;
            }
            .carousel-text .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            .carousel-text .btn {
                width: 100%;
                padding: 0.75rem 1rem !important;
                font-size: 0.9rem;
            }
            .carousel-img {
                margin-top: 1.5rem;
            }
            
            /* Video Section */
            .bg-primary h3 {
                font-size: 1rem !important;
                margin-left: 0 !important;
                margin-top: 1rem !important;
                padding: 0 1rem;
            }
            .btn-play {
                width: 60px !important;
                height: 60px !important;
            }
            
            /* About Section */
            .about-img {
                margin-bottom: 2rem;
            }
            .display-6 {
                font-size: 1.75rem !important;
            }
            .section-title {
                font-size: 0.9rem !important;
            }
            
            /* Service Section */
            .service-title h1.display-6 {
                font-size: 1.5rem !important;
            }
            .service-item {
                margin-bottom: 1.5rem;
            }
            .service-item h3 {
                font-size: 1.25rem;
            }
            .service-item p {
                font-size: 0.9rem;
            }
            
            /* Features Section */
            .text-center.bg-primary,
            .text-center.bg-secondary {
                padding: 1.5rem 1rem !important;
            }
            .text-center.bg-primary h1.display-5,
            .text-center.bg-secondary h1.display-5 {
                font-size: 2rem !important;
            }
            .text-center.bg-primary i,
            .text-center.bg-secondary i {
                font-size: 2rem !important;
            }
            
            /* Donation Section */
            .donation-item {
                flex-direction: column !important;
            }
            .donation-progress {
                width: 100% !important;
                margin-right: 0 !important;
                margin-bottom: 1rem;
            }
            
            /* Banner Section */
            .banner-inner {
                padding: 2rem 1rem !important;
            }
            .banner-inner h1.display-6 {
                font-size: 1.5rem !important;
            }
            .banner-inner p.fs-5 {
                font-size: 1rem !important;
            }
            .banner-inner .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            .banner-inner .btn {
                width: 100%;
            }
            
            /* Event Section */
            .event-item {
                margin-bottom: 2rem;
            }
            .event-item h3 {
                font-size: 1.25rem;
            }
            
            /* Donate Section */
            .donate-text,
            .donate-form {
                padding: 2rem 1rem !important;
            }
            .donate-text h1.display-6 {
                font-size: 1.5rem !important;
            }
            .btn-group {
                flex-wrap: wrap;
                width: 100%;
            }
            .btn-group .btn {
                flex: 1 1 auto;
                min-width: calc(50% - 0.25rem);
                font-size: 0.85rem;
                padding: 0.5rem 0.75rem;
            }
            
            /* Team Section */
            .team-item {
                flex-direction: column !important;
            }
            .team-detail {
                padding-right: 0 !important;
                margin-bottom: 1rem;
            }
            .team-social {
                flex-direction: row !important;
                width: 100%;
                padding: 1rem !important;
            }
            .team-social .btn {
                margin: 0 0.25rem !important;
            }
            
            /* Testimonial Section */
            .testimonial-title h1.display-6 {
                font-size: 1.5rem !important;
            }
            .testimonial-item .row {
                flex-direction: column;
            }
            .testimonial-img {
                margin-bottom: 1.5rem;
            }
            .testimonial-text {
                padding-bottom: 1.5rem !important;
            }
            
            /* Newsletter Section */
            .bg-primary h1.display-6 {
                font-size: 1.5rem !important;
            }
            .position-relative input {
                font-size: 0.9rem !important;
                padding-left: 1rem !important;
            }
            
            /* Footer Section */
            .footer .row.g-5 {
                margin-bottom: 2rem;
            }
            .footer .col-lg-3 {
                margin-bottom: 2rem;
            }
            .footer h4 {
                font-size: 1.1rem;
            }
            .footer p,
            .footer a {
                font-size: 0.9rem;
            }
            
            /* General */
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .py-5 {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }
            .px-4 {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .px-5 {
                padding-left: 1.5rem !important;
                padding-right: 1.5rem !important;
            }
        }
        
        @media (max-width: 576px) {
            /* Extra Small Devices */
            .carousel-text h1.display-1 {
                font-size: 1.5rem !important;
            }
            .display-6 {
                font-size: 1.5rem !important;
            }
            .display-5 {
                font-size: 1.75rem !important;
            }
            .fs-5 {
                font-size: 0.95rem !important;
            }
            .btn {
                padding: 0.6rem 1rem !important;
                font-size: 0.85rem;
            }
            .btn-lg {
                padding: 0.75rem 1.25rem !important;
                font-size: 0.9rem;
            }
            .py-3 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
            .px-4 {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .px-3 {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            .mb-4 {
                margin-bottom: 1rem !important;
            }
            .mb-5 {
                margin-bottom: 1.5rem !important;
            }
            .g-5 {
                gap: 1.5rem !important;
            }
            .g-4 {
                gap: 1rem !important;
            }
            .g-3 {
                gap: 0.75rem !important;
            }
            
            /* Video Section */
            .bg-primary .row {
                flex-direction: column;
            }
            .bg-primary .col-lg-11 {
                padding: 1.5rem 1rem;
            }
            
            /* Service Cards */
            .col-sm-6 {
                margin-bottom: 1rem;
            }
            
            /* Features Grid */
            .col-sm-6 {
                padding: 1rem 0.5rem;
            }
            
            /* Donation Cards */
            .donation-item {
                padding: 1rem !important;
            }
            
            /* Event Cards */
            .event-item {
                padding: 1rem !important;
            }
            
            /* Team Cards */
            .team-item {
                padding: 1rem !important;
            }
            
            /* Form Elements */
            .form-floating {
                margin-bottom: 1rem;
            }
            .form-control {
                font-size: 0.9rem;
                padding: 0.75rem;
            }
            
            /* Buttons Group */
            .btn-group .btn {
                font-size: 0.75rem;
                padding: 0.4rem 0.5rem;
            }
            
            /* Footer */
            .footer {
                padding: 2rem 0 !important;
            }
            .footer .row.g-5 {
                gap: 1.5rem !important;
            }
        }
        
        @media (max-width: 480px) {
            /* Very Small Devices */
            .carousel-text h1.display-1 {
                font-size: 1.25rem !important;
            }
            .display-6 {
                font-size: 1.25rem !important;
            }
            .display-5 {
                font-size: 1.5rem !important;
            }
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
        
        /* Ensure images are responsive */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Fix overflow issues */
        .container-fluid {
            overflow-x: hidden;
        }
        
        /* Improve touch targets on mobile */
        @media (max-width: 768px) {
            .btn {
                min-height: 44px;
                min-width: 44px;
            }
            a {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
            }
        }
    </style>

</head>

<body>

@include('includes.web_nav')
    <!-- Carousel Start -->
    <div class="container-fluid p-0 wow fadeIn" data-wow-delay="0.1s">
        <div class="owl-carousel header-carousel py-5">
            <div class="container py-5">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="carousel-text">
                            <h1 class="display-1 text-uppercase mb-3">Transform Your School Management</h1>
                            <p class="fs-5 mb-5">ShuleLink is a comprehensive school management system designed for pre-schools, primary, secondary, and advanced schools. Manage all your operations seamlessly.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-primary py-3 px-4 me-3" href="{{ route('AdminDashboard') }}">Get Started</a>
                                <a class="btn btn-secondary py-3 px-4" href="#!">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="carousel-img">
                            <img class="w-100" src="img/carousel-1.jpg" alt="Image">
                        </div>
                    </div>
                </div>
            </div>
            <div class="container py-5">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="carousel-text">
                            <h1 class="display-1 text-uppercase mb-3">Connect Teachers, Students & Parents</h1>
                            <p class="fs-5 mb-5">Streamline communication with bulk SMS notifications for results, attendance, events, and important announcements. Keep parents informed instantly.</p>
                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <a class="btn btn-primary py-3 px-4 me-3" href="#!">Request Demo</a>
                                <a class="btn btn-secondary py-3 px-4" href="#!">View Features</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="carousel-img">
                            <img class="w-100" src="img/carousel-2.jpg" alt="Image">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->


    <!-- Video Start -->
    <div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="row g-0">
                <div class="col-lg-11">
                    <div class="h-100 py-5 d-flex align-items-center">
                        <button type="button" class="btn-play" data-bs-toggle="modal"
                            data-src="https://www.youtube.com/embed/DWRcNpR6Kdc" data-bs-target="#videoModal">
                            <span></span>
                        </button>
                        <h3 class="ms-5 mb-0">Empower your school with smart technology that streamlines every aspect of education management.
                        </h3>
                    </div>
                </div>
                <div class="d-none d-lg-block col-lg-1">
                    <div class="h-100 w-100 bg-secondary d-flex align-items-center justify-content-center">
                        <span class="text-white" style="transform: rotate(-90deg);">Scroll Down</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Video End -->


    <!-- Video Modal Start -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Youtube Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- 16:9 aspect ratio -->
                    <div class="ratio ratio-16x9">
                        <iframe class="embed-responsive-item" src="" id="video" allowfullscreen
                            allowscriptaccess="always" allow="autoplay"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Video Modal End -->


    <!-- About Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.2s">
                    <div class="about-img">
                        <img class="img-fluid w-100" src="img/about.jpg" alt="Image">
                    </div>
                </div>
                <div class="col-lg-6">
                    <p class="section-title bg-white text-start text-primary pe-3">About Us</p>
                    <h1 class="display-6 mb-4 wow fadeIn" data-wow-delay="0.2s">Innovative School Management Solutions</h1>
                    <p class="mb-4 wow fadeIn" data-wow-delay="0.3s">ShuleLink revolutionizes how schools operate by providing a comprehensive management system that streamlines academic operations, communication, and administrative tasks. Join hundreds of schools already using ShuleLink to enhance their educational delivery.</p>
                    <div class="row g-4 pt-2">
                        <div class="col-sm-6 wow fadeIn" data-wow-delay="0.4s">
                            <div class="h-100">
                                <h3>Our Vision</h3>
                                <p>To transform education management by providing schools with powerful, intuitive tools for seamless operations.</p>
                                <p class="text-dark"><i class="fa fa-check text-primary me-2"></i>Digitalize school records</p>
                                <p class="text-dark"><i class="fa fa-check text-primary me-2"></i>Enhance parent-teacher communication</p>
                                <p class="text-dark mb-0"><i class="fa fa-check text-primary me-2"></i>Improve academic performance tracking</p>
                            </div>
                        </div>
                        <div class="col-sm-6 wow fadeIn" data-wow-delay="0.5s">
                            <div class="h-100 bg-primary p-4 text-center">
                                <p class="fs-5 text-dark">Experience the future of school management with ShuleLink's comprehensive platform designed for Tanzanian schools.</p>
                                <a class="btn btn-secondary py-2 px-4" href="#!">Try Free Trial</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->


    <!-- Service Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-md-12 col-lg-4 col-xl-3 wow fadeIn" data-wow-delay="0.1s">
                    <div class="service-title">
                        <h1 class="display-6 mb-4">Complete School Management Platform</h1>
                        <p class="fs-5 mb-0">Everything you need to manage your school operations efficiently - from student records to parent communications.</p>
                    </div>
                </div>
                <div class="col-md-12 col-lg-8 col-xl-9">
                    <div class="row g-5">
                        <div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.1s">
                            <div class="service-item h-100">
                                <div class="btn-square bg-light mb-4">
                                    <i class="fa fa-user-graduate fa-2x text-secondary"></i>
                                </div>
                                <h3>Result Management</h3>
                                <p class="mb-2">Teachers can easily record and manage student results. Generate comprehensive reports and send results to parents via SMS.</p>
                                <a href="#!">Read More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.3s">
                            <div class="service-item h-100">
                                <div class="btn-square bg-light mb-4">
                                    <i class="fa fa-sms fa-2x text-secondary"></i>
                                </div>
                                <h3>Bulk SMS Notifications</h3>
                                <p class="mb-2">Send instant notifications to parents about meetings, results, events, school closing/opening, and important announcements.</p>
                                <a href="#!">Read More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.5s">
                            <div class="service-item h-100">
                                <div class="btn-square bg-light mb-4">
                                    <i class="fa fa-user-check fa-2x text-secondary"></i>
                                </div>
                                <h3>Attendance Tracking</h3>
                                <p class="mb-2">Monitor student attendance daily. Generate attendance reports per parent and track patterns efficiently.</p>
                                <a href="#!">Read More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.1s">
                            <div class="service-item h-100">
                                <div class="btn-square bg-light mb-4">
                                    <i class="fa fa-credit-card fa-2x text-secondary"></i>
                                </div>
                                <h3>Fee Payment Tracking</h3>
                                <p class="mb-2">Track all fee payments, manage payment history, send payment reminders to parents, and generate financial reports.</p>
                                <a href="#!">Read More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.3s">
                            <div class="service-item h-100">
                                <div class="btn-square bg-light mb-4">
                                    <i class="fa fa-users fa-2x text-secondary"></i>
                                </div>
                                <h3>Class Management</h3>
                                <p class="mb-2">Enable class teachers to manage their students, track performance, communicate with parents, and oversee class activities.</p>
                                <a href="#!">Read More</a>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.5s">
                            <div class="service-item h-100">
                                <div class="btn-square bg-light mb-4">
                                    <i class="fa fa-trophy fa-2x text-secondary"></i>
                                </div>
                                <h3>Achievement Recognition</h3>
                                <p class="mb-2">Automatically recognize and announce top-performing students via SMS to parents, promoting excellence and motivation.</p>
                                <a href="#!">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Service End -->


    <!-- Features Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="rounded overflow-hidden">
                        <div class="row g-0">
                            <div class="col-sm-6 wow fadeIn" data-wow-delay="0.1s">
                                <div class="text-center bg-primary py-5 px-4 h-100">
                                    <i class="fa fa-users fa-3x text-secondary mb-3"></i>
                                    <h1 class="display-5 mb-0" data-toggle="counter-up">500</h1>
                                    <span class="text-dark">Active Schools</span>
                                </div>
                            </div>
                            <div class="col-sm-6 wow fadeIn" data-wow-delay="0.3s">
                                <div class="text-center bg-secondary py-5 px-4 h-100">
                                    <i class="fa fa-award fa-3x text-primary mb-3"></i>
                                    <h1 class="display-5 text-white mb-0" data-toggle="counter-up">70</h1>
                                    <span class="text-white">Satisfied Schools</span>
                                </div>
                            </div>
                            <div class="col-sm-6 wow fadeIn" data-wow-delay="0.5s">
                                <div class="text-center bg-secondary py-5 px-4 h-100">
                                    <i class="fa fa-list-check fa-3x text-primary mb-3"></i>
                                    <h1 class="display-5 text-white mb-0" data-toggle="counter-up">3000</h1>
                                    <span class="text-white">Students Managed</span>
                                </div>
                            </div>
                            <div class="col-sm-6 wow fadeIn" data-wow-delay="0.7s">
                                <div class="text-center bg-primary py-5 px-4 h-100">
                                    <i class="fa fa-comments fa-3x text-secondary mb-3"></i>
                                    <h1 class="display-5 mb-0" data-toggle="counter-up">7000</h1>
                                    <span class="text-dark">Happy Parents</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <p class="section-title bg-white text-start text-primary pe-3">Why Us!</p>
                    <h1 class="display-6 mb-4 wow fadeIn" data-wow-delay="0.2s">Why Schools Choose ShuleLink!</h1>
                    <p class="mb-4 wow fadeIn" data-wow-delay="0.3s">ShuleLink provides comprehensive school management solutions that streamline operations, improve communication, and enhance educational outcomes. Join hundreds of schools transforming their operations.</p>
                    <p class="text-dark wow fadeIn" data-wow-delay="0.4s"><i
                            class="fa fa-check text-primary me-2"></i>Cloud-based system accessible anywhere</p>
                    <p class="text-dark wow fadeIn" data-wow-delay="0.5s"><i
                            class="fa fa-check text-primary me-2"></i>Bulk SMS integration for instant communication</p>
                    <p class="text-dark wow fadeIn" data-wow-delay="0.6s"><i
                            class="fa fa-check text-primary me-2"></i>Comprehensive reporting and analytics</p>
                    <div class="d-flex flex-wrap gap-2 mt-4 wow fadeIn" data-wow-delay="0.7s">
                        <a class="btn btn-primary py-3 px-4 me-3" href="#!">Start Free Trial</a>
                        <a class="btn btn-secondary py-3 px-4" href="#!">Contact Sales</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Features End -->


    <!-- Donation Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
                <p class="section-title bg-white text-center text-primary px-3">Donation</p>
                <h1 class="display-6 mb-4">Our Donation Causes Around the World</h1>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="donation-item d-flex h-100 p-4">
                        <div class="donation-progress d-flex flex-column flex-shrink-0 text-center me-4">
                            <h6 class="mb-0">Raised</h6>
                            <span class="mb-2">$8000</span>
                            <div class="progress d-flex align-items-end w-100 h-100 mb-2">
                                <div class="progress-bar w-100 bg-secondary" role="progressbar" aria-valuenow="85"
                                    aria-valuemin="0" aria-valuemax="100">
                                    <span class="fs-4">85%</span>
                                </div>
                            </div>
                            <h6 class="mb-0">Goal</h6>
                            <span>$10000</span>
                        </div>
                        <div class="donation-detail">
                            <div class="position-relative mb-4">
                                <img class="img-fluid w-100" src="img/donation-1.jpg" alt="">
                                <a href="#!"
                                    class="btn btn-sm btn-secondary px-3 position-absolute top-0 end-0">Food</a>
                            </div>
                            <a href="#!" class="h3 d-inline-block">Healthy Food</a>
                            <p>Through your donations and volunteer work, we spread kindness and support to children.
                            </p>
                            <a href="#!" class="btn btn-primary w-100 py-3"><i class="fa fa-plus me-2"></i>Donate
                                Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.13s">
                    <div class="donation-item d-flex h-100 p-4">
                        <div class="donation-progress d-flex flex-column flex-shrink-0 text-center me-4">
                            <h6 class="mb-0">Raised</h6>
                            <span class="mb-2">$8000</span>
                            <div class="progress d-flex align-items-end w-100 h-100 mb-2">
                                <div class="progress-bar w-100 bg-secondary" role="progressbar" aria-valuenow="95"
                                    aria-valuemin="0" aria-valuemax="100">
                                    <span class="fs-4">95%</span>
                                </div>
                            </div>
                            <h6 class="mb-0">Goal</h6>
                            <span>$10000</span>
                        </div>
                        <div class="donation-detail">
                            <div class="position-relative mb-4">
                                <img class="img-fluid w-100" src="img/donation-2.jpg" alt="">
                                <a href="#!"
                                    class="btn btn-sm btn-secondary px-3 position-absolute top-0 end-0">Health</a>
                            </div>
                            <a href="#!" class="h3 d-inline-block">Water Treatment</a>
                            <p>Through your donations and volunteer work, we spread kindness and support to children.
                            </p>
                            <a href="#!" class="btn btn-primary w-100 py-3"><i class="fa fa-plus me-2"></i>Donate
                                Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
                    <div class="donation-item d-flex h-100 p-4">
                        <div class="donation-progress d-flex flex-column flex-shrink-0 text-center me-4">
                            <h6 class="mb-0">Raised</h6>
                            <span class="mb-2">$8000</span>
                            <div class="progress d-flex align-items-end w-100 h-100 mb-2">
                                <div class="progress-bar w-100 bg-secondary" role="progressbar" aria-valuenow="75"
                                    aria-valuemin="0" aria-valuemax="100">
                                    <span class="fs-4">75%</span>
                                </div>
                            </div>
                            <h6 class="mb-0">Goal</h6>
                            <span>$10000</span>
                        </div>
                        <div class="donation-detail">
                            <div class="position-relative mb-4">
                                <img class="img-fluid w-100" src="img/donation-3.jpg" alt="">
                                <a href="#!"
                                    class="btn btn-sm btn-secondary px-3 position-absolute top-0 end-0">Education</a>
                            </div>
                            <a href="#!" class="h3 d-inline-block">Education Support</a>
                            <p>Through your donations and volunteer work, we spread kindness and support to children.
                            </p>
                            <a href="#!" class="btn btn-primary w-100 py-3"><i class="fa fa-plus me-2"></i>Donate
                                Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Donation End -->


    <!-- Banner Start -->
    <div class="container-fluid banner py-5">
        <div class="container">
            <div class="banner-inner bg-light p-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="row justify-content-center">
                    <div class="col-lg-8 py-5 text-center">
                        <h1 class="display-6 wow fadeIn" data-wow-delay="0.3s">Transform Your School Operations Today!</h1>
                        <p class="fs-5 mb-4 wow fadeIn" data-wow-delay="0.5s">Join hundreds of schools already using ShuleLink to streamline their operations, enhance communication, and improve educational outcomes. Get started with a free trial today!</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2 wow fadeIn" data-wow-delay="0.7s">
                            <a class="btn btn-primary py-3 px-4 me-3" href="#!">Get Started</a>
                            <a class="btn btn-secondary py-3 px-4" href="#!">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Banner End -->


    <!-- Event Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
                <p class="section-title bg-white text-center text-primary px-3">Events</p>
                <h1 class="display-6 mb-4">Upcoming School Events & Trainings</h1>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="event-item h-100 p-4">
                        <img class="img-fluid w-100 mb-4" src="img/event-1.jpg" alt="">
                        <a href="#!" class="h3 d-inline-block">ShuleLink Training Workshop</a>
                        <p>Learn how to maximize ShuleLink features for your school operations and improve management efficiency.</p>
                        <div class="bg-light p-4">
                            <p class="mb-1"><i class="fa fa-clock text-primary me-2"></i>10:00 AM - 18:00 PM</p>
                            <p class="mb-1"><i class="fa fa-calendar-alt text-primary me-2"></i>Jan 01 - Jan 10</p>
                            <p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i>123 Street, New York,
                                USA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
                    <div class="event-item h-100 p-4">
                        <img class="img-fluid w-100 mb-4" src="img/event-2.jpg" alt="">
                        <a href="#!" class="h3 d-inline-block">Student Performance Analytics</a>
                        <p>Discover how ShuleLink helps schools track and improve student performance with comprehensive analytics.</p>
                        <div class="bg-light p-4">
                            <p class="mb-1"><i class="fa fa-clock text-primary me-2"></i>10:00 AM - 18:00 PM</p>
                            <p class="mb-1"><i class="fa fa-calendar-alt text-primary me-2"></i>Jan 01 - Jan 10</p>
                            <p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i>123 Street, New York,
                                USA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
                    <div class="event-item h-100 p-4">
                        <img class="img-fluid w-100 mb-4" src="img/event-3.jpg" alt="">
                        <a href="#!" class="h3 d-inline-block">Digital Transformation Summit</a>
                        <p>Join us for a summit on digitalizing school operations and achieving educational excellence with technology.</p>
                        <div class="bg-light p-4">
                            <p class="mb-1"><i class="fa fa-clock text-primary me-2"></i>10:00 AM - 18:00 PM</p>
                            <p class="mb-1"><i class="fa fa-calendar-alt text-primary me-2"></i>Jan 01 - Jan 10</p>
                            <p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i>123 Street, New York,
                                USA</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Event End -->


    <!-- Donate Start -->
    <div class="container-fluid donate py-5">
        <div class="container">
            <div class="row g-0">
                <div class="col-lg-7 donate-text bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
                    <div class="d-flex flex-column justify-content-center h-100 p-5 wow fadeIn" data-wow-delay="0.3s">
                        <h1 class="display-6 mb-4">Ready to Transform Your School Management?</h1>
                        <p class="fs-5 mb-0">Experience the power of ShuleLink with our comprehensive school management platform. Streamline operations, enhance communication, and improve educational outcomes.</p>
                    </div>
                </div>
                <div class="col-lg-5 donate-form bg-primary py-5 text-center wow fadeIn" data-wow-delay="0.5s">
                    <div class="h-100 p-5">
                        <form>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" placeholder="Your Name">
                                        <label for="name">Your Name</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" placeholder="Your Email">
                                        <label for="email">Your Email</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check" name="btnradio" id="btnradio1"
                                            autocomplete="off" checked>
                                        <label class="btn btn-light" for="btnradio1">Basic</label>

                                        <input type="radio" class="btn-check" name="btnradio" id="btnradio2"
                                            autocomplete="off">
                                        <label class="btn btn-light" for="btnradio2">Standard</label>

                                        <input type="radio" class="btn-check" name="btnradio" id="btnradio3"
                                            autocomplete="off">
                                        <label class="btn btn-light" for="btnradio3">Premium</label>

                                        <input type="radio" class="btn-check" name="btnradio" id="btnradio4"
                                            autocomplete="off">
                                        <label class="btn btn-light" for="btnradio4">Enterprise</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-secondary py-3 w-100" type="submit">Request Demo</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Donate End -->


    <!-- Team Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
                <p class="section-title bg-white text-center text-primary px-3">Our Team</p>
                <h1 class="display-6 mb-4">Meet Our Dedicated Team Members</h1>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="team-item d-flex h-100 p-4">
                        <div class="team-detail pe-4">
                            <img class="img-fluid mb-4" src="img/team-1.jpg" alt="">
                            <h3>Boris Johnson</h3>
                            <span>Founder & CEO</span>
                        </div>
                        <div class="team-social bg-light d-flex flex-column justify-content-center flex-shrink-0 p-4">
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-x-twitter"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-instagram"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
                    <div class="team-item d-flex h-100 p-4">
                        <div class="team-detail pe-4">
                            <img class="img-fluid mb-4" src="img/team-2.jpg" alt="">
                            <h3>Donald Pakura</h3>
                            <span>Project Manager</span>
                        </div>
                        <div class="team-social bg-light d-flex flex-column justify-content-center flex-shrink-0 p-4">
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-x-twitter"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-instagram"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
                    <div class="team-item d-flex h-100 p-4">
                        <div class="team-detail pe-4">
                            <img class="img-fluid mb-4" src="img/team-3.jpg" alt="">
                            <h3>Alexander Bell</h3>
                            <span>Volunteer</span>
                        </div>
                        <div class="team-social bg-light d-flex flex-column justify-content-center flex-shrink-0 p-4">
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-x-twitter"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-instagram"></i></a>
                            <a class="btn btn-square btn-primary my-2" href="#!"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->


    <!-- Testimonial Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-md-12 col-lg-4 col-xl-3 wow fadeIn" data-wow-delay="0.1s">
                    <div class="testimonial-title">
                        <h1 class="display-6 mb-4">What People Say About Our Activities.</h1>
                        <p class="fs-5 mb-0">We work to bring smiles, hope, and a brighter future to those in need.</p>
                    </div>
                </div>
                <div class="col-md-12 col-lg-8 col-xl-9">
                    <div class="owl-carousel testimonial-carousel wow fadeIn" data-wow-delay="0.3s">
                        <div class="testimonial-item">
                            <div class="row g-5 align-items-center">
                                <div class="col-md-6">
                                    <div class="testimonial-img">
                                        <img class="img-fluid" src="img/testimonial-1.jpg" alt="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="testimonial-text pb-5 pb-md-0">
                                        <div class="mb-2">
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                        </div>
                                        <p class="fs-5">Education is the foundation of change. By funding schools,
                                            scholarships, and training programs, we can help children and adults unlock
                                            their potential for a better future.</p>
                                        <div class="d-flex align-items-center">
                                            <div class="btn-lg-square bg-light text-secondary flex-shrink-0">
                                                <i class="fa fa-quote-right fa-2x"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h5 class="mb-0">Alexander Bell</h5>
                                                <span>CEO, Founder</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-item">
                            <div class="row g-5 align-items-center">
                                <div class="col-md-6">
                                    <div class="testimonial-img">
                                        <img class="img-fluid" src="img/testimonial-2.jpg" alt="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="testimonial-text pb-5 pb-md-0">
                                        <div class="mb-2">
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                        </div>
                                        <p class="fs-5">Every hand extended in kindness brings us closer to a world free
                                            from suffering. Be part of a global movement dedicated to building a future
                                            where equality and compassion thrive.</p>
                                        <div class="d-flex align-items-center">
                                            <div class="btn-lg-square bg-light text-secondary flex-shrink-0">
                                                <i class="fa fa-quote-right fa-2x"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h5 class="mb-0">Donald Pakura</h5>
                                                <span>CEO, Founder</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-item">
                            <div class="row g-5 align-items-center">
                                <div class="col-md-6">
                                    <div class="testimonial-img">
                                        <img class="img-fluid" src="img/testimonial-3.jpg" alt="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="testimonial-text pb-5 pb-md-0">
                                        <div class="mb-2">
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                        </div>
                                        <p class="fs-5">Love and compassion have the power to heal. Through your
                                            donations and volunteer work, we can spread kindness and support to
                                            children, families, and communities struggling to find stability.</p>
                                        <div class="d-flex align-items-center">
                                            <div class="btn-lg-square bg-light text-secondary flex-shrink-0">
                                                <i class="fa fa-quote-right fa-2x"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h5 class="mb-0">Boris Johnson</h5>
                                                <span>CEO, Founder</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->


    <!-- Newsletter Start -->
    <div class="container-fluid bg-primary py-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 text-center wow fadeIn" data-wow-delay="0.5s">
                    <h1 class="display-6 mb-4">Stay Updated with ShuleLink</h1>
                    <div class="position-relative w-100 mb-2">
                        <input class="form-control border-0 w-100 ps-4 pe-5" type="text" placeholder="Enter Your Email"
                            style="height: 60px;">
                        <button type="button"
                            class="btn btn-lg-square shadow-none position-absolute top-0 end-0 mt-2 me-2"><i
                                class="fa fa-paper-plane text-primary fs-4"></i></button>
                    </div>
                    <p class="mb-0">Get the latest updates on features, tips, and school management best practices.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Newsletter End -->


    <!-- Footer Start -->
    <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="row g-5 py-5">
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">ShuleLink Office</h4>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Dar es Salaam, Tanzania</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+255 754 000 000</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>emca@emca.tech</p>
                    <div class="d-flex pt-3">
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-x-twitter"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Quick Links</h4>
                    <a class="btn btn-link" href="#!">About ShuleLink</a>
                    <a class="btn btn-link" href="#!">Contact Us</a>
                    <a class="btn btn-link" href="#!">Our Services</a>
                    <a class="btn btn-link" href="#!">Pricing</a>
                    <a class="btn btn-link" href="#!">Documentation</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Business Hours</h4>
                    <p class="mb-1">Monday - Friday</p>
                    <h6 class="text-light">09:00 am - 07:00 pm</h6>
                    <p class="mb-1">Saturday</p>
                    <h6 class="text-light">09:00 am - 12:00 pm</h6>
                    <p class="mb-1">Sunday</p>
                    <h6 class="text-light">Closed</h6>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Gallery</h4>
                    <div class="row g-2">
                        <div class="col-4">
                            <img class="img-fluid w-100" src="img/gallery-1.jpg" alt="">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="img/gallery-2.jpg" alt="">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="img/gallery-3.jpg" alt="">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="img/gallery-4.jpg" alt="">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="img/gallery-5.jpg" alt="">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="img/gallery-6.jpg" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright pt-5">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <a class="fw-semi-bold" href="#!">ShuleLink</a>, All Right Reserved.
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        Powered by <a class="fw-semi-bold" href="https://emca.tech">EMCATechnology</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#!" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>


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
</body>

</html>
