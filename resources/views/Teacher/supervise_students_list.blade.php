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
                <h4 class="mb-0"><i class="bi bi-people"></i> Students List</h4>
                <p class="mb-0">
                    <strong>Exam:</strong> {{ $exam->exam_name }} | 
                    <strong>Subject:</strong> {{ $subject->subject_name }} | 
                    <strong>Hall:</strong> {{ $hall->hall_name ?? 'Class Assignment' }}
                </p>
            </div>
            <a href="{{ route('supervise_exams') }}" class="btn btn-custom">
                <i class="bi bi-arrow-left"></i> Back to assignments
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="studentsTable">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Class</th>
                            <th>Subclass</th>
                            <th>Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    loadStudents();

    function loadStudents() {
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
                    let html = '';
                    response.students.forEach((s, index) => {
                        const statusBadge = s.status === 'Present' 
                            ? '<span class="badge badge-success">Present</span>' 
                            : '<span class="badge badge-danger">Absent</span>';
                        
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${s.name}</strong></td>
                                <td>${s.gender}</td>
                                <td>${s.class_name}</td>
                                <td>${s.subclass}</td>
                                <td>${statusBadge}</td>
                            </tr>
                        `;
                    });
                    $('#studentsTable tbody').html(html);
                    $('#studentsTable').DataTable({
                        pageLength: 25,
                        language: {
                            search: "Filter students:"
                        }
                    });
                }
            }
        });
    }
});
</script>
