@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Progress Steps -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col"><div class="step-indicator"><div class="step-number">1</div><div class="step-title">Student Particulars</div></div></div>
                        <div class="col"><div class="step-indicator"><div class="step-number">2</div><div class="step-title">Guardian Info</div></div></div>
                        <div class="col"><div class="step-indicator"><div class="step-number">3</div><div class="step-title">Health Info</div></div></div>
                        <div class="col"><div class="step-indicator"><div class="step-number">4</div><div class="step-title">Emergency Contact</div></div></div>
                        <div class="col"><div class="step-indicator active"><div class="step-number">5</div><div class="step-title">Declaration</div></div></div>
                    </div>
                </div>
            </div>

            <!-- SECTION E: Declaration -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> SECTION E: Parent/Guardian Declaration</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Important:</strong> I hereby declare that the information provided above is true and correct to the best of my knowledge.
                    </div>

                    <form method="POST" action="{{ route('student.registration.store-step5') }}" enctype="multipart/form-data">
                        @csrf

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

                        <!-- Parent/Guardian Declaration -->
                        <div class="mb-4 p-3 border rounded bg-light">
                            <div class="form-check mb-3">
                                <input type="checkbox" name="parent_declaration_checkbox" id="declaration" class="form-check-input"
                                    value="on" required {{ old('parent_declaration_checkbox') ? 'checked' : '' }}>
                                <label class="form-check-label" for="declaration">
                                    <strong>I declare that all information provided above is true and correct</strong>
                                </label>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Declaration Date *</strong></label>
                                    <input type="date" name="declaration_date" class="form-control @error('declaration_date') is-invalid @enderror"
                                        value="{{ old('declaration_date', now()->toDateString()) }}" required>
                                    @error('declaration_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label"><strong>Parent/Guardian Signature (Optional)</strong></label>
                                    <input type="text" name="parent_signature" class="form-control"
                                        placeholder="Signature (digital or typed name)" value="{{ old('parent_signature') }}">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION F: Official Use Only -->
                        <div class="card border-warning mt-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bi bi-shield-check"></i> SECTION F: For Official Use Only</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Registering Officer Name</strong></label>
                                        <input type="text" name="registering_officer_name" class="form-control"
                                            placeholder="Officer name" value="{{ old('registering_officer_name') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Officer Title</strong></label>
                                        <input type="text" name="registering_officer_title" class="form-control"
                                            placeholder="e.g., Principal, Admin" value="{{ old('registering_officer_title') }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label"><strong>Officer Signature (Optional)</strong></label>
                                        <input type="text" name="registering_officer_signature" class="form-control"
                                            placeholder="Officer signature" value="{{ old('registering_officer_signature') }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>School Stamp/Seal (Optional)</strong></label>
                                        <input type="file" name="school_stamp" class="form-control @error('school_stamp') is-invalid @enderror"
                                            accept="image/*">
                                        <small class="text-muted">Upload school stamp/seal image</small>
                                        @error('school_stamp')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Summary -->
                        <div class="card border-info mt-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-card-checklist"></i> Registration Summary</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Student Name:</strong> {{ $registration['first_name'] ?? '' }} {{ $registration['middle_name'] ?? '' }} {{ $registration['last_name'] ?? '' }}</p>
                                <p class="mb-2"><strong>Gender:</strong> {{ $registration['gender'] ?? '' }}</p>
                                <p class="mb-2"><strong>Date of Birth:</strong> {{ $registration['date_of_birth'] ?? '' }}</p>
                                <p class="mb-2"><strong>Religion:</strong> {{ $registration['religion'] ?? 'Not provided' }}</p>
                                <p class="mb-2"><strong>Nationality:</strong> {{ $registration['nationality'] ?? 'Not provided' }}</p>
                                <div class="alert alert-success mt-3">
                                    <i class="bi bi-check-circle"></i> All sections completed. Please review information above and submit.
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle"></i> Complete Registration
                                </button>
                                <a href="{{ route('student.registration.step4') }}" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                                <a href="{{ route('student.registration.cancel') }}" class="btn btn-outline-danger btn-lg">
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

    .btn-success {
        background-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .step-indicator {
        padding: 15px;
        border-radius: 8px;
        background-color: #f0f0f0;
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

@include('includes.footer')
