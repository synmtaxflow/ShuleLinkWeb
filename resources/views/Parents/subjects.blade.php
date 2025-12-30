@include('includes.parent_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
    }

    body {
        background-color: #f8f9fa;
    }

    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .subject-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .subject-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15) !important;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid mt-3">
    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary-custom text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-book"></i> My Children's Subjects
                </h5>
            </div>
        </div>
    </div>

    <!-- Student Selection -->
    @if($students->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Select Student <span class="text-danger">*</span></label>
                <select id="studentSelect" class="form-select">
                    <option value="">Choose a student...</option>
                    @foreach($students as $student)
                        <option value="{{ $student->studentID }}">
                            {{ $student->first_name }} {{ $student->last_name }} 
                            ({{ $student->admission_number ?? 'N/A' }}) - 
                            {{ $student->subclass && $student->subclass->class ? ($student->subclass->class->class_name . ' ' . trim($student->subclass->subclass_name)) : 'N/A' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Subjects Container -->
    <div id="subjectsContainer">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-info-circle text-primary-custom" style="font-size: 48px;"></i>
                <p class="mt-3 mb-0 text-muted">Please select a student to view their subjects.</p>
            </div>
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-primary-custom" style="font-size: 64px;"></i>
            <h5 class="mt-3 mb-2">No Students Found</h5>
            <p class="text-muted mb-0">You don't have any active students registered.</p>
        </div>
    </div>
    @endif
</div>

@include('includes.footer')

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    (function($) {
        'use strict';

        $(document).ready(function() {
            // Handle Student Selection
            $('#studentSelect').on('change', function() {
                var studentID = $(this).val();
                
                if (!studentID) {
                    $('#subjectsContainer').html(
                        '<div class="card border-0 shadow-sm">' +
                        '<div class="card-body text-center py-5">' +
                        '<i class="bi bi-info-circle text-primary-custom" style="font-size: 48px;"></i>' +
                        '<p class="mt-3 mb-0 text-muted">Please select a student to view their subjects.</p>' +
                        '</div>' +
                        '</div>'
                    );
                    return;
                }

                // Show loading
                $('#subjectsContainer').html(
                    '<div class="card border-0 shadow-sm">' +
                    '<div class="card-body text-center py-4">' +
                    '<div class="spinner-border text-primary-custom" role="status">' +
                    '<span class="visually-hidden">Loading...</span>' +
                    '</div>' +
                    '<p class="mt-2">Loading subjects...</p>' +
                    '</div>' +
                    '</div>'
                );

                // Fetch subjects
                $.ajax({
                    url: "{{ route('get_student_subjects', ':id') }}".replace(':id', studentID),
                    type: "GET",
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success && response.subjects) {
                            var html = '<div class="card border-0 shadow-sm mb-4">';
                            html += '<div class="card-header bg-primary-custom text-white">';
                            html += '<h6 class="mb-0">';
                            html += '<i class="bi bi-person-circle"></i> ' + response.student.first_name + ' ' + response.student.last_name;
                            html += ' - ' + response.student.subclass_name;
                            html += '</h6>';
                            html += '</div>';
                            html += '<div class="card-body">';
                            html += '<div class="table-responsive">';
                            html += '<table class="table table-hover table-bordered" id="subjectsTable">';
                            html += '<thead class="bg-light">';
                            html += '<tr>';
                            html += '<th>#</th>';
                            html += '<th>Subject Name</th>';
                            html += '<th>Subject Code</th>';
                            html += '<th>Teacher</th>';
                            html += '<th>Status</th>';
                            html += '<th>Student Status</th>';
                            html += '<th>Election Stats</th>';
                            html += '<th>Actions</th>';
                            html += '</tr>';
                            html += '</thead>';
                            html += '<tbody>';

                            if (response.subjects.length > 0) {
                                response.subjects.forEach(function(subject, index) {
                                    html += '<tr>';
                                    html += '<td>' + (index + 1) + '</td>';
                                    html += '<td><strong>' + subject.subject_name + '</strong></td>';
                                    html += '<td>' + (subject.subject_code || 'N/A') + '</td>';
                                    html += '<td>' + (subject.teacher_name || 'Not Assigned') + '</td>';
                                    
                                    // Status Badge
                                    var statusBadge = subject.status === 'Active' 
                                        ? '<span class="badge bg-success">Active</span>' 
                                        : '<span class="badge bg-secondary">Inactive</span>';
                                    html += '<td>' + statusBadge + '</td>';
                                    
                                    // Student Status Badge
                                    var studentStatusBadge = '';
                                    if (subject.student_status === 'Required') {
                                        studentStatusBadge = '<span class="badge bg-warning text-dark">Required</span>';
                                    } else if (subject.student_status === 'Optional') {
                                        studentStatusBadge = '<span class="badge bg-info text-white">Optional</span>';
                                    } else {
                                        studentStatusBadge = '<span class="badge bg-secondary">Not Set</span>';
                                    }
                                    html += '<td>' + studentStatusBadge + '</td>';
                                    
                                    // Election Stats
                                    var electionStats = '';
                                    if (subject.student_status === 'Optional') {
                                        var electedCount = subject.elected_count || 0;
                                        var nonElectedCount = subject.non_elected_count || 0;
                                        var totalStudents = subject.total_students || 0;
                                        electionStats = '<div class="small">';
                                        electionStats += '<span class="badge bg-success me-1">Elected: ' + electedCount + '</span>';
                                        electionStats += '<span class="badge bg-secondary">Not Elected: ' + nonElectedCount + '</span>';
                                        electionStats += '<div class="mt-1"><small class="text-muted">Total: ' + totalStudents + ' students</small></div>';
                                        electionStats += '</div>';
                                    } else {
                                        electionStats = '<span class="text-muted small">N/A</span>';
                                    }
                                    html += '<td>' + electionStats + '</td>';
                                    
                                    // Actions
                                    html += '<td>';
                                    if (subject.student_status === 'Optional' && subject.status === 'Active') {
                                        if (subject.is_elected) {
                                            html += '<button class="btn btn-sm btn-danger deselect-subject-btn" ';
                                            html += 'data-student-id="' + response.student.studentID + '" ';
                                            html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                            html += 'data-subject-name="' + subject.subject_name + '" ';
                                            html += 'title="Deselect Subject">';
                                            html += '<i class="bi bi-x-circle"></i> Deselect';
                                            html += '</button>';
                                        } else {
                                            html += '<button class="btn btn-sm btn-primary-custom elect-subject-btn" ';
                                            html += 'data-student-id="' + response.student.studentID + '" ';
                                            html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                            html += 'data-subject-name="' + subject.subject_name + '" ';
                                            html += 'title="Elect Subject">';
                                            html += '<i class="bi bi-check-circle"></i> Elect';
                                            html += '</button>';
                                        }
                                    } else {
                                        html += '<span class="text-muted small">N/A</span>';
                                    }
                                    html += '</td>';
                                    html += '</tr>';
                                });
                            } else {
                                html += '<tr><td colspan="8" class="text-center">No subjects found for this student.</td></tr>';
                            }

                            html += '</tbody>';
                            html += '</table>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';

                            $('#subjectsContainer').html(html);

                            // Initialize DataTable
                            if ($.fn.DataTable) {
                                $('#subjectsTable').DataTable({
                                    "pageLength": 25,
                                    "order": [[1, "asc"]],
                                    "language": {
                                        "search": "Search subjects:"
                                    }
                                });
                            }
                        } else {
                            $('#subjectsContainer').html(
                                '<div class="card border-0 shadow-sm">' +
                                '<div class="card-body text-center py-5">' +
                                '<i class="bi bi-exclamation-triangle text-warning" style="font-size: 48px;"></i>' +
                                '<p class="mt-3 mb-0 text-muted">No subjects found for this student.</p>' +
                                '</div>' +
                                '</div>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching subjects:', xhr);
                        $('#subjectsContainer').html(
                            '<div class="card border-0 shadow-sm">' +
                            '<div class="card-body text-center py-5">' +
                            '<i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>' +
                            '<p class="mt-3 mb-0 text-danger">Failed to load subjects. Please try again.</p>' +
                            '</div>' +
                            '</div>'
                        );
                    }
                });
            });

            // Handle Elect Subject Button
            $(document).on('click', '.elect-subject-btn', function(e) {
                e.preventDefault();
                var studentID = $(this).data('student-id');
                var classSubjectID = $(this).data('class-subject-id');
                var subjectName = $(this).data('subject-name');

                Swal.fire({
                    title: 'Elect Subject?',
                    html: 'Are you sure you want to elect <strong>' + subjectName + '</strong> for your child?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#940000',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, elect it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var $btn = $(this);
                        var originalText = $btn.html();
                        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Processing...');

                        $.ajax({
                            url: "{{ route('elect_subject') }}",
                            type: "POST",
                            data: {
                                studentID: studentID,
                                classSubjectID: classSubjectID,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response && response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: response.success,
                                        confirmButtonColor: '#940000',
                                        timer: 2000
                                    }).then(function() {
                                        // Refresh subjects
                                        $('#studentSelect').trigger('change');
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.error || 'Failed to elect subject.',
                                        confirmButtonColor: '#940000'
                                    });
                                    $btn.prop('disabled', false).html(originalText);
                                }
                            },
                            error: function(xhr) {
                                $btn.prop('disabled', false).html(originalText);
                                let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                                    ? xhr.responseJSON.error
                                    : 'Something went wrong. Please try again.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg,
                                    confirmButtonColor: '#940000'
                                });
                            }
                        });
                    }
                });
            });

            // Handle Deselect Subject Button
            $(document).on('click', '.deselect-subject-btn', function(e) {
                e.preventDefault();
                var studentID = $(this).data('student-id');
                var classSubjectID = $(this).data('class-subject-id');
                var subjectName = $(this).data('subject-name');

                Swal.fire({
                    title: 'Deselect Subject?',
                    html: 'Are you sure you want to deselect <strong>' + subjectName + '</strong> for your child?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, deselect it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var $btn = $(this);
                        var originalText = $btn.html();
                        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Processing...');

                        $.ajax({
                            url: "{{ route('deselect_subject') }}",
                            type: "POST",
                            data: {
                                studentID: studentID,
                                classSubjectID: classSubjectID,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response && response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: response.success,
                                        confirmButtonColor: '#940000',
                                        timer: 2000
                                    }).then(function() {
                                        // Refresh subjects
                                        $('#studentSelect').trigger('change');
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.error || 'Failed to deselect subject.',
                                        confirmButtonColor: '#940000'
                                    });
                                    $btn.prop('disabled', false).html(originalText);
                                }
                            },
                            error: function(xhr) {
                                $btn.prop('disabled', false).html(originalText);
                                let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                                    ? xhr.responseJSON.error
                                    : 'Something went wrong. Please try again.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg,
                                    confirmButtonColor: '#940000'
                                });
                            }
                        });
                    }
                });
            });
        });
    })(jQuery);
</script>





