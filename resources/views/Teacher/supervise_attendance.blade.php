@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    div, .card, .btn, .table {
        border-radius: 0 !important;
    }
    body {
        font-family: "Century Gothic", "CenturyGothic", "AppleGothic", sans-serif;
        background-color: #f8f9fa;
    }
    .page-hero {
        background: linear-gradient(135deg, #fff2f2 0%, #f7dede 100%);
        border: 1px solid #e8c8c8;
        color: #7a1f1f;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(148, 0, 0, 0.05);
    }
    .card {
        border-radius: 15px !important;
        border: 1px solid #eef0f2 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04) !important;
    }
    .btn-custom {
        border-radius: 8px !important;
        border: 1px solid #940000;
        color: #940000;
        background: white;
        transition: all 0.3s;
        font-weight: 600;
    }
    .btn-custom:hover {
        background: #940000;
        color: white;
        transform: translateY(-1px);
    }
    .btn-save {
        background: #940000;
        color: white;
        border: none;
        padding: 12px 35px;
        font-weight: bold;
        border-radius: 50px !important;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(148, 0, 0, 0.2);
    }
    .btn-save:hover {
        background: #7a1f1f;
        color: white;
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(148, 0, 0, 0.3);
    }
    .table thead th {
        background-color: #fcf6f6 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-top: none !important;
    }
</style>

<div class="container-fluid mt-4">
    <div class="page-hero">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="bi bi-clipboard-check"></i> Take Exam Attendance</h4>
                <p class="mb-0">
                    <strong>Exam:</strong> {{ $exam->exam_name ?? 'N/A' }} | 
                    <strong>Subject:</strong> {{ $subject->subject_name ?? 'N/A' }} | 
                    <strong>Date:</strong> {{ isset($date) ? date('D, M d, Y', strtotime($date)) : 'N/A' }}
                </p>
            </div>
            <a href="{{ route('supervise_exams') }}" class="btn btn-custom">
                <i class="bi bi-arrow-left"></i> Cancel & Back
            </a>
        </div>
    </div>

    <form id="attendanceForm">
        @csrf
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-0">
                <!-- Desktop Table (Hidden on small screens) -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover mb-0" id="attendanceTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Student Name</th>
                                <th>Gender</th>
                                <th>Class Info</th>
                                <th width="200">Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List (Hidden on medium/large screens) -->
                <div class="d-md-none" id="attendanceMobileList">
                    <div class="p-3 bg-light border-bottom">
                        <small class="text-muted fw-bold">STUDENTS ATTENDANCE</small>
                    </div>
                    <div id="mobileListContainer">
                        <!-- Card data will be loaded here via AJAX -->
                    </div>
                </div>
                
                <div class="p-4 bg-white border-top text-center text-md-right mt-0">
                    <button type="submit" class="btn btn-save shadow-sm w-100 w-md-auto">
                        <i class="bi bi-check-all"></i> Save Attendance Records
                    </button>
                    <div class="mt-2 d-md-none">
                        <small class="text-muted">Tip: Scroll down to save after checking all students</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Styling for mobile cards */
    .attendance-mobile-card {
        padding: 15px;
        border-bottom: 1px solid #eee;
        background: white;
    }
    .attendance-mobile-card:last-child {
        border-bottom: none;
    }
    .attendance-mobile-card .student-name {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
        display: block;
    }
    .attendance-mobile-card .student-info {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 10px;
    }
    .status-selector-mobile {
        display: flex;
        gap: 10px;
    }
    .status-btn-mobile {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
        border-radius: 8px !important;
        font-size: 0.9rem;
        cursor: pointer;
        background: white;
    }
    .status-btn-mobile.active-present {
        background: #e8f5e9;
        border-color: #4caf50;
        color: #2e7d32;
        font-weight: bold;
    }
    .status-btn-mobile.active-absent {
        background: #ffebee;
        border-color: #f44336;
        color: #c62828;
        font-weight: bold;
    }
    .btn-save {
        border-radius: 12px !important;
    }
</style>

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    loadAttendanceData();

    function loadAttendanceData() {
        $.ajax({
            url: '/hall_students/{{ $hallID ?: 'null' }}',
            method: 'GET',
            data: {
                subject_id: '{{ $subjectID }}',
                examID: '{{ $examID }}',
                exam_category: '{{ $exam_category }}',
                classID: '{{ $classID }}',
                timetable_id: '{{ $timetable_id }}',
                scope: '{{ $scope }}'
            },
            success: function(response) {
                if (response.success) {
                    let tableHtml = '';
                    let mobileHtml = '';
                    
                    response.students.forEach((s, index) => {
                        // Desktop Table Row
                        tableHtml += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${s.name}</strong></td>
                                <td>${s.gender}</td>
                                <td>${s.class_name} - ${s.subclass}</td>
                                <td>
                                    <input type="hidden" name="attendance[${index}][studentID]" value="${s.studentID}">
                                    <select name="attendance[${index}][status]" class="form-control form-control-sm border-primary status-sync" data-index="${index}">
                                        <option value="Present" ${s.status === 'Present' ? 'selected' : ''}>Present</option>
                                        <option value="Absent" ${s.status === 'Absent' ? 'selected' : ''}>Absent</option>
                                    </select>
                                </td>
                            </tr>
                        `;

                        // Mobile Card
                        mobileHtml += `
                            <div class="attendance-mobile-card">
                                <span class="student-name">${s.name}</span>
                                <div class="student-info">
                                    <i class="bi bi-gender-ambiguous"></i> ${s.gender} | ${s.class_name}
                                </div>
                                <div class="status-selector-mobile">
                                    <div class="status-btn-mobile ${s.status === 'Present' || !s.status ? 'active-present' : ''}" 
                                         data-status="Present" data-index="${index}">
                                        <i class="bi bi-check-circle"></i> Present
                                    </div>
                                    <div class="status-btn-mobile ${s.status === 'Absent' ? 'active-absent' : ''}" 
                                         data-status="Absent" data-index="${index}">
                                        <i class="bi bi-x-circle"></i> Absent
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    $('#attendanceTable tbody').html(tableHtml);
                    $('#mobileListContainer').html(mobileHtml);

                    // Initialize DataTable for desktop
                    if (!$.fn.DataTable.isDataTable('#attendanceTable')) {
                        $('#attendanceTable').DataTable({
                            pageLength: 100,
                            ordering: false,
                            language: { search: "Filter students:" }
                        });
                    }

                    // Mobile Button Toggle and Sync with Hidden Selects
                    $('.status-btn-mobile').on('click', function() {
                        const index = $(this).data('index');
                        const status = $(this).data('status');
                        const parent = $(this).parent();

                        // Visual feedback
                        parent.find('.status-btn-mobile').removeClass('active-present active-absent');
                        if (status === 'Present') $(this).addClass('active-present');
                        else $(this).addClass('active-absent');

                        // Sync with the hidden form input (using the selects in table rows as our primary source)
                        $(`select[name="attendance[${index}][status]"]`).val(status);
                    });

                    // Sync Table selects back to Mobile (if user is on large screen then resizes)
                    $('.status-sync').on('change', function() {
                        const index = $(this).data('index');
                        const status = $(this).val();
                        const mobileButtons = $(`.status-btn-mobile[data-index="${index}"]`);
                        
                        mobileButtons.removeClass('active-present active-absent');
                        mobileButtons.filter(`[data-status="${status}"]`).addClass(status === 'Present' ? 'active-present' : 'active-absent');
                    });
                }
            }
        });
    }

    $('#attendanceForm').on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait while we update attendance records',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Use standard form serialization but handle the array correctly
        const formData = $(this).serialize();

        $.ajax({
            url: '/update_exam_attendance/{{ $examID }}',
            method: 'POST',
            data: formData + '&subjectID={{ $subjectID }}&date={{ $date }}',
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Attendance has been recorded successfully.',
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    window.location.href = "{{ route('supervise_exams') }}";
                });
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to save attendance. Please try again.', 'error');
            }
        });
    });
});
</script>
