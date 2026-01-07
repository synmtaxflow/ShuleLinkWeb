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
                        <div class="col"><div class="step-indicator active"><div class="step-number">4</div><div class="step-title">Emergency Contact</div></div></div>
                        <div class="col"><div class="step-indicator"><div class="step-number">5</div><div class="step-title">Declaration</div></div></div>
                    </div>
                </div>
            </div>

            <!-- SECTION D: Emergency Contact -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-telephone"></i> SECTION D: Emergency Contact</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle"></i> Provide contact information of someone who can be reached in case of emergency.
                    </div>

                    <form method="POST" action="{{ route('student.registration.store-step4') }}">
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

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Emergency Contact Name *</strong></label>
                                <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                    value="{{ old('emergency_contact_name') }}" required>
                                @error('emergency_contact_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Relationship to Student *</strong></label>
                                <input type="text" name="emergency_contact_relationship" class="form-control @error('emergency_contact_relationship') is-invalid @enderror"
                                    value="{{ old('emergency_contact_relationship') }}" placeholder="e.g., Uncle, Aunt, Cousin" required>
                                @error('emergency_contact_relationship')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Emergency Contact Phone Number *</strong></label>
                                <input type="text" name="emergency_contact_phone" class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                                    value="{{ old('emergency_contact_phone') }}" required>
                                @error('emergency_contact_phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-arrow-right"></i> Next: Declaration
                                </button>
                                <a href="{{ route('student.registration.step3') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                                <a href="{{ route('student.registration.cancel') }}" class="btn btn-outline-danger">
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
