@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .table {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .btn, .form-control, div { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .section-title { font-weight: 600; margin-bottom: 12px; }
    .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
    }
</style>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Watchman Management</strong>
        </div>
        <div class="card-body">
            <div class="section-title">Register Watchman</div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
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

            <div class="form-loading" id="watchmanLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Saving...</span>
                <div class="form-progress"></div>
            </div>

            <form method="POST" action="{{ route('save_watchman') }}" class="mb-4" id="watchmanForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', '255') }}" placeholder="255612345678" required>
                        <small class="text-muted">Username will be this phone number.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email (optional)</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="example@email.com">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fa fa-save"></i> Save Watchman
                    </button>
                </div>
            </form>

            <div class="section-title">Registered Watchmen</div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($watchmen as $index => $watchman)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $watchman->first_name }} {{ $watchman->last_name }}</td>
                                <td>{{ $watchman->phone_number }}</td>
                                <td>{{ $watchman->email ?? '-' }}</td>
                                <td>{{ $watchman->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No watchmen registered.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const watchmanForm = document.getElementById('watchmanForm');
    const watchmanLoading = document.getElementById('watchmanLoading');
    if (watchmanForm) {
        watchmanForm.addEventListener('submit', () => {
            if (watchmanLoading) {
                watchmanLoading.style.display = 'flex';
            }
        });
    }
</script>
