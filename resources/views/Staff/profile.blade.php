@include('includes.staff_nav')
<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: white;
    }
    .page-card {
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .page-header {
        background: #fff7f7;
        border-bottom: 1px solid #f0dada;
        color: #940000;
        font-weight: 600;
        padding: 12px 16px;
    }
    .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(148, 0, 0, 0.35);
        background: rgba(148, 0, 0, 0.06);
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }
    .detail-item {
        border: 1px solid #f0f0f0;
        padding: 10px 12px;
        background: #fafafa;
    }
    .detail-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .detail-value {
        font-weight: 600;
        color: #2f2f2f;
    }
</style>

<div class="container-fluid mt-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card page-card">
        <div class="page-header">My Profile</div>
        <div class="card-body">
            @php
                $profileImg = isset($staff) && $staff && $staff->image
                    ? asset('userImages/' . $staff->image)
                    : (isset($staff) && $staff && $staff->gender == 'Female'
                        ? asset('images/female.png')
                        : asset('images/male.png'));
            @endphp

            <div class="d-flex align-items-center mb-4">
                <img src="{{ $profileImg }}" alt="Profile" class="profile-avatar mr-3">
                <div>
                    <h5 class="mb-1">{{ $staff->first_name ?? 'Staff' }} {{ $staff->last_name ?? '' }}</h5>
                    <div class="text-muted">
                        {{ $staffProfession->name ?? 'Staff' }}
                    </div>
                    <div class="text-muted" style="font-size: 0.9rem;">
                        {{ $user->email ?? $staff->email ?? '' }}
                    </div>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value">
                        {{ trim(($staff->first_name ?? '') . ' ' . ($staff->middle_name ?? '') . ' ' . ($staff->last_name ?? '')) ?: 'N/A' }}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Employee Number</div>
                    <div class="detail-value">{{ $staff->employee_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Profession</div>
                    <div class="detail-value">{{ $staffProfession->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Position</div>
                    <div class="detail-value">{{ $staff->position ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Qualification</div>
                    <div class="detail-value">{{ $staff->qualification ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Specialization</div>
                    <div class="detail-value">{{ $staff->specialization ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Experience</div>
                    <div class="detail-value">{{ $staff->experience ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Gender</div>
                    <div class="detail-value">{{ $staff->gender ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date of Birth</div>
                    <div class="detail-value">{{ $staff->date_of_birth ? $staff->date_of_birth->format('Y-m-d') : 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date Hired</div>
                    <div class="detail-value">{{ $staff->date_hired ? $staff->date_hired->format('Y-m-d') : 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">National ID</div>
                    <div class="detail-value">{{ $staff->national_id ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">{{ $staff->email ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value">{{ $staff->phone_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Address</div>
                    <div class="detail-value">{{ $staff->address ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Bank Account</div>
                    <div class="detail-value">{{ $staff->bank_account_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Fingerprint ID</div>
                    <div class="detail-value">{{ $staff->fingerprint_id ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">{{ $staff->status ?? 'N/A' }}</div>
                </div>
            </div>

            <form action="{{ route('staff.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary-custom mb-3">Profile Picture</h6>
                        <div class="form-group">
                            <label for="profile_image">Upload New Picture</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">JPG/PNG, max 2MB.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-primary-custom mb-3">Change Password</h6>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('staffDashboard') }}" class="btn btn-outline-secondary">
                        Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')
