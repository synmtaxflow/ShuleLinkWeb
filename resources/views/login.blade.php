<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ShuleLink - Settings</title>
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
        :root { --brand: #940000; }
        .brand-text { color: var(--brand) !important; }
        .brand-bg { background-color: var(--brand) !important; }
        .card-brand { border-top: 4px solid var(--brand); }
        .form-label { font-weight: 600; }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand) !important;
            box-shadow: 0 0 0 .2rem rgba(148,0,0,.15) !important;
        }
        .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--brand); }
        .with-icon { padding-left: 42px; }
        .help { font-size: .85rem; color: #6c757d; }
        .is-valid + .valid-feedback { display: block; }
        .is-invalid + .invalid-feedback { display: block; }
        .btn-brand { background: var(--brand); color: #fff; border-color: var(--brand); }
        .btn-brand:hover { background: #7a0000; border-color: #7a0000; }
        .required::after { content: " *"; color: var(--brand); font-weight: 700; }
        .fade-in { animation: fadeIn .5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>

<body>
@include('includes.web_nav')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card shadow-sm card-brand fade-in">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 brand-text"><i class="bi bi-building me-2"></i>School Member Login</h5>
                        <span class="text-muted small">ShuleLink</span>
                    </div>
                    <div class="card-body">
              @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
            @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

            @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


<form id="schoolForm" method="POST" action="{{ route('auth') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        {{-- Username --}}
        <div class="col-md-12">
            <label class="form-label required" for="school_name">username</label>
            <div class="position-relative">
                <i class="bi bi-mortarboard input-icon"></i>
                <input type="text" id="school_name" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="username" value="{{ old('username') }}" required>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Pasword --}}
        <div class="col-md-12">
            <label class="form-label" for="registration_number">Password</label>
            <div class="position-relative">
                <i class="bi bi-hash input-icon"></i>
                <input type="password" id="registration_number" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>


            <button type="submit" class="btn btn-brand">
                <i class="bi bi-save me-1"></i>Login
            </button>
        </div>
    </div>
</form>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

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
        (function() {
            const form = document.getElementById('schoolForm');
            if (!form) return;

            const validators = {
                school_name: v => v.trim().length > 2,
                school_type: v => !!v,
                ownership: v => !!v,
                region: v => v.trim().length > 1,
                district: v => v.trim().length > 1,
                email: v => !v || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
                phone: v => !v || /^[+0-9\s-]{7,20}$/.test(v),
                established_year: v => !v || (+v >= 1900 && +v <= new Date().getFullYear()),
                agree: v => !!v,
            };

            const setValidity = (el, valid) => {
                el.classList.remove(valid ? 'is-invalid' : 'is-valid');
                el.classList.add(valid ? 'is-valid' : 'is-invalid');
            };

            form.addEventListener('input', e => {
                const t = e.target;
                if (!t.name || !(t.name in validators)) return;
                const valid = validators[t.name](t.type === 'checkbox' ? (t.checked ? '1' : '') : t.value || '');
                setValidity(t, valid);
            });

            form.addEventListener('submit', e => {
                let ok = true;
                Array.from(form.elements).forEach(el => {
                    if (!el.name || !(el.name in validators)) return;
                    const valid = validators[el.name](el.type === 'checkbox' ? (el.checked ? '1' : '') : el.value || '');
                    setValidity(el, valid);
                    if (!valid) ok = false;
                });
                if (!ok) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        })();
    </script>
</html>
