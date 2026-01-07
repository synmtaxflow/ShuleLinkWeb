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
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    .exam-paper-card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1), 0 4px 16px rgba(148, 0, 0, 0.08) !important;
        border: 1px solid rgba(148, 0, 0, 0.1) !important;
    }
    .exam-paper-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }
    .badge-status-wait-approval {
        background-color: #ffc107;
        color: #000;
    }
    .badge-status-approved {
        background-color: #28a745;
        color: white;
    }
    .badge-status-rejected {
        background-color: #dc3545;
        color: white;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

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

            @if(isset($rejectionNotifications) && count($rejectionNotifications) > 0)
                @foreach($rejectionNotifications as $notification)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-x-circle"></i> 
                        <strong>Examination Rejected:</strong> {{ $notification['message'] }}
                        @if(isset($notification['reason']))
                            <br><small><strong>Reason:</strong> {{ $notification['reason'] }}</small>
                        @endif
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="dismissNotification('{{ $notification['exam_name'] ?? '' }}')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endforeach
            @endif

            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-primary-custom text-white rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i> Exam Papers Management
                        </h4>
                        <button class="btn btn-light text-primary-custom fw-bold" type="button" data-toggle="modal" data-target="#uploadExamPaperModal">
                            <i class="bi bi-plus-circle"></i> Upload Exam Paper
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="examPapersTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="upload-tab" data-toggle="tab" href="#upload" role="tab">
                        <i class="bi bi-cloud-upload"></i> Upload Exam Paper
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="my-papers-tab" data-toggle="tab" href="#my-papers" role="tab">
                        <i class="bi bi-file-earmark-text"></i> My Exam Papers
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="examPapersTabContent">
                <!-- Upload/Create Tab -->
                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form id="uploadExamPaperForm">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="selected_exam" class="form-label">
                                            <i class="bi bi-clipboard-check"></i> Select Examination <span class="text-danger">*</span>
                                        </label>
                                        @if($examinations && $examinations->count() > 0)
                                            <select class="form-control" id="selected_exam" name="examID" required>
                                                <option value="">Select Examination</option>
                                                @foreach($examinations as $exam)
                                                    <option value="{{ $exam->examID }}">
                                                        {{ $exam->exam_name }} 
                                                        @if($exam->term)
                                                            - {{ ucfirst(str_replace('_', ' ', $exam->term)) }}
                                                        @endif
                                                        ({{ $exam->year }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                <i class="bi bi-info-circle"></i> Only examinations with upload paper enabled are shown.
                                            </small>
                                        @else
                                            <select class="form-control" id="selected_exam" name="examID" required disabled>
                                                <option value="">No examinations available</option>
                                            </select>
                                            <div class="alert alert-warning mt-2">
                                                <i class="bi bi-exclamation-triangle"></i> 
                                                <strong>No examinations available:</strong> There are no examinations with upload paper enabled at the moment. Please contact the administrator to enable upload paper for examinations.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="class_subject" class="form-label">
                                            <i class="bi bi-book"></i> Select Subject <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="class_subject" name="class_subjectID" required>
                                            <option value="">Select Subject</option>
                                            @foreach($teacherSubjects as $subject)
                                                @php
                                                    $subjectName = $subject->subject->subject_name ?? 'N/A';
                                                    $className = $subject->class->class_name ?? '';
                                                    $subclassName = $subject->subclass ? $subject->subclass->subclass_name : '';
                                                    $classDisplay = trim($className . ' ' . $subclassName);
                                                @endphp
                                                <option value="{{ $subject->class_subjectID }}">
                                                    {{ $subjectName }}@if($classDisplay) ({{ $classDisplay }})@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="exam_file" class="form-label">
                                            <i class="bi bi-file-earmark"></i> Exam Paper File <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control-file" id="exam_file" name="file" accept=".pdf,.doc,.docx" required>
                                        <small class="form-text text-muted">Maximum file size: 10MB. Allowed formats: PDF, DOC, DOCX</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary-custom">
                                            <i class="bi bi-check-circle"></i> Submit Exam Paper
                                        </button>
                                        <button type="reset" class="btn btn-secondary ml-2">
                                            <i class="bi bi-x-circle"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- My Exam Papers Tab -->
                <div class="tab-pane fade" id="my-papers" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="search_my_papers" placeholder="Search my exam papers...">
                                </div>
                            </div>
                            <div id="my_exam_papers_list">
                                @if($myExamPapers && $myExamPapers->count() > 0)
                                    @foreach($myExamPapers as $paper)
                                        <div class="card exam-paper-card mb-3" data-paper-id="{{ $paper->exam_paperID }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h5 class="card-title text-primary-custom">
                                                            <i class="bi bi-file-earmark-text"></i>
                                                            {{ $paper->examination->exam_name ?? 'N/A' }}
                                                        </h5>
                                                        <p class="mb-2">
                                                            <strong>Subject:</strong> {{ $paper->classSubject->subject->subject_name ?? 'N/A' }} |
                                                            <strong>Class:</strong> {{ $paper->classSubject->subclass ? $paper->classSubject->subclass->subclass_name : ($paper->classSubject->class->class_name ?? 'N/A') }}
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>Type:</strong>
                                                            <span class="badge badge-info">{{ ucfirst($paper->upload_type) }}</span>
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>Status:</strong>
                                                            <span class="badge badge-status-{{ $paper->status }}">
                                                                @if($paper->status == 'wait_approval')
                                                                    <i class="bi bi-clock-history"></i> Waiting Approval
                                                                @elseif($paper->status == 'approved')
                                                                    <i class="bi bi-check-circle"></i> Approved
                                                                @else
                                                                    <i class="bi bi-x-circle"></i> Rejected
                                                                @endif
                                                            </span>
                                                        </p>
                                                        @if($paper->status == 'rejected' && $paper->rejection_reason)
                                                            <div class="alert alert-danger mt-2">
                                                                <strong><i class="bi bi-exclamation-triangle"></i> Rejection Reason:</strong>
                                                                <p class="mb-0">{{ $paper->rejection_reason }}</p>
                                                            </div>
                                                        @endif
                                                        @if($paper->status == 'approved' && $paper->approval_comment)
                                                            <div class="alert alert-success mt-2">
                                                                <strong><i class="bi bi-check-circle"></i> Approval Comment:</strong>
                                                                <p class="mb-0">{{ $paper->approval_comment }}</p>
                                                            </div>
                                                        @endif
                                                        <p class="mb-0 text-muted">
                                                            <small>
                                                                <i class="bi bi-calendar"></i> Uploaded: {{ $paper->created_at->format('M d, Y H:i') }}
                                                            </small>
                                                        </p>
                                                    </div>
                                                    <div class="ml-3">
                                                        @if($paper->status == 'wait_approval')
                                                            <button class="btn btn-sm btn-warning edit-paper-btn" data-paper-id="{{ $paper->exam_paperID }}">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </button>
                                                        @endif
                                                        @if($paper->status == 'rejected')
                                                            <button class="btn btn-sm btn-danger delete-paper-btn" data-paper-id="{{ $paper->exam_paperID }}">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        @endif
                                                        @if($paper->upload_type == 'upload' && $paper->file_path)
                                                            <a href="{{ route('download_exam_paper', $paper->exam_paperID) }}" class="btn btn-sm btn-primary-custom">
                                                                <i class="bi bi-download"></i> Download
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-info text-center">
                                        <i class="bi bi-info-circle"></i> No exam papers uploaded yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Exam Paper Modal -->
<div class="modal fade" id="uploadExamPaperModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload"></i> Upload Exam Paper
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="modalUploadForm">
                    <div class="form-group">
                        <label for="modal_selected_exam">Select Examination <span class="text-danger">*</span></label>
                        @if($examinations && $examinations->count() > 0)
                            <select class="form-control" id="modal_selected_exam" name="examID" required>
                                <option value="">Select Examination</option>
                                @foreach($examinations as $exam)
                                    <option value="{{ $exam->examID }}">
                                        {{ $exam->exam_name }} 
                                        @if($exam->term)
                                            - {{ ucfirst(str_replace('_', ' ', $exam->term)) }}
                                        @endif
                                        ({{ $exam->year }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> Only examinations with upload paper enabled are shown.
                            </small>
                        @else
                            <select class="form-control" id="modal_selected_exam" name="examID" required disabled>
                                <option value="">No examinations available</option>
                            </select>
                            <div class="alert alert-warning mt-2">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>No examinations available:</strong> There are no examinations with upload paper enabled at the moment. Please contact the administrator to enable upload paper for examinations.
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="modal_class_subject">Select Subject <span class="text-danger">*</span></label>
                        <select class="form-control" id="modal_class_subject" name="class_subjectID" required>
                            <option value="">Select Subject</option>
                            @foreach($teacherSubjects as $subject)
                                @php
                                    $subjectName = $subject->subject->subject_name ?? 'N/A';
                                    $className = $subject->class->class_name ?? '';
                                    $subclassName = $subject->subclass ? $subject->subclass->subclass_name : '';
                                    $classDisplay = trim($className . ' ' . $subclassName);
                                @endphp
                                <option value="{{ $subject->class_subjectID }}">
                                    {{ $subjectName }}@if($classDisplay) ({{ $classDisplay }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal_exam_file">Exam Paper File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="modal_exam_file" name="file" accept=".pdf,.doc,.docx" required>
                        <small class="form-text text-muted">Maximum file size: 10MB. Allowed formats: PDF, DOC, DOCX</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="submitModalForm">
                    <i class="bi bi-check-circle"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Exam Paper Modal -->
<div class="modal fade" id="editExamPaperModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Edit Exam Paper
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editExamPaperForm">
                    <input type="hidden" id="edit_paper_id" name="exam_paperID">
                    <div class="form-group">
                        <label for="edit_exam_file">Exam Paper File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="edit_exam_file" name="file" accept=".pdf,.doc,.docx" required>
                        <small class="form-text text-muted">Maximum file size: 10MB. Allowed formats: PDF, DOC, DOCX</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="submitEditForm">
                    <i class="bi bi-check-circle"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const examinations = @json($examinations ?? []);

    // Dismiss notification
    window.dismissNotification = function(examName) {
        // Remove notification from session via AJAX
        $.ajax({
            url: '/dismiss_exam_rejection_notification',
            method: 'POST',
            data: {
                exam_name: examName,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                // Remove alert from DOM
                $('.alert').filter(function() {
                    return $(this).text().includes(examName);
                }).fadeOut();
            }
        });
    };


    // Submit form
    $('#uploadExamPaperForm').on('submit', function(e) {
        e.preventDefault();
        submitExamPaper($(this), false);
    });

    $('#submitModalForm').on('click', function() {
        const form = $('#modalUploadForm');
        submitExamPaper(form, true);
    });

    function submitExamPaper(form, isModal) {
        const examID = isModal ? $('#modal_selected_exam').val() : $('#selected_exam').val();
        const classSubjectID = isModal ? $('#modal_class_subject').val() : $('#class_subject').val();

        if (!examID) {
            Swal.fire('Error', 'Please select an examination', 'error');
            return;
        }

        // Verify selected exam exists and has upload_paper enabled
        const selectedExam = examinations.find(exam => exam.examID == examID);
        if (!selectedExam) {
            Swal.fire('Error', 'Selected examination not found or upload paper is disabled', 'error');
            return;
        }

        if (!classSubjectID) {
            Swal.fire('Error', 'Please select a subject', 'error');
            return;
        }

        const fileInput = isModal ? $('#modal_exam_file')[0] : $('#exam_file')[0];
        if (fileInput.files.length === 0) {
            Swal.fire('Error', 'Please select a file to upload', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('examID', examID);
        formData.append('class_subjectID', classSubjectID);
        formData.append('upload_type', 'upload');
        formData.append('file', fileInput.files[0]);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: '{{ route("store_exam_paper") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Success', response.success || 'Exam paper submitted successfully', 'success').then(() => {
                    if (isModal) {
                        $('#uploadExamPaperModal').modal('hide');
                    }
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMsg = 'Failed to submit exam paper';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    }

    // Edit exam paper
    $(document).on('click', '.edit-paper-btn', function() {
        const paperID = $(this).data('paper-id');

        $.ajax({
            url: '/get_my_exam_papers',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const paper = response.exam_papers.find(p => p.exam_paperID == paperID);
                    if (paper) {
                        $('#edit_paper_id').val(paper.exam_paperID);
                        $('#editExamPaperModal').modal('show');
                    }
                }
            }
        });
    });

    $('#submitEditForm').on('click', function() {
        const paperID = $('#edit_paper_id').val();
        const fileInput = $('#edit_exam_file')[0];

        if (fileInput.files.length === 0) {
            Swal.fire('Error', 'Please select a file to upload', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('upload_type', 'upload');
        formData.append('file', fileInput.files[0]);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: '/update_exam_paper/' + paperID,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Success', response.success || 'Exam paper updated successfully', 'success').then(() => {
                    $('#editExamPaperModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMsg = 'Failed to update exam paper';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });

    // Delete exam paper (for rejected papers)
    $(document).on('click', '.delete-paper-btn', function() {
        const paperID = $(this).data('paper-id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will permanently delete this exam paper',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/delete_exam_paper/' + paperID,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.success || 'Exam paper deleted successfully', 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to delete exam paper';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            }
        });
    });

    // Search my exam papers
    $('#search_my_papers').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.exam-paper-card').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

});
</script>

@include('includes.footer')
