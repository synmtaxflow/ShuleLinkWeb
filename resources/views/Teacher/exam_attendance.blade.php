<div class="container-fluid">
    <!-- Filters Section -->
    <div class="card mb-3">
        <div class="card-header bg-primary-custom text-white">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filter Exam Attendance</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="filterYear" class="form-label">Year</label>
                    <select class="form-select" id="filterYear">
                        <option value="">Select Year</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterTerm" class="form-label">Term</label>
                    <select class="form-select" id="filterTerm" disabled>
                        <option value="">Select Term</option>
                        @foreach($terms as $term)
                            <option value="{{ $term }}">{{ ucfirst($term) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterExam" class="form-label">Exam</label>
                    <select class="form-select" id="filterExam" disabled>
                        <option value="">Select Exam</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div id="attendanceStats" style="display:none;">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card bg-success text-white text-center clickable-stat" data-filter="present" style="cursor: pointer;">
                    <div class="card-body">
                        <h6 class="mb-0">Total Present</h6>
                        <h4 class="mb-0" id="totalPresent">0</h4>
                        <small class="d-block mt-1">Click to view</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white text-center clickable-stat" data-filter="absent" style="cursor: pointer;">
                    <div class="card-body">
                        <h6 class="mb-0">Total Absent</h6>
                        <h4 class="mb-0" id="totalAbsent">0</h4>
                        <small class="d-block mt-1">Click to view</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <h6 class="mb-0">Total Students</h6>
                        <h4 class="mb-0" id="totalStudents">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark text-center">
                    <div class="card-body">
                        <h6 class="mb-0">Percentage</h6>
                        <h4 class="mb-0" id="attendancePercentage">0%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance by Subclass -->
    <div id="attendanceTableContainer" style="display:none;">
        <div class="card mb-3">
            <div class="card-header bg-primary-custom text-white">
                <h6 class="mb-0"><i class="bi bi-table"></i> Attendance by Subclass</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="attendanceTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Total</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Select exam to view attendance</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div id="studentsTableContainer" style="display:none;">
        <div class="card">
            <div class="card-header bg-primary-custom text-white">
                <h6 class="mb-0"><i class="bi bi-people"></i> Students Attendance</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="studentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">Select exam to view students</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="noDataMessage" class="alert alert-info text-center" style="display:none;">
        <i class="bi bi-info-circle"></i> No exam attendance data available for the selected filters.
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<style>
.clickable-stat:hover {
    opacity: 0.9;
    transform: scale(1.02);
    transition: all 0.2s;
}
.absent-student-row {
    background-color: #dc3545 !important;
    color: white !important;
}
.absent-student-row:hover {
    background-color: #c82333 !important;
    color: white !important;
}
</style>

<script>
$(document).ready(function() {
    const subjectID = {{ $subjectID }};
    const classSubjectID = {{ $classSubject->class_subjectID }};

    // When year is selected, enable term and load terms
    $('#filterYear').on('change', function() {
        const year = $(this).val();
        if (year) {
            $('#filterTerm').prop('disabled', false);
            loadTermsForYear(year);
        } else {
            $('#filterTerm').prop('disabled', true).val('');
            $('#filterExam').prop('disabled', true).html('<option value="">Select Exam</option>');
            hideAttendanceData();
        }
    });

    // When term is selected, enable exam and load exams
    $('#filterTerm').on('change', function() {
        const year = $('#filterYear').val();
        const term = $(this).val();
        if (year && term) {
            $('#filterExam').prop('disabled', false);
            loadExamsForYearTerm(year, term);
        } else {
            $('#filterExam').prop('disabled', true).html('<option value="">Select Exam</option>');
            hideAttendanceData();
        }
    });

    // When exam is selected, load attendance data
    $('#filterExam').on('change', function() {
        const examID = $(this).val();
        if (examID) {
            loadExamAttendance(examID);
        } else {
            hideAttendanceData();
        }
    });

    function loadTermsForYear(year) {
        $.get('/teacher/get-terms-for-year', { year: year })
            .done(function(response) {
                if (response.success) {
                    let options = '<option value="">Select Term</option>';
                    response.terms.forEach(function(term) {
                        options += `<option value="${term}">${term.charAt(0).toUpperCase() + term.slice(1)}</option>`;
                    });
                    $('#filterTerm').html(options);
                }
            })
            .fail(function() {
                $('#filterTerm').html('<option value="">Error loading terms</option>');
            });
    }

    function loadExamsForYearTerm(year, term) {
        $.get('/teacher/get-exams-for-year-term', {
            year: year,
            term: term,
            subjectID: subjectID
        })
            .done(function(response) {
                if (response.success) {
                    let options = '<option value="">Select Exam</option>';
                    response.exams.forEach(function(exam) {
                        options += `<option value="${exam.examID}">${exam.exam_name}</option>`;
                    });
                    $('#filterExam').html(options);
                }
            })
            .fail(function() {
                $('#filterExam').html('<option value="">Error loading exams</option>');
            });
    }

    function loadExamAttendance(examID) {
        $('#attendanceTableBody').html('<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        $.get('/teacher/get-exam-attendance-data', {
            examID: examID,
            subjectID: subjectID,
            classSubjectID: classSubjectID
        })
            .done(function(response) {
                if (response.success && response.data) {
                    displayAttendanceData(response.data);
                } else {
                    $('#noDataMessage').show();
                    hideAttendanceData();
                }
            })
            .fail(function() {
                $('#attendanceTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading attendance data</td></tr>');
                hideAttendanceData();
            });
    }

    let studentsTable = null;
    let allStudentsData = [];
    let currentFilter = 'all'; // 'all', 'present', 'absent'

    function displayAttendanceData(data) {
        const tbody = $('#attendanceTableBody');
        tbody.empty();

        if (!data.subclasses || data.subclasses.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center text-muted">No attendance data available</td></tr>');
            hideAttendanceData();
            return;
        }

        // Store students data - ensure it's properly formatted
        allStudentsData = (data.students || []).map(function(student) {
            return {
                studentID: student.studentID,
                name: student.name || 'N/A',
                subclassID: student.subclassID,
                subclass_name: student.subclass_name,
                class_name: student.class_name,
                class_display: student.class_display || (student.class_name + ' - ' + student.subclass_name) || 'N/A',
                status: student.status || 'Absent' // Default to Absent if status is missing
            };
        });

        let totalPresent = 0;
        let totalAbsent = 0;
        let totalStudents = 0;

        data.subclasses.forEach(function(subclass, index) {
            const present = subclass.present || 0;
            const absent = subclass.absent || 0;
            const total = subclass.total || 0;
            const percentage = total > 0 ? Math.round((present / total) * 100) : 0;

            totalPresent += present;
            totalAbsent += absent;
            totalStudents += total;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${subclass.class_display || (subclass.class_name + ' - ' + subclass.subclass_name) || 'N/A'}</strong></td>
                    <td class="text-success"><strong>${present}</strong></td>
                    <td class="text-danger"><strong>${absent}</strong></td>
                    <td><strong>${total}</strong></td>
                    <td><strong>${percentage}%</strong></td>
                </tr>
            `;
            tbody.append(row);
        });

        // Update statistics
        const overallPercentage = totalStudents > 0 ? Math.round((totalPresent / totalStudents) * 100) : 0;
        $('#totalPresent').text(totalPresent);
        $('#totalAbsent').text(totalAbsent);
        $('#totalStudents').text(totalStudents);
        $('#attendancePercentage').text(overallPercentage + '%');

        // Show statistics and tables
        $('#attendanceStats').show();
        $('#attendanceTableContainer').show();
        $('#noDataMessage').hide();

        // Display students table
        displayStudentsTable('all');
    }

    function displayStudentsTable(filter) {
        currentFilter = filter;
        
        // Destroy existing DataTable if it exists
        if (studentsTable) {
            studentsTable.destroy();
            studentsTable = null;
        }

        // Filter students based on status
        let filteredStudents = allStudentsData;
        if (filter === 'present') {
            filteredStudents = allStudentsData.filter(s => s.status === 'Present' || s.status === 'present');
        } else if (filter === 'absent') {
            filteredStudents = allStudentsData.filter(s => {
                const status = (s.status || '').toLowerCase();
                return status === 'absent' || !status || status === '';
            });
        }

        const tbody = $('#studentsTableBody');
        tbody.empty();

        if (filteredStudents.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center text-muted">No students found</td></tr>');
            $('#studentsTableContainer').show();
            return;
        }

        filteredStudents.forEach(function(student, index) {
            const isAbsent = student.status === 'Absent' || !student.status;
            const rowClass = isAbsent ? 'absent-student-row' : '';
            const statusBadge = isAbsent 
                ? '<span class="badge bg-danger">Absent</span>' 
                : '<span class="badge bg-success">Present</span>';

            const row = `
                <tr class="${rowClass}">
                    <td>${index + 1}</td>
                    <td><strong>${student.name || 'N/A'}</strong></td>
                    <td>${student.class_display || (student.class_name + ' - ' + student.subclass_name) || 'N/A'}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
            tbody.append(row);
        });

        // Initialize DataTable
        studentsTable = $('#studentsTable').DataTable({
            responsive: true,
            paging: true,
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            order: [[1, 'asc']], // Sort by name
            columnDefs: [
                { orderable: false, targets: [0] }, // Disable sorting on #
                { searchable: true, targets: [1, 2] } // Enable search on Name and Class
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries to show",
                infoFiltered: "(filtered from _MAX_ total entries)"
            }
        });

        $('#studentsTableContainer').show();
    }

    function hideAttendanceData() {
        $('#attendanceStats').hide();
        $('#attendanceTableContainer').hide();
        $('#studentsTableContainer').hide();
        if (studentsTable) {
            studentsTable.destroy();
            studentsTable = null;
        }
    }

    // Click handlers for statistics cards - load data via AJAX
    $(document).on('click', '.clickable-stat', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const filter = $(this).data('filter'); // 'present' or 'absent'
        const examID = $('#filterExam').val();
        const year = $('#filterYear').val();
        const term = $('#filterTerm').val();
        
        // Check if exam is selected first
        if (!examID) {
            // Check if SweetAlert2 is available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Select Exam First',
                    text: 'Please select an exam from the filter above to view attendance data.',
                    icon: 'info',
                    confirmButtonColor: '#940000'
                });
            } else {
                alert('Please select an exam first to view attendance data.');
            }
            return;
        }
        
        // Show loading indicator immediately
        const tbody = $('#studentsTableBody');
        tbody.html('<tr><td colspan="4" class="text-center py-5"><div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;"><span class="sr-only">Loading...</span></div><div class="mt-3 text-muted"><strong>Loading students...</strong><br><small>Please wait while we fetch the data</small></div></td></tr>');
        $('#studentsTableContainer').show();
        
        // Scroll to students table to show loading
        $('html, body').animate({
            scrollTop: $('#studentsTableContainer').offset().top - 100
        }, 500);
        
        // Load data via AJAX
        $.get('/teacher/get-exam-attendance-data', {
            examID: examID,
            subjectID: subjectID,
            classSubjectID: classSubjectID
        })
        .done(function(response) {
            if (response.success && response.data && response.data.students) {
                // Store all students data
                allStudentsData = (response.data.students || []).map(function(student) {
                    return {
                        studentID: student.studentID,
                        name: student.name || 'N/A',
                        subclassID: student.subclassID,
                        subclass_name: student.subclass_name,
                        class_name: student.class_name,
                        class_display: student.class_display || (student.class_name + ' - ' + student.subclass_name) || 'N/A',
                        status: student.status || 'Absent'
                    };
                });
                
                // Display filtered students
                displayStudentsTable(filter);
            } else {
                tbody.html('<tr><td colspan="4" class="text-center text-muted py-4">No students data available for the selected exam.</td></tr>');
            }
        })
        .fail(function(xhr) {
            console.error('Error loading students:', xhr);
            tbody.html('<tr><td colspan="4" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle"></i> Error loading students data. Please try again.</td></tr>');
        });
    });
});
</script>

