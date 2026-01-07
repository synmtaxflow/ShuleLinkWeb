@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 80px;"></i>
                    </div>

                    <h2 class="mb-3 text-success"><strong>Registration Successful!</strong></h2>

                    <div class="alert alert-success mb-4">
                        <p class="mb-0"><strong>{{ $student->first_name }} {{ $student->last_name }}</strong> has been successfully registered.</p>
                    </div>

                    <div class="card border-info mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Student Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6 text-start">
                                    <p><strong>Full Name:</strong> {{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</p>
                                    <p><strong>Gender:</strong> {{ $student->gender }}</p>
                                    <p><strong>Date of Birth:</strong> {{ \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') }}</p>
                                    <p><strong>Religion:</strong> {{ $student->religion ?? 'Not provided' }}</p>
                                </div>
                                <div class="col-md-6 text-start">
                                    <p><strong>Admission Number:</strong> <span class="badge bg-primary" style="font-size: 14px;">{{ $student->admission_number }}</span></p>
                                    <p><strong>Admission Date:</strong> {{ \Carbon\Carbon::parse($student->admission_date)->format('d M Y') }}</p>
                                    <p><strong>Nationality:</strong> {{ $student->nationality ?? 'Not provided' }}</p>
                                    <p><strong>Status:</strong> <span class="badge bg-success">{{ $student->status }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-warning mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">Health Information</h6>
                        </div>
                        <div class="card-body text-start">
                            <p><strong>General Health Condition:</strong> {{ $student->general_health_condition ?? 'Not provided' }}</p>
                            <p><strong>Has Disability:</strong> {{ $student->has_disability ? 'Yes - ' . $student->disability_details : 'No' }}</p>
                            <p><strong>Chronic Illness:</strong> {{ $student->has_chronic_illness ? 'Yes - ' . $student->chronic_illness_details : 'No' }}</p>
                            <p><strong>Immunization Details:</strong> {{ $student->immunization_details ?? 'Not provided' }}</p>
                        </div>
                    </div>

                    <div class="card border-secondary mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">Emergency Contact</h6>
                        </div>
                        <div class="card-body text-start">
                            <p><strong>Name:</strong> {{ $student->emergency_contact_name }}</p>
                            <p><strong>Relationship:</strong> {{ $student->emergency_contact_relationship }}</p>
                            <p><strong>Phone:</strong> {{ $student->emergency_contact_phone }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('AdminDashboard') }}" class="btn btn-primary-custom btn-lg">
                            <i class="bi bi-house"></i> Return to Dashboard
                        </a>
                        <a href="{{ route('student.registration.step1') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-plus-circle"></i> Register Another Student
                        </a>
                    </div>

                    <div class="alert alert-info mt-4">
                        <small>
                            <i class="bi bi-info-circle"></i> Student's admission number is <strong>{{ $student->admission_number }}</strong>.
                            Please save this for your records.
                        </small>
                    </div>
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
</style>

@include('includes.footer')
