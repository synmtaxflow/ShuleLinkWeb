@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
    }

    body {
        background-color: #f8f9fa;
    }

    /* Statistics Cards - Smaller and Cleaner */
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
        border-top: 3px solid #e9ecef;
        height: 100%;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-top-color: var(--primary-color);
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #495057;
        font-size: 22px;
        margin-bottom: 12px;
    }

    .stat-card:hover .stat-icon {
        background: var(--primary-color);
        color: white;
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        color: #212529;
        margin: 8px 0;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
        margin: 0;
    }

    /* School Details Card - Clean Design */
    .school-details-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 25px;
    }

    .school-header 
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .school-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    .school-logo-preview {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid #e9ecef;
    }

    .school-logo-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .school-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .info-item i {
        color: #6c757d;
        margin-right: 10px;
        margin-top: 3px;
        font-size: 18px;
        width: 20px;
    }

    .info-item-content {
        flex: 1;
    }

    .info-item-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }

    .info-item-value {
        font-size: 0.95rem;
        color: #212529;
        font-weight: 500;
    }

    /* Chart Cards */
    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        height: 100%;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .chart-title i {
        margin-right: 8px;
        color: var(--primary-color);
        font-size: 1.2rem;
    }

    /* Section Headers */
    .section-header {
        font-size: 1.4rem;
        font-weight: 600;
        color: #212529;
        margin: 25px 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    /* Edit Button */
    .btn-edit {
        background: white;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .btn-edit:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    /* Modal Styles */
    .modal-header {
        border-bottom: 2px solid #e9ecef;
        padding: 20px;
    }

    .modal-title {
        font-weight: 600;
        color: #212529;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 6px;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15);
    }

    .btn-save {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        padding: 10px 24px;
        border-radius: 6px;
        font-weight: 500;
    }

    .btn-save:hover {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
        color: white;
    }

    /* Additional Info Cards */
    .info-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border-left: 3px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .info-card:hover {
        border-left-color: var(--primary-color);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    .info-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.5rem;
        }
        
        .school-title {
            font-size: 1.25rem;
        }
        
        .school-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid" style="padding: 20px;">
    
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="section-header">
                <i class="bi bi-building"></i> School Information
            </h2>
        </div>
    </div>

    <!-- School Details Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="school-details-card">
                <div class="school-header">
                    <div class="d-flex align-items-center">
                        <div class="school-logo-preview me-3">
                            @if($school->school_logo)
                                <img src="{{ asset($school->school_logo) }}" alt="School Logo">
                            @else
                                <i class="bi bi-building" style="font-size: 40px; color: #6c757d;"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="school-title">{{ $school->school_name }}</h3>
                            <small class="text-muted">ID: {{ $school->registration_number ?? 'N/A' }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editSchoolModal">
                        <i class="bi bi-pencil-square me-1"></i> Edit Details
                    </button>
                </div>

                <div class="school-info-grid">
                    <div class="info-item">
                        <i class="bi bi-bookmark"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">School Type</div>
                            <div class="info-item-value">{{ $school->school_type }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-shield-check"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Ownership</div>
                            <div class="info-item-value">{{ $school->ownership }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-geo-alt"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Region</div>
                            <div class="info-item-value">{{ $school->region }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">District</div>
                            <div class="info-item-value">{{ $school->district }}</div>
                        </div>
                    </div>
                    @if($school->ward)
                    <div class="info-item">
                        <i class="bi bi-pin-map"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Ward</div>
                            <div class="info-item-value">{{ $school->ward }}</div>
                        </div>
                    </div>
                    @endif
                    @if($school->email)
                    <div class="info-item">
                        <i class="bi bi-envelope"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Email</div>
                            <div class="info-item-value">{{ $school->email }}</div>
                        </div>
                    </div>
                    @endif
                    @if($school->phone)
                    <div class="info-item">
                        <i class="bi bi-telephone"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Phone</div>
                            <div class="info-item-value">{{ $school->phone }}</div>
                        </div>
                    </div>
                    @endif
                    @if($school->established_year)
                    <div class="info-item">
                        <i class="bi bi-calendar-event"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Established Year</div>
                            <div class="info-item-value">{{ $school->established_year }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="info-item">
                        <i class="bi bi-check-circle"></i>
                        <div class="info-item-content">
                            <div class="info-item-label">Status</div>
                            <div class="info-item-value">
                                <span class="badge bg-{{ $school->status == 'Active' ? 'success' : 'secondary' }}">
                                    {{ $school->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-number">{{ number_format($totalStudents) }}</div>
                <div class="stat-label">Students</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="stat-number">{{ number_format($totalTeachers) }}</div>
                <div class="stat-label">Teachers</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-person-heart"></i>
                </div>
                <div class="stat-number">{{ number_format($totalParents) }}</div>
                <div class="stat-label">Parents</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-book"></i>
                </div>
                <div class="stat-number">{{ number_format($totalSubjects) }}</div>
                <div class="stat-label">Subjects</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-diagram-3"></i>
                </div>
                <div class="stat-number">{{ number_format($totalClasses) }}</div>
                <div class="stat-label">Classes</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-layers"></i>
                </div>
                <div class="stat-number">{{ number_format($totalSubclasses) }}</div>
                <div class="stat-label">Subclasses</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-number">{{ number_format($activeStudents) }}</div>
                <div class="stat-label">Active Students</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="stat-number">{{ number_format($activeTeachers) }}</div>
                <div class="stat-label">Active Teachers</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-bar-chart"></i> Gender Statistics
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Students Gender Chart -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="chart-card">
                <h4 class="chart-title">
                    <i class="bi bi-graph-up"></i> Students Gender Distribution
                </h4>
                <canvas id="studentsGenderChart" height="250"></canvas>
                <div class="row mt-3 text-center">
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label">Male</div>
                            <div class="info-value">{{ number_format($maleStudents) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label">Female</div>
                            <div class="info-value">{{ number_format($femaleStudents) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teachers Gender Chart -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="chart-card">
                <h4 class="chart-title">
                    <i class="bi bi-graph-up"></i> Teachers Gender Distribution
                </h4>
                <canvas id="teachersGenderChart" height="250"></canvas>
                <div class="row mt-3 text-center">
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label">Male</div>
                            <div class="info-value">{{ number_format($maleTeachers) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label">Female</div>
                            <div class="info-value">{{ number_format($femaleTeachers) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-info-circle"></i> Additional Statistics
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="info-card">
                <div class="info-label">
                    <i class="bi bi-x-circle"></i> Inactive Students
                </div>
                <div class="info-value">{{ number_format($inactiveStudents) }}</div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="info-card">
                <div class="info-label">
                    <i class="bi bi-person-x"></i> Inactive Teachers
                </div>
                <div class="info-value">{{ number_format($inactiveTeachers) }}</div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="info-card">
                <div class="info-label">
                    <i class="bi bi-calendar-plus"></i> Recent Admissions (30 Days)
                </div>
                <div class="info-value">{{ number_format($recentAdmissions) }}</div>
            </div>
        </div>
    </div>

    <!-- Students by Class Distribution -->
    @if($studentsByClass->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="section-header">
                <i class="bi bi-bar-chart-line"></i> Students Distribution by Class
            </h3>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-card">
                <h4 class="chart-title">
                    <i class="bi bi-bar-chart"></i> Number of Students per Class
                </h4>
                <canvas id="studentsByClassChart" height="80"></canvas>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Edit School Modal -->
<div class="modal fade" id="editSchoolModal" tabindex="-1" aria-labelledby="editSchoolModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSchoolModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit School Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSchoolForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">School Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="school_name" value="{{ $school->school_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Registration Number</label>
                            <input type="text" class="form-control" name="registration_number" value="{{ $school->registration_number }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">School Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="school_type" required>
                                <option value="Primary" {{ $school->school_type == 'Primary' ? 'selected' : '' }}>Primary</option>
                                <option value="Secondary" {{ $school->school_type == 'Secondary' ? 'selected' : '' }}>Secondary</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ownership <span class="text-danger">*</span></label>
                            <select class="form-select" name="ownership" required>
                                <option value="Public" {{ $school->ownership == 'Public' ? 'selected' : '' }}>Public</option>
                                <option value="Private" {{ $school->ownership == 'Private' ? 'selected' : '' }}>Private</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Region <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="region" value="{{ $school->region }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">District <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="district" value="{{ $school->district }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ward</label>
                            <input type="text" class="form-control" name="ward" value="{{ $school->ward }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Village</label>
                            <input type="text" class="form-control" name="village" value="{{ $school->village }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2">{{ $school->address }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ $school->email }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="{{ $school->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Established Year</label>
                            <input type="number" class="form-control" name="established_year" value="{{ $school->established_year }}" min="1900" max="{{ date('Y') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="Active" {{ $school->status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $school->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">School Logo</label>
                            <input type="file" class="form-control" name="school_logo" accept="image/*">
                            <small class="text-muted">JPG/PNG only, max 2MB</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-save">
                        <i class="bi bi-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Bootstrap 5 JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Students Gender Chart - Line Chart
    const studentsCtx = document.getElementById('studentsGenderChart').getContext('2d');
    const studentsGenderChart = new Chart(studentsCtx, {
        type: 'line',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                label: 'Students',
                data: [{{ $maleStudents }}, {{ $femaleStudents }}],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = {{ $maleStudents }} + {{ $femaleStudents }};
                            const percentage = total > 0 ? Math.round(context.parsed.y / total * 100) : 0;
                            return context.label + ': ' + context.parsed.y + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Teachers Gender Chart - Line Chart
    const teachersCtx = document.getElementById('teachersGenderChart').getContext('2d');
    const teachersGenderChart = new Chart(teachersCtx, {
        type: 'line',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                label: 'Teachers',
                data: [{{ $maleTeachers }}, {{ $femaleTeachers }}],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = {{ $maleTeachers }} + {{ $femaleTeachers }};
                            const percentage = total > 0 ? Math.round(context.parsed.y / total * 100) : 0;
                            return context.label + ': ' + context.parsed.y + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    @if($studentsByClass->count() > 0)
    // Students by Class Chart
    const classCtx = document.getElementById('studentsByClassChart').getContext('2d');
    const studentsByClassChart = new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: [
                @foreach($studentsByClass as $class)
                    '{{ $class->class_name }}',
                @endforeach
            ],
            datasets: [{
                label: 'Students',
                data: [
                    @foreach($studentsByClass as $class)
                        {{ $class->student_count }},
                    @endforeach
                ],
                backgroundColor: '#8b5cf6',
                borderColor: '#7c3aed',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    @endif

    // Ensure modal works properly
    document.addEventListener('DOMContentLoaded', function() {
        // Edit button click handler
        const editButton = document.querySelector('[data-bs-target="#editSchoolModal"]');
        if (editButton) {
            editButton.addEventListener('click', function(e) {
                e.preventDefault();
                const modalElement = document.getElementById('editSchoolModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        }

        // Edit School Form Submission
        const editForm = document.getElementById('editSchoolForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';

                fetch('{{ route("update_school") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw err;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'School information updated successfully',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'An error occurred');
                    }
                })
                .catch(error => {
                    let errorMessage = 'An error occurred while updating information';
                    
                    if (error.errors) {
                        // Handle validation errors
                        const errors = Object.values(error.errors).flat();
                        errorMessage = errors.join('<br>');
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        }
    });
</script>

@include('includes.footer')
