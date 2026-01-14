<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ShuleXpert - School Management System</title>
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
            .service-title {
                margin-bottom: 2rem !important;
                padding-right: 0 !important;
            }
            .service-title h1.display-6 {
                font-size: 1.5rem !important;
                line-height: 1.3 !important;
            }
            .service-title p.fs-5 {
                font-size: 0.95rem !important;
            }
            .service-item {
                margin-bottom: 1.5rem !important;
                padding: 25px 20px !important;
            }
            .service-item h3 {
                font-size: 1.15rem !important;
                min-height: auto !important;
                margin-bottom: 12px !important;
            }
            .service-item p {
                font-size: 0.9rem !important;
                line-height: 1.6 !important;
                margin-bottom: 15px !important;
            }
            .service-item .btn-square {
                width: 60px !important;
                height: 60px !important;
                margin-bottom: 20px !important;
            }
            .service-item .btn-square i {
                font-size: 24px !important;
            }
            .service-item a {
                font-size: 0.9rem !important;
            }
            
            /* Header/Navbar Mobile */
            .top-bar {
                padding: 10px 0 !important;
            }
            .top-bar .row {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .container-fluid.px-0 {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            .nav-bar {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .nav-bar .navbar {
                padding: 12px 0 !important;
            }
            .navbar h4.d-lg-none {
                font-size: 1.1rem !important;
                margin: 0 !important;
                padding-right: 15px !important;
                padding-left: 0 !important;
            }
            .navbar-toggler {
                padding: 8px 12px !important;
                margin-left: auto !important;
                margin-right: 0 !important;
                border-width: 2px !important;
                border-radius: 5px !important;
            }
            .navbar-toggler-icon {
                width: 24px !important;
                height: 24px !important;
            }
            .navbar-collapse {
                margin-top: 15px !important;
                padding-top: 15px !important;
                border-top: 1px solid rgba(148, 0, 0, 0.1) !important;
            }
            .navbar-nav .nav-link {
                padding: 10px 15px !important;
                margin: 5px 0 !important;
                border-radius: 5px !important;
                display: block !important;
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
            
            /* Service Items - Extra Small */
            .service-title h1.display-6 {
                font-size: 1.3rem !important;
            }
            .service-item {
                padding: 20px 15px !important;
                margin-bottom: 1.25rem !important;
            }
            .service-item h3 {
                font-size: 1.05rem !important;
                margin-bottom: 10px !important;
            }
            .service-item p {
                font-size: 0.85rem !important;
                line-height: 1.5 !important;
            }
            .service-item .btn-square {
                width: 50px !important;
                height: 50px !important;
                margin-bottom: 15px !important;
            }
            .service-item .btn-square i {
                font-size: 20px !important;
            }
            
            /* Header/Navbar - Extra Small */
            .top-bar {
                padding: 8px 0 !important;
            }
            .nav-bar {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .nav-bar .navbar {
                padding: 10px 0 !important;
            }
            .navbar h4.d-lg-none {
                font-size: 1rem !important;
                padding-right: 10px !important;
                padding-left: 0 !important;
            }
            .navbar-toggler {
                padding: 6px 10px !important;
                margin-right: 0 !important;
            }
            .navbar-nav .nav-link {
                padding: 8px 12px !important;
                margin: 3px 0 !important;
                font-size: 0.9rem !important;
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
        
        /* Gradient Background Styles */
        .btn-primary {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
            border: none !important;
            box-shadow: 0 2px 5px rgba(148, 0, 0, 0.2) !important;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #940000 0%, #ed9999 100%) !important;
            box-shadow: 0 3px 8px rgba(148, 0, 0, 0.3) !important;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #940000 0%, #ed9999 100%) !important;
            border: none !important;
            box-shadow: 0 2px 5px rgba(148, 0, 0, 0.2) !important;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
            box-shadow: 0 3px 8px rgba(148, 0, 0, 0.3) !important;
            transform: translateY(-2px);
        }
        
        /* Carousel Styling */
        .carousel-img img {
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(148, 0, 0, 0.15) !important;
            transition: transform 0.3s ease;
        }
        
        .carousel-img img:hover {
            transform: scale(1.02);
        }
        
        .carousel-text {
            position: relative;
        }
        
        .carousel-text::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #ed9999 0%, #940000 100%);
            border-radius: 2px;
        }
        
        /* Reduce Box Shadows - Light shadows only */
        .card, .service-item, .donation-item, .event-item, .team-item, 
        .testimonial-item, .banner-inner, .donate-text, .donate-form {
            box-shadow: 0 2px 8px rgba(148, 0, 0, 0.1) !important;
            border-radius: 8px;
        }
        
        .card:hover, .service-item:hover, .event-item:hover, .team-item:hover {
            box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15) !important;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        
        /* Background Primary with Gradient */
        .bg-primary {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
        }
        
        /* Section Titles Styling */
        .section-title {
            color: #940000 !important;
            font-weight: 600;
            position: relative;
            padding-left: 15px;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 20px;
            background: linear-gradient(180deg, #ed9999 0%, #940000 100%);
            border-radius: 2px;
        }
        
        .section-title.bg-white {
            background: transparent !important;
        }
        
        /* Service Items Styling - Complete Redesign */
        .service-item {
            background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(237, 153, 153, 0.03) 100%) !important;
            border: 2px solid #940000 !important;
            border-radius: 12px !important;
            padding: 35px 25px !important;
            margin-bottom: 30px !important;
            transition: all 0.4s ease;
            box-shadow: 0 3px 15px rgba(148, 0, 0, 0.08) !important;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .service-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #ed9999 0%, #940000 100%);
            transform: scaleX(1);
            transition: transform 0.4s ease;
        }
        
        .service-item:hover {
            box-shadow: 0 8px 25px rgba(148, 0, 0, 0.15) !important;
            transform: translateY(-8px);
        }
        
        .service-item .btn-square {
            width: 70px !important;
            height: 70px !important;
            border-radius: 12px !important;
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
            margin-bottom: 25px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(148, 0, 0, 0.2) !important;
            transition: all 0.3s ease;
        }
        
        .service-item:hover .btn-square {
            transform: rotate(5deg) scale(1.1);
            box-shadow: 0 6px 20px rgba(148, 0, 0, 0.3) !important;
        }
        
        .service-item .btn-square i {
            color: #ffffff !important;
            font-size: 28px !important;
        }
        
        .service-item h3 {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: #940000 !important;
            margin-bottom: 18px !important;
            line-height: 1.3;
            min-height: 60px;
        }
        
        .service-item p {
            font-size: 15px !important;
            line-height: 1.8 !important;
            color: #555 !important;
            margin-bottom: 20px !important;
            flex-grow: 1;
        }
        
        .service-item a {
            color: #940000 !important;
            font-weight: 600 !important;
            font-size: 15px !important;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            margin-top: auto;
        }
        
        .service-item a:hover {
            color: #ed9999 !important;
            transform: translateX(5px);
        }
        
        .service-item a::after {
            content: '→';
            margin-left: 8px;
            transition: transform 0.3s ease;
        }
        
        .service-item a:hover::after {
            transform: translateX(5px);
        }
        
        .service-title {
            padding-right: 20px;
        }
        
        .service-title h1 {
            font-size: 36px !important;
            font-weight: 800 !important;
            color: #940000 !important;
            line-height: 1.2;
            margin-bottom: 20px !important;
        }
        
        .service-title p {
            font-size: 17px !important;
            line-height: 1.8 !important;
            color: #666 !important;
        }
        
        /* Button Square with Gradient */
        .btn-square {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
            box-shadow: 0 2px 5px rgba(148, 0, 0, 0.15) !important;
        }
        
        /* Footer Styling - Override external CSS */
        .footer,
        .container-fluid.footer,
        div.footer,
        .footer.container-fluid {
            background-color: #000000 !important;
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
        }
        
        /* Ensure no gradient or image background */
        .footer::before,
        .footer::after {
            display: none !important;
        }
        
        .footer h4 {
            color: #ffffff !important;
        }
        
        .footer p,
        .footer .text-light {
            color: #cccccc !important;
        }
        
        .footer a {
            color: #cccccc !important;
        }
        
        .footer .btn-link {
            color: #cccccc !important;
        }
        
        /* Footer Links Hover */
        .footer a:hover,
        .footer .btn-link:hover {
            color: #ed9999 !important;
            transition: color 0.3s ease;
        }
        
        /* Footer Powered By */
        .footer-powered-by {
            color: #ed9999 !important;
            font-weight: 600;
        }
        
        /* Progress Bar Gradient */
        .progress-bar {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
        }
        
        /* Social Buttons */
        .btn-square.btn-primary {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
        }
        
        .btn-square.btn-primary:hover {
            background: linear-gradient(135deg, #940000 0%, #ed9999 100%) !important;
        }
        
        /* Text Primary Color */
        .text-primary {
            color: #940000 !important;
        }
        
        /* Top Bar Styling - White Background with Gradient */
        .top-bar,
        .container-fluid.top-bar,
        .top-bar.bg-primary {
            background: #ffffff !important;
            background-color: #ffffff !important;
            border-bottom: 2px solid transparent;
            border-image: linear-gradient(90deg, #ed9999 0%, #940000 100%) 1;
            box-shadow: 0 1px 5px rgba(148, 0, 0, 0.1);
        }
        
        .top-bar *,
        .top-bar h1,
        .top-bar .display-5,
        .top-bar h6,
        .top-bar span,
        .top-bar a,
        .top-bar p,
        .top-bar div {
            color: #940000 !important;
        }
        
        .top-bar .btn-square {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
        }
        
        .top-bar .btn-square i {
            color: #ffffff !important;
        }
        
        /* Header/Navbar Styling - White Background with Gradient */
        .navbar,
        .navbar.bg-primary,
        .nav-bar .navbar,
        .navbar.bg-primary.navbar-dark {
            background: #ffffff !important;
            background-color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(148, 0, 0, 0.15);
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(90deg, #ed9999 0%, #940000 100%) 1;
            padding: 15px 0 !important;
        }
        
        .navbar .nav-link,
        .navbar-nav .nav-link {
            color: #940000 !important;
            font-weight: 500;
            padding: 8px 15px !important;
            margin: 0 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .navbar .nav-link:hover {
            color: #ffffff !important;
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
            transform: translateY(-2px);
        }
        
        .navbar .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
            font-weight: 600;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(148, 0, 0, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }
        
        .navbar-toggler {
            border-color: #940000 !important;
            border-width: 2px !important;
            padding: 8px 12px !important;
            border-radius: 5px !important;
        }
        
        /* Ensure navbar has proper spacing on mobile */
        @media (max-width: 991px) {
            .navbar {
                position: relative !important;
            }
            .navbar > .container-fluid {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            .nav-bar {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .navbar .navbar-brand,
            .navbar h4.d-lg-none {
                margin-right: 10px !important;
            }
            .navbar-toggler {
                margin-left: auto !important;
                margin-right: 0 !important;
            }
        }
        
        .navbar h4 {
            color: #940000 !important;
        }
        
        .dropdown-menu {
            background: #ffffff !important;
            border: 1px solid rgba(148, 0, 0, 0.1);
            box-shadow: 0 4px 15px rgba(148, 0, 0, 0.1);
        }
        
        .dropdown-item {
            color: #940000 !important;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(237, 153, 153, 0.1) 0%, rgba(148, 0, 0, 0.1) 100%) !important;
            color: #940000 !important;
        }
        
        .navbar .btn-square.btn-dark {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;
            border: none;
            color: #ffffff;
        }
        
        .navbar .btn-square.btn-dark:hover {
            background: linear-gradient(135deg, #940000 0%, #ed9999 100%) !important;
            transform: scale(1.1);
        }
        
        .navbar .btn-square.btn-dark i {
            color: #ffffff !important;
        }
        
        /* Override any remaining heavy shadows */
        .shadow, .shadow-sm, .shadow-lg {
            box-shadow: 0 2px 8px rgba(148, 0, 0, 0.1) !important;
        }
        
        /* Testimonial Styling */
        .testimonial-item {
            background: #ffffff !important;
            border-radius: 15px !important;
            padding: 30px !important;
            box-shadow: 0 5px 20px rgba(148, 0, 0, 0.1) !important;
            transition: all 0.3s ease;
            border: 1px solid rgba(148, 0, 0, 0.1);
        }
        
        .testimonial-item:hover {
            box-shadow: 0 8px 30px rgba(148, 0, 0, 0.2) !important;
            transform: translateY(-5px);
        }
        
        .testimonial-text {
            position: relative;
        }
        
        .testimonial-text p {
            line-height: 1.8;
            color: #333;
        }
        
        .testimonial-icon-bg {
            transition: transform 0.3s ease;
        }
        
        .testimonial-item:hover .testimonial-icon-bg {
            transform: scale(1.05);
        }
        
        .testimonial-title h1 {
            color: #940000 !important;
            font-weight: 700;
        }
        
        .testimonial-title p {
            color: #666;
            line-height: 1.8;
        }
        
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
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ffffff;
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
        <div class="splash-text">ShuleXpert</div>
        <div class="splash-loader"></div>
        <div class="splash-powered">Powered by EmCa Techonology</div>
    </div>
</div>

<script>
    // Hide splash screen after 1 second
    window.addEventListener('load', function() {
        setTimeout(function() {
            var splash = document.getElementById('splash-screen');
            splash.classList.add('hide');
            setTimeout(function() {
                splash.style.display = 'none';
            }, 500);
        }, 1000);
    });
</script>

@include('includes.web_nav')
    <!-- Carousel Start -->
    <div class="container-fluid p-0 wow fadeIn" data-wow-delay="0.1s">
        <div class="owl-carousel header-carousel py-5">
            <div class="container py-5">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="carousel-text">
                            <h1 class="display-1 text-uppercase mb-3">Transform Your School Management</h1>
                            <p class="fs-5 mb-5">ShuleXpert is a comprehensive school management system designed for pre-schools, primary, secondary, and advanced schools. Manage all your operations seamlessly.</p>
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
                            <img class="w-100" src="{{ asset('images/S1.jpg') }}" alt="Image">
                        </div>
                    </div>
                </div>
            </div>
            <div class="container py-5">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="carousel-text">
                            <h1 class="display-1 text-uppercase mb-3">Comprehensive School Management</h1>
                            <p class="fs-5 mb-5">Manage all your school operations from one centralized platform. Track attendance, manage results, handle fees, and communicate effectively with parents and students.</p>
                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <a class="btn btn-primary py-3 px-4 me-3" href="#!">Learn More</a>
                                <a class="btn btn-secondary py-3 px-4" href="#!">Get Started</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="carousel-img">
                            <img class="w-100" src="{{ asset('images/S2.jpg') }}" alt="Image">
                        </div>
                    </div>
                </div>
            </div>
            <div class="container py-5">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="carousel-text">
                            <h1 class="display-1 text-uppercase mb-3">Empower Your Educational Institution</h1>
                            <p class="fs-5 mb-5">Experience the power of digital transformation in education. Join schools across Tanzania that are revolutionizing their management with ShuleXpert.</p>
                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <a class="btn btn-primary py-3 px-4 me-3" href="#!">Request Demo</a>
                                <a class="btn btn-secondary py-3 px-4" href="#!">Contact Us</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="carousel-img">
                            <img class="w-100" src="{{ asset('images/S3.webp') }}" alt="Image">
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
                        <img class="img-fluid w-100" src="{{ asset('images/c1.webp') }}" alt="Image">
                    </div>
                </div>
                <div class="col-lg-6">
                    <p class="section-title bg-white text-start text-primary pe-3">About Us</p>
                    <h1 class="display-6 mb-4 wow fadeIn" data-wow-delay="0.2s">Innovative School Management Solutions</h1>
                    <p class="mb-4 wow fadeIn" data-wow-delay="0.3s">ShuleXpert revolutionizes how schools operate by providing a comprehensive management system that streamlines academic operations, communication, and administrative tasks. Join hundreds of schools already using ShuleXpert to enhance their educational delivery.</p>
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
                                <p class="fs-5 text-dark">Experience the future of school management with ShuleXpert's comprehensive platform designed for Tanzanian schools.</p>
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
                    <h1 class="display-6 mb-4 wow fadeIn" data-wow-delay="0.2s">Why Schools Choose ShuleXpert!</h1>
                    <p class="mb-4 wow fadeIn" data-wow-delay="0.3s">ShuleXpert provides comprehensive school management solutions that streamline operations, improve communication, and enhance educational outcomes. Join hundreds of schools transforming their operations.</p>
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


    <!-- Key Features Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 700px;">
                <p class="section-title bg-white text-center text-primary px-3">Features</p>
                <h1 class="display-6 mb-4">Powerful Features for Modern School Management</h1>
                <p class="fs-5 mb-4">ShuleXpert provides comprehensive solutions for schools, teachers, parents, and students. Powered by EmCa Technology.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="donation-item h-100 p-4">
                        <div class="donation-detail">
                            <div class="position-relative mb-4">
                                <img class="img-fluid w-100 rounded" src="{{ asset('images/exam.jpg') }}" alt="Fee Payment Management" style="height: 200px; object-fit: cover;">
                                <span class="badge bg-primary px-3 py-2 position-absolute top-0 end-0 m-3" style="font-size: 0.85rem;">Payments</span>
                            </div>
                            <h3 class="mb-3">Easy Fee Payment Management</h3>
                            <p class="mb-4">ShuleXpert provides easy payment of fees using control numbers through different banks or Mobile Network Operators (MNO). Parents can pay school fees conveniently from anywhere, anytime with clear fee structures and payment tracking.</p>
                            <ul class="list-unstyled mb-4">
                                <li><i class="fa fa-check text-primary me-2"></i>Control number payment system</li>
                                <li><i class="fa fa-check text-primary me-2"></i>Multiple payment channels (Banks & MNO)</li>
                                <li><i class="fa fa-check text-primary me-2"></i>Clear fee structure & tracking</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
                    <div class="donation-item h-100 p-4">
                        <div class="donation-detail">
                            <div class="position-relative mb-4">
                                <img class="img-fluid w-100 rounded" src="{{ asset('images/biometric.jpg') }}" alt="Biometric Attendance" style="height: 200px; object-fit: cover;">
                                <span class="badge bg-primary px-3 py-2 position-absolute top-0 end-0 m-3" style="font-size: 0.85rem;">Attendance</span>
                            </div>
                            <h3 class="mb-3">Biometric Fingerprint Attendance</h3>
                            <p class="mb-4">Maintain accurate attendance records for staff, teachers, and students using advanced biometric fingerprint devices. Real-time tracking ensures accountability and provides instant attendance data for better management.</p>
                            <ul class="list-unstyled mb-4">
                                <li><i class="fa fa-check text-primary me-2"></i>Fingerprint biometric technology</li>
                                <li><i class="fa fa-check text-primary me-2"></i>Real-time attendance tracking</li>
                                <li><i class="fa fa-check text-primary me-2"></i>Staff, teachers & student management</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
                    <div class="donation-item h-100 p-4">
                        <div class="donation-detail">
                            <div class="position-relative mb-4">
                                <img class="img-fluid w-100 rounded" src="{{ asset('images/timetable.jpg') }}" alt="Timetable & Examination Management" style="height: 200px; object-fit: cover;">
                                <span class="badge bg-primary px-3 py-2 position-absolute top-0 end-0 m-3" style="font-size: 0.85rem;">Management</span>
                            </div>
                            <h3 class="mb-3">Timetable & Examination Management</h3>
                            <p class="mb-4">Streamline timetable management, examination scheduling, and class sessions. Easier examination management and results processing with SMS notifications to parents. Real-time session alerts keep teachers informed via mobile application.</p>
                            <ul class="list-unstyled mb-4">
                                <li><i class="fa fa-check text-primary me-2"></i>Timetable & class session management</li>
                                <li><i class="fa fa-check text-primary me-2"></i>Examination & results management</li>
                                <li><i class="fa fa-check text-primary me-2"></i>Real-time SMS result notifications</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Key Features End -->


    <!-- Banner Start -->
    <div class="container-fluid banner py-5">
        <div class="container">
            <div class="banner-inner bg-light p-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="row justify-content-center">
                    <div class="col-lg-8 py-5 text-center">
                        <h1 class="display-6 wow fadeIn" data-wow-delay="0.3s">Transform Your School Operations Today!</h1>
                        <p class="fs-5 mb-4 wow fadeIn" data-wow-delay="0.5s">Join hundreds of schools already using ShuleXpert to streamline their operations, enhance communication, and improve educational outcomes. Get started with a free trial today!</p>
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


    <!-- Additional Features Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 700px;">
                <p class="section-title bg-white text-center text-primary px-3">Features</p>
                <h1 class="display-6 mb-4">More Powerful Features</h1>
                <p class="fs-5 mb-4">Discover additional capabilities that make ShuleXpert the complete school management solution. Powered by EmCa Technology.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="event-item h-100 p-4">
                        <img class="img-fluid w-100 mb-4 rounded" src="{{ asset('images/S1.jpg') }}" alt="ShuleXpert Mobile App" style="height: 220px; object-fit: cover;">
                        <h3 class="mb-3">ShuleXpert Mobile App & Real-time Alerts</h3>
                        <p class="mb-4">Access ShuleXpert on the go with our mobile applications available for iOS and Android. Teachers and parents can stay connected with real-time session alerts and instant notifications delivered directly to their mobile devices.</p>
                        <div class="bg-light p-4 rounded">
                            <p class="mb-2"><i class="fa fa-mobile-alt text-primary me-2"></i><strong>Mobile App:</strong> iOS and Android apps for teachers and parents</p>
                            <p class="mb-2"><i class="fa fa-bell text-primary me-2"></i><strong>Real-time Alerts:</strong> Session notifications to teachers via mobile app</p>
                            <p class="mb-0"><i class="fa fa-comments text-primary me-2"></i><strong>Better Communication:</strong> Enhanced parent-teacher communication</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
                    <div class="event-item h-100 p-4">
                        <img class="img-fluid w-100 mb-4 rounded" src="{{ asset('images/S2.jpg') }}" alt="Trust & Insights" style="height: 220px; object-fit: cover;">
                        <h3 class="mb-3">Trust & Insights, Communication & Reports</h3>
                        <p class="mb-4">Build trust through comprehensive analytics and insights. Enable better communication between parents and teachers, and facilitate suggestion and incidence reporting for continuous improvement.</p>
                        <div class="bg-light p-4 rounded">
                            <p class="mb-2"><i class="fa fa-chart-line text-primary me-2"></i><strong>Trust & Insights:</strong> Analytics and reporting for better decision making</p>
                            <p class="mb-2"><i class="fa fa-clipboard-list text-primary me-2"></i><strong>Suggestion & Reports:</strong> Incidence reporting and feedback system</p>
                            <p class="mb-0"><i class="fa fa-users text-primary me-2"></i><strong>Communication:</strong> Better connection between parents and teachers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
                    <div class="event-item h-100 p-4">
                        <img class="img-fluid w-100 mb-4 rounded" src="{{ asset('images/S3.webp') }}" alt="Management Features" style="height: 220px; object-fit: cover;">
                        <h3 class="mb-3">Accommodation, Roles & SMS Notifications</h3>
                        <p class="mb-4">Complete boarding management system with accommodation tracking. Manage teachers and staff roles and permissions efficiently. Send SMS notifications for results and important updates to parents.</p>
                        <div class="bg-light p-4 rounded">
                            <p class="mb-2"><i class="fa fa-bed text-primary me-2"></i><strong>Accommodation:</strong> Complete boarding management system</p>
                            <p class="mb-2"><i class="fa fa-user-shield text-primary me-2"></i><strong>Roles & Permissions:</strong> Teachers and staff role management</p>
                            <p class="mb-0"><i class="fa fa-sms text-primary me-2"></i><strong>SMS Notifications:</strong> Result alerts and updates to parents</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Additional Features End -->


    <!-- Donate Start -->
    <div class="container-fluid donate py-5">
        <div class="container">
            <div class="row g-0">
                <div class="col-lg-7 donate-text bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
                    <div class="d-flex flex-column justify-content-center h-100 p-5 wow fadeIn" data-wow-delay="0.3s">
                        <h1 class="display-6 mb-4">Ready to Transform Your School Management?</h1>
                        <p class="fs-5 mb-0">Experience the power of ShuleXpert with our comprehensive school management platform. Streamline operations, enhance communication, and improve educational outcomes.</p>
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
                            <img class="img-fluid mb-4" src="{{ asset('images/H.jpg') }}" alt="Hassani Saidi Hassani" style="width: 200px; height: 200px; object-fit: cover; border-radius: 8px;">
                            <h3>Hassani Saidi Hassani</h3>
                            <span>Software Developer</span>
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
                        <h1 class="display-6 mb-4">What Our Users Say About ShuleXpert</h1>
                        <p class="fs-5 mb-0">Discover how ShuleXpert is transforming school management and improving communication between schools, teachers, and parents across Tanzania.</p>
                    </div>
                </div>
                <div class="col-md-12 col-lg-8 col-xl-9">
                    <div class="owl-carousel testimonial-carousel wow fadeIn" data-wow-delay="0.3s">
                        <div class="testimonial-item">
                            <div class="row g-5 align-items-center">
                                <div class="col-md-6">
                                    <div class="testimonial-img">
                                        <div class="testimonial-icon-bg" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%); width: 100%; height: 300px; border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-user-tie fa-5x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="testimonial-text pb-5 pb-md-0">
                                        <div class="mb-3">
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                        </div>
                                        <p class="fs-5 mb-4">ShuleXpert has revolutionized how we manage our school operations. The biometric attendance system saves us hours of manual work, and parents love receiving instant SMS notifications about their children's performance and attendance. It's truly transformed our school management!</p>
                                        <div class="d-flex align-items-center">
                                            <div class="btn-lg-square bg-light text-secondary flex-shrink-0" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;">
                                                <i class="fa fa-quote-right fa-2x text-white"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h5 class="mb-0 fw-bold">Dr. Sarah Mwanga</h5>
                                                <span class="text-muted">School Principal, Kilimanjaro Secondary School</span>
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
                                        <div class="testimonial-icon-bg" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%); width: 100%; height: 300px; border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-chalkboard-teacher fa-5x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="testimonial-text pb-5 pb-md-0">
                                        <div class="mb-3">
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                        </div>
                                        <p class="fs-5 mb-4">As a teacher, ShuleXpert has made my work so much easier. I can record results, track attendance, and communicate with parents instantly. The mobile app keeps me updated with real-time alerts about my sessions. It's an essential tool for modern teaching!</p>
                                        <div class="d-flex align-items-center">
                                            <div class="btn-lg-square bg-light text-secondary flex-shrink-0" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;">
                                                <i class="fa fa-quote-right fa-2x text-white"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h5 class="mb-0 fw-bold">John Mtei</h5>
                                                <span class="text-muted">Mathematics Teacher, Moshi High School</span>
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
                                        <div class="testimonial-icon-bg" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%); width: 100%; height: 300px; border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-user-friends fa-5x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="testimonial-text pb-5 pb-md-0">
                                        <div class="mb-3">
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                            <i class="fa fa-star text-primary"></i>
                                        </div>
                                        <p class="fs-5 mb-4">I love how ShuleXpert keeps me connected with my child's education. I receive SMS notifications for results, attendance, and school events. The fee payment system is so convenient - I can pay from anywhere using my mobile money. It gives me peace of mind knowing I'm always informed!</p>
                                        <div class="d-flex align-items-center">
                                            <div class="btn-lg-square bg-light text-secondary flex-shrink-0" style="background: linear-gradient(135deg, #ed9999 0%, #940000 100%) !important;">
                                                <i class="fa fa-quote-right fa-2x text-white"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h5 class="mb-0 fw-bold">Amina Hassan</h5>
                                                <span class="text-muted">Parent, Arusha Primary School</span>
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
                    <a class="btn btn-link" href="#!">About ShuleXpert</a>
                    <a class="btn btn-link" href="#!">Contact Us</a>
                    <a class="btn btn-link" href="#!">Our Services</a>
                    <a class="btn btn-link" href="#!">Pricing</a>
                    <a class="btn btn-link" href="#!">Documentation</a>
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
