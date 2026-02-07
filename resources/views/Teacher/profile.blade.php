@include('includes.teacher_nav')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid mt-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary-custom text-white rounded">
            <h4 class="mb-0"><i class="fa fa-user"></i> My Profile</h4>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                @php
                    $profileImage = $teacher->image
                        ? asset('userImages/'.$teacher->image)
                        : (($teacher->gender ?? '') === 'Female' ? asset('images/female.png') : asset('images/male.png'));
                @endphp
                <img src="{{ $profileImage }}" alt="Profile" class="rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                <div class="ml-3">
                    <h5 class="mb-1">{{ trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? '')) }}</h5>
                    <small class="text-muted">Employee No: {{ $teacher->employee_number ?? 'N/A' }}</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-2"><strong>Gender:</strong> {{ $teacher->gender ?? 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Phone:</strong> {{ $teacher->phone_number ?? 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $teacher->email ?? 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Qualification:</strong> {{ $teacher->qualification ?? 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Specialization:</strong> {{ $teacher->specialization ?? 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Experience:</strong> {{ $teacher->experience ?? 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Date of Birth:</strong> {{ $teacher->date_of_birth ? $teacher->date_of_birth->format('Y-m-d') : 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Date Hired:</strong> {{ $teacher->date_hired ? $teacher->date_hired->format('Y-m-d') : 'N/A' }}</div>
                <div class="col-md-12 mb-2"><strong>Address:</strong> {{ $teacher->address ?? 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Status:</strong> {{ $teacher->status ?? 'N/A' }}</div>
                <div class="col-md-6 mb-2"><strong>Username:</strong> {{ $user ? $user->name : ($teacher->employee_number ?? 'N/A') }}</div>
            </div>
        </div>
    </div>

    <div class="card" id="change-password">
        <div class="card-header bg-light">
            <strong>Change Password</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('teacher.change_password') }}">
                @csrf
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                    @error('current_password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    @error('new_password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-primary-custom">
                    <i class="fa fa-save"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</div>
