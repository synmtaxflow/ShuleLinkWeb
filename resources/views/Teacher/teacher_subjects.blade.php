@include('includes.teacher_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Color scheme for #940000 */
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
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
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1), 0 4px 16px rgba(148, 0, 0, 0.08) !important;
        border: 1px solid rgba(148, 0, 0, 0.1) !important;
    }
    .subject-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }
    .action-icon {
        font-size: 1.3rem;
        cursor: pointer;
        transition: all 0.2s;
        padding: 8px;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .action-icon:hover {
        background-color: rgba(148, 0, 0, 0.1);
        transform: scale(1.1);
    }
    .action-icon.view-students {
        color: #17a2b8;
    }
    .action-icon.view-results {
        color: #28a745;
    }
    .action-icon.edit-results {
        color: #ffc107;
    }
    .action-icon.add-results {
        color: #940000;
    }
    .stat-badge {
        background-color: rgba(148, 0, 0, 0.1);
        color: #940000;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-primary-custom text-white rounded">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h4 class="mb-0">
                            <i class="bi bi-book"></i> My Subjects
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="subjectSearchInput">
                                    <i class="bi bi-search"></i> Search Subjects or Classes
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="subjectSearchInput" placeholder="Search by subject name, subject code, or class name...">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display: none;">
                                            <i class="bi bi-x-circle"></i> Clear
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle"></i> Type to filter subjects by name, code, or class name.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subjects Grid -->
            <div class="row" id="subjectsGrid">
                @if($classSubjects && $classSubjects->count() > 0)
                    @foreach($classSubjects as $classSubject)
                        @php
                            // Only display if subject exists and has Active status
                            if (!$classSubject->subject || $classSubject->subject->status !== 'Active' || $classSubject->status !== 'Active') {
                                continue;
                            }
                            $subjectName = strtolower($classSubject->subject->subject_name ?? '');
                            $subjectCode = strtolower($classSubject->subject->subject_code ?? '');
                            $className = $classSubject->subclass ? strtolower($classSubject->subclass->subclass_name ?? '') : 'all subclasses';
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-4 subject-item" 
                             data-subject-name="{{ $subjectName }}"
                             data-subject-code="{{ $subjectCode }}"
                             data-class-name="{{ $className }}">
                            <div class="card subject-card border-0 h-100">
                                <div class="card-body">
                                    <!-- Subject Icon and Name -->
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-book-half text-primary-custom" style="font-size: 2.5rem;"></i>
                                        <div class="ml-3">
                                            <h5 class="card-title text-primary-custom mb-0">
                                                {{ $classSubject->subject->subject_name ?? 'N/A' }}
                                            </h5>
                                            @if($classSubject->subject->subject_code)
                                                <small class="text-muted">Code: {{ $classSubject->subject->subject_code }}</small>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Statistics -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">
                                                <i class="bi bi-people"></i> Total Students:
                                            </span>
                                            <span class="stat-badge">{{ $classSubject->total_students ?? 0 }}</span>
                                        </div>

                                        @if($classSubject->subclass)
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">
                                                    <i class="bi bi-diagram-3"></i> Class:
                                                </span>
                                                <strong>
                                                    @if($classSubject->subclass->class)
                                                        {{ $classSubject->subclass->class->class_name }} - {{ $classSubject->subclass->subclass_name }}
                                                    @else
                                                        {{ $classSubject->subclass->subclass_name ?? 'N/A' }}
                                                    @endif
                                                </strong>
                                            </div>
                                        @else
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">
                                                    <i class="bi bi-diagram-3"></i> Class:
                                                </span>
                                                <strong class="text-info">
                                                    @if($classSubject->class)
                                                        {{ $classSubject->class->class_name }} - All Subclasses
                                                    @else
                                                        All Subclasses
                                                    @endif
                                                </strong>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Action Icons -->
                                    <div class="d-flex justify-content-around align-items-center pt-3 border-top flex-wrap">
                                        <div class="text-center mb-2" title="View Students">
                                            <i class="bi bi-people-fill action-icon view-students" onclick="viewStudents({{ $classSubject->class_subjectID }})"></i>
                                            <small class="d-block text-muted mt-1">Students</small>
                                        </div>
                                        <div class="text-center mb-2" title="Session Attendance">
                                            <i class="bi bi-clock-history action-icon" style="color: #17a2b8;" onclick="viewSessionAttendance({{ $classSubject->class_subjectID }})"></i>
                                            <small class="d-block text-muted mt-1">Session Attendance</small>
                                        </div>
                                        <div class="text-center mb-2" title="Exam Attendance">
                                            <i class="bi bi-calendar-check action-icon" style="color: #940000;" onclick="viewExamAttendance({{ $classSubject->class_subjectID }}, {{ $classSubject->subject->subjectID ?? 0 }})"></i>
                                            <small class="d-block text-muted mt-1">Exam Attendance</small>
                                        </div>
                                        <div class="text-center mb-2" title="View Results">
                                            <i class="bi bi-clipboard-check action-icon view-results" onclick="viewResults({{ $classSubject->class_subjectID }})"></i>
                                            <small class="d-block text-muted mt-1">View Results</small>
                                        </div>
                                        <div class="text-center mb-2" title="Edit Results">
                                            <i class="bi bi-pencil-square action-icon edit-results" onclick="editResults({{ $classSubject->class_subjectID }})"></i>
                                            <small class="d-block text-muted mt-1">Edit Result</small>
                                        </div>
                                        <div class="text-center mb-2" title="Add Results">
                                            <i class="bi bi-plus-circle-fill action-icon add-results" onclick="addResults({{ $classSubject->class_subjectID }})"></i>
                                            <small class="d-block text-muted mt-1">Add Result</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> No subjects assigned to you yet.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- View Students Modal -->
<div class="modal fade" id="viewStudentsModal" tabindex="-1" role="dialog" aria-labelledby="viewStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewStudentsModalLabel">
                    <i class="bi bi-people"></i> Students
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="studentsModalBody" style="max-height: 80vh; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #viewStudentsModal .modal-body::-webkit-scrollbar {
                        width: 0px;
                        background: transparent;
                    }
                    #viewStudentsModal .modal-body::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #viewStudentsModal .modal-body::-webkit-scrollbar-thumb {
                        background: transparent;
                    }
                </style>
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Attendance Modal -->
<div class="modal fade" id="sessionAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="sessionAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="sessionAttendanceModalLabel">
                    <i class="bi bi-clock-history"></i> Session Attendance
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="sessionAttendanceModalBody" style="max-height: 80vh; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #sessionAttendanceModal .modal-body::-webkit-scrollbar {
                        width: 0px;
                        background: transparent;
                    }
                    #sessionAttendanceModal .modal-body::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #sessionAttendanceModal .modal-body::-webkit-scrollbar-thumb {
                        background: transparent;
                    }
                </style>
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Exam Attendance Modal -->
<div class="modal fade" id="examAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="examAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="examAttendanceModalLabel">
                    <i class="bi bi-calendar-check"></i> Exam Attendance
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="examAttendanceModalBody" style="max-height: 80vh; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #examAttendanceModal .modal-body::-webkit-scrollbar {
                        width: 0px;
                        background: transparent;
                    }
                    #examAttendanceModal .modal-body::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #examAttendanceModal .modal-body::-webkit-scrollbar-thumb {
                        background: transparent;
                    }
                </style>
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Results Modal -->
<div class="modal fade" id="viewResultsModal" tabindex="-1" role="dialog" aria-labelledby="viewResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewResultsModalLabel">
                    <i class="bi bi-clipboard-check"></i> Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="resultsModalBody" style="max-height: 80vh; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #viewResultsModal .modal-body::-webkit-scrollbar {
                        width: 0px;
                        background: transparent;
                    }
                    #viewResultsModal .modal-body::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #viewResultsModal .modal-body::-webkit-scrollbar-thumb {
                        background: transparent;
                    }
                </style>
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Results Modal -->
<div class="modal fade" id="addEditResultsModal" tabindex="-1" role="dialog" aria-labelledby="addEditResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addEditResultsModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="addEditResultsModalBody" style="max-height: 80vh; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #addEditResultsModal .modal-body::-webkit-scrollbar {
                        width: 0px;
                        background: transparent;
                    }
                    #addEditResultsModal .modal-body::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #addEditResultsModal .modal-body::-webkit-scrollbar-thumb {
                        background: transparent;
                    }
                </style>
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Excel Modal -->
<div class="modal fade" id="uploadExcelModal" tabindex="-1" role="dialog" aria-labelledby="uploadExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="uploadExcelModalLabel">
                    <i class="bi bi-upload"></i> Upload Excel Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="uploadExcelForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="upload_class_subject_id" name="class_subject_id">
                    <input type="hidden" id="upload_exam_id" name="exam_id">
                    <div class="form-group">
                        <label>Select Microsoft Excel Worksheet File <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required>
                            <label class="custom-file-label" for="excel_file" id="excel_file_label">
                                <i class="bi bi-file-earmark-excel"></i> Choose Excel file (.xlsx or .xls)
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Only Microsoft Excel Worksheet files (.xlsx or .xls) are allowed.
                        </small>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Note:</strong> Make sure the Excel file matches the downloaded template format.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-upload"></i> Upload & Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const isSecondarySchool = @json(strtolower($schoolType ?? 'Secondary')) === 'secondary';
const questionColspan = isSecondarySchool ? 7 : 6;
let examQuestionData = {
    questions: [],
    marksByStudent: {},
    maxTotal: 0,
    optionalTotal: 0,
    optionalRanges: []
};

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Initialize search functionality
    initializeSubjectSearch();
});

// Initialize Subject Search
function initializeSubjectSearch() {
    $('#subjectSearchInput').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase().trim();
        
        if (searchTerm.length > 0) {
            $('#clearSearchBtn').show();
        } else {
            $('#clearSearchBtn').hide();
        }
        
        // Filter subject cards
        $('.subject-item').each(function() {
            var subjectName = $(this).data('subject-name') || '';
            var subjectCode = $(this).data('subject-code') || '';
            var className = $(this).data('class-name') || '';
            
            var matches = subjectName.includes(searchTerm) || 
                         subjectCode.includes(searchTerm) || 
                         className.includes(searchTerm);
            
            if (matches) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Show message if no results
        var visibleCount = $('.subject-item:visible').length;
        
        if (searchTerm.length > 0 && visibleCount === 0) {
            if ($('#noResultsMessage').length === 0) {
                $('#subjectsGrid').append(
                    '<div class="col-12" id="noResultsMessage">' +
                    '<div class="alert alert-info text-center">' +
                    '<i class="bi bi-info-circle"></i> No subjects or classes found matching "' + searchTerm + '".' +
                    '</div>' +
                    '</div>'
                );
            }
        } else {
            $('#noResultsMessage').remove();
        }
    });
    
    // Clear search button
    $('#clearSearchBtn').on('click', function() {
        $('#subjectSearchInput').val('');
        $('#clearSearchBtn').hide();
        $('.subject-item').show();
        $('#noResultsMessage').remove();
    });
}

// Show student photo in larger view
function showStudentPhoto(photoUrl, studentName) {
    Swal.fire({
        title: studentName,
        imageUrl: photoUrl,
        imageWidth: 300,
        imageHeight: 300,
        imageAlt: 'Student Photo',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            popup: 'swal2-popup-custom'
        }
    });
}

// Calculate grade and remark from marks
function calculateGradeAndRemark(marks) {
    if (!marks || marks === '' || isNaN(marks)) {
        return { grade: '', remark: '' };
    }

    const marksNum = parseFloat(marks);

    if (marksNum >= 75) {
        return { grade: 'A', remark: 'Excellent' };
    } else if (marksNum >= 65) {
        return { grade: 'B', remark: 'Very Good' };
    } else if (marksNum >= 45) {
        return { grade: 'C', remark: 'Good' };
    } else if (marksNum >= 30) {
        return { grade: 'D', remark: 'Pass' };
    } else {
        return { grade: 'F', remark: 'Fail' };
    }
}

// Get grade cell class for styling
function getGradeCellClass(grade) {
    if (!grade || grade === '' || grade.toLowerCase() === 'incomplete') {
        return 'bg-warning text-dark';
    }
    const gradeUpper = grade.toUpperCase();
    if (gradeUpper === 'A') {
        return 'bg-success text-white';
    } else if (gradeUpper === 'E' || gradeUpper === 'F') {
        return 'bg-danger text-white';
    }
    return 'bg-info text-white';
}

function resetQuestionData() {
    examQuestionData = {
        questions: [],
        marksByStudent: {},
        maxTotal: 0,
        optionalTotal: 0,
        optionalRanges: []
    };
    if (isSecondarySchool) {
        $('.question-details-row').addClass('d-none').removeClass('loaded');
        $('.question-detail-container').html('<div class="text-muted small">Select examination to load question formats.</div>');
    }
}

function loadExamQuestionData(classSubjectID, examID) {
    if (!isSecondarySchool) {
        return;
    }

    resetQuestionData();

    if (!examID) {
        return;
    }

    $.ajax({
        url: `/get_exam_paper_question_data/${classSubjectID}/${examID}`,
        method: 'GET',
        success: function(response) {
            if (!response.success) {
                return;
            }

            examQuestionData.questions = response.questions || [];
            examQuestionData.marksByStudent = response.marks_by_student || {};
            examQuestionData.maxTotal = response.max_total || 0;
            examQuestionData.optionalTotal = response.optional_total || 0;
            examQuestionData.optionalRanges = response.optional_ranges || [];

            if (examQuestionData.questions.length === 0) {
                $('.question-detail-container').html(
                    '<div class="alert alert-warning mb-0">' +
                    '<i class="bi bi-exclamation-triangle"></i> No approved exam paper question formats found for this subject.' +
                    '</div>'
                );
                return;
            }

            updateTotalsFromCache();
        },
        error: function() {
            $('.question-detail-container').html(
                '<div class="alert alert-danger mb-0">' +
                '<i class="bi bi-exclamation-triangle"></i> Failed to load question formats.' +
                '</div>'
            );
        }
    });
}

function buildQuestionDetails(studentID) {
    if (!isSecondarySchool) {
        return '';
    }

    if (!examQuestionData.questions || examQuestionData.questions.length === 0) {
        return `
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle"></i> No question formats available.
            </div>
        `;
    }

    let html = '<div class="question-list">';
    examQuestionData.questions.forEach(function(question) {
        const existingMarks = examQuestionData.marksByStudent[studentID] &&
            examQuestionData.marksByStudent[studentID][question.exam_paper_questionID] !== undefined
            ? examQuestionData.marksByStudent[studentID][question.exam_paper_questionID]
            : '';
        let optionalTag = '';
        if (question.is_optional) {
            const rangeLabel = question.optional_range_number ? `Opt ${question.optional_range_number}` : 'Optional';
            optionalTag = `<span class="badge badge-warning ml-2">${rangeLabel}</span>`;
        }
        html += `
            <div class="form-row align-items-end mb-2">
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Qn ${question.question_number}</label>
                </div>
                <div class="col-md-6">
                    <div class="small font-weight-bold">${question.question_description} ${optionalTag}</div>
                    <div class="small text-muted">Max: ${question.marks}</div>
                </div>
                <div class="col-md-4">
                    ${question.is_optional ? `
                        <div class="form-check mb-1">
                            <input class="form-check-input optional-select" type="checkbox"
                                   data-student="${studentID}"
                                   data-question-id="${question.exam_paper_questionID}"
                                   data-optional-range="${question.optional_range_number || 0}">
                            <label class="form-check-label small">Selected</label>
                        </div>
                    ` : ''}
                    <input type="number"
                           class="form-control form-control-sm question-mark-input"
                           data-student="${studentID}"
                           data-question-id="${question.exam_paper_questionID}"
                           data-optional="${question.is_optional ? 1 : 0}"
                           data-optional-range="${question.optional_range_number || 0}"
                           data-max="${question.marks}"
                           min="0"
                           max="${question.marks}"
                           step="0.01"
                           value="${existingMarks}"
                           ${question.is_optional ? 'disabled' : ''}>
                    <div class="text-danger small question-max-warning d-none"></div>
                </div>
            </div>
        `;
    });
    html += `
        </div>
        <div class="text-muted small mt-2">
            Total: <span class="student-question-total" data-student="${studentID}">0</span> / ${examQuestionData.maxTotal || 100}
        </div>
        ${examQuestionData.optionalTotal > 0 ? `
            <div class="text-muted small">
                Optional Total: ${examQuestionData.optionalTotal}
            </div>
        ` : ''}
        <div class="text-danger small optional-total-warning d-none" data-student="${studentID}"></div>
    `;

    return html;
}

function updateTotalsFromCache() {
    if (!isSecondarySchool || !examQuestionData.marksByStudent) {
        return;
    }

    Object.keys(examQuestionData.marksByStudent).forEach(function(studentID) {
        const questionMarks = examQuestionData.marksByStudent[studentID] || {};
        let total = 0;
        let hasMarks = false;
        Object.keys(questionMarks).forEach(function(questionId) {
            const value = parseFloat(questionMarks[questionId]);
            if (!isNaN(value)) {
                total += value;
                hasMarks = true;
            }
        });

        if (hasMarks) {
            const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
            $(`#marks_${studentID}`).val(displayTotal);
            autoCalculateGrade(studentID);
        }
    });
}

function updateStudentTotal(studentID) {
    let total = 0;
    let requiredTotal = 0;
    let optionalTotal = 0;
    let optionalTotalsByRange = {};
    let hasMarks = false;
    const optionalSelectedByRange = {};
    $(`.question-mark-input[data-student="${studentID}"]`).each(function() {
        const value = parseFloat($(this).val());
        const isOptional = $(this).data('optional') == 1;
        const rangeNumber = parseInt($(this).data('optional-range'), 10);
        if (!isNaN(value)) {
            total += value;
            if (isOptional) {
                optionalTotal += value;
                if (rangeNumber > 0) {
                    optionalTotalsByRange[rangeNumber] = (optionalTotalsByRange[rangeNumber] || 0) + value;
                }
            } else {
                requiredTotal += value;
            }
            hasMarks = true;
        }
    });

    $(`.optional-select[data-student="${studentID}"]`).each(function() {
        const rangeNumber = parseInt($(this).data('optional-range'), 10);
        if (rangeNumber > 0 && $(this).is(':checked')) {
            optionalSelectedByRange[rangeNumber] = (optionalSelectedByRange[rangeNumber] || 0) + 1;
        }
    });

    const $optionalWarning = $(`.optional-total-warning[data-student="${studentID}"]`);
    const rangeExceeded = Object.keys(optionalTotalsByRange).some(function(range) {
        const sum = optionalTotalsByRange[range];
        const rangeTotal = (examQuestionData.optionalRanges || []).find(r => r.range_number == range);
        return rangeTotal && sum > parseFloat(rangeTotal.total_marks);
    });

    const selectionExceeded = Object.keys(optionalSelectedByRange).some(function(range) {
        const selected = optionalSelectedByRange[range];
        const rangeMeta = (examQuestionData.optionalRanges || []).find(r => r.range_number == range);
        const requiredCount = rangeMeta ? parseInt(rangeMeta.required_questions || 0, 10) : 0;
        return requiredCount > 0 && selected > requiredCount;
    });

    if (selectionExceeded) {
        $optionalWarning.text('Optional questions selected exceed required count.').removeClass('d-none');
    } else if (rangeExceeded) {
        $optionalWarning.text('Optional marks exceed allowed range total.').removeClass('d-none');
    } else if (examQuestionData.optionalTotal > 0 && optionalTotal > examQuestionData.optionalTotal) {
        $optionalWarning.text('Optional marks exceed allowed total.').removeClass('d-none');
    } else {
        $optionalWarning.addClass('d-none').text('');
    }

    const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
    $(`.student-question-total[data-student="${studentID}"]`).text(hasMarks ? displayTotal : 0);
    $(`#marks_${studentID}`).val(hasMarks ? displayTotal : '');
    autoCalculateGrade(studentID);
}

// View Students
function viewStudents(classSubjectID) {
    $('#viewStudentsModal').modal('show');
    $('#studentsModalBody').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

    $.ajax({
        url: '/get_subject_students/' + classSubjectID,
        method: 'GET',
        success: function(response) {
            if (response.success && response.students) {
                let html = `
                    <div class="mb-3">
                        <h6 class="text-primary-custom">
                            <i class="bi bi-book"></i> ${response.class_subject.subject ? response.class_subject.subject.subject_name : 'Subject'}
                            <span class="badge badge-primary ml-2">${response.students.length} Students</span>
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Admission No.</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Date of Birth</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                response.students.forEach(function(student, index) {
                    // Get student photo or default based on gender
                    const baseUrl = '{{ asset("") }}';
                    let photoUrl = '';
                    if (student.photo) {
                        photoUrl = baseUrl + 'userImages/' + student.photo;
                    } else {
                        photoUrl = student.gender === 'Female'
                            ? baseUrl + 'images/female.png'
                            : baseUrl + 'images/male.png';
                    }

                    const dob = student.date_of_birth ? new Date(student.date_of_birth).toLocaleDateString() : 'N/A';
                    const studentName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');
                    const fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';
                    
                    // Add red alarm icon if student has health conditions
                    let healthAlarmIcon = '';
                    if ((student.is_disabled && student.is_disabled == 1) || 
                        (student.has_epilepsy && student.has_epilepsy == 1) || 
                        (student.has_allergies && student.has_allergies == 1)) {
                        healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                    }

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <img src="${photoUrl}"
                                     alt="Student Photo"
                                     class="rounded-circle"
                                     style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #940000; cursor: pointer;"
                                     onclick="showStudentPhoto('${photoUrl.replace(/'/g, "\\'")}', '${studentName.replace(/'/g, "\\'")}')"
                                     onerror="this.src='${fallbackPhoto}'">
                            </td>
                            <td><strong>${student.admission_number || 'N/A'}</strong></td>
                            <td>${studentName}${healthAlarmIcon}</td>
                            <td>${student.subclass ? (student.subclass.subclass_name || 'N/A') : 'N/A'}</td>
                            <td>${student.gender || 'N/A'}</td>
                            <td>${dob}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                $('#studentsModalBody').html(html);
            } else {
                $('#studentsModalBody').html('<div class="alert alert-info">No students found.</div>');
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr.responseJSON?.error || 'Failed to load students',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            $('#viewStudentsModal').modal('hide');
        }
    });
}

// View Results
function viewResults(classSubjectID) {
    $('#viewResultsModal').modal('show');
    $('#resultsModalBody').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

    // First get examinations for this subject
    $.ajax({
        url: '/get_examinations_for_subject/' + classSubjectID,
        method: 'GET',
        success: function(examResponse) {
            if (examResponse.success && examResponse.examinations && examResponse.examinations.length > 0) {
                // Show exam selector
                let html = `
                    <div class="mb-3">
                        <label>Select Examination:</label>
                        <select class="form-control" id="examSelector" onchange="loadResultsForExam(${classSubjectID}, this.value)">
                            <option value="">All Examinations</option>
                `;

                examResponse.examinations.forEach(function(exam) {
                    // Only show examinations where enter_result is true
                    if (exam.enter_result === true || exam.enter_result === 1) {
                        html += `<option value="${exam.examID}">${exam.exam_name} (${exam.year})</option>`;
                    }
                });

                html += `
                        </select>
                    </div>
                    <div id="resultsContent"></div>
                `;

                $('#resultsModalBody').html(html);
                loadResultsForExam(classSubjectID, '');
            } else {
                $('#resultsModalBody').html('<div class="alert alert-info">No examinations found for this subject.</div>');
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr.responseJSON?.error || 'Failed to load examinations',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

function loadResultsForExam(classSubjectID, examID) {
    const url = examID ? `/get_subject_results/${classSubjectID}/${examID}` : `/get_subject_results/${classSubjectID}`;

    $('#resultsContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            if (response.success && response.results) {
                const baseUrl = '{{ asset("") }}';
                let html = `
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Student Name</th>
                                    <th>Admission No.</th>
                                    <th>Examination</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                response.results.forEach(function(result, index) {
                    const student = result.student || {};
                    const studentName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');
                    
                    // Add red alarm icon if student has health conditions
                    let healthAlarmIcon = '';
                    if ((student.is_disabled && student.is_disabled == 1) || 
                        (student.has_epilepsy && student.has_epilepsy == 1) || 
                        (student.has_allergies && student.has_allergies == 1)) {
                        healthAlarmIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" title="Health Condition Alert"></i>';
                    }

                    // Get student photo or default based on gender
                    let photoUrl = '';
                    if (student.photo) {
                        photoUrl = baseUrl + 'userImages/' + student.photo;
                    } else {
                        photoUrl = student.gender === 'Female'
                            ? baseUrl + 'images/female.png'
                            : baseUrl + 'images/male.png';
                    }
                    const fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';

                    // Handle marks, grade, and remark
                    const marks = result.marks !== null && result.marks !== '' ? result.marks : null;
                    let grade = result.grade || '';
                    let remark = result.remark || '';

                    // If marks not filled, show Incomplete
                    if (marks === null) {
                        grade = 'Incomplete';
                        remark = 'Incomplete';
                    }

                    // Get grade cell class for styling
                    const gradeClass = getGradeCellClass(grade);

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <img src="${photoUrl}"
                                     alt="Student Photo"
                                     class="rounded-circle"
                                     style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #940000; cursor: pointer;"
                                     onclick="showStudentPhoto('${photoUrl.replace(/'/g, "\\'")}', '${studentName.replace(/'/g, "\\'")}')"
                                     onerror="this.src='${fallbackPhoto}'">
                            </td>
                            <td>${studentName || 'N/A'}${healthAlarmIcon}</td>
                            <td>${student.admission_number || 'N/A'}</td>
                            <td>${result.examination ? result.examination.exam_name : 'N/A'}</td>
                            <td><strong>${marks !== null ? marks : '<span class="text-muted">-</span>'}</strong></td>
                            <td><span class="badge ${gradeClass}" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">${grade || '<span class="text-muted">-</span>'}</span></td>
                            <td>${remark || '<span class="text-muted">-</span>'}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                $('#resultsContent').html(html);
            } else {
                $('#resultsContent').html('<div class="alert alert-info">No results found.</div>');
            }
        },
        error: function(xhr) {
            $('#resultsContent').html('<div class="alert alert-danger">Error loading results.</div>');
        }
    });
}

// Removed checkExamStatusForAddEdit function - now only checking enter_result

// Edit Results
function editResults(classSubjectID) {
    // Clear exam data cache when opening modal
    window.examDataCache = {};
    resetQuestionData();
    
    // First check if there are any examinations with enter_result = true
    $.ajax({
        url: '/get_examinations_for_subject/' + classSubjectID,
        method: 'GET',
        success: function(examsResponse) {
            // Check if there are any examinations with enter_result = true
            let hasEnterResultEnabled = false;
            if (examsResponse.success && examsResponse.examinations && examsResponse.examinations.length > 0) {
                hasEnterResultEnabled = examsResponse.examinations.some(function(exam) {
                    return exam.enter_result === true || exam.enter_result === 1;
                });
            }

            if (!hasEnterResultEnabled) {
                Swal.fire({
                    title: 'Access Denied!',
                    text: 'You are not allowed to edit results. Result entry has been disabled for all examinations.',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            // If there are examinations with enter_result = true, proceed with opening modal
            const modalTitle = 'Edit Results';
            $('#addEditResultsModalLabel').html(`<i class="bi bi-pencil"></i> ${modalTitle}`);
            $('#addEditResultsModal').modal('show');
            $('#addEditResultsModalBody').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

            // Get students, examinations, and existing results
            $.ajax({
                url: '/get_subject_students/' + classSubjectID,
                method: 'GET',
                success: function(studentsResponse) {
                    // Get all results for this subject
                    $.ajax({
                        url: '/get_subject_results/' + classSubjectID,
                        method: 'GET',
                        success: function(resultsResponse) {
                            let html = `
                                <form id="resultsForm">
                                    <input type="hidden" id="class_subject_id" value="${classSubjectID}">
                                    <div class="form-group">
                                        <label>Select Examination <span class="text-danger">*</span></label>
                                        <select class="form-control" id="exam_id" name="exam_id" required onchange="handleExamSelection(${classSubjectID}, this.value)">
                                            <option value="">Select Examination</option>
                            `;

                            // Store exam data globally for use in handleExamSelection
                            window.examDataCache = window.examDataCache || {};
                            
                            if (examsResponse.success && examsResponse.examinations) {
                                examsResponse.examinations.forEach(function(exam) {
                                    // Only show examinations where enter_result is true
                                    if (exam.enter_result === true || exam.enter_result === 1) {
                                        // Store exam data in cache
                                        window.examDataCache[exam.examID] = exam;
                                        
                                        const statusText = exam.status === 'awaiting_results' ? ' (Awaiting Results)' :
                                                          exam.status === 'ongoing' ? ' (Ongoing)' : ' (Results Available)';
                                        const termClosedText = exam.is_term_closed ? ' (Term Closed - Not Editable)' : '';
                                        const disabledAttr = exam.is_term_closed ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '';
                                        html += `<option value="${exam.examID}" data-status="${exam.status}" data-term-closed="${exam.is_term_closed}" data-enter-result="${exam.enter_result}" ${disabledAttr}>${exam.exam_name} (${exam.year})${statusText}${termClosedText}</option>`;
                                    }
                                });
                            }

                            html += `
                                        </select>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle"></i> Only examinations with "Enter Result" enabled can be edited.
                                        </small>
                                    </div>
                                    ${isSecondarySchool ? `
                                        <div class="alert alert-warning mt-3">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Question-based results are enabled. Excel import/export is disabled.
                                        </div>
                                    ` : `
                                        <div class="alert alert-info mt-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-excel"></i>
                                                    <strong>Excel Import/Export:</strong> Download template, fill in results, then upload.
                                                </div>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-success" onclick="downloadExcelTemplate(${classSubjectID})" id="downloadExcelBtn" disabled>
                                                        <i class="bi bi-download"></i> Download Excel Template
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="showUploadExcelModal(${classSubjectID})" id="uploadExcelBtn" disabled>
                                                        <i class="bi bi-upload"></i> Upload Excel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    `}
                                    <div class="table-responsive mt-3">
                                        <table class="table table-hover">
                                            <thead class="bg-primary-custom text-white">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Student Name</th>
                                                    <th>Admission No.</th>
                                                    ${isSecondarySchool ? '<th class="text-center"><i class="bi bi-list-check"></i></th>' : ''}
                                                    <th>Marks</th>
                                                    <th>Grade</th>
                                                    <th>Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody id="resultsTableBody">
                            `;

                            if (studentsResponse.success && studentsResponse.students) {
                                studentsResponse.students.forEach(function(student, index) {
                                    html += `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${student.first_name} ${student.middle_name || ''} ${student.last_name}</td>
                                            <td>${student.admission_number || 'N/A'}</td>
                                            ${isSecondarySchool ? `
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary toggle-question-btn" data-student="${student.studentID}" title="Add result questions">
                                                        <i class="bi bi-list-check"></i>
                                                    </button>
                                                </td>
                                            ` : ''}
                                            <td>
                                                <input type="number" class="form-control form-control-sm marks-input"
                                                       name="marks[${student.studentID}]"
                                                       id="marks_${student.studentID}"
                                                       data-student="${student.studentID}"
                                                       step="0.01" min="0" max="100" placeholder="0.00"
                                                       oninput="autoCalculateGrade(${student.studentID})"
                                                       ${isSecondarySchool ? 'readonly' : ''}>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm grade-input"
                                                       name="grade[${student.studentID}]"
                                                       id="grade_${student.studentID}"
                                                       data-student="${student.studentID}"
                                                       placeholder="A, B, C..." readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark-input"
                                                       name="remark[${student.studentID}]"
                                                       id="remark_${student.studentID}"
                                                       data-student="${student.studentID}"
                                                       placeholder="Remark" readonly>
                                            </td>
                                        </tr>
                                        ${isSecondarySchool ? `
                                            <tr class="question-details-row d-none" id="question_details_${student.studentID}" data-student="${student.studentID}">
                                                <td colspan="${questionColspan}">
                                                    <div class="question-detail-container" data-student="${student.studentID}">
                                                        <div class="text-muted small">Select examination to load question formats.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        ` : ''}
                                    `;
                                });
                            }

                            html += `
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary-custom">
                                            <i class="bi bi-check-circle"></i> Update Results
                                        </button>
                                    </div>
                                </form>
                            `;

                            $('#addEditResultsModalBody').html(html);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.error || 'Failed to load results',
                                icon: 'error',
                                confirmButtonColor: '#940000'
                            });
                        }
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.error || 'Failed to load examinations',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr.responseJSON?.error || 'Failed to load students',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

function loadExistingResults(classSubjectID, examID) {
    if (!examID) {
        // Clear all inputs
        $('.marks-input, .grade-input, .remark-input').val('');
        return;
    }

    $.ajax({
        url: `/get_subject_results/${classSubjectID}/${examID}`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.results) {
                // Clear all inputs first
                $('.marks-input, .grade-input, .remark-input').val('');

                // Populate with existing results
                response.results.forEach(function(result) {
                    if (result.studentID) {
                        const marks = result.marks || '';
                        $(`#marks_${result.studentID}`).val(marks);
                        // Auto-calculate grade and remark if marks exist
                        if (marks) {
                            autoCalculateGrade(result.studentID);
                        } else {
                            $(`#grade_${result.studentID}`).val(result.grade || '');
                            $(`#remark_${result.studentID}`).val(result.remark || '');
                        }
                    }
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading existing results:', xhr);
        }
    });
}

// Auto-calculate grade and remark when marks are entered
function autoCalculateGrade(studentID) {
    const marksInput = $(`#marks_${studentID}`);
    const gradeInput = $(`#grade_${studentID}`);
    const remarkInput = $(`#remark_${studentID}`);

    const marks = marksInput.val();

    if (marks && marks !== '' && !isNaN(marks)) {
        const result = calculateGradeAndRemark(marks);
        gradeInput.val(result.grade);
        remarkInput.val(result.remark);
    } else {
        gradeInput.val('');
        remarkInput.val('');
    }
}

// Add Results
function addResults(classSubjectID, isEdit = false) {
    const modalTitle = isEdit ? 'Edit Results' : 'Add Results';
    $('#addEditResultsModalLabel').html(`<i class="bi bi-${isEdit ? 'pencil' : 'plus-circle'}"></i> ${modalTitle}`);
    $('#addEditResultsModal').modal('show');
    $('#addEditResultsModalBody').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

    // Clear exam data cache when opening modal
    window.examDataCache = {};
    resetQuestionData();

    // Get students and examinations for this subject
    $.ajax({
        url: '/get_subject_students/' + classSubjectID,
        method: 'GET',
        success: function(studentsResponse) {
            $.ajax({
                url: '/get_examinations_for_subject/' + classSubjectID,
                method: 'GET',
                success: function(examsResponse) {
                    let html = `
                        <form id="resultsForm">
                            <input type="hidden" id="class_subject_id" value="${classSubjectID}">
                            <div class="form-group">
                                <label>Select Examination <span class="text-danger">*</span></label>
                                <select class="form-control" id="exam_id" name="exam_id" required onchange="handleExamSelection(${classSubjectID}, this.value)">
                                    <option value="">Select Examination</option>
                    `;

                    // Store exam data globally for use in handleExamSelection
                    window.examDataCache = window.examDataCache || {};
                    
                    if (examsResponse.success && examsResponse.examinations) {
                        examsResponse.examinations.forEach(function(exam) {
                            // Only show examinations where enter_result is true
                            if (exam.enter_result === true || exam.enter_result === 1) {
                                // Store exam data in cache
                                window.examDataCache[exam.examID] = exam;
                                
                                const statusText = exam.status === 'awaiting_results' ? ' (Awaiting Results)' :
                                                  exam.status === 'ongoing' ? ' (Ongoing)' : ' (Results Available)';
                                const termClosedText = exam.is_term_closed ? ' (Term Closed - Not Editable)' : '';
                                const disabledAttr = exam.is_term_closed ? 'disabled style="background-color: #f0f0f0; color: #999;"' : '';
                                html += `<option value="${exam.examID}" data-status="${exam.status}" data-term-closed="${exam.is_term_closed}" data-enter-result="${exam.enter_result}" ${disabledAttr}>${exam.exam_name} (${exam.year})${statusText}${termClosedText}</option>`;
                            }
                        });
                    }

                    html += `
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle"></i> Only examinations with "Enter Result" enabled can be used.
                                </small>
                            </div>
                            ${isSecondarySchool ? `
                                <div class="alert alert-warning mt-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Question-based results are enabled. Excel import/export is disabled.
                                </div>
                            ` : `
                                <div class="alert alert-info mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-file-earmark-excel"></i>
                                            <strong>Excel Import/Export:</strong> Download template, fill in results, then upload.
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-success" onclick="downloadExcelTemplate(${classSubjectID})" id="downloadExcelBtn" disabled>
                                                <i class="bi bi-download"></i> Download Excel Template
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="showUploadExcelModal(${classSubjectID})" id="uploadExcelBtn" disabled>
                                                <i class="bi bi-upload"></i> Upload Excel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `}
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Admission No.</th>
                                            ${isSecondarySchool ? '<th class="text-center"><i class="bi bi-list-check"></i></th>' : ''}
                                            <th>Marks</th>
                                            <th>Grade</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody id="resultsTableBody">
                    `;

                    if (studentsResponse.success && studentsResponse.students) {
                        studentsResponse.students.forEach(function(student, index) {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${student.first_name} ${student.middle_name || ''} ${student.last_name}</td>
                                    <td>${student.admission_number || 'N/A'}</td>
                                    ${isSecondarySchool ? `
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary toggle-question-btn" data-student="${student.studentID}" title="Add result questions">
                                                <i class="bi bi-list-check"></i>
                                            </button>
                                        </td>
                                    ` : ''}
                                    <td>
                                        <input type="number" class="form-control form-control-sm marks-input"
                                               name="marks[${student.studentID}]"
                                               id="marks_${student.studentID}"
                                               data-student="${student.studentID}"
                                               step="0.01" min="0" max="100" placeholder="0.00"
                                               oninput="autoCalculateGrade(${student.studentID})"
                                               ${isSecondarySchool ? 'readonly' : ''}>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm grade-input"
                                               name="grade[${student.studentID}]"
                                               id="grade_${student.studentID}"
                                               data-student="${student.studentID}"
                                               placeholder="A, B, C..." readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm remark-input"
                                               name="remark[${student.studentID}]"
                                               id="remark_${student.studentID}"
                                               data-student="${student.studentID}"
                                               placeholder="Remark" readonly>
                                    </td>
                                </tr>
                                ${isSecondarySchool ? `
                                    <tr class="question-details-row d-none" id="question_details_${student.studentID}" data-student="${student.studentID}">
                                        <td colspan="${questionColspan}">
                                            <div class="question-detail-container" data-student="${student.studentID}">
                                                <div class="text-muted small">Select examination to load question formats.</div>
                                            </div>
                                        </td>
                                    </tr>
                                ` : ''}
                            `;
                        });
                    }

                    html += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-check-circle"></i> ${isEdit ? 'Update' : 'Save'} Results
                                </button>
                            </div>
                        </form>
                    `;

                    $('#addEditResultsModalBody').html(html);
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.error || 'Failed to load examinations',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr.responseJSON?.error || 'Failed to load students',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

// Form submission for results
$(document).on('submit', '#resultsForm', function(e) {
    e.preventDefault();

    const classSubjectID = $('#class_subject_id').val();
    const examID = $('#exam_id').val();

    // Check if selected exam is in a closed term
    const selectedExam = $('#exam_id option:selected');
    const isTermClosed = selectedExam.data('term-closed');
    if (isTermClosed) {
        Swal.fire({
            title: 'Access Denied!',
            text: 'You cannot edit results for this examination. The term has been closed.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return false;
    }

    // Check only if form is disabled (which happens if enter_result is false)
    const formDisabled = $('#resultsForm button[type="submit"]').prop('disabled');
    if (formDisabled) {
        Swal.fire({
            title: 'Access Denied!',
            text: 'You are not allowed to enter results for this examination. Result entry has been disabled.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return false;
    }

    const results = [];

    if (isSecondarySchool) {
        if (!examQuestionData.questions || examQuestionData.questions.length === 0) {
            Swal.fire({
                title: 'Warning!',
                text: 'No question formats found for this examination.',
                icon: 'warning',
                confirmButtonColor: '#940000'
            });
            return;
        }

        let hasValidationError = false;
        $('.marks-input').each(function() {
            const studentID = $(this).data('student');
            const questionMarks = examQuestionData.marksByStudent[studentID] || {};
            const optionalRanges = examQuestionData.optionalRanges || [];
            const hasAnyMarks = Object.keys(questionMarks).length > 0;
            const selectedOptionalCounts = {};

            $(`.optional-select[data-student="${studentID}"]:checked`).each(function() {
                const rangeNumber = parseInt($(this).data('optional-range'), 10);
                if (rangeNumber > 0) {
                    selectedOptionalCounts[rangeNumber] = (selectedOptionalCounts[rangeNumber] || 0) + 1;
                }
            });

            if (!hasAnyMarks) {
                return;
            }

            let total = 0;
            const questionPayload = [];
            const selectedOptionalIds = {};

            $(`.optional-select[data-student="${studentID}"]:checked`).each(function() {
                selectedOptionalIds[$(this).data('question-id')] = true;
            });

            for (let i = 0; i < examQuestionData.questions.length; i++) {
                const question = examQuestionData.questions[i];
                const value = questionMarks[question.exam_paper_questionID];
                const isOptional = question.is_optional;
                const isSelectedOptional = selectedOptionalIds[question.exam_paper_questionID] === true;

                if (isOptional && !isSelectedOptional) {
                    continue;
                }

                if (value === undefined || value === null || value === '') {
                    hasValidationError = true;
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please fill all question marks for each student before saving.',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }

                const numericValue = parseFloat(value);
                if (isNaN(numericValue) || numericValue < 0 || numericValue > parseFloat(question.marks)) {
                    hasValidationError = true;
                    Swal.fire({
                        title: 'Error!',
                        text: 'Question marks must not exceed the allowed maximum.',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    return false;
                }

                total += numericValue;
                questionPayload.push({
                    question_id: question.exam_paper_questionID,
                    marks: numericValue
                });
            }

            const displayTotal = Number.isInteger(total) ? total : total.toFixed(2);
            $(`#marks_${studentID}`).val(displayTotal);
            autoCalculateGrade(studentID);

            results.push({
                studentID: studentID,
                marks: total,
                grade: $(`.grade-input[data-student="${studentID}"]`).val() || null,
                remark: $(`.remark-input[data-student="${studentID}"]`).val() || null,
                question_marks: questionPayload
            });
        });

        if (hasValidationError) {
            return;
        }
    } else {
        $('.marks-input').each(function() {
            const studentID = $(this).data('student');
            const marks = $(this).val();
            const grade = $(`.grade-input[data-student="${studentID}"]`).val();
            const remark = $(`.remark-input[data-student="${studentID}"]`).val();

            if (marks || grade || remark) {
                results.push({
                    studentID: studentID,
                    marks: marks || null,
                    grade: grade || null,
                    remark: remark || null
                });
            }
        });
    }

    if (results.length === 0) {
        Swal.fire({
            title: 'Warning!',
            text: 'Please enter at least one result',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }

    Swal.fire({
        title: 'Saving...',
        text: 'Please wait while we save the results.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '/save_subject_results',
        method: 'POST',
        data: {
            class_subjectID: classSubjectID,
            examID: examID,
            results: results
        },
        success: function(response) {
            Swal.fire({
                title: 'Success!',
                text: response.success || 'Results saved successfully!',
                icon: 'success',
                confirmButtonColor: '#940000'
            }).then(() => {
                $('#addEditResultsModal').modal('hide');
            });
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr.responseJSON?.error || 'Failed to save results',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });

    // Upload Excel Form Handler
    $('#uploadExcelForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const classSubjectID = $('#upload_class_subject_id').val();
        const examID = $('#upload_exam_id').val();

        if (!examID) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select an examination first.',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
            return;
        }

        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while we process your Excel file.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/upload_excel_results',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message || 'Results uploaded successfully!',
                    icon: 'success',
                    confirmButtonColor: '#940000'
                }).then(() => {
                    $('#uploadExcelModal').modal('hide');
                    // Reload the results form
                    const currentClassSubjectID = $('#class_subject_id').val();
                    const currentExamID = $('#exam_id').val();
                    if (currentClassSubjectID && currentExamID) {
                        loadExistingResults(currentClassSubjectID, currentExamID);
                    }
                });
            },
            error: function(xhr) {
                let errorMsg = 'Failed to upload Excel file.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
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

// Handle exam selection - enable/disable Excel buttons
function handleExamSelection(classSubjectID, examID) {
    if (!examID) {
        $('#downloadExcelBtn').prop('disabled', true);
        $('#uploadExcelBtn').prop('disabled', true);
        disableResultsForm();
        resetQuestionData();
        return;
    }

    // Use cached exam data instead of making API call
    const examData = window.examDataCache && window.examDataCache[examID];
    
    if (examData) {
        // Check ONLY enter_result status - no other logic
        const enterResult = examData.enter_result === true || examData.enter_result === 1;
        
        if (!enterResult) {
            // Disable form inputs
            disableResultsForm();
            $('#downloadExcelBtn').prop('disabled', true);
            $('#uploadExcelBtn').prop('disabled', true);
            showResultsStatusError('You are not allowed to enter results for this examination. Result entry has been disabled.');
            resetQuestionData();
            return;
        }

        // If enter_result is true, enable form - no other checks
        enableResultsForm();
        $('#downloadExcelBtn').prop('disabled', false);
        $('#uploadExcelBtn').prop('disabled', false);
        $('.results-status-error').remove();
        
        // Load existing results after enabling form
        loadExistingResults(classSubjectID, examID);
        loadExamQuestionData(classSubjectID, examID);
    } else {
        // Fallback: try to get exam data from option attributes
        const selectedOption = $(`#exam_id option[value="${examID}"]`);
        const enterResult = selectedOption.data('enter-result') === true || selectedOption.data('enter-result') === 1;
        
        if (!enterResult) {
            disableResultsForm();
            $('#downloadExcelBtn').prop('disabled', true);
            $('#uploadExcelBtn').prop('disabled', true);
            showResultsStatusError('You are not allowed to enter results for this examination. Result entry has been disabled.');
            resetQuestionData();
            return;
        }

        enableResultsForm();
        $('#downloadExcelBtn').prop('disabled', false);
        $('#uploadExcelBtn').prop('disabled', false);
        $('.results-status-error').remove();
        loadExistingResults(classSubjectID, examID);
        loadExamQuestionData(classSubjectID, examID);
    }
}

// Helper function to disable form inputs
function disableResultsForm() {
    $('.marks-input, .grade-input, .remark-input').prop('disabled', true).css({
        'background-color': '#e9ecef',
        'cursor': 'not-allowed',
        'color': '#dc3545'
    });
    $('.question-mark-input, .toggle-question-btn').prop('disabled', true);
    $('#resultsForm button[type="submit"]').prop('disabled', true);
}

// Helper function to enable form inputs
function enableResultsForm() {
    $('.marks-input, .grade-input, .remark-input').prop('disabled', false).css({
        'background-color': '',
        'cursor': '',
        'color': ''
    });
    $('.grade-input, .remark-input').prop('readonly', true); // Keep readonly for grade and remark
    if (isSecondarySchool) {
        $('.marks-input').prop('readonly', true);
    }
    $('.question-mark-input, .toggle-question-btn').prop('disabled', false);
    $('#resultsForm button[type="submit"]').prop('disabled', false);
}

// Helper function to show error message
function showResultsStatusError(message) {
    // Remove existing error messages
    $('.results-status-error').remove();
    
    // Add error message above the table
    const errorHtml = `
        <div class="alert alert-danger results-status-error mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Access Denied:</strong> ${message}
        </div>
    `;
    $('#resultsTableBody').closest('.table-responsive').before(errorHtml);
}

// Toggle question details per student
$(document).on('click', '.toggle-question-btn', function() {
    if (examQuestionData.questions.length === 0) {
        Swal.fire({
            title: 'No Question Formats',
            text: 'Please select an examination with approved question formats first.',
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }
    const studentID = $(this).data('student');
    const $detailRow = $(`#question_details_${studentID}`);
    if ($detailRow.length === 0) {
        return;
    }

    if (!$detailRow.hasClass('loaded')) {
        $detailRow.find('.question-detail-container').html(buildQuestionDetails(studentID));
        $detailRow.addClass('loaded');
        $detailRow.find('.optional-select').each(function() {
            const questionId = $(this).data('question-id');
            const hasMark = examQuestionData.marksByStudent[studentID] &&
                examQuestionData.marksByStudent[studentID][questionId] !== undefined &&
                examQuestionData.marksByStudent[studentID][questionId] !== '';
            if (hasMark) {
                $(this).prop('checked', true);
                const $input = $detailRow.find(`.question-mark-input[data-question-id="${questionId}"]`);
                $input.prop('disabled', false);
            }
        });
        updateStudentTotal(studentID);
    }

    $detailRow.toggleClass('d-none');
});

$(document).on('change', '.optional-select', function() {
    const $checkbox = $(this);
    const studentID = $checkbox.data('student');
    const rangeNumber = parseInt($checkbox.data('optional-range'), 10);
    const questionId = $checkbox.data('question-id');
    const $input = $(`.question-mark-input[data-student="${studentID}"][data-question-id="${questionId}"]`);
    const rangeMeta = (examQuestionData.optionalRanges || []).find(r => r.range_number == rangeNumber);
    const requiredCount = rangeMeta ? parseInt(rangeMeta.required_questions || 0, 10) : 0;
    const selectedCount = $(`.optional-select[data-student="${studentID}"][data-optional-range="${rangeNumber}"]:checked`).length;

    if (requiredCount > 0 && selectedCount > requiredCount) {
        $checkbox.prop('checked', false);
        Swal.fire({
            title: 'Optional Limit',
            text: `Question needed to opt is ${requiredCount}. You exceed the limit.`,
            icon: 'warning',
            confirmButtonColor: '#940000'
        });
        return;
    }

    if ($checkbox.is(':checked')) {
        $input.prop('disabled', false);
    } else {
        $input.val('');
        $input.prop('disabled', true);
        if (examQuestionData.marksByStudent[studentID]) {
            delete examQuestionData.marksByStudent[studentID][questionId];
        }
    }
    updateStudentTotal(studentID);
});

// Question marks input handler
$(document).on('input', '.question-mark-input', function() {
    const $input = $(this);
    const max = parseFloat($input.data('max'));
    let value = parseFloat($input.val());
    const $warning = $input.siblings('.question-max-warning');
    const isOptional = $input.data('optional') == 1;
    const rangeNumber = parseInt($input.data('optional-range'), 10);

    if (!isNaN(value) && value > max) {
        value = max;
        $input.val(max);
        $warning.text(`Max ${max}`).removeClass('d-none');
    } else {
        $warning.addClass('d-none').text('');
    }

    const studentID = $input.data('student');
    const questionId = $input.data('question-id');

    if (!examQuestionData.marksByStudent[studentID]) {
        examQuestionData.marksByStudent[studentID] = {};
    }

    if (isOptional && rangeNumber > 0) {
        let optionalSum = 0;
        $(`.question-mark-input[data-student="${studentID}"]`).each(function() {
            if (parseInt($(this).data('optional-range'), 10) === rangeNumber) {
                const val = parseFloat($(this).val());
                if (!isNaN(val)) {
                    optionalSum += val;
                }
            }
        });

        const rangeTotal = (examQuestionData.optionalRanges || []).find(r => r.range_number == rangeNumber);
        const rangeLimit = rangeTotal ? parseFloat(rangeTotal.total_marks) : 0;

        if (rangeLimit > 0 && optionalSum > rangeLimit) {
            const otherSum = optionalSum - (isNaN(value) ? 0 : value);
            const remaining = Math.max(rangeLimit - otherSum, 0);
            $input.val(remaining);
            value = remaining;
        }
    }

    if ($input.val() === '') {
        delete examQuestionData.marksByStudent[studentID][questionId];
    } else {
        examQuestionData.marksByStudent[studentID][questionId] = $input.val();
    }

    updateStudentTotal(studentID);
});

// Helper function to check if in edit mode
function checkIfEditMode(examID, classSubjectID) {
    // Check if any results exist for this exam and class subject
    let hasResults = false;
    $('.marks-input').each(function() {
        const marks = $(this).val();
        if (marks && marks !== '') {
            hasResults = true;
            return false; // Break loop
        }
    });
    return hasResults;
}

// Download Excel Template
function downloadExcelTemplate(classSubjectID) {
    const examID = $('#exam_id').val();

    if (!examID) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select an examination first.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }

    window.location.href = `/download_excel_template/${classSubjectID}/${examID}`;
}

// Show Upload Excel Modal
function showUploadExcelModal(classSubjectID) {
    const examID = $('#exam_id').val();

    if (!examID) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select an examination first.',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }

    $('#upload_class_subject_id').val(classSubjectID);
    $('#upload_exam_id').val(examID);
    $('#excel_file').val('');
    $('#excel_file_label').html('<i class="bi bi-file-earmark-excel"></i> Choose Excel file (.xlsx or .xls)');
    $('#uploadExcelModal').modal('show');
    
    // Update file label when file is selected
    $('#excel_file').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $('#excel_file_label').html('<i class="bi bi-file-earmark-excel-fill"></i> ' + fileName);
        } else {
            $('#excel_file_label').html('<i class="bi bi-file-earmark-excel"></i> Choose Excel file (.xlsx or .xls)');
        }
    });
}

// View Session Attendance
function viewSessionAttendance(classSubjectID) {
    $('#sessionAttendanceModal').modal('show');
    $('#sessionAttendanceModalBody').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
    
    // Load session attendance view
    $.get(`/teacher/session-attendance/${classSubjectID}`)
    .done(function(response) {
        $('#sessionAttendanceModalBody').html(response);
    })
    .fail(function(xhr) {
        $('#sessionAttendanceModalBody').html('<div class="alert alert-danger">Failed to load session attendance. Please try again.</div>');
    });
}

// View Exam Attendance
function viewExamAttendance(classSubjectID, subjectID) {
    $('#examAttendanceModal').modal('show');
    $('#examAttendanceModalBody').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"><span class="sr-only">Loading...</span></div></div>');
    
    // Load exam attendance view
    $.get(`/teacher/exam-attendance/${classSubjectID}`, {
        subjectID: subjectID
    })
    .done(function(response) {
        $('#examAttendanceModalBody').html(response);
    })
    .fail(function(xhr) {
        $('#examAttendanceModalBody').html('<div class="alert alert-danger">Failed to load exam attendance. Please try again.</div>');
    });
}
</script>

@include('includes.footer')
