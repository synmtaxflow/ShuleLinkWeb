@include('includes.teacher_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .approval-chain {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px 0;
        flex-wrap: wrap;
    }
    .approval-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0 10px;
        position: relative;
    }
    .approval-step::after {
        content: '→';
        position: absolute;
        right: -25px;
        font-size: 24px;
        color: #940000;
    }
    .approval-step:last-child::after {
        display: none;
    }
    .approval-badge {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        margin-bottom: 10px;
    }
    .approval-badge.pending {
        background-color: #ffc107;
    }
    .approval-badge.approved {
        background-color: #28a745;
    }
    .approval-badge.rejected {
        background-color: #dc3545;
    }
    .approval-badge.completed {
        background-color: #6c757d;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Title -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body bg-primary-custom text-white rounded">
                    <h3 class="mb-0">
                        <i class="bi bi-check-circle"></i> Approve {{ $examination->exam_name }} Result After Reviewing
                    </h3>
                </div>
            </div>

            <!-- Approval Chain Visualization -->
            <div class="card mb-4">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Approval Chain</h5>
                </div>
                <div class="card-body">
                    <div class="approval-chain">
                        @foreach($allApprovals as $index => $approval)
                            <div class="approval-step">
                                <div class="approval-badge {{ $approval->status }}">
                                    {{ $approval->approval_order }}
                                </div>
                                <small class="text-center">
                                    <strong>
                                        @if($approval->special_role_type === 'class_teacher')
                                            Class Teacher
                                        @elseif($approval->special_role_type === 'coordinator')
                                            Coordinator
                                        @else
                                            {{ $approval->role->name ?? $approval->role->role_name ?? 'N/A' }}
                                        @endif
                                    </strong><br>
                                    <span class="badge badge-{{ $approval->status === 'approved' ? 'success' : ($approval->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($approval->status) }}
                                    </span>
                                </small>
                                @if(($approval->special_role_type === 'class_teacher' || $approval->special_role_type === 'coordinator') && $approval->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-info mt-2 view-special-role-approvals" 
                                            data-approval-id="{{ $approval->result_approvalID }}" 
                                            data-role-type="{{ $approval->special_role_type }}"
                                            data-exam-id="{{ $examination->examID }}">
                                        <i class="bi bi-eye"></i> View Details
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <p class="text-muted">
                            <strong>Current Step:</strong> You are at approval step {{ $resultApproval->approval_order }} 
                            (
                            @if($resultApproval->special_role_type === 'class_teacher')
                                Class Teacher
                            @elseif($resultApproval->special_role_type === 'coordinator')
                                Coordinator
                            @else
                                {{ $resultApproval->role->name ?? $resultApproval->role->role_name ?? 'N/A' }}
                            @endif
                            )
                        </p>
                    </div>
                </div>
            </div>

            <!-- Examination Info -->
            <div class="card mb-4">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Examination: {{ $examination->exam_name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Year:</strong> {{ $examination->year }}
                        </div>
                        <div class="col-md-3">
                            <strong>Term:</strong> {{ ucfirst(str_replace('_', ' ', $examination->term ?? 'N/A')) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Start Date:</strong> {{ \Carbon\Carbon::parse($examination->start_date)->format('d M Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>End Date:</strong> {{ \Carbon\Carbon::parse($examination->end_date)->format('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Statistics -->
            @if($examHasEnded && $attendanceStats)
            <div class="card mb-4">
                <div class="card-header bg-primary-custom text-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Examination Statistics</h5>
                </div>
                <div class="card-body">
                    <!-- Overall Attendance Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                    <div class="card-body text-center">
                                    <h4 class="mb-1 fw-bold">{{ $attendanceStats['expected'] ?? 0 }}</h4>
                                    <p class="mb-0">Walio Takwa Kufanya</p>
                                    <small>(Expected Students)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                    <div class="card-body text-center">
                                    <h4 class="mb-1 fw-bold">{{ $attendanceStats['present'] ?? 0 }}</h4>
                                    <p class="mb-0">Walio Hudhuria</p>
                                    <small>(Present - Attended at least one subject)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white h-100">
                                    <div class="card-body text-center">
                                    <h4 class="mb-1 fw-bold">{{ $attendanceStats['absent'] ?? 0 }}</h4>
                                    <p class="mb-0">Hawaja Hudhuria</p>
                                    <small>(Absent - Didn't attend any subject)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                    <div class="card-body text-center">
                                    <h4 class="mb-1 fw-bold">
                                        @if(($attendanceStats['expected'] ?? 0) > 0)
                                            {{ number_format((($attendanceStats['present'] ?? 0) / ($attendanceStats['expected'] ?? 1)) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </h4>
                                    <p class="mb-0">Attendance Rate</p>
                                    <small>(Percentage of students who attended)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Attendance by Subject -->
                    @if(!empty($attendanceStats['by_subject']))
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="bi bi-book"></i> Attendance by Subject</h6>
                                <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Subject</th>
                                        <th class="text-center">Expected</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Attendance Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                    @foreach($attendanceStats['by_subject'] as $subject)
                                    <tr>
                                        <td><strong>{{ $subject['subject_name'] }}</strong></td>
                                        <td class="text-center">{{ $subject['expected'] }}</td>
                                        <td class="text-center text-success"><strong>{{ $subject['present'] }}</strong></td>
                                        <td class="text-center text-danger"><strong>{{ $subject['absent'] }}</strong></td>
                                        <td class="text-center">
                                            @if($subject['expected'] > 0)
                                                <span class="badge badge-{{ (($subject['present'] / $subject['expected']) * 100) >= 80 ? 'success' : ((($subject['present'] / $subject['expected']) * 100) >= 50 ? 'warning' : 'danger') }}">
                                                    {{ number_format(($subject['present'] / $subject['expected']) * 100, 1) }}%
                                                </span>
                        @else
                                                <span class="badge badge-secondary">N/A</span>
                                            @endif
                                        </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                                        </div>
            @endif

            @if(isset($showOnlyRoadmap) && $showOnlyRoadmap)
                <!-- Show only roadmap when turn hasn't come -->
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Waiting for Previous Approvals</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-info-circle"></i> Your approval turn has not come yet.</h6>
                            <p class="mb-2">The following approvals must be completed before you can review and approve results:</p>
                            <ul class="mb-0">
                                @foreach($previousApprovals as $prevApproval)
                                    <li>
                                        <strong>Step {{ $prevApproval->approval_order }}:</strong> 
                                        @if($prevApproval->special_role_type === 'class_teacher')
                                            Class Teacher
                                        @elseif($prevApproval->special_role_type === 'coordinator')
                                            Coordinator
                                        @else
                                            {{ $prevApproval->role->name ?? $prevApproval->role->role_name ?? 'N/A' }}
                                        @endif
                                        - 
                                        <span class="badge badge-{{ $prevApproval->status === 'approved' ? 'success' : ($prevApproval->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($prevApproval->status) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <a href="{{ route('teachersDashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            @else
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Results</h5>
                                        </div>
                <div class="card-body">
                    <form id="filterForm">
                                    <div class="row">
                            @if(isset($isClassTeacherApproval) && $isClassTeacherApproval)
                                <!-- Class Teacher: Show subclass selection only (no main class) -->
                                <div class="col-md-6 mb-3">
                                    <label for="filterSubclass"><i class="bi bi-diagram-3"></i> Select Your Class (Subclass) <span class="text-danger">*</span></label>
                                    <select class="form-control" id="filterSubclass" name="filter_subclass" required>
                                        <option value="">-- Select Your Class --</option>
                                        @foreach($availableSubclasses as $subclass)
                                            <option value="{{ $subclass['subclassID'] }}" data-class-id="{{ $subclass['classID'] }}" data-class-name="{{ $subclass['class_name'] }}">
                                                {{ $subclass['class_name'] }} - {{ $subclass['subclass_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Please select the class you manage to approve its results.</small>
                                </div>
                                <input type="hidden" id="filterMainClass" value="">
                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary-custom w-100" id="filterResultsBtn">
                                        <i class="bi bi-funnel-fill"></i> Load Results
                                    </button>
                                </div>
                            @elseif(isset($isCoordinatorApproval) && $isCoordinatorApproval)
                                <!-- Coordinator: Show mainclass selection (read-only after selection) -->
                                <div class="col-md-4 mb-3">
                                    <label for="filterMainClass"><i class="bi bi-building"></i> Select Main Class <span class="text-danger">*</span></label>
                                    <select class="form-control" id="filterMainClass" name="filter_main_class" required>
                                        <option value="">-- Select Main Class --</option>
                                        @foreach($availableMainClasses as $mainClass)
                                            <option value="{{ $mainClass['classID'] }}">{{ $mainClass['class_name'] }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Please select the main class you coordinate to approve its results.</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="filterSubclass"><i class="bi bi-diagram-3"></i> Filter by Subclass</label>
                                    <select class="form-control" id="filterSubclass" name="filter_subclass">
                                        <option value="all">All Subclasses</option>
                                        <!-- Will be populated dynamically based on selected main class -->
                                    </select>
                                    <small class="form-text text-muted">You can filter by subclass within the selected main class.</small>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary-custom w-100" id="filterResultsBtn">
                                        <i class="bi bi-funnel-fill"></i> Filter Results
                                    </button>
                                </div>
                            @else
                                <!-- Regular role: Show both filters -->
                                <div class="col-md-4 mb-3">
                                    <label for="filterMainClass"><i class="bi bi-building"></i> Filter by Main Class</label>
                                    <select class="form-control" id="filterMainClass" name="filter_main_class">
                                        <option value="all">All Classes</option>
                                        @foreach($participatingClasses as $class)
                                            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="filterSubclass"><i class="bi bi-diagram-3"></i> Filter by Subclass</label>
                                    <select class="form-control" id="filterSubclass" name="filter_subclass">
                                        <option value="all">All Subclasses</option>
                                        @foreach($participatingSubclasses as $subclass)
                                            <option value="{{ $subclass->subclassID }}" data-class-id="{{ $subclass->classID }}" data-class-name="{{ $subclass->class->class_name ?? '' }}">
                                                {{ ($subclass->class->class_name ?? 'N/A') }} - {{ $subclass->subclass_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary-custom w-100" id="filterResultsBtn">
                                        <i class="bi bi-funnel-fill"></i> Filter Results
                                    </button>
                                </div>
                            @endif
                                                    </div>
                    </form>
                                    </div>
                                </div>

            <!-- Statistics Section -->
            <div class="card mb-4" id="statisticsSection" style="display: none;">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Statistics za Matokeo</h5>
                </div>
                <div class="card-body">
                    <!-- Overall Statistics -->
                    <div class="row mb-4" id="overallStats">
                        <!-- Grade/Division Statistics will be inserted here -->
                    </div>
                    
                    <!-- Gender Breakdown -->
                    <div class="row mb-4" id="genderStats">
                        <!-- Gender statistics will be inserted here -->
                    </div>
                    
                    <!-- Per Class Statistics -->
                    <div class="row mb-4" id="classStats">
                        <!-- Per class statistics will be inserted here -->
                    </div>
                    
                    <!-- Per Subject Statistics -->
                    <div class="row mb-4" id="subjectStats">
                        <!-- Per subject statistics will be inserted here -->
                    </div>
                    
                    <!-- Charts -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Grade/Distribution Chart</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="gradeDivisionChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Gender Breakdown Chart</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="genderChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Pass Rate Chart (shown when filtering all classes) -->
                    <div class="row" id="classPassRateSection" style="display: none;">
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Ufaulu wa Madarasa (Pass Rate by Class)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="classPassRateChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Container -->
            <div class="card mb-4">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Results</h5>
                </div>
                <div class="card-body">
                    <div id="resultsContainer">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary-custom" role="status">
                                <span class="sr-only">Loading results...</span>
                                </div>
                            <p class="mt-3 text-muted">Loading results...</p>
                                    </div>
                    </div>
                </div>
            </div>

            <!-- Approval Actions -->
            <div class="card mt-4">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Approval Action</h5>
                </div>
                <div class="card-body">
                    @if($resultApproval->status === 'rejected')
                        <div class="alert alert-warning">
                            <strong><i class="bi bi-exclamation-triangle"></i> Previous Rejection:</strong>
                            <p class="mb-0">{{ $resultApproval->rejection_reason ?? 'No reason provided' }}</p>
                            <small class="text-muted">You can review and approve again if needed.</small>
                        </div>
                    @endif

                    <form id="approvalForm">
                        <div class="form-group">
                            <label>Action <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="action_approve" value="approve" {{ $resultApproval->status === 'rejected' ? '' : 'checked' }}>
                                <label class="form-check-label" for="action_approve">
                                    <i class="bi bi-check-circle text-success"></i> Approve Results
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="action_reject" value="reject" {{ $resultApproval->status === 'rejected' ? 'checked' : '' }}>
                                <label class="form-check-label" for="action_reject">
                                    <i class="bi bi-x-circle text-danger"></i> Reject Results
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="approvalCommentGroup">
                            <label for="approval_comment">Approval Comment (Optional)</label>
                            <textarea class="form-control" id="approval_comment" name="approval_comment" rows="3" placeholder="Enter approval comment..."></textarea>
                        </div>

                        <div class="form-group" id="rejectionReasonGroup" style="display: none;">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Enter reason for rejection..." required></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary-custom" id="submitApprovalBtn">
                                <i class="bi bi-check-circle"></i> Submit Approval
                            </button>
                            <a href="{{ route('teachersDashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize form based on current status
    @if($resultApproval->status === 'rejected')
        $('#action_reject').prop('checked', true);
        $('#approvalCommentGroup').hide();
        $('#rejectionReasonGroup').show();
        $('#rejection_reason').prop('required', true);
        @if($resultApproval->rejection_reason)
            $('#rejection_reason').val('{{ addslashes($resultApproval->rejection_reason) }}');
        @endif
    @else
        $('#action_approve').prop('checked', true);
        $('#approvalCommentGroup').show();
        $('#rejectionReasonGroup').hide();
    @endif

    // Handle action radio change
    $('input[name="action"]').on('change', function() {
        if ($(this).val() === 'approve') {
            $('#approvalCommentGroup').show();
            $('#approval_comment').prop('required', false);
            $('#rejectionReasonGroup').hide();
            $('#rejection_reason').prop('required', false);
        } else {
            $('#approvalCommentGroup').hide();
            $('#approval_comment').prop('required', false);
            $('#rejectionReasonGroup').show();
            $('#rejection_reason').prop('required', true);
        }
    });


    // Store all subclass options initially
    const allSubclassOptions = $('#filterSubclass').html();
    const examID = {{ $examination->examID }};
    
    // Store current results data globally
    let currentResultsData = [];
    
    // Check if this is a special role approval
    const isClassTeacherApproval = {{ isset($isClassTeacherApproval) && $isClassTeacherApproval ? 'true' : 'false' }};
    const isCoordinatorApproval = {{ isset($isCoordinatorApproval) && $isCoordinatorApproval ? 'true' : 'false' }};
    
    // Auto-select for special roles
    @if(isset($isClassTeacherApproval) && $isClassTeacherApproval && count($availableSubclasses) == 1)
        // Auto-select if only one subclass
        $('#filterSubclass').val('{{ $availableSubclasses[0]["subclassID"] }}');
        // Auto-load results
        setTimeout(function() {
            loadFilteredResults();
        }, 500);
    @endif
    
    @if(isset($isCoordinatorApproval) && $isCoordinatorApproval && count($availableMainClasses) == 1)
        // Auto-select if only one mainclass
        $('#filterMainClass').val('{{ $availableMainClasses[0]["classID"] }}');
        // Load subclasses for this mainclass
        loadSubclassesForMainClass('{{ $availableMainClasses[0]["classID"] }}');
        // Auto-load results
        setTimeout(function() {
            loadFilteredResults();
        }, 500);
    @endif
    
    // Function to load filtered results
    function loadFilteredResults() {
        let mainClassID = $('#filterMainClass').val() || 'all';
        let subclassID = $('#filterSubclass').val() || 'all';
        
        // For class_teacher, subclassID is required
        if (isClassTeacherApproval) {
            if (!subclassID || subclassID === 'all' || subclassID === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select your class (subclass) first.'
                });
                return;
            }
            mainClassID = 'all'; // Class teacher doesn't filter by mainclass
        }
        
        // For coordinator, mainClassID is required
        if (isCoordinatorApproval) {
            if (!mainClassID || mainClassID === 'all' || mainClassID === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select main class first.'
                });
                return;
            }
        }
        
        // Show loading
        $('#resultsContainer').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary-custom" role="status">
                    <span class="sr-only">Loading results...</span>
                </div>
                <p class="mt-3 text-muted">Loading results...</p>
            </div>
        `);
        
        $.ajax({
            url: '/get_filtered_results_for_approval/' + examID,
            method: 'GET',
            data: {
                main_class_id: mainClassID,
                subclass_id: subclassID
            },
            success: function(response) {
                if (response.success && response.results) {
                    // Store results data globally
                    currentResultsData = response.results;
                    const schoolType = response.school_type || 'Secondary';
                    
                    let html = '';
                    
                    if (response.results.length === 0) {
                        html = '<div class="alert alert-info text-center"><i class="bi bi-info-circle"></i> No results found for the selected filters.</div>';
                        $('#statisticsSection').hide();
        } else {
                        // Display statistics if available
                        if (response.statistics) {
                            console.log('Statistics received:', response.statistics); // Debug
                            // Get class/subclass names for table title
                            let mainClassName = '';
                            let subclassName = '';
                            
                            if (mainClassID !== 'all') {
                                mainClassName = $('#filterMainClass option:selected').text();
                            }
                            if (subclassID !== 'all') {
                                subclassName = $('#filterSubclass option:selected').text();
                            }
                            
                            displayStatistics(response.statistics, schoolType, mainClassID === 'all', mainClassID, subclassID, mainClassName, subclassName);
                        } else {
                            console.log('No statistics in response'); // Debug
                            $('#statisticsSection').hide();
                        }
                        response.results.forEach(function(classResult) {
                            html += `
                                <div class="card mb-4 border-0 shadow-sm class-results" data-class="${classResult.class_name}" data-subclass-id="${classResult.subclassID}">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h5 class="mb-0">
                                            <i class="bi bi-building"></i> ${classResult.subclass_name}
                                            <span class="badge badge-light ml-2">${classResult.students.length} Students</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-hover">
                                                <thead class="bg-primary-custom text-white">
                                                    <tr>
                                                        <th>Position</th>
                                                        <th>Admission No.</th>
                                                        <th>Student Name</th>
                                                        <th>Total Marks</th>
                                                        <th>${schoolType === 'Primary' ? 'Grade' : 'Division'}</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                            `;
                            
                            classResult.students.forEach(function(student) {
                                // Determine grade/division display
                                let gradeDivisionDisplay = 'N/A';
                                if (schoolType === 'Primary') {
                                    gradeDivisionDisplay = student.grade || 'N/A';
                                } else {
                                    gradeDivisionDisplay = student.division || 'N/A';
                                }
                                
                                html += `
                                    <tr class="result-row" data-student-id="${student.studentID}">
                                        <td><span class="badge badge-success">${student.position}</span></td>
                                        <td>${student.admission_number}</td>
                                        <td><strong>${student.student_name}</strong></td>
                                        <td><strong>${Math.round(student.total_marks)}</strong></td>
                                        <td>
                                            <span class="badge ${schoolType === 'Primary' ? 'badge-info' : 'badge-warning'}">
                                                ${gradeDivisionDisplay}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary view-student-details-btn" 
                                                    data-student-id="${student.studentID}"
                                                    data-student-name="${student.student_name}"
                                                    data-admission="${student.admission_number}"
                                                    data-total-marks="${Math.round(student.total_marks)}"
                                                    data-grade="${student.grade || 'N/A'}"
                                                    data-division="${student.division || 'N/A'}"
                                                    data-school-type="${schoolType}"
                                                    title="View More Details">
                                                <i class="bi bi-eye"></i> View More
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                            
                            html += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    
                    $('#resultsContainer').html(html);
                    
                    // Initialize DataTables for each results table
                    $('.class-results table').each(function() {
                        // Destroy existing DataTable if it exists
                        if ($.fn.DataTable.isDataTable(this)) {
                            $(this).DataTable().destroy();
                        }
                        
                        // Initialize DataTable
                        $(this).DataTable({
                            'paging': true,
                            'lengthChange': true,
                            'searching': true,
                            'ordering': true,
                            'info': true,
                            'autoWidth': false,
                            'pageLength': 5,
                            'lengthMenu': [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                            'order': [[0, "asc"]], // Sort by position
                            'language': {
                                'search': 'Search:',
                                'lengthMenu': 'Show _MENU_ entries',
                                'info': 'Showing _START_ to _END_ of _TOTAL_ entries',
                                'infoEmpty': 'No entries to show',
                                'infoFiltered': '(filtered from _MAX_ total entries)',
                                'paginate': {
                                    'first': 'First',
                                    'last': 'Last',
                                    'next': 'Next',
                                    'previous': 'Previous'
                                }
                            }
                        });
                    });
                } else {
                    $('#resultsContainer').html('<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Failed to load results.</div>');
                    $('#statisticsSection').hide();
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load results.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.error || response.message || errorMsg;
                    } catch (e) {
                        errorMsg = 'Failed to load results. Please check your connection and try again.';
                    }
                }
                console.error('Error loading results:', xhr);
                $('#resultsContainer').html(`<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${errorMsg}</div>`);
                $('#statisticsSection').hide();
            }
        });
    }
    
    // Load results on page load
    loadFilteredResults();
    
    // Function to load subclasses for a main class (for coordinator)
    function loadSubclassesForMainClass(mainClassID) {
        const subclassSelect = $('#filterSubclass');
        
        if (mainClassID && mainClassID !== 'all') {
            // Clear current options
            subclassSelect.empty();
            
            // Add "All Subclasses" option
            subclassSelect.append('<option value="all">All Subclasses</option>');
            
            // Get subclasses for this main class from participating subclasses
            const tempDiv = $('<div>').html(allSubclassOptions);
            tempDiv.find('option').each(function() {
                const optionValue = $(this).val();
                if (optionValue === '' || optionValue === 'all') return;
                
                const optionClassID = $(this).data('class-id');
                
                if (String(optionClassID) === String(mainClassID)) {
                    subclassSelect.append($(this).clone());
                }
            });
        } else {
            // Show all subclasses
            subclassSelect.html(allSubclassOptions);
        }
        
        // Reset subclass selection to "All Subclasses"
        subclassSelect.val('all');
    }
    
    // Filter by main class - update subclass options
    $('#filterMainClass').on('change', function() {
        const selectedClassID = $(this).val();
        
        // For coordinator, disable mainclass dropdown after selection
        if (isCoordinatorApproval && selectedClassID && selectedClassID !== 'all' && selectedClassID !== '') {
            $(this).prop('disabled', true);
            $(this).css('background-color', '#e9ecef');
        }
        
        loadSubclassesForMainClass(selectedClassID);
        
        // Auto-load results when main class changes (only if not coordinator or if subclass is selected)
        if (!isCoordinatorApproval || (selectedClassID && selectedClassID !== 'all' && selectedClassID !== '')) {
            loadFilteredResults();
        }
    });
    
    // Auto-load results when subclass changes
    $('#filterSubclass').on('change', function() {
        loadFilteredResults();
    });

    // Filter Results Button
    $('#filterResultsBtn').on('click', function() {
        loadFilteredResults();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#resultsContainer').offset().top - 100
        }, 500);
    });

    // Function to display statistics
    let gradeDivisionChartInstance = null;
    let genderChartInstance = null;
    let classPassRateChartInstance = null;
    
    // Store exam name and class info globally
    let currentExamName = '{{ $examination->exam_name }}';
    let currentMainClass = '';
    let currentSubclass = '';
    
    function displayStatistics(statistics, schoolType, showAllClasses, mainClassID, subclassID, mainClassName, subclassName) {
        // Show statistics section
        $('#statisticsSection').show();
        
        // Clear previous charts
        if (gradeDivisionChartInstance) {
            gradeDivisionChartInstance.destroy();
        }
        if (genderChartInstance) {
            genderChartInstance.destroy();
        }
        if (classPassRateChartInstance) {
            classPassRateChartInstance.destroy();
        }
        
        // Display overall grade/division statistics
        let overallHtml = '';
        if (schoolType === 'Primary') {
            overallHtml = `
                <div class="col-12 mb-3">
                    <h6><i class="bi bi-trophy"></i> Grade Distribution (A-F)</h6>
                </div>
            `;
            ['A', 'B', 'C', 'D', 'E', 'F'].forEach(grade => {
                const count = statistics.grade_stats[grade] || 0;
                overallHtml += `
                    <div class="col-md-2 mb-3">
                        <div class="card text-center ${grade === 'A' || grade === 'B' ? 'border-success' : (grade === 'F' ? 'border-danger' : 'border-warning')}">
                            <div class="card-body">
                                <h4 class="mb-1 fw-bold">${count}</h4>
                                <p class="mb-0 text-muted">Grade ${grade}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            overallHtml = `
                <div class="col-12 mb-3">
                    <h6><i class="bi bi-trophy"></i> Division Distribution (I-IV-0)</h6>
                </div>
            `;
            ['I', 'II', 'III', 'IV', '0'].forEach(div => {
                const count = statistics.division_stats[div] || 0;
                const isPass = div !== '0';
                overallHtml += `
                    <div class="col-md-2 mb-3">
                        <div class="card text-center ${isPass ? 'border-success' : 'border-danger'}">
                            <div class="card-body">
                                <h4 class="mb-1 fw-bold">${count}</h4>
                                <p class="mb-0 text-muted">Division ${div}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        $('#overallStats').html(overallHtml);
        
        // Display gender breakdown
        let genderHtml = `
            <div class="col-12 mb-3">
                <h6><i class="bi bi-gender-ambiguous"></i> Gender Breakdown</h6>
            </div>
        `;
        
        if (schoolType === 'Primary') {
            genderHtml += '<div class="col-12"><table class="table table-bordered table-sm"><thead class="bg-light"><tr><th>Grade</th><th>Male</th><th>Female</th><th>Total</th></tr></thead><tbody>';
            ['A', 'B', 'C', 'D', 'E', 'F'].forEach(grade => {
                const male = statistics.grade_gender_stats[grade]['Male'] || 0;
                const female = statistics.grade_gender_stats[grade]['Female'] || 0;
                const gradeTotal = statistics.grade_stats[grade] || 0; // Get total from grade_stats to match
                const calculatedTotal = male + female;
                // Use gradeTotal if it's different (means there are Unknown genders)
                const displayTotal = gradeTotal > calculatedTotal ? gradeTotal : calculatedTotal;
                genderHtml += `<tr><td><strong>Grade ${grade}</strong></td><td>${male}</td><td>${female}</td><td><strong>${displayTotal}</strong></td></tr>`;
            });
            genderHtml += '</tbody></table></div>';
        } else {
            genderHtml += '<div class="col-12"><table class="table table-bordered table-sm"><thead class="bg-light"><tr><th>Division</th><th>Male</th><th>Female</th><th>Total</th></tr></thead><tbody>';
            ['I', 'II', 'III', 'IV', '0'].forEach(div => {
                const male = statistics.division_gender_stats[div]['Male'] || 0;
                const female = statistics.division_gender_stats[div]['Female'] || 0;
                const divisionTotal = statistics.division_stats[div] || 0; // Get total from division_stats to match
                const calculatedTotal = male + female;
                // Use divisionTotal if it's different (means there are Unknown genders)
                const displayTotal = divisionTotal > calculatedTotal ? divisionTotal : calculatedTotal;
                genderHtml += `<tr><td><strong>Division ${div}</strong></td><td>${male}</td><td>${female}</td><td><strong>${displayTotal}</strong></td></tr>`;
            });
            genderHtml += '</tbody></table></div>';
        }
        $('#genderStats').html(genderHtml);
        
        // Display per-class statistics
        let classHtml = `
            <div class="col-12 mb-3">
                <h6><i class="bi bi-building"></i> Statistics per Class</h6>
            </div>
        `;
        
        Object.keys(statistics.class_stats).forEach(classKey => {
            const classStat = statistics.class_stats[classKey];
            classHtml += `
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>${classStat.class_name} - ${classStat.subclass_name}</strong>
                            <span class="badge badge-info float-right">${classStat.total_students} Students</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
            `;
            
            if (schoolType === 'Primary') {
                ['A', 'B', 'C', 'D', 'E', 'F'].forEach(grade => {
                    const count = classStat.grades[grade] || 0;
                    classHtml += `
                        <div class="col-md-2 text-center mb-2">
                            <strong>Grade ${grade}:</strong> ${count}
                        </div>
                    `;
                });
            } else {
                ['I', 'II', 'III', 'IV', '0'].forEach(div => {
                    const count = classStat.divisions[div] || 0;
                    classHtml += `
                        <div class="col-md-2 text-center mb-2">
                            <strong>Div ${div}:</strong> ${count}
                        </div>
                    `;
                });
            }
            
            classHtml += `
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#classStats').html(classHtml);
        
        // Display per-subject statistics with gender breakdown
        console.log('Subject stats:', statistics.subject_stats); // Debug
        console.log('Statistics object:', statistics); // Debug
        
        // Check if subject_stats exists and has data
        if (statistics.subject_stats && Array.isArray(statistics.subject_stats) && statistics.subject_stats.length > 0) {
            // Calculate pass rate for each subject (A, B, C, D = pass; E, F = fail)
            let subjectsWithPassRate = statistics.subject_stats.map(function(subject) {
                const gradeCounts = subject.grade_counts || {};
                const total = (gradeCounts['A'] || 0) + (gradeCounts['B'] || 0) + (gradeCounts['C'] || 0) + 
                             (gradeCounts['D'] || 0) + (gradeCounts['E'] || 0) + (gradeCounts['F'] || 0);
                const passed = (gradeCounts['A'] || 0) + (gradeCounts['B'] || 0) + (gradeCounts['C'] || 0) + (gradeCounts['D'] || 0);
                const passRate = total > 0 ? (passed / total) * 100 : 0;
                return {
                    ...subject,
                    total_students: total,
                    passed: passed,
                    failed: (gradeCounts['E'] || 0) + (gradeCounts['F'] || 0),
                    pass_rate: passRate
                };
            });
            
            // Sort by pass rate to find best and worst
            subjectsWithPassRate.sort(function(a, b) {
                return b.pass_rate - a.pass_rate;
            });
            
            const bestSubject = subjectsWithPassRate.length > 0 ? subjectsWithPassRate[0] : null;
            const worstSubject = subjectsWithPassRate.length > 0 ? subjectsWithPassRate[subjectsWithPassRate.length - 1] : null;
            
            // Reorder: Best first, Worst second, then others
            let sortedSubjects = [];
            if (bestSubject && worstSubject && bestSubject.subject_name !== worstSubject.subject_name) {
                sortedSubjects.push(bestSubject);
                sortedSubjects.push(worstSubject);
                // Add others (excluding best and worst)
                subjectsWithPassRate.forEach(function(subject) {
                    if (subject.subject_name !== bestSubject.subject_name && 
                        subject.subject_name !== worstSubject.subject_name) {
                        sortedSubjects.push(subject);
                    }
                });
            } else {
                // If best and worst are the same, or only one subject, just use sorted list
                sortedSubjects = subjectsWithPassRate;
            }
            
            // Update statistics.subject_stats with sorted order
            statistics.subject_stats = sortedSubjects;
            
            // Build table title based on filter
            let tableTitle = '';
            if (mainClassName && mainClassName !== 'All Classes') {
                if (subclassName && subclassName !== 'All Subclasses') {
                    tableTitle = subclassName + ' ' + currentExamName + ' STUDENT SUBJECT RESULT OVERVIEW';
                } else {
                    tableTitle = mainClassName + ' ' + currentExamName + ' STUDENT SUBJECT RESULT OVERVIEW';
                }
            } else {
                tableTitle = currentExamName + ' STUDENT SUBJECT RESULT OVERVIEW';
            }
            
            let subjectHtml = `
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary-custom text-white">
                            <h6 class="mb-0"><i class="bi bi-book"></i> ${tableTitle}</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover" id="subjectPerformanceTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Subject</th>
                                            <th class="text-center">A</th>
                                            <th class="text-center">B</th>
                                            <th class="text-center">C</th>
                                            <th class="text-center">D</th>
                                            <th class="text-center">E</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;
            
            statistics.subject_stats.forEach(function(subject) {
                const gradeCounts = subject.grade_counts || {};
                const gradeGenderCounts = subject.grade_gender_counts || {};
                
                // Check if this is best or worst performing subject
                const isBest = bestSubject && subject.subject_name === bestSubject.subject_name;
                const isWorst = worstSubject && subject.subject_name === worstSubject.subject_name;
                
                let rowClass = '';
                if (isBest) {
                    rowClass = 'table-success';
                } else if (isWorst) {
                    rowClass = 'table-danger';
                }
                
                subjectHtml += `
                    <tr class="${rowClass}">
                        <td>
                            <strong>${subject.subject_name}</strong>
                            ${isBest ? '<span class="badge bg-success ms-2">Best</span>' : ''}
                            ${isWorst ? '<span class="badge bg-danger ms-2">Worst</span>' : ''}
                        </td>
                `;
                
                // Display each grade with gender breakdown - Format: total, then "M: X, F: Y"
                ['A', 'B', 'C', 'D', 'E', 'F'].forEach(function(grade) {
                    const total = gradeCounts[grade] || 0;
                    const male = gradeGenderCounts[grade] ? (gradeGenderCounts[grade]['Male'] || 0) : 0;
                    const female = gradeGenderCounts[grade] ? (gradeGenderCounts[grade]['Female'] || 0) : 0;
                    
                    // Format: "50 M: 10, F: 40" - total first, then gender breakdown
                    subjectHtml += `
                        <td class="text-center">
                            <div><strong>${total}</strong></div>
                            <div style="font-size: 0.9em; color: #6c757d;">M: ${male}, F: ${female}</div>
                        </td>
                    `;
                });
                
                // Add Action column with different buttons based on performance
                let actionButton = '';
                if (isBest) {
                    actionButton = `
                        <button class="btn btn-sm btn-success contact-teacher-btn" 
                                data-subject-name="${subject.subject_name}" 
                                data-subject-type="best"
                                data-grade-counts='${JSON.stringify(gradeCounts)}'
                                title="Congratulate Teacher">
                            <i class="bi bi-trophy"></i> Congratulate
                        </button>
                        <button class="btn btn-sm btn-info view-subject-details-btn mt-1" 
                                data-subject-name="${subject.subject_name}"
                                data-grade-counts='${JSON.stringify(gradeCounts)}'
                                title="View Details">
                            <i class="bi bi-eye"></i> View
                        </button>
                    `;
                } else if (isWorst) {
                    actionButton = `
                        <button class="btn btn-sm btn-danger contact-teacher-btn" 
                                data-subject-name="${subject.subject_name}" 
                                data-subject-type="worst"
                                data-grade-counts='${JSON.stringify(gradeCounts)}'
                                title="Complain to Teacher">
                            <i class="bi bi-exclamation-triangle"></i> Complain
                        </button>
                        <button class="btn btn-sm btn-info view-subject-details-btn mt-1" 
                                data-subject-name="${subject.subject_name}"
                                data-grade-counts='${JSON.stringify(gradeCounts)}'
                                title="View Details">
                            <i class="bi bi-eye"></i> View
                        </button>
                    `;
                } else {
                    actionButton = `
                        <button class="btn btn-sm btn-warning contact-teacher-btn" 
                                data-subject-name="${subject.subject_name}" 
                                data-subject-type="normal"
                                data-grade-counts='${JSON.stringify(gradeCounts)}'
                                title="Contact Teacher">
                            <i class="bi bi-envelope"></i> Contact
                        </button>
                        <button class="btn btn-sm btn-info view-subject-details-btn mt-1" 
                                data-subject-name="${subject.subject_name}"
                                data-grade-counts='${JSON.stringify(gradeCounts)}'
                                title="View Details">
                            <i class="bi bi-eye"></i> View
                        </button>
                    `;
                }
                
                subjectHtml += `
                        <td class="text-center">
                            ${actionButton}
                        </td>
                    </tr>
                `;
            });
            
            subjectHtml += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#subjectStats').html(subjectHtml);
            
            // Initialize DataTable for subject performance table after a short delay to ensure DOM is ready
            setTimeout(function() {
                if ($.fn.DataTable.isDataTable('#subjectPerformanceTable')) {
                    $('#subjectPerformanceTable').DataTable().destroy();
                }
                $('#subjectPerformanceTable').DataTable({
                    'paging': true,
                    'lengthChange': true,
                    'searching': true,
                    'ordering': true,
                    'info': true,
                    'autoWidth': false,
                    'pageLength': 5,
                    'lengthMenu': [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    'order': [[0, "asc"]], // Sort by subject name
                    'language': {
                        'search': 'Search:',
                        'lengthMenu': 'Show _MENU_ entries',
                        'info': 'Showing _START_ to _END_ of _TOTAL_ entries',
                        'infoEmpty': 'No entries to show',
                        'infoFiltered': '(filtered from _MAX_ total entries)',
                        'paginate': {
                            'first': 'First',
                            'last': 'Last',
                            'next': 'Next',
                            'previous': 'Previous'
                        }
                    }
                });
            }, 100);
            
            // Add click handler for view subject details - use event delegation
            $(document).off('click', '.view-subject-details-btn').on('click', '.view-subject-details-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const subjectName = $(this).data('subject-name');
                const gradeCountsStr = $(this).attr('data-grade-counts');
                
                // Parse grade counts if it's a string
                let gradeCounts = {};
                if (gradeCountsStr) {
                    try {
                        gradeCounts = JSON.parse(gradeCountsStr);
                    } catch (e) {
                        console.error('Error parsing grade counts:', e);
                        gradeCounts = {};
                    }
                }
                
                // Calculate totals
                const total = (gradeCounts['A'] || 0) + (gradeCounts['B'] || 0) + (gradeCounts['C'] || 0) + 
                             (gradeCounts['D'] || 0) + (gradeCounts['E'] || 0) + (gradeCounts['F'] || 0);
                const passed = (gradeCounts['A'] || 0) + (gradeCounts['B'] || 0) + (gradeCounts['C'] || 0) + (gradeCounts['D'] || 0);
                const failed = (gradeCounts['E'] || 0) + (gradeCounts['F'] || 0);
                const passRate = total > 0 ? ((passed / total) * 100).toFixed(1) : 0;
                
                // Show subject details in a modal or alert
                let detailsHtml = `
                    <div class="text-center mb-3">
                        <h5><strong>${subjectName}</strong></h5>
                        <p class="text-muted">Grade Distribution</p>
                    </div>
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                ['A', 'B', 'C', 'D', 'E', 'F'].forEach(function(grade) {
                    const count = gradeCounts[grade] || 0;
                    const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                    detailsHtml += `
                        <tr>
                            <td class="text-center"><strong>${grade}</strong></td>
                            <td class="text-center">${count}</td>
                            <td class="text-center">${percentage}%</td>
                        </tr>
                    `;
                });
                
                detailsHtml += `
                        </tbody>
                    </table>
                    <div class="mt-3 p-2 bg-light rounded">
                        <div class="row text-center">
                            <div class="col-4">
                                <strong>Total Students</strong><br>
                                <span class="text-primary">${total}</span>
                            </div>
                            <div class="col-4">
                                <strong>Passed (A-D)</strong><br>
                                <span class="text-success">${passed}</span>
                            </div>
                            <div class="col-4">
                                <strong>Failed (E-F)</strong><br>
                                <span class="text-danger">${failed}</span>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <strong>Pass Rate: <span class="text-primary">${passRate}%</span></strong>
                        </div>
                    </div>
                `;
                
                Swal.fire({
                    title: subjectName + ' - Performance Details',
                    html: detailsHtml,
                    icon: 'info',
                    width: '700px',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#940000'
                });
            });
        } else {
            // Show more detailed message for debugging
            let debugMsg = 'No subject statistics available.';
            if (statistics.subject_stats === undefined) {
                debugMsg += ' (subject_stats is undefined)';
            } else if (!Array.isArray(statistics.subject_stats)) {
                debugMsg += ' (subject_stats is not an array)';
            } else if (statistics.subject_stats.length === 0) {
                debugMsg += ' (subject_stats array is empty)';
            }
            console.log('Subject stats issue:', debugMsg, statistics);
            $('#subjectStats').html(`<div class="col-12"><div class="alert alert-info">${debugMsg}</div></div>`);
        }
        
        // Create charts
        createGradeDivisionChart(statistics, schoolType);
        createGenderChart(statistics, schoolType);
        
        // Show class pass rate chart if filtering all classes
        if (showAllClasses && statistics.class_pass_rates && statistics.class_pass_rates.length > 0) {
            $('#classPassRateSection').show();
            createClassPassRateChart(statistics.class_pass_rates);
        } else {
            $('#classPassRateSection').hide();
        }
    }
    
    function createGradeDivisionChart(statistics, schoolType) {
        const ctx = document.getElementById('gradeDivisionChart').getContext('2d');
        
        let labels, data, colors;
        if (schoolType === 'Primary') {
            labels = ['A', 'B', 'C', 'D', 'E', 'F'];
            data = labels.map(grade => statistics.grade_stats[grade] || 0);
            colors = ['#28a745', '#20c997', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'];
        } else {
            labels = ['I', 'II', 'III', 'IV', '0'];
            data = labels.map(div => statistics.division_stats[div] || 0);
            colors = ['#28a745', '#20c997', '#ffc107', '#fd7e14', '#dc3545'];
        }
        
        gradeDivisionChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: schoolType === 'Primary' ? 'Grade Count' : 'Division Count',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors.map(c => c.replace('0.8', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    function createGenderChart(statistics, schoolType) {
        const ctx = document.getElementById('genderChart').getContext('2d');
        
        let labels, maleData, femaleData;
        if (schoolType === 'Primary') {
            labels = ['A', 'B', 'C', 'D', 'E', 'F'];
            maleData = labels.map(grade => statistics.grade_gender_stats[grade]['Male'] || 0);
            femaleData = labels.map(grade => statistics.grade_gender_stats[grade]['Female'] || 0);
        } else {
            labels = ['I', 'II', 'III', 'IV', '0'];
            maleData = labels.map(div => statistics.division_gender_stats[div]['Male'] || 0);
            femaleData = labels.map(div => statistics.division_gender_stats[div]['Female'] || 0);
        }
        
        genderChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Male',
                    data: maleData,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Female',
                    data: femaleData,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    function createClassPassRateChart(classPassRates) {
        const ctx = document.getElementById('classPassRateChart').getContext('2d');
        
        // Calculate average marks (pass_rate field now contains average marks)
        const totalAverage = classPassRates.reduce((sum, c) => sum + (c.pass_rate || 0), 0);
        const overallAverage = classPassRates.length > 0 ? totalAverage / classPassRates.length : 0;
        
        // Sort by average marks (highest to lowest) to show best performing first (tallest bar)
        const sortedRates = [...classPassRates].sort((a, b) => b.pass_rate - a.pass_rate);
        
        // Identify best and worst performing classes
        const bestClass = sortedRates.length > 0 ? sortedRates[0] : null;
        const worstClass = sortedRates.length > 0 ? sortedRates[sortedRates.length - 1] : null;
        
        const labels = sortedRates.map(c => c.subclass_name || c.class_name);
        // Data shows actual average marks (bars will show difference from overall average visually)
        const data = sortedRates.map(c => c.pass_rate || 0); // pass_rate now contains average marks
        
        // Color coding: Average below 30 = red, Others = green
        const colors = sortedRates.map((c, index) => {
            const averageMarks = c.pass_rate || 0; // pass_rate now contains average marks
            if (averageMarks < 30) {
                return '#dc3545'; // Red for average below 30
            } else {
                return '#28a745'; // Green for average 30 and above
            }
        });
        
        classPassRateChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Marks',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors.map(c => c.replace('0.8', '1')),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'x', // Vertical bar chart - darasa lilofaulisha sana lina bar refu zaidi (juu), lilio felisha sana lina bar fupi zaidi (chini)
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Average Marks'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1);
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Classes'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label || '';
                            },
                            label: function(context) {
                                const index = context.dataIndex;
                                const classData = sortedRates[index];
                                const averageMarks = classData.pass_rate || 0; // pass_rate now contains average marks
                                const diff = averageMarks - overallAverage;
                                const label = context.label || '';
                                let performance = '';
                                
                                if (bestClass && label === (bestClass.subclass_name || bestClass.class_name)) {
                                    performance = ' (Best Performing - Highest Average)';
                                } else if (worstClass && label === (worstClass.subclass_name || worstClass.class_name)) {
                                    performance = ' (Worst Performing - Lowest Average)';
                                }
                                
                                let diffText = '';
                                if (diff > 0) {
                                    diffText = ` (+${diff.toFixed(1)} above overall average)`;
                                } else if (diff < 0) {
                                    diffText = ` (${diff.toFixed(1)} below overall average)`;
                                } else {
                                    diffText = ' (at overall average)';
                                }
                                
                                return [
                                    `Average Marks: ${averageMarks.toFixed(1)}${performance}`,
                                    `Overall Average: ${overallAverage.toFixed(1)}`,
                                    `Difference: ${diffText}`,
                                    `Students: ${classData.total || 0}`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }

    // View Student Details Button
    $(document).on('click', '.view-student-details-btn', function() {
        const studentID = $(this).data('student-id');
        const studentName = $(this).data('student-name');
                const admission = $(this).data('admission');
        const totalMarks = $(this).data('total-marks');
        const grade = $(this).data('grade');
        const division = $(this).data('division');
        const schoolType = $(this).data('school-type');
        
        // Get student's subjects from the stored results data
        const studentData = currentResultsData.find(function(cr) {
            return cr.students.find(function(s) {
                return s.studentID == studentID;
            });
        });
        
        if (!studentData) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Student data not found.'
            });
            return;
        }
        
        const student = studentData.students.find(function(s) {
            return s.studentID == studentID;
        });
        
        if (!student) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Student not found.'
            });
            return;
        }
        
        // Build modal content
        let html = `
            <div class="card mb-3">
                <div class="card-header bg-primary-custom text-white">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Student Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Admission Number:</strong> ${admission}</p>
                            <p><strong>Student Name:</strong> ${studentName}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Marks:</strong> <span class="badge badge-info">${Math.round(parseFloat(totalMarks))}</span></p>
                            <p><strong>${schoolType === 'Primary' ? 'Grade' : 'Division'}:</strong> 
                                <span class="badge ${schoolType === 'Primary' ? 'badge-info' : 'badge-warning'}">
                                    ${schoolType === 'Primary' ? grade : division}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary-custom text-white">
                    <h6 class="mb-0"><i class="bi bi-book"></i> Subject Results</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
        `;
        
        student.subjects.forEach(function(subject) {
            html += `
                <tr>
                    <td><strong>${subject.subject_name}</strong></td>
                                    <td>${subject.marks !== 'N/A' ? Math.round(parseFloat(subject.marks)) : 'N/A'}</td>
                    <td><span class="badge badge-info">${subject.grade}</span></td>
                    <td><span class="badge ${subject.remark === 'Pass' ? 'badge-success' : 'badge-danger'}">${subject.remark}</span></td>
                </tr>
            `;
        });
        
        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        $('#studentDetailsModalLabel').html(`<i class="bi bi-person"></i> ${studentName} - Detailed Results`);
        $('#studentDetailsContent').html(html);
        $('#studentDetailsModal').modal('show');
    });

    // Also handle button click directly (in case form submit doesn't work)
    $('#submitApprovalBtn').on('click', function(e) {
        e.preventDefault();
        $('#approvalForm').submit();
    });

    // Form submission
    $('#approvalForm').on('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submission started');
        console.log('isClassTeacherApproval:', isClassTeacherApproval);
        console.log('isCoordinatorApproval:', isCoordinatorApproval);
        
        // Check if action is selected
        const action = $('input[name="action"]:checked').val();
        console.log('Selected action:', action);
        
        if (!action) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select an action (Approve or Reject).'
            });
            return false;
        }

        const approvalComment = $('#approval_comment').val();
        const rejectionReason = $('#rejection_reason').val();

        if (action === 'reject' && !rejectionReason.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please provide a rejection reason.'
            });
            return false;
        }
        
        // For class teacher, check if subclass is selected
        if (isClassTeacherApproval) {
            const selectedSubclassID = $('#filterSubclass').val();
            console.log('Class teacher - selectedSubclassID:', selectedSubclassID);
            if (!selectedSubclassID || selectedSubclassID === 'all' || selectedSubclassID === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Selection Required',
                    text: 'Please select your class (subclass) first before submitting approval.'
                });
                return false;
            }
        }
        
        // For coordinator, check if mainclass is selected
        if (isCoordinatorApproval) {
            const selectedMainClassID = $('#filterMainClass').val();
            console.log('Coordinator - selectedMainClassID:', selectedMainClassID);
            if (!selectedMainClassID || selectedMainClassID === 'all' || selectedMainClassID === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Selection Required',
                    text: 'Please select main class first before submitting approval.'
                });
                return false;
            }
        }
        
        console.log('Validation passed, showing confirmation dialog');

        // Show confirmation dialog
        Swal.fire({
            title: action === 'approve' ? 'Approve Results?' : 'Reject Results?',
            text: action === 'approve' 
                ? 'Are you sure you want to approve these results?'
                : 'Are you sure you want to reject these results?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, ' + action + ' it!',
            allowOutsideClick: false,
            allowEscapeKey: true
        }).then((result) => {
            console.log('Confirmation dialog result:', result);
            if (result.isConfirmed) {
                // Show loading message
                Swal.fire({
                    title: 'Please Wait...',
                    html: `<div class="text-center">
                        <i class="bi bi-hourglass-split" style="font-size: 3rem; color: #940000;"></i>
                        <p class="mt-3">Sending SMS to all teachers for this ${action === 'approve' ? 'approval' : 'rejection'}...</p>
                        <p class="text-muted">This may take a few moments.</p>
                    </div>`,
                    icon: null,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // Prepare data based on action
                const formData = {
                    action: action,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                // Include subclass_id and main_class_id for special roles
                // Note: Validation already done above, so we can safely add these
                if (isClassTeacherApproval) {
                    const selectedSubclassID = $('#filterSubclass').val();
                    if (selectedSubclassID && selectedSubclassID !== 'all' && selectedSubclassID !== '') {
                        formData.subclass_id = selectedSubclassID;
                    }
                } else if (isCoordinatorApproval) {
                    const selectedMainClassID = $('#filterMainClass').val();
                    if (selectedMainClassID && selectedMainClassID !== 'all' && selectedMainClassID !== '') {
                        formData.main_class_id = selectedMainClassID;
                    }
                }

                // Only include approval_comment if action is approve
                if (action === 'approve') {
                    formData.approval_comment = approvalComment;
                } else {
                    // Only include rejection_reason if action is reject
                    formData.rejection_reason = rejectionReason;
                }

                $.ajax({
                    url: '{{ route("submit_result_approval", $examination->examID) }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || (action === 'approve' 
                                ? 'Results approved successfully! SMS sent to ' + (response.sent_count || 0) + ' teacher(s).'
                                : 'Results rejected. SMS sent to ' + (response.sent_count || 0) + ' teacher(s).'),
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            window.location.href = '{{ route("teachersDashboard") }}';
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to submit approval.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            }
        });
    });
});
</script>

<!-- Modal for Student Details -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="studentDetailsModalLabel">
                    <i class="bi bi-person"></i> Student Detailed Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="studentDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading student details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Special Role Approvals Modal (Class Teacher/Coordinator) -->
<div class="modal fade" id="specialRoleApprovalsModal" tabindex="-1" role="dialog" aria-labelledby="specialRoleApprovalsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="specialRoleApprovalsModalLabel">
                    <i class="bi bi-people"></i> <span id="modalRoleType"></span> Approvals Status
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="specialRoleApprovalsContent">
                    <div class="text-center">
                        <div class="spinner-border text-info" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading approvals...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Contact Teacher Modal -->
<div class="modal fade" id="contactTeacherModal" tabindex="-1" aria-labelledby="contactTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="contactTeacherModalLabel">
                    <i class="bi bi-envelope"></i> Contact Teacher - <span id="modalSubjectName"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="border: none; background: none; font-size: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="teachersList" class="mb-3">
                    <h6><i class="bi bi-people"></i> Teachers Teaching This Subject:</h6>
                    <div id="teachersContainer" class="list-group">
                        <!-- Teachers will be loaded here -->
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="teacherMessage" class="form-label">
                        <i class="bi bi-chat-text"></i> Message/Comment:
                    </label>
                    <textarea class="form-control" id="teacherMessage" rows="4" 
                              placeholder="e.g., Wanafunzi wamefeli mno katika somo hili. Tafadhali fanya kazi zaidi..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong>Note:</strong> Ujumbe huu utatumwa kwa walimu wote wanaofundisha hilo somo kwa njia ya SMS.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="sendMessageToTeachers">
                    <i class="bi bi-send"></i> Send SMS
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Store current filter info globally
    let currentMainClassID = '';
    let currentSubclassID = '';
    let currentExamID = {{ $examination->examID }};
    
    // Update filter info when statistics are displayed
    $(document).on('statisticsDisplayed', function(e, mainClassID, subclassID) {
        currentMainClassID = mainClassID;
        currentSubclassID = subclassID;
    });
    
    // Function to load teachers for a subject
    function loadTeachersForSubject(subjectName) {
        // Get current filter values from the filter dropdowns
        const currentMainClassID = $('#filterMainClass').val() || '';
        const currentSubclassID = $('#filterSubclass').val() || '';
        
        console.log('Loading teachers for subject:', subjectName);
        console.log('Current filters - mainClassID:', currentMainClassID, 'subclassID:', currentSubclassID);
        
        $.ajax({
            url: '/get_teachers_for_subject',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                subject_name: subjectName,
                exam_id: window.currentExamID || {{ $examination->examID }},
                main_class_id: currentMainClassID,
                subclass_id: currentSubclassID
            },
            success: function(response) {
                if (response.success && response.teachers && response.teachers.length > 0) {
                    let teachersHtml = '';
                    response.teachers.forEach(function(teacher) {
                        teachersHtml += `
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${teacher.first_name} ${teacher.last_name}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-telephone"></i> ${teacher.phone_number || 'N/A'}
                                        </small>
                                    </div>
                                    <span class="badge bg-primary">${teacher.class_name || 'N/A'}</span>
                                </div>
                            </div>
                        `;
                    });
                    $('#teachersContainer').html(teachersHtml);
                } else {
                    $('#teachersContainer').html('<div class="alert alert-warning">No teachers found for this subject.</div>');
                }
            },
            error: function(xhr) {
                console.error('Error loading teachers:', xhr);
                $('#teachersContainer').html('<div class="alert alert-danger">Error loading teachers. Please try again.</div>');
            }
        });
    }
    
    // Handle contact teacher button click
    $(document).on('click', '.contact-teacher-btn', function() {
        const subjectName = $(this).data('subject-name');
        const subjectType = $(this).data('subject-type') || 'normal';
        const gradeCountsStr = $(this).attr('data-grade-counts');
        
        $('#modalSubjectName').text(subjectName);
        $('#teachersContainer').html('<div class="text-center"><i class="bi bi-hourglass-split"></i> Loading teachers...</div>');
        
        // Parse grade counts if available
        let gradeCounts = {};
        if (gradeCountsStr) {
            try {
                gradeCounts = JSON.parse(gradeCountsStr);
            } catch (e) {
                console.error('Error parsing grade counts:', e);
            }
        }
        
        // Set default message based on subject type
        let defaultMessage = '';
        if (subjectType === 'best') {
            const total = (gradeCounts['A'] || 0) + (gradeCounts['B'] || 0) + (gradeCounts['C'] || 0) + 
                         (gradeCounts['D'] || 0) + (gradeCounts['E'] || 0) + (gradeCounts['F'] || 0);
            const passed = (gradeCounts['A'] || 0) + (gradeCounts['B'] || 0) + (gradeCounts['C'] || 0) + (gradeCounts['D'] || 0);
            defaultMessage = `Hongera! Wanafunzi wamefanya vizuri sana katika ${subjectName}. Wamefaulu ${passed} kati ya ${total} wanafunzi. Endelea na kazi nzuri!`;
        } else if (subjectType === 'worst') {
            const total = (gradeCounts['A'] || 0) + (gradeCounts['B'] || 0) + (gradeCounts['C'] || 0) + 
                         (gradeCounts['D'] || 0) + (gradeCounts['E'] || 0) + (gradeCounts['F'] || 0);
            const failed = (gradeCounts['E'] || 0) + (gradeCounts['F'] || 0);
            defaultMessage = `Wanafunzi wamefeli mno katika ${subjectName}. Wamefeli ${failed} kati ya ${total} wanafunzi. Tafadhali fanya kazi zaidi ili kuboresha matokeo.`;
        } else {
            defaultMessage = '';
        }
        $('#teacherMessage').val(defaultMessage);
        
        // Load teachers for this subject (will show only one teacher for subclass filter)
        loadTeachersForSubject(subjectName);
        
        $('#contactTeacherModal').modal('show');
    });
    
    // Function to send SMS to teachers
    $('#sendMessageToTeachers').on('click', function() {
        const subjectName = $('#modalSubjectName').text();
        const message = $('#teacherMessage').val().trim();
        
        if (!message) {
            Swal.fire({
                title: 'Warning!',
                text: 'Please enter a message before sending.',
                icon: 'warning',
                confirmButtonColor: '#940000'
            });
            return;
        }
        
        // Get current filter values from the filter dropdowns
        const currentMainClassID = $('#filterMainClass').val() || '';
        const currentSubclassID = $('#filterSubclass').val() || '';
        
        // Show confirmation dialog using SweetAlert
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to send this message to all teachers teaching this subject?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Send SMS',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const sendButton = $('#sendMessageToTeachers');
                sendButton.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');
                
                $.ajax({
                    url: '/send_message_to_teachers',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        subject_name: subjectName,
                        exam_id: window.currentExamID || {{ $examination->examID }},
                        main_class_id: currentMainClassID,
                        subclass_id: currentSubclassID,
                        message: message
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Message sent successfully to ' + (response.sent_count || 0) + ' teacher(s).',
                                icon: 'success',
                                confirmButtonColor: '#940000'
                            }).then(() => {
                                $('#contactTeacherModal').modal('hide');
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to send message.',
                                icon: 'error',
                                confirmButtonColor: '#940000'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error sending message:', xhr);
                        let errorMsg = 'Error sending message. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    },
                    complete: function() {
                        $('#sendMessageToTeachers').prop('disabled', false).html('<i class="bi bi-send"></i> Send SMS');
                    }
                });
            }
        });
    });
    
    // Handle special role approvals button click (Class Teacher/Coordinator)
    $(document).on('click', '.view-special-role-approvals', function() {
        const approvalID = $(this).data('approval-id');
        const roleType = $(this).data('role-type');
        const examID = $(this).data('exam-id');
        
        $('#specialRoleApprovalsModal').modal('show');
        
        // Set modal title
        const roleTypeName = roleType === 'class_teacher' ? 'Class Teacher' : 'Coordinator';
        $('#modalRoleType').text(roleTypeName);
        
        // Load approvals based on role type
        const route = roleType === 'class_teacher' 
            ? '{{ route("get_class_teacher_approvals", ":examID") }}'.replace(':examID', examID)
            : '{{ route("get_coordinator_approvals", ":examID") }}'.replace(':examID', examID);
        
        $.ajax({
            url: route,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    let html = `<h6 class="mb-3"><strong>${response.exam_name}</strong></h6>`;
                    html += '<table class="table table-bordered table-hover">';
                    html += '<thead class="thead-light">';
                    
                    if (roleType === 'class_teacher') {
                        html += '<tr>';
                        html += '<th>Class</th>';
                        html += '<th>Subclass</th>';
                        html += '<th>Class Teacher</th>';
                        html += '<th>Phone</th>';
                        html += '<th>Status</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        response.details.forEach(function(detail) {
                            const statusBadge = detail.status === 'approved' 
                                ? '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Approved</span>'
                                : detail.status === 'rejected'
                                ? '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> Rejected</span>'
                                : '<span class="badge badge-warning"><i class="bi bi-clock"></i> Pending</span>';
                            
                            const teacherName = detail.class_teacher ? detail.class_teacher.name : 'Not Assigned';
                            const teacherPhone = detail.class_teacher ? (detail.class_teacher.phone || 'N/A') : 'N/A';
                            
                            html += '<tr>';
                            html += `<td>${detail.class_name}</td>`;
                            html += `<td>${detail.subclass_name}</td>`;
                            html += `<td>${teacherName}</td>`;
                            html += `<td>${teacherPhone}</td>`;
                            html += `<td>${statusBadge}</td>`;
                            html += '</tr>';
                        });
                    } else {
                        // Coordinator
                        html += '<tr>';
                        html += '<th>Class</th>';
                        html += '<th>Coordinator</th>';
                        html += '<th>Phone</th>';
                        html += '<th>Status</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        response.details.forEach(function(detail) {
                            const statusBadge = detail.status === 'approved' 
                                ? '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Approved</span>'
                                : detail.status === 'rejected'
                                ? '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> Rejected</span>'
                                : '<span class="badge badge-warning"><i class="bi bi-clock"></i> Pending</span>';
                            
                            const coordinatorName = detail.coordinator ? detail.coordinator.name : 'Not Assigned';
                            const coordinatorPhone = detail.coordinator ? (detail.coordinator.phone || 'N/A') : 'N/A';
                            
                            html += '<tr>';
                            html += `<td>${detail.class_name}</td>`;
                            html += `<td>${coordinatorName}</td>`;
                            html += `<td>${coordinatorPhone}</td>`;
                            html += `<td>${statusBadge}</td>`;
                            html += '</tr>';
                        });
                    }
                    
                    html += '</tbody>';
                    html += '</table>';
                    
                    $('#specialRoleApprovalsContent').html(html);
                } else {
                    $('#specialRoleApprovalsContent').html('<div class="alert alert-danger">Failed to load approvals.</div>');
                }
            },
            error: function(xhr) {
                $('#specialRoleApprovalsContent').html('<div class="alert alert-danger">Error loading approvals. Please try again.</div>');
            }
        });
    });
});
</script>

@include('includes.footer')

