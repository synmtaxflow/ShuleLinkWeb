<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Apply - ShuleXpert School Management System</title>
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

        .form-section {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(148, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #940000;
            box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
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

        .required-field::after {
            content: " *";
            color: #dc3545;
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
            <div class="splash-powered">Powered by EmCa Techonology</div>
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
            <h1 class="display-3 animated slideInDown">Student Application</h1>
            <nav aria-label="breadcrumb animated slideInDown">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('online_application') }}">Online Application</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Apply</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Application Form Start -->
    <div class="container-xxl py-5">
        <div class="container">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- School & Class Info -->
            <div class="form-section">
                <h4 class="mb-4" style="color: #940000;">
                    <i class="fa fa-school me-2"></i>Application Details
                </h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">School:</label>
                        <div class="form-control-plaintext fw-bold">{{ $school->school_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class:</label>
                        <div class="form-control-plaintext fw-bold">{{ $class->class_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Available Spaces:</label>
                        <div class="form-control-plaintext text-success fw-bold">{{ $availableSpaces }} space(s)</div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <form action="{{ route('online_application.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="schoolID" value="{{ $school->schoolID }}">
                <input type="hidden" name="classID" value="{{ $class->classID }}">

                <!-- Personal Information -->
                <div class="form-section">
                    <h4 class="mb-4" style="color: #940000;">
                        <i class="fa fa-user me-2"></i>Personal Information
                    </h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="first_name" class="form-label required-field">First Name</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                id="middle_name" name="middle_name" value="{{ old('middle_name') }}">
                            @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="last_name" class="form-label required-field">Last Name</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label required-field">Gender</label>
                            <select class="form-select @error('gender') is-invalid @enderror" 
                                id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label required-field">Date of Birth</label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                id="photo" name="photo" accept="image/jpeg,image/jpg,image/png">
                            <small class="text-muted">Max size: 2MB (JPG, JPEG, PNG only)</small>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                id="address" name="address" rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Health Information -->
                <div class="form-section">
                    <h4 class="mb-4" style="color: #940000;">
                        <i class="fa fa-heartbeat me-2"></i>Health Information
                    </h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_disabled" 
                                    name="is_disabled" value="1" {{ old('is_disabled') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_disabled">
                                    Has Disability
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_epilepsy" 
                                    name="has_epilepsy" value="1" {{ old('has_epilepsy') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_epilepsy">
                                    Has Epilepsy
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_allergies" 
                                    name="has_allergies" value="1" {{ old('has_allergies') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_allergies">
                                    Has Allergies
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="allergies_details" class="form-label">Allergies Details</label>
                            <textarea class="form-control @error('allergies_details') is-invalid @enderror" 
                                id="allergies_details" name="allergies_details" rows="3" 
                                placeholder="Please provide details about any allergies">{{ old('allergies_details') }}</textarea>
                            @error('allergies_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center mb-5">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-paper-plane me-2"></i>Submit Application
                    </button>
                    <a href="{{ route('online_application') }}" class="btn btn-secondary btn-lg ms-3">
                        <i class="fa fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <!-- Application Form End -->

    <!-- Footer will be added here if needed -->

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

