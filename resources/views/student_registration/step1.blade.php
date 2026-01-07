@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Progress Steps -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="step-indicator active">
                                <div class="step-number">1</div>
                                <div class="step-title">Student Particulars</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator">
                                <div class="step-number">2</div>
                                <div class="step-title">Guardian Info</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator">
                                <div class="step-number">3</div>
                                <div class="step-title">Health Info</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator">
                                <div class="step-number">4</div>
                                <div class="step-title">Emergency Contact</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator">
                                <div class="step-number">5</div>
                                <div class="step-title">Declaration</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION A: Student Particulars -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> SECTION A: Student Particulars</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.registration.store-step1') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label"><strong>First Name *</strong></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                    value="{{ old('first_name') }}" required>
                                @error('first_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Middle Name</strong></label>
                                <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Last Name *</strong></label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name') }}" required>
                                @error('last_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label"><strong>Gender *</strong></label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Date of Birth *</strong></label>
                                <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                                    value="{{ old('date_of_birth') }}" required>
                                @error('date_of_birth')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Age (Auto-calculated)</strong></label>
                                <input type="number" class="form-control" id="age" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label"><strong>Birth Certificate Number</strong></label>
                                <input type="text" name="birth_certificate_number" class="form-control"
                                    value="{{ old('birth_certificate_number') }}" placeholder="e.g., BCN123456">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Religion</strong></label>
                                <input type="text" name="religion" class="form-control" value="{{ old('religion') }}"
                                    placeholder="e.g., Christian, Muslim">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Nationality</strong></label>
                                <input type="text" name="nationality" class="form-control" value="{{ old('nationality') }}"
                                    placeholder="e.g., Kenyan">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Student Photo (Optional)</strong></label>
                                <input type="file" name="student_photo" class="form-control @error('student_photo') is-invalid @enderror"
                                    accept="image/*">
                                <small class="text-muted">Max 2MB. Formats: JPEG, PNG</small>
                                @error('student_photo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-arrow-right"></i> Next: Guardian Information
                                </button>
                                <a href="{{ route('student.registration.cancel') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }

    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }

    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
    }

    .step-indicator {
        padding: 15px;
        border-radius: 8px;
        background-color: #f0f0f0;
        transition: all 0.3s ease;
    }

    .step-indicator.active {
        background-color: #940000;
        color: white;
    }

    .step-number {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .step-title {
        font-size: 12px;
        margin-top: 5px;
    }
</style>

<script>
    // Calculate age from date of birth
    document.querySelector('input[name="date_of_birth"]').addEventListener('change', function() {
        const dob = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        document.getElementById('age').value = age;
    });
</script>

@include('includes.footer')
