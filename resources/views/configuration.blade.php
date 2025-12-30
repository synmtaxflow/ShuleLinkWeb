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
                        <h5 class="mb-0 brand-text"><i class="bi bi-building me-2"></i>School Configuration</h5>
                        <span class="text-muted small">ShuleLink</span>
                    </div>
                    <div class="card-body">
              @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>There were some errors in your form:</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form id="schoolForm" method="POST" action="{{ route('save_school') }}" enctype="multipart/form-data" novalidate>
    @csrf
    <div class="row g-4">
        {{-- SCHOOL NAME --}}
        <div class="col-md-8">
            <label class="form-label required" for="school_name">School Name</label>
            <div class="position-relative">
                <i class="bi bi-mortarboard input-icon"></i>
                <input type="text" id="school_name" name="school_name" class="form-control with-icon @error('school_name') is-invalid @enderror" value="{{ old('school_name') }}" placeholder="e.g., Kilimanjaro Primary School" required>
                @error('school_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- REGISTRATION NUMBER --}}
        <div class="col-md-4">
            <label class="form-label" for="registration_number">Registration Number</label>
            <div class="position-relative">
                <i class="bi bi-hash input-icon"></i>
                <input type="text" id="registration_number" name="registration_number" class="form-control with-icon @error('registration_number') is-invalid @enderror" value="{{ old('registration_number') }}" placeholder="e.g., REG-12345">
                @error('registration_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- SCHOOL TYPE --}}
        <div class="col-md-4">
            <label class="form-label required" for="school_type">School Type</label>
            <select id="school_type" name="school_type" class="form-select @error('school_type') is-invalid @enderror" required>
                <option value="" disabled {{ old('school_type') ? '' : 'selected' }}>Select type</option>
                <option value="Primary" {{ old('school_type') == 'Primary' ? 'selected' : '' }}>Primary</option>
                <option value="Secondary" {{ old('school_type') == 'Secondary' ? 'selected' : '' }}>Secondary</option>
            </select>
            @error('school_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- OWNERSHIP --}}
        <div class="col-md-4">
            <label class="form-label required" for="ownership">Ownership</label>
            <select id="ownership" name="ownership" class="form-select @error('ownership') is-invalid @enderror" required>
                <option value="" disabled {{ old('ownership') ? '' : 'selected' }}>Select ownership</option>
                <option value="Public" {{ old('ownership') == 'Public' ? 'selected' : '' }}>Public</option>
                <option value="Private" {{ old('ownership') == 'Private' ? 'selected' : '' }}>Private</option>
            </select>
            @error('ownership')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- REGION --}}
        <div class="col-md-4">
            <label class="form-label required" for="region">Region</label>
            <input type="text" id="region" name="region" class="form-control @error('region') is-invalid @enderror" value="{{ old('region') }}" placeholder="e.g., Kilimanjaro" required>
            @error('region')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- DISTRICT --}}
        <div class="col-md-4">
            <label class="form-label required" for="district">District</label>
            <input type="text" id="district" name="district" class="form-control @error('district') is-invalid @enderror" value="{{ old('district') }}" placeholder="e.g., Moshi" required>
            @error('district')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- WARD --}}
        <div class="col-md-4">
            <label class="form-label" for="ward">Ward</label>
            <input type="text" id="ward" name="ward" class="form-control" value="{{ old('ward') }}" placeholder="e.g., Rau">
        </div>

        {{-- VILLAGE --}}
        <div class="col-md-4">
            <label class="form-label" for="village">Village</label>
            <input type="text" id="village" name="village" class="form-control" value="{{ old('village') }}" placeholder="e.g., Rau Village">
        </div>

        {{-- ADDRESS --}}
        <div class="col-md-8">
            <label class="form-label" for="address">Address</label>
            <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}" placeholder="P.O. Box 123, Moshi">
        </div>

        {{-- EMAIL --}}
        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="info@school.tz">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- PHONE --}}
        <div class="col-md-6">
            <label class="form-label" for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="e.g., +255 712 000 000">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ESTABLISHED YEAR --}}
        <div class="col-md-4">
            <label class="form-label" for="established_year">Established Year</label>
            <input type="number" id="established_year" name="established_year" class="form-control @error('established_year') is-invalid @enderror" min="1900" max="{{ date('Y') }}" value="{{ old('established_year') }}" placeholder="e.g., 1998">
            @error('established_year')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- SCHOOL LOGO --}}
        <div class="col-md-5">
            <label class="form-label" for="school_logo">School Logo</label>
            <input type="file" id="school_logo" name="school_logo" class="form-control @error('school_logo') is-invalid @enderror" accept="image/*">
            <div class="help">Optional. JPG/PNG only, max 2MB.</div>
            @error('school_logo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- STATUS --}}
        <div class="col-md-3">
            <label class="form-label required" for="status">Status</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between pt-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="agree" required>
            <label class="form-check-label" for="agree">I confirm the details are correct</label>
            <div class="invalid-feedback">Please confirm before submitting.</div>
        </div>
        <div>
            <button type="reset" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </button>
            <button type="submit" class="btn btn-brand">
                <i class="bi bi-save me-1"></i>Save School
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
