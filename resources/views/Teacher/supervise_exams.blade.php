@include('includes.teacher_nav')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid mt-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary-custom text-white rounded">
            <h4 class="mb-0"><i class="bi bi-building"></i> My Supervise Exams</h4>
        </div>
    </div>

    @if(isset($assignments) && count($assignments) > 0)
        <div class="row">
            @foreach($assignments as $assignment)
                @php
                    $isActive = $assignment->is_active ?? 0;
                    $examDate = $assignment->exam_date ? \Carbon\Carbon::parse($assignment->exam_date)->format('d/m/Y') : 'N/A';
                    $startTime = $assignment->start_time ? \Carbon\Carbon::parse($assignment->start_time)->format('h:i A') : 'N/A';
                    $endTime = $assignment->end_time ? \Carbon\Carbon::parse($assignment->end_time)->format('h:i A') : 'N/A';
                    $examStartDate = $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date)->format('d/m/Y') : 'N/A';
                    $examEndDate = $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date)->format('d/m/Y') : 'N/A';
                @endphp
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm {{ $isActive ? 'border-success' : 'border-secondary' }}">
                        <div class="card-header {{ $isActive ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                            <h6 class="mb-0">
                                <i class="bi bi-building"></i> {{ $assignment->hall_name ?? 'N/A' }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-primary mb-3">{{ $assignment->exam_name ?? 'N/A' }}</h6>

                            <div class="mb-2">
                                <small class="text-muted d-block">Class:</small>
                                <strong>{{ $assignment->class_name ?? 'N/A' }}</strong>
                            </div>

                            @if($assignment->subject_name)
                            <div class="mb-2">
                                <small class="text-muted d-block">Subject:</small>
                                <strong>{{ $assignment->subject_name }}</strong>
                            </div>
                            @endif

                            @if($assignment->exam_date)
                            <div class="mb-2">
                                <small class="text-muted d-block">Exam Date & Time:</small>
                                <strong>{{ $examDate }} ({{ $startTime }} - {{ $endTime }})</strong>
                            </div>
                            @endif

                            <div class="mb-2">
                                <small class="text-muted d-block">Exam Period:</small>
                                <strong>{{ $examStartDate }} - {{ $examEndDate }}</strong>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted d-block">Capacity:</small>
                                <strong>{{ $assignment->students_count ?? 0 }} / {{ $assignment->capacity ?? 0 }} students</strong>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted d-block">Gender:</small>
                                <span class="badge badge-info">{{ ucfirst($assignment->gender_allowed ?? 'Both') }}</span>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted d-block">Term:</small>
                                <strong>{{ ucfirst(str_replace('_', ' ', $assignment->term ?? 'N/A')) }}</strong>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Status:</small>
                                @if($isActive)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Completed</span>
                                @endif
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-sm btn-primary view-hall-students-btn"
                                        data-hall-id="{{ $assignment->exam_hallID }}"
                                        data-subject-id="{{ $assignment->subjectID }}"
                                        data-timetable-id="{{ $assignment->exam_timetableID }}"
                                        data-exam-name="{{ $assignment->exam_name }}"
                                        data-hall-name="{{ $assignment->hall_name }}">
                                    <i class="bi bi-people"></i> View Students
                                </button>

                                @if($isActive && $assignment->exam_date)
                                <button class="btn btn-sm btn-success"
                                    onclick="takeAttendance({{ json_encode($assignment->exam_hallID) }}, {{ json_encode($assignment->subjectID) }}, {{ json_encode($assignment->exam_timetableID) }}, {{ json_encode($assignment->examID) }}, {{ json_encode($assignment->exam_date) }})">
                                    <i class="bi bi-clipboard-check"></i> Take Attendance
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> No exam supervision assignments found.
        </div>
    @endif
</div>

<!-- View Hall Students Modal -->
<div class="modal fade" id="viewHallStudentsModal" tabindex="-1" role="dialog" aria-labelledby="viewHallStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewHallStudentsModalLabel">
                    <i class="bi bi-people"></i> Hall Students
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="hallStudentsContent" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Take Attendance Modal -->
<div class="modal fade" id="takeAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="takeAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="takeAttendanceModalLabel">
                    <i class="bi bi-clipboard-check"></i> Take Attendance
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="attendanceContent" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveAttendanceBtn">
                    <i class="bi bi-check-circle"></i> Save Attendance
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Swap Student Modal -->
<div class="modal fade" id="swapStudentModal" tabindex="-1" role="dialog" aria-labelledby="swapStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="swapStudentModalLabel">
                    <i class="bi bi-arrow-left-right"></i> Swap Student
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="swap_student_id">
                <input type="hidden" id="swap_from_hall_id">
                <div class="form-group">
                    <label>Select Target Hall:</label>
                    <select class="form-control" id="swap_target_hall_id">
                        <option value="">Select Hall</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="confirmSwapBtn">
                    <i class="bi bi-check-circle"></i> Swap
                </button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Function to load attendance data - globally accessible
function loadAttendanceData(hallID, subjectID, timetableID, examID, examDate) {
    $('#attendanceContent').html('<div class="text-center py-5"><div class="spinner-border text-success" role="status"><span class="sr-only">Loading...</span></div></div>');

    let url = '/hall_students/' + hallID;
    const params = [];
    if (subjectID) params.push('subjectID=' + subjectID);
    if (timetableID) params.push('exam_timetableID=' + timetableID);
    if (params.length > 0) url += '?' + params.join('&');

    $.ajax({
        url: url,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.students) {
                // Statistics Widgets
                let html = '<div class="row mb-3" id="attendanceStats">' +
                    '<div class="col-6 col-md-4 mb-2">' +
                        '<div class="card text-white text-center" style="background-color: #940000;">' +
                            '<div class="card-body p-2">' +
                                '<h6 class="mb-0 small">Present</h6>' +
                                '<h4 class="mb-0" id="statPresent">0</h4>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-6 col-md-4 mb-2">' +
                        '<div class="card text-white text-center" style="background-color: #940000;">' +
                            '<div class="card-body p-2">' +
                                '<h6 class="mb-0 small">Absent</h6>' +
                                '<h4 class="mb-0" id="statAbsent">0</h4>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-6 col-md-4 mb-2">' +
                        '<div class="card text-white text-center" style="background-color: #940000;">' +
                            '<div class="card-body p-2">' +
                                '<h6 class="mb-0 small">Excused</h6>' +
                                '<h4 class="mb-0" id="statExcused">0</h4>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="row mb-3">' +
                    '<div class="col-12">' +
                        '<div class="btn-group btn-group-sm w-100" role="group">' +
                            '<button type="button" class="btn btn-success" id="markAllPresentBtn">' +
                                '<i class="bi bi-check-circle"></i> Mark All Present' +
                            '</button>' +
                            '<button type="button" class="btn btn-danger" id="markAllAbsentBtn">' +
                                '<i class="bi bi-x-circle"></i> Mark All Absent' +
                            '</button>' +
                            '<button type="button" class="btn btn-warning" id="markAllExcusedBtn">' +
                                '<i class="bi bi-clock-history"></i> Mark All Excused' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="table-responsive">' +
                    '<table class="table table-striped table-hover" id="attendanceTable">' +
                        '<thead class="thead-light">' +
                            '<tr>' +
                                '<th>#</th>' +
                                '<th>Name</th>' +
                                '<th>Class</th>' +
                                '<th>Gender</th>' +
                                '<th>Status</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>';

                if (response.students.length === 0) {
                    html += '<tr><td colspan="5" class="text-center text-muted py-4">No students found in this hall.</td></tr>';
                } else {
                    response.students.forEach(function(student, index) {
                        const currentStatus = student.is_present ? 'Present' : (student.status || 'Absent');
                        const statusValue = currentStatus === 'Present' ? 'Present' : (currentStatus === 'Excused' ? 'Excused' : 'Absent');

                        const selectedPresent = statusValue === 'Present' ? ' selected' : '';
                        const selectedAbsent = statusValue === 'Absent' ? ' selected' : '';
                        const selectedExcused = statusValue === 'Excused' ? ' selected' : '';
                        const subclassDisplay = student.subclass ? ' - ' + student.subclass : '';

                        html += '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td><strong>' + (student.name || 'N/A') + '</strong></td>' +
                            '<td>' + (student.class_name || 'N/A') + subclassDisplay + '</td>' +
                            '<td>' + (student.gender || 'N/A') + '</td>' +
                            '<td>' +
                                '<select class="form-control form-control-sm attendance-status-select" ' +
                                        'data-student-id="' + student.studentID + '" ' +
                                        'id="status_' + student.studentID + '">' +
                                    '<option value="Present"' + selectedPresent + '>Present</option>' +
                                    '<option value="Absent"' + selectedAbsent + '>Absent</option>' +
                                    '<option value="Excused"' + selectedExcused + '>Excused</option>' +
                                '</select>' +
                            '</td>' +
                        '</tr>';
                    });
                }

                html += '</tbody></table></div>';

                $('#attendanceContent').html(html);

                // Initialize DataTable with max 5 rows per page
                $('#attendanceTable').DataTable({
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    responsive: true,
                    order: [[1, 'asc']]
                });

                // Update statistics
                if (typeof updateAttendanceStats === 'function') {
                    updateAttendanceStats();
                }

                // Store hallID and examID for saving
                $('#takeAttendanceModal').data('hall-id', hallID);
                $('#takeAttendanceModal').data('exam-id', examID);
            } else {
                $('#attendanceContent').html('<div class="alert alert-info text-center">No students found in this hall.</div>');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to load students.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            $('#attendanceContent').html('<div class="alert alert-danger text-center">' + errorMsg + '</div>');
        }
    });
}

// Take Attendance function - globally accessible
window.takeAttendance = function(hallID, subjectID, timetableID, examID, examDate) {
    function executeWhenJQueryReady() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(executeWhenJQueryReady, 50);
            return;
        }

        $('#takeAttendanceModal').modal('show');
        $('#attendanceContent').html('<div class="text-center py-5"><div class="spinner-border text-success" role="status"><span class="sr-only">Loading...</span></div></div>');

        // Load attendance data
        loadAttendanceData(hallID, subjectID, timetableID, examID, examDate);
    }
    executeWhenJQueryReady();
};

// Wait for jQuery and Bootstrap to be loaded
(function() {
    function initSuperviseExams() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            setTimeout(initSuperviseExams, 100);
            return;
        }

        $(document).ready(function() {
            console.log('Supervise Exams script initialized');

            // View Hall Students
            $(document).on('click', '.view-hall-students-btn', function(e) {
                e.preventDefault();
                console.log('View Students button clicked');

                const hallID = $(this).data('hall-id');
                const subjectID = $(this).data('subject-id');
                const timetableID = $(this).data('timetable-id');
                const examName = $(this).data('exam-name');
                const hallName = $(this).data('hall-name');

                console.log('Hall ID:', hallID, 'Subject ID:', subjectID);

                $('#viewHallStudentsModalLabel').html(`<i class="bi bi-people"></i> ${hallName} - ${examName}`);
                $('#viewHallStudentsModal').modal('show');
                $('#hallStudentsContent').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');

                let url = `/hall_students/${hallID}`;
                const params = [];
                if (subjectID) params.push(`subjectID=${subjectID}`);
                if (timetableID) params.push(`exam_timetableID=${timetableID}`);
                if (params.length > 0) url += '?' + params.join('&');

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success && response.students) {
                            let html = `
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="hallStudentsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Gender</th>
                                        <th>Attendance Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    if (response.students.length === 0) {
                        html += '<tr><td colspan="6" class="text-center text-muted py-4">No students found in this hall.</td></tr>';
                    } else {
                        response.students.forEach(function(student, index) {
                            const status = student.is_present ? 'Present' : (student.status || 'Absent');
                            let statusBadge = '';
                            if (status === 'Present') {
                                statusBadge = '<span class="badge bg-success">Present</span>';
                            } else if (status === 'Excused') {
                                statusBadge = '<span class="badge bg-warning">Excused</span>';
                            } else {
                                statusBadge = '<span class="badge bg-danger">Absent</span>';
                            }

                            html += `<tr>
                                <td>${index + 1}</td>
                                <td><strong>${student.name || 'N/A'}</strong></td>
                                <td>${student.class_name || 'N/A'}${(student.subclass ? ' - ' + student.subclass : '')}</td>
                                <td>${student.gender || 'N/A'}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary swap-student-btn"
                                            data-student-id="${student.studentID}"
                                            data-student-exam-hall-id="${student.student_exam_hallID}"
                                            data-from-hall-id="${hallID}"
                                            data-exam-id="${response.examID || ''}"
                                            title="Swap to Another Hall">
                                        <i class="bi bi-arrow-left-right"></i> Swap
                                    </button>
                                </td>
                            </tr>`;
                        });
                    }

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    $('#hallStudentsContent').html(html);

                            // Initialize DataTable with max 5 rows per page
                            $('#hallStudentsTable').DataTable({
                                pageLength: 5,
                                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                                responsive: true,
                                order: [[1, 'asc']]
                            });
                        } else {
                            $('#hallStudentsContent').html('<div class="alert alert-info text-center">No students found in this hall.</div>');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to load students.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        $('#hallStudentsContent').html(`<div class="alert alert-danger text-center">${errorMsg}</div>`);
                    }
                });
            }


            // Update statistics when status changes
            function updateAttendanceStats() {
                let present = 0, absent = 0, excused = 0;

                $('.attendance-status-select').each(function() {
                    const status = $(this).val();
                    if (status === 'Present') present++;
                    else if (status === 'Absent') absent++;
                    else if (status === 'Excused') excused++;
                });

                $('#statPresent').text(present);
                $('#statAbsent').text(absent);
                $('#statExcused').text(excused);
            }

            // Update statistics on status change
            $(document).on('change', '.attendance-status-select', function() {
                updateAttendanceStats();
            });

            // Mark All Present
            $(document).on('click', '#markAllPresentBtn', function() {
                $('.attendance-status-select').val('Present').trigger('change');
                updateAttendanceStats();
            });

            // Mark All Absent
            $(document).on('click', '#markAllAbsentBtn', function() {
                $('.attendance-status-select').val('Absent').trigger('change');
                updateAttendanceStats();
            });

            // Mark All Excused
            $(document).on('click', '#markAllExcusedBtn', function() {
                $('.attendance-status-select').val('Excused').trigger('change');
                updateAttendanceStats();
            });

            // Save Attendance
            $(document).on('click', '#saveAttendanceBtn', function() {
                const hallID = $('#takeAttendanceModal').data('hall-id');
                const examID = $('#takeAttendanceModal').data('exam-id');

                if (!hallID || !examID) {
                    alert('Error: Missing hall or exam information.');
                    return;
                }

                // Collect all students with their status
                const attendanceData = [];
                $('.attendance-status-select').each(function() {
                    const studentID = $(this).data('student-id');
                    const status = $(this).val();
                    attendanceData.push({
                        studentID: studentID,
                        status: status
                    });
                });

                $.ajax({
                    url: `/hall_attendance/${hallID}`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        attendance_data: attendanceData,
                        examID: examID
                    },
                    success: function(response) {
                        $('#takeAttendanceModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Attendance saved successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to save attendance.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            });
        });
    }

    // Initialize when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSuperviseExams);
    } else {
        initSuperviseExams();
    }
})();

// Additional handlers that need to be in document ready - wait for jQuery
(function() {
    function initSwapHandlers() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(initSwapHandlers, 100);
            return;
        }

        $(document).ready(function() {
            // Swap Student functionality
            $(document).on('click', '.swap-student-btn', function() {
                const studentID = $(this).data('student-id');
                const studentExamHallID = $(this).data('student-exam-hall-id');
                const fromHallID = $(this).data('from-hall-id');
                const examID = $(this).data('exam-id');

                $('#swap_student_id').val(studentID);
                $('#swap_from_hall_id').val(fromHallID);
                $('#swapStudentModal').data('student-exam-hall-id', studentExamHallID);
                $('#swapStudentModal').data('exam-id', examID);

                // Load available halls for this exam
                $.ajax({
                    url: `/admin/get-exam-halls/${examID}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.halls) {
                            let options = '<option value="">Select Hall</option>';
                            response.halls.forEach(function(hall) {
                                // Don't show current hall
                                if (hall.exam_hallID != fromHallID) {
                                    options += `<option value="${hall.exam_hallID}">${hall.hall_name} (${hall.class_name || 'N/A'}) - Capacity: ${hall.capacity}</option>`;
                                }
                            });
                            $('#swap_target_hall_id').html(options);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to load halls',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });

                $('#swapStudentModal').modal('show');
            });

            // Confirm Swap
            $('#confirmSwapBtn').on('click', function() {
                const targetHallID = $('#swap_target_hall_id').val();
                const studentExamHallID = $('#swapStudentModal').data('student-exam-hall-id');
                const fromHallID = $('#swap_from_hall_id').val();

                if (!targetHallID) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a target hall',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return;
                }

                $.ajax({
                    url: `/move_student_hall/${fromHallID}`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        target_hall_id: targetHallID,
                        student_exam_hall_id: studentExamHallID
                    },
                    success: function(response) {
                        $('#swapStudentModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Student swapped successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to swap student.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                });
            });
        });
    }

    // Initialize swap handlers
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSwapHandlers);
    } else {
        initSwapHandlers();
    }
})();
</script>
