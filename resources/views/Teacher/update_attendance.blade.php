@extends('layouts.teacher')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary-custom text-white">
            <h5 class="mb-0">
                <i class="fa fa-edit"></i> Update Attendance
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="attendanceTable" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Class-Subclass</th>
                            <th>Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Update Attendance Modal -->
<div class="modal fade" id="updateAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="fa fa-edit"></i> Update Attendance
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="updateAttendanceModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
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
        background-color: #7a0000;
        border-color: #7a0000;
        color: white;
    }
    table, .card, .modal-content, .btn {
        border-radius: 0 !important;
    }
</style>

<script>
$(document).ready(function() {
    var table = $('#attendanceTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("teacher.get_collected_attendance") }}',
            type: 'GET',
            dataSrc: function(json) {
                if (json.success) {
                    return json.data;
                }
                return [];
            }
        },
        columns: [
            { data: 'attendance_date_formatted', name: 'attendance_date' },
            { data: 'day', name: 'day' },
            { 
                data: null,
                render: function(data, type, row) {
                    return row.start_time + ' - ' + row.end_time;
                }
            },
            { data: 'subject', name: 'subject' },
            { data: 'class', name: 'class' },
            { data: 'students_count', name: 'students_count' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<button class="btn btn-sm btn-primary-custom" onclick="openUpdateModal(' + row.session_timetableID + ', \'' + row.attendance_date + '\')"><i class="fa fa-edit"></i> Update</button>';
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25
    });

    window.openUpdateModal = function(sessionTimetableID, date) {
        $('#updateAttendanceModal').modal('show');
        $('#updateAttendanceModalBody').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        // Load students for this session
        $.ajax({
            url: '/teacher/get-session-students',
            method: 'GET',
            data: {
                session_timetableID: sessionTimetableID,
                attendance_date: date
            },
            success: function(response) {
                if (response.success) {
                    let html = '<form id="updateAttendanceForm">';
                    html += '<input type="hidden" name="session_timetableID" value="' + sessionTimetableID + '">';
                    html += '<input type="hidden" name="attendance_date" value="' + date + '">';
                    html += '<input type="hidden" name="is_update" value="true">';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-bordered">';
                    html += '<thead><tr><th>Student Name</th><th>Status</th><th>Remark</th></tr></thead>';
                    html += '<tbody>';
                    
                    response.students.forEach(function(student) {
                        html += '<tr>';
                        html += '<td>' + student.name + '</td>';
                        html += '<td>';
                        html += '<select class="form-control" name="attendance[' + student.studentID + '][status]" required>';
                        html += '<option value="Present"' + (student.status === 'Present' ? ' selected' : '') + '>Present</option>';
                        html += '<option value="Absent"' + (student.status === 'Absent' ? ' selected' : '') + '>Absent</option>';
                        html += '<option value="Late"' + (student.status === 'Late' ? ' selected' : '') + '>Late</option>';
                        html += '<option value="Excused"' + (student.status === 'Excused' ? ' selected' : '') + '>Excused</option>';
                        html += '</select>';
                        html += '<input type="hidden" name="attendance[' + student.studentID + '][studentID]" value="' + student.studentID + '">';
                        html += '</td>';
                        html += '<td><input type="text" class="form-control" name="attendance[' + student.studentID + '][remark]" value="' + (student.remark || '') + '" placeholder="Optional"></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                    html += '<button type="submit" class="btn btn-primary-custom btn-block mt-3">Update Attendance</button>';
                    html += '</form>';
                    
                    $('#updateAttendanceModalBody').html(html);
                    
                    // Handle form submission
                    $('#updateAttendanceForm').on('submit', function(e) {
                        e.preventDefault();
                        const formData = $(this).serialize();
                        
                        $.ajax({
                            url: '{{ route("teacher.collect_session_attendance") }}',
                            method: 'POST',
                            data: formData,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'Attendance updated successfully',
                                        icon: 'success',
                                        confirmButtonColor: '#940000'
                                    }).then(() => {
                                        $('#updateAttendanceModal').modal('hide');
                                        table.ajax.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.error || 'Failed to update attendance',
                                        icon: 'error',
                                        confirmButtonColor: '#940000'
                                    });
                                }
                            },
                            error: function(xhr) {
                                const error = xhr.responseJSON?.error || 'Failed to update attendance';
                                Swal.fire({
                                    title: 'Error!',
                                    text: error,
                                    icon: 'error',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        });
                    });
                } else {
                    $('#updateAttendanceModalBody').html('<div class="alert alert-danger">' + response.error + '</div>');
                }
            },
            error: function() {
                $('#updateAttendanceModalBody').html('<div class="alert alert-danger">Failed to load students</div>');
            }
        });
    };
});
</script>
@endsection

