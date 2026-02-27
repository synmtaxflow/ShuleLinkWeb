<style>
        /* ShuleXpert Brand Colors */
        :root {
            --bs-primary: #940000 !important;
            --bs-white: #ffffff !important;
        }

        /* Global Color Overrides */
        * {
            transition: all 0.3s ease !important;
        }

        /* Background Colors - Only specific sections use red */
        .navbar.bg-primary, .footer, .newsletter.bg-primary,
        .video-section.bg-primary, .donate-form.bg-primary {
            background: linear-gradient(135deg, #940000 0%, #780000 100%) !important;
        }

        /* Other sections keep original backgrounds */
        .service-item, .event-item, .team-item,
        .donation-item, .testimonial-item,
        .carousel-text, .about-section {
            background: transparent !important;
        }

        /* Text Colors */
        .text-primary, a.text-primary, h1.text-primary, h2.text-primary, h3.text-primary,
        h4.text-primary, h5.text-primary, h6.text-primary, .section-title,
        i.text-primary, .fa.text-primary {
            color: #940000 !important;
        }
        .text-secondary, .text-dark {
            color: #940000 !important;
        }

        /* Border Colors */
        .border-primary, .border-top-primary, .border-bottom-primary,
        .border-start-primary, .border-end-primary {
            border-color: #940000 !important;
        }

        /* Button Colors */
        .btn-primary {
            background-color: #940000 !important;
            border-color: #940000 !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 5px;
        }
        .btn-primary:hover {
            background-color: #780000 !important;
            border-color: #780000 !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(148, 0, 0, 0.3);
        }
        .btn-secondary {
            background-color: #ffffff !important;
            border-color: #940000 !important;
            color: #940000 !important;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 5px;
        }
        .btn-secondary:hover {
            background-color: #940000 !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(148, 0, 0, 0.3);
        }

        /* Spinner */
        .spinner-border {
            border-color: #940000 !important;
        }

        /* Progress Bars */
        .progress-bar, .progress-bar.w-100 {
            background-color: #940000 !important;
        }

        /* Section Titles */
        .section-title.bg-white {
            color: #940000 !important;
        }
        .section-title.bg-white::after {
            background-color: #940000 !important;
        }

        /* Links and hover states */
        a.text-primary:hover {
            color: #780000 !important;
        }

        /* All Icons - Force ShuleXpert Brand Colors */
        i.fa, .fa, i.fas, i.far, i.fab, i.fal, i.fad {
            color: inherit;
        }
        .fa-check.text-primary, .fa-star.text-primary, .fa-clock.text-primary,
        .fa-calendar-alt.text-primary, .fa-map-marker-alt.text-primary,
        .fa-phone-alt.text-primary, .fa-envelope.text-primary, .fa-envelope-open.text-primary,
        .fa-users.text-primary, .fa-award.text-primary, .fa-list-check.text-primary,
        .fa-comments.text-primary, .fa-paper-plane.text-primary,
        .fa-user-graduate, .fa-sms, .fa-user-check, .fa-credit-card, .fa-trophy,
        .btn-square i, i.text-secondary {
            color: #940000 !important;
        }
        .fa-quote-right.text-secondary {
            color: #940000 !important;
        }

        /* Service Icons */
        .service-item .btn-square i,
        .fa-envelope-open.text-dark, .fa-phone-alt.text-dark, .fa-map-marker-alt.text-dark,
        .fa-user-graduate.fa-2x.text-secondary, .fa-sms.fa-2x.text-secondary,
        .fa-user-check.fa-2x.text-secondary, .fa-credit-card.fa-2x.text-secondary,
        .fa-users.fa-2x.text-secondary, .fa-trophy.fa-2x.text-secondary {
            color: #940000 !important;
        }

        /* Force all icons in services to use brand color */
        .service-item .btn-square {
            background: #ffffff !important;
        }
        .service-item .btn-square i.fa {
            color: #940000 !important;
        }

        /* Override any other colors to use brand colors */
        .text-success, .bg-success, .btn-success, .border-success {
            color: #940000 !important;
            background-color: #940000 !important;
            border-color: #940000 !important;
        }

        /* Cards and Hover Effects */
        .service-item, .event-item, .donation-item, .team-item, .testimonial-item {
            border-radius: 10px;
            overflow: hidden;
        }
        .service-item:hover, .event-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(148, 0, 0, 0.2);
        }

        /* Navbar */
        .navbar {
            box-shadow: 0 2px 10px rgba(148, 0, 0, 0.1);
        }
        .nav-link {
            font-weight: 500;
        }
        .nav-link:hover, .nav-link.active {
            color: #ffffff !important;
        }

        /* Top Bar - Red Background with White Text */
        .top-bar {
            background: linear-gradient(135deg, #940000 0%, #780000 100%) !important;
            border-bottom: 1px solid #780000;
        }
        .top-bar h1, .top-bar .display-5 {
            color: #ffffff !important;
        }
        .top-bar .text-white {
            color: #ffffff !important;
        }
        .top-bar span {
            color: #ffffff !important;
        }
        /* Top bar contact info text */
        .top-bar .flex-shrink-0 + .ms-2 span {
            color: #ffffff !important;
        }
        /* Top bar all text should be white */
        .top-bar *, .top-bar h1, .top-bar h6, .top-bar span, .top-bar a {
            color: #ffffff !important;
        }

        /* Carousel Enhancements */
        .carousel-text h1 {
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Stats Cards - Keep white background for text readability */
        .features-section .bg-primary, .donation-section .bg-primary {
            background: linear-gradient(135deg, #940000 0%, #780000 100%) !important;
        }

        /* Footer - Keep red gradient */
        .footer {
            background: linear-gradient(135deg, #940000 0%, #780000 100%) !important;
        }

        /* Ensure ALL content sections have white backgrounds */
        .container-fluid.py-5,
        .about-section,
        .service-section,
        .event-section,
        .team-section,
        .testimonial-section,
        .donation-section,
        .container.py-5 {
            background: #ffffff !important;
        }

        /* Force all sections to white background */
        .container-fluid:not(.bg-primary):not(.footer):not(.top-bar):not(.video-section):not(.newsletter.bg-primary):not(.donate-form.bg-primary) {
            background: #ffffff !important;
        }

        /* Individual cards keep white */
        .service-item,
        .event-item,
        .team-item,
        .donation-item,
        .testimonial-item,
        .about-img,
        .carousel-text {
            background: transparent !important;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #940000;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #780000;
        }

        /* Enhanced Typography */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Animation for counters */
        [data-toggle="counter-up"] {
            font-weight: 700;
            color: #ffffff !important;
        }

        /* Donation/Feature Card Improvements */
        .donation-item, .service-item h3 {
            color: #940000;
            font-weight: 600;
        }

        /* Team Cards */
        .team-item h3 {
            color: #940000;
            font-weight: 600;
        }

        /* Testimonial Section */
        .testimonial-text h5 {
            color: #940000;
        }

        /* Form Input Focus */
        .form-control:focus {
            border-color: #940000;
            box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
        }

        /* News letter input border */
        .newsletter input.form-control {
            border: 2px solid #940000;
            border-radius: 5px;
        }

        /* Back to Top Button */
        .back-to-top {
            background: #940000 !important;
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(148, 0, 0, 0.3);
        }
        .back-to-top:hover {
            background: #780000 !important;
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Social Media Icons */
        .btn-square {
            background: rgba(148, 0, 0, 0.1);
        }
        .btn-square:hover {
            background: #940000 !important;
            transform: scale(1.1);
        }

        /* Ensure all text in primary backgrounds is white */
        .bg-primary, .bg-secondary {
            color: #ffffff !important;
        }
        .bg-primary span, .bg-primary h1, .bg-primary h2, .bg-primary h3,
        .bg-primary h4, .bg-primary h5, .bg-primary h6, .bg-primary p {
            color: #ffffff !important;
        }

        /* Fix text on red backgrounds - must be white (only for navbar, footer, stats cards) */
        .navbar.bg-primary *, .footer *, .newsletter.bg-primary *,
        .video-section.bg-primary *, .donate-form.bg-primary *,
        .features-section .bg-primary *, .donation-section .bg-primary * {
            color: #ffffff !important;
        }

        /* Sections with red backgrounds */
        .navbar.bg-primary, .footer, .newsletter.bg-primary,
        .video-section.bg-primary, .donate-form.bg-primary,
        .features-section .bg-primary, .donation-section .bg-primary {
            color: #ffffff !important;
        }

        /* White backgrounds sections use dark text */
        .container-fluid.py-5 h1, .container-fluid.py-5 h2,
        .container-fluid.py-5 h3, .container-fluid.py-5 p {
            color: #333333 !important;
        }

        /* Section titles on white backgrounds */
        .section-title.bg-white::before {
            background-color: #940000 !important;
        }

        /* White backgrounds - text should be #940000 */
        .bg-white, .bg-light, .service-item, .event-item, .team-item,
        .donation-item, .testimonial-item {
            background: #ffffff !important;
        }

        /* Text on white background should be #940000 where appropriate */
        .bg-white h1:not(.text-primary):not(.text-white),
        .bg-white h2:not(.text-primary):not(.text-white),
        .bg-white h3:not(.text-primary):not(.text-white),
        .bg-white h4:not(.text-primary):not(.text-white),
        .bg-white p:not(.text-white) {
            color: #333333 !important;
        }

        /* Navigation active state */
        .navbar .nav-link.active {
            background: rgba(255, 255, 255, 0.2) !important;
            border-radius: 5px;
        }

        /* Fix topbar icons */
        .top-bar .btn-square {
            background: #940000 !important;
        }
        .top-bar .btn-square i {
            color: #ffffff !important;
        }
        .top-bar .text-white {
            color: #940000 !important;
        }
        .top-bar h6 {
            color: #940000 !important;
        }

        /* Navigation bar text */
        .navbar.bg-primary .nav-link,
        .navbar.bg-primary .navbar-nav .nav-link {
            color: #ffffff !important;
        }
        .navbar.bg-primary .nav-link:hover {
            color: #ffffff !important;
            opacity: 0.8;
        }

        /* All icons in navbar should be white */
        .navbar.bg-primary i.fa, .navbar.bg-primary i.fab {
            color: #ffffff !important;
        }

        /* Stats section - make sure text is white on red background */
        .bg-primary h1, .bg-primary .display-5,
        .bg-primary .display-6 {
            color: #ffffff !important;
        }

        /* Footer - ensure all text is white */
        .footer, .footer *, .footer h4, .footer p,
        .footer a, .footer span, .footer i {
            color: #ffffff !important;
        }

        /* Footer links hover */
        .footer a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        /* Newsletter section */
        .bg-primary form, .bg-primary h1, .bg-primary p {
            color: #ffffff !important;
        }

        /* Carousel text on white background should be dark */
        .carousel-text h1 {
            color: #940000 !important;
        }
        .carousel-text p {
            color: #333333 !important;
        }

        /* Video section text */
        .container-fluid.bg-primary h3 {
            color: #ffffff !important;
        }

        /* Progress bars on dark background */
        .bg-secondary .progress-bar {
            background: #ffffff !important;
        }

        /* Banner section text */
        .banner-inner.bg-light h1,
        .banner-inner.bg-light p {
            color: #940000 !important;
        }

        /* Donate form section */
        .donate-form.bg-primary h1,
        .donate-form.bg-primary p,
        .donate-form.bg-primary label {
            color: #ffffff !important;
        }
    </style>

  <!-- Spinner Start -->
  <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
                        <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; border-color: #940000;"></div>
    </div>
    <!-- Spinner End -->


    <!-- Topbar Start -->
    <div class="container-fluid top-bar wow fadeIn" data-wow-delay="0.1s" style="background: #ffffff !important; background-color: #ffffff !important; border-bottom: 2px solid; border-image: linear-gradient(90deg, #ed9999 0%, #940000 100%) 1; box-shadow: 0 1px 5px rgba(148, 0, 0, 0.1);">
        <div class="row align-items-center h-100">
            <div class="col-lg-4 text-center text-lg-start">
                <a href="/" style="text-decoration: none;">
                    <h1 class="display-5 m-0" style="color: #940000 !important;">ShuleXpertsssssssssssssssss</h1>
                </a>
            </div>
            <div class="col-lg-8 d-none d-lg-block">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-end">
                            <div class="flex-shrink-0 btn-square" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important; width: 50px; height: 50px; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-phone-alt" style="color: #ffffff !important;"></i>
                            </div>
                            <div class="ms-2">
                                <h6 style="color: #940000 !important;" class="mb-0">Call Us</h6>
                                <span style="color: #940000 !important;">+255757166599</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-end">
                            <div class="flex-shrink-0 btn-square" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important; width: 50px; height: 50px; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-envelope-open" style="color: #ffffff !important;"></i>
                            </div>
                            <div class="ms-2">
                                <h6 style="color: #940000 !important;" class="mb-0">Mail Us</h6>
                                <span style="color: #940000 !important;">emca@emca.tech</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-end">
                            <div class="flex-shrink-0 btn-square" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important; width: 50px; height: 50px; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-map-marker-alt" style="color: #ffffff !important;"></i>
                            </div>
                            <div class="ms-2">
                                <h6 style="color: #940000 !important;" class="mb-0">Address</h6>
                                <span style="color: #940000 !important;">Moshi Kilimanjaro</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar Start -->
    <div class="container-fluid px-0 wow fadeIn" data-wow-delay="0.1s" style="background: #ffffff !important;">
        <div class="nav-bar">
            <nav class="navbar navbar-expand-lg px-4 py-lg-0" style="background: #ffffff !important; background-color: #ffffff !important; box-shadow: 0 2px 10px rgba(148, 0, 0, 0.15); border-bottom: 3px solid; border-image: linear-gradient(90deg, #ed9999 0%, #940000 100%) 1; padding: 15px 0 !important;">
                <h4 class="d-lg-none m-0" style="color: #940000 !important;">Menu</h4>
                <button type="button" class="navbar-toggler me-0" data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse" style="border-color: #940000 !important;">
                    <span class="navbar-toggler-icon" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(148, 0, 0, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e&quot;) !important;"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav me-auto">
                        <a href="/" class="nav-item nav-link active" style="color: #ffffff !important; background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important; border-radius: 5px; font-weight: 600; padding: 8px 15px !important; margin: 0 5px;">Home</a>
                        <a href="/about" class="nav-item nav-link" style="color: #940000 !important; font-weight: 500; padding: 8px 15px !important; margin: 0 5px; border-radius: 5px; transition: all 0.3s ease;" onmouseover="this.style.color='#ffffff'; this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)';" onmouseout="this.style.color='#940000'; this.style.background='transparent';">About</a>
                        <a href="/services" class="nav-item nav-link" style="color: #940000 !important; font-weight: 500; padding: 8px 15px !important; margin: 0 5px; border-radius: 5px; transition: all 0.3s ease;" onmouseover="this.style.color='#ffffff'; this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)';" onmouseout="this.style.color='#940000'; this.style.background='transparent';">Services</a>
                        <a href="{{ route('login') }}" class="nav-item nav-link" style="color: #940000 !important; font-weight: 500; padding: 8px 15px !important; margin: 0 5px; border-radius: 5px; transition: all 0.3s ease;" onmouseover="this.style.color='#ffffff'; this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)';" onmouseout="this.style.color='#940000'; this.style.background='transparent';">Login</a>
                        <a href="{{ route('pricing') }}" class="nav-item nav-link" style="color: #940000 !important; font-weight: 500; padding: 8px 15px !important; margin: 0 5px; border-radius: 5px; transition: all 0.3s ease;" onmouseover="this.style.color='#ffffff'; this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)';" onmouseout="this.style.color='#940000'; this.style.background='transparent';">Pricing</a>
                        <!-- <a href="{{ route('online_application') }}" class="nav-item nav-link" style="color: #940000 !important; font-weight: 500; padding: 8px 15px !important; margin: 0 5px; border-radius: 5px; transition: all 0.3s ease;" onmouseover="this.style.color='#ffffff'; this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)';" onmouseout="this.style.color='#940000'; this.style.background='transparent';">Online Application</a> -->
                        <a href="/contact" class="nav-item nav-link" style="color: #940000 !important; font-weight: 500; padding: 8px 15px !important; margin: 0 5px; border-radius: 5px; transition: all 0.3s ease;" onmouseover="this.style.color='#ffffff'; this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)';" onmouseout="this.style.color='#940000'; this.style.background='transparent';">Contact</a>
                        <a href="{{ asset('shuleXpert.apk') }}" class="nav-item nav-link" style="color: #940000 !important; font-weight: 500; padding: 8px 15px !important; margin: 0 5px; border-radius: 5px; transition: all 0.3s ease;" onmouseover="this.style.color='#ffffff'; this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)'; if(this.querySelector('i')) this.querySelector('i').style.color='#ffffff';" onmouseout="this.style.color='#940000'; this.style.background='transparent'; if(this.querySelector('i')) this.querySelector('i').style.color='#940000';">
                            <i class="fa fa-download me-1" style="color: #940000 !important; transition: all 0.3s ease;"></i> Download App
                        </a>
                    </div>
                    <div class="d-none d-lg-flex ms-auto">
                        <a class="btn btn-square ms-2" href="#!" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important; border: none; width: 40px; height: 40px; border-radius: 5px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.background='linear-gradient(135deg, #940000 0%, #ed9999 100%)'; this.style.transform='scale(1.1)';" onmouseout="this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)'; this.style.transform='scale(1)';"><i class="fab fa-twitter" style="color: #ffffff !important;"></i></a>
                        <a class="btn btn-square ms-2" href="#!" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important; border: none; width: 40px; height: 40px; border-radius: 5px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.background='linear-gradient(135deg, #940000 0%, #ed9999 100%)'; this.style.transform='scale(1.1)';" onmouseout="this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)'; this.style.transform='scale(1)';"><i class="fab fa-facebook-f" style="color: #ffffff !important;"></i></a>
                        <a class="btn btn-square ms-2" href="#!" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important; border: none; width: 40px; height: 40px; border-radius: 5px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.background='linear-gradient(135deg, #940000 0%, #ed9999 100%)'; this.style.transform='scale(1.1)';" onmouseout="this.style.background='linear-gradient(135deg, #ed9999 0%, #940000 100%)'; this.style.transform='scale(1)';"><i class="fab fa-youtube" style="color: #ffffff !important;"></i></a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Navbar End -->
