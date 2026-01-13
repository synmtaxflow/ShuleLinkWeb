<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Application Success - ShuleLink School Management System</title>
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
        .success-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(148, 0, 0, 0.1);
            margin: 40px 0;
            border-top: 5px solid #940000;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-icon i {
            font-size: 50px;
            color: #ffffff;
        }

        .credentials-box {
            background: #f8f9fa;
            border: 2px dashed #940000;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }

        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #ffffff;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .credential-label {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .credential-value {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #940000;
            letter-spacing: 1px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ed9999 0%, #940000 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #940000 0%, #ed9999 100%);
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #940000;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .info-box i {
            color: #940000;
            margin-right: 10px;
        }

        .alert-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    @include('includes.web_nav')

    <!-- Page Header Start -->
    <div class="container-fluid page-header py-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center py-4">
            <h1 class="display-3 animated slideInDown">Application Submitted</h1>
            <nav aria-label="breadcrumb animated slideInDown">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('online_application') }}">Online Application</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Success</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Success Content Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="success-card text-center">
                        <!-- Success Icon -->
                        <div class="success-icon">
                            <i class="fa fa-check"></i>
                        </div>

                        <!-- Success Message -->
                        <h2 class="mb-3" style="color: #940000;">Application Submitted Successfully!</h2>
                        <p class="lead mb-4">Thank you, <strong>{{ $studentName }}</strong>, for applying to <strong>{{ $schoolName }}</strong> - <strong>{{ $className }}</strong>.</p>

                        <!-- Alert Box -->
                        <div class="alert-box">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Important:</strong> Please save these credentials. You will need them to check your application status.
                        </div>

                        <!-- Credentials Box -->
                        <div class="credentials-box">
                            <h4 class="mb-4" style="color: #940000;">
                                <i class="fa fa-key me-2"></i>Your Login Credentials
                            </h4>
                            
                            <div class="credential-item">
                                <span class="credential-label">
                                    <i class="fa fa-user me-2"></i>Username (Application Number):
                                </span>
                                <span class="credential-value" id="username">{{ $applicationNumber }}</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $applicationNumber }}', 'username')">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>

                            <div class="credential-item">
                                <span class="credential-label">
                                    <i class="fa fa-lock me-2"></i>Password:
                                </span>
                                <span class="credential-value" id="password">{{ $password }}</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $password }}', 'password')">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Information Box -->
                        <div class="info-box text-start">
                            <h5 class="mb-3">
                                <i class="fa fa-info-circle"></i>What's Next?
                            </h5>
                            <ul class="mb-0" style="padding-left: 20px;">
                                <li>Your application has been received and is pending review.</li>
                                <li>Use the credentials above to log in and check your application status.</li>
                                <li>You will receive an SMS notification once your application is reviewed.</li>
                                <li>If accepted, you will receive further instructions via SMS.</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-5">
                            <a href="{{ route('online_application') }}" class="btn btn-primary btn-lg">
                                <i class="fa fa-home me-2"></i>Back to Online Application
                            </a>
                            <button onclick="printPage()" class="btn btn-secondary btn-lg ms-3">
                                <i class="fa fa-print me-2"></i>Print Credentials
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Success Content End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

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

    <script>
        function copyToClipboard(text, elementId) {
            navigator.clipboard.writeText(text).then(function() {
                // Show feedback
                const btn = event.target.closest('button');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-check"></i>';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
                
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            }, function(err) {
                alert('Failed to copy. Please copy manually: ' + text);
            });
        }

        function printPage() {
            window.print();
        }

        // Auto-select text when clicking on credentials (for easy copying)
        document.getElementById('username').addEventListener('click', function() {
            if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(this);
                range.select();
            } else if (window.getSelection) {
                var range = document.createRange();
                range.selectNode(this);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
            }
        });

        document.getElementById('password').addEventListener('click', function() {
            if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(this);
                range.select();
            } else if (window.getSelection) {
                var range = document.createRange();
                range.selectNode(this);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
            }
        });
    </script>
</body>

</html>











