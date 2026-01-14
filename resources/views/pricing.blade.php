<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Pricing - ShuleXpert School Management System</title>
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
        /* Pricing Page Styles */
        .pricing-header {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%);
            padding: 80px 0 60px;
            color: #ffffff;
        }

        .pricing-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            background: #ffffff;
        }

        .pricing-card:hover {
            border-color: #940000;
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(148, 0, 0, 0.2);
        }

        .pricing-card.featured {
            border-color: #940000;
            background: linear-gradient(135deg, rgba(237, 153, 153, 0.1) 0%, rgba(148, 0, 0, 0.05) 100%);
            position: relative;
        }

        .pricing-card.featured::before {
            content: 'Most Popular';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%);
            color: #ffffff;
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .pricing-price {
            font-size: 48px;
            font-weight: 700;
            color: #940000;
            margin: 20px 0;
        }

        .pricing-price span {
            font-size: 24px;
            color: #666;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }

        .pricing-features li {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .pricing-features li:last-child {
            border-bottom: none;
        }

        .pricing-features li i {
            color: #940000;
            margin-right: 10px;
        }

        .btn-pricing {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%);
            color: #ffffff;
            border: none;
            padding: 12px 40px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-pricing:hover {
            background: linear-gradient(135deg, #940000 0%, #ed9999 100%);
            transform: scale(1.05);
            color: #ffffff;
        }

        /* Footer Styling - Same as home.blade.php */
        .footer,
        .container-fluid.footer,
        div.footer,
        .footer.container-fluid {
            background-color: #000000 !important;
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
        }

        .footer::before,
        .footer::after {
            display: none !important;
        }

        .footer * {
            color: #ffffff !important;
        }

        .footer-powered-by {
            color: #ed9999 !important;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>
    <!-- Spinner End -->

    @include('includes.web_nav')

    <!-- Pricing Header Start -->
    <div class="container-fluid pricing-header wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center">
            <h1 class="display-5 mb-3">Choose Your Plan</h1>
            <p class="fs-5 mb-0">Select the perfect ShuleXpert plan for your school's needs</p>
        </div>
    </div>
    <!-- Pricing Header End -->

    <!-- Pricing Plans Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Basic Plan -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="pricing-card">
                        <h3 class="mb-3">Basic</h3>
                        <div class="pricing-price">
                            TZS <span>500,000</span>
                            <small class="d-block" style="font-size: 14px; color: #666;">per month</small>
                        </div>
                        <ul class="pricing-features">
                            <li><i class="fa fa-check"></i> Up to 200 Students</li>
                            <li><i class="fa fa-check"></i> Up to 10 Teachers</li>
                            <li><i class="fa fa-check"></i> Basic Attendance Tracking</li>
                            <li><i class="fa fa-check"></i> Result Management</li>
                            <li><i class="fa fa-check"></i> Fee Payment System</li>
                            <li><i class="fa fa-check"></i> SMS Notifications (500/month)</li>
                            <li><i class="fa fa-check"></i> Email Support</li>
                        </ul>
                        <a href="{{ route('login') }}" class="btn btn-pricing">Get Started</a>
                    </div>
                </div>

                <!-- Professional Plan (Featured) -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="pricing-card featured">
                        <h3 class="mb-3">Professional</h3>
                        <div class="pricing-price">
                            TZS <span>1,200,000</span>
                            <small class="d-block" style="font-size: 14px; color: #666;">per month</small>
                        </div>
                        <ul class="pricing-features">
                            <li><i class="fa fa-check"></i> Up to 500 Students</li>
                            <li><i class="fa fa-check"></i> Up to 30 Teachers</li>
                            <li><i class="fa fa-check"></i> Biometric Attendance</li>
                            <li><i class="fa fa-check"></i> Advanced Result Management</li>
                            <li><i class="fa fa-check"></i> Fee Payment with Control Numbers</li>
                            <li><i class="fa fa-check"></i> SMS Notifications (2000/month)</li>
                            <li><i class="fa fa-check"></i> Timetable Management</li>
                            <li><i class="fa fa-check"></i> Exam Management</li>
                            <li><i class="fa fa-check"></i> Mobile App Access</li>
                            <li><i class="fa fa-check"></i> Priority Support</li>
                        </ul>
                        <a href="{{ route('login') }}" class="btn btn-pricing">Get Started</a>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="pricing-card">
                        <h3 class="mb-3">Enterprise</h3>
                        <div class="pricing-price">
                            TZS <span>2,500,000</span>
                            <small class="d-block" style="font-size: 14px; color: #666;">per month</small>
                        </div>
                        <ul class="pricing-features">
                            <li><i class="fa fa-check"></i> Unlimited Students</li>
                            <li><i class="fa fa-check"></i> Unlimited Teachers</li>
                            <li><i class="fa fa-check"></i> Advanced Biometric System</li>
                            <li><i class="fa fa-check"></i> Complete Result Management</li>
                            <li><i class="fa fa-check"></i> Multi-Bank Payment Integration</li>
                            <li><i class="fa fa-check"></i> Unlimited SMS Notifications</li>
                            <li><i class="fa fa-check"></i> Full Timetable & Exam System</li>
                            <li><i class="fa fa-check"></i> Library Management</li>
                            <li><i class="fa fa-check"></i> Accommodation Management</li>
                            <li><i class="fa fa-check"></i> Role & Permission Management</li>
                            <li><i class="fa fa-check"></i> Custom Reports & Analytics</li>
                            <li><i class="fa fa-check"></i> 24/7 Priority Support</li>
                            <li><i class="fa fa-check"></i> Custom Training & Onboarding</li>
                        </ul>
                        <a href="{{ route('login') }}" class="btn btn-pricing">Contact Sales</a>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <p class="fs-5 mb-4">All plans include:</p>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fa fa-shield-alt fa-2x text-primary me-3"></i>
                                <div class="text-start">
                                    <h6 class="mb-1">Secure & Reliable</h6>
                                    <small class="text-muted">Data encryption & backup</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fa fa-mobile-alt fa-2x text-primary me-3"></i>
                                <div class="text-start">
                                    <h6 class="mb-1">Mobile Access</h6>
                                    <small class="text-muted">iOS & Android apps</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fa fa-headset fa-2x text-primary me-3"></i>
                                <div class="text-start">
                                    <h6 class="mb-1">Support Included</h6>
                                    <small class="text-muted">Dedicated support team</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="row mt-5">
                <div class="col-12 text-center bg-light p-5 rounded">
                    <h3 class="mb-3">Need a Custom Plan?</h3>
                    <p class="mb-4">Contact us to discuss your specific requirements</p>
                    <a href="/contact" class="btn btn-primary me-2">Contact Us</a>
                    <a href="tel:+255757166599" class="btn btn-outline-primary">Call: +255 757 166 599</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Pricing Plans End -->

    <!-- Footer Start -->
    <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.1s" style="background-color: #000000 !important; background: #000000 !important;">
        <div class="container">
            <div class="row g-5 py-5">
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">ShuleXpert Office</h4>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Moshi, Kilimanjaro</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+255757166599</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>emca@emca.tech</p>
                    <p class="mb-3 mt-3"><span class="footer-powered-by">Powered by EmCa Techonology</span></p>
                    <div class="d-flex pt-3">
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-x-twitter"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Quick Links</h4>
                    <a class="btn btn-link" href="/">Home</a>
                    <a class="btn btn-link" href="/about">About Us</a>
                    <a class="btn btn-link" href="/services">Our Services</a>
                    <a class="btn btn-link" href="{{ route('pricing') }}">Pricing</a>
                    <a class="btn btn-link" href="/contact">Contact Us</a>
                    <p class="mb-0 mt-3"><span class="footer-powered-by">Powered by EmCa Techonology</span></p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Business Hours</h4>
                    <p class="mb-1">Monday - Friday</p>
                    <h6 class="text-light">09:00 am - 07:00 pm</h6>
                    <p class="mb-1">Saturday</p>
                    <h6 class="text-light">09:00 am - 12:00 pm</h6>
                    <p class="mb-1">Sunday</p>
                    <h6 class="text-light">Closed</h6>
                    <p class="mb-0 mt-3"><span class="footer-powered-by">Powered by EmCa Techonology</span></p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Gallery</h4>
                    <div class="row g-2">
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('images/S1.jpg') }}" alt="Gallery Image 1" style="height: 80px; object-fit: cover; border-radius: 4px;">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('images/S2.jpg') }}" alt="Gallery Image 2" style="height: 80px; object-fit: cover; border-radius: 4px;">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('images/S3.webp') }}" alt="Gallery Image 3" style="height: 80px; object-fit: cover; border-radius: 4px;">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('images/timetable.jpg') }}" alt="Timetable" style="height: 80px; object-fit: cover; border-radius: 4px;">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('images/exam.jpg') }}" alt="Examination" style="height: 80px; object-fit: cover; border-radius: 4px;">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('images/biometric.jpg') }}" alt="Biometric" style="height: 80px; object-fit: cover; border-radius: 4px;">
                        </div>
                    </div>
                    <p class="mb-0 mt-3"><span class="footer-powered-by">Powered by EmCa Techonology</span></p>
                </div>
            </div>
            <div class="copyright pt-5">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <a class="fw-semi-bold" href="#!">ShuleXpert</a>, All Right Reserved.
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <span class="footer-powered-by">Powered by EmCa Techonology</span>
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











