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
                                                @php
                                                    $subjectClassId = $subject->subclass
                                                        ? ($subject->subclass->classID ?? null)
                                                        : ($subject->classID ?? ($subject->class->classID ?? null));
                                                @endphp
                                                <option value="{{ $subject->class_subjectID }}" data-class-id="{{ $subjectClassId }}">
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

                                @if(strtolower($schoolType ?? 'Secondary') === 'secondary')
                                    <div class="card border-primary-custom mt-3" id="question-format-main">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <h6 class="mb-1 text-primary-custom">
                                                        <i class="bi bi-list-check"></i> Question Formats & Marks (Total 100)
                                                    </h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-optional-range" data-target="#question-rows-main">
                                                        <i class="bi bi-plus-circle"></i> Add optional range
                                                    </button>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-primary-custom add-question-row" data-target="#question-rows-main">
                                                    <i class="bi bi-plus-circle"></i> Add Question
                                                </button>
                                            </div>
                                            <div class="optional-ranges-wrapper" data-wrapper-for="#question-rows-main"></div>
                                            <div id="question-rows-main"></div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Total Marks: <span class="total-marks" data-total-for="#question-rows-main">0</span>/100
                                                </small>
                                                <div class="text-danger small mt-1 marks-warning d-none" data-warning-for="#question-rows-main"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

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
                                <div class="col-md-4 mb-2">
                                    <input type="text" class="form-control" id="search_my_papers" placeholder="Search my exam papers...">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select class="form-control" id="filter_my_papers_term">
                                        <option value="">All Terms</option>
                                        @php
                                            $myTerms = $myExamPapers ? $myExamPapers->pluck('examination.term')->filter()->unique() : collect();
                                        @endphp
                                        @foreach($myTerms as $term)
                                            <option value="{{ $term }}">{{ ucfirst(str_replace('_', ' ', $term)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select class="form-control" id="filter_my_papers_year">
                                        <option value="">All Years</option>
                                        @php
                                            $myYears = $myExamPapers ? $myExamPapers->pluck('examination.year')->filter()->unique()->sort() : collect();
                                        @endphp
                                        @foreach($myYears as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <select class="form-control" id="filter_my_papers_exam">
                                        <option value="">All Exams</option>
                                        @php
                                            $myExams = $myExamPapers ? $myExamPapers->pluck('examination')->filter()->unique('examID') : collect();
                                        @endphp
                                        @foreach($myExams as $exam)
                                            <option value="{{ $exam->examID }}">{{ $exam->exam_name }} ({{ $exam->year }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="my_exam_papers_list">
                                @if($myExamPapers && $myExamPapers->count() > 0)
                                    @foreach($myExamPapers as $paper)
                                        <div class="card exam-paper-card mb-3"
                                             data-paper-id="{{ $paper->exam_paperID }}"
                                             data-term="{{ $paper->examination->term ?? '' }}"
                                             data-year="{{ $paper->examination->year ?? '' }}"
                                             data-exam-id="{{ $paper->examID }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h5 class="card-title text-primary-custom">
                                                            <i class="bi bi-file-earmark-text"></i>
                                                            {{ $paper->examination->exam_name ?? 'N/A' }}
                                                        </h5>
                                                        <p class="mb-2">
                                                            <strong>Subject:</strong> {{ $paper->classSubject->subject->subject_name ?? 'N/A' }} |
                                                            <strong>Class:</strong>
                                                            @if($paper->classSubject->subclass)
                                                                {{ $paper->classSubject->subclass->class->class_name ?? 'N/A' }} {{ $paper->classSubject->subclass->subclass_name }}
                                                            @else
                                                                {{ $paper->classSubject->class->class_name ?? 'N/A' }}
                                                            @endif
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
                                                                <button class="btn btn-sm btn-info edit-questions-btn mt-1" data-paper-id="{{ $paper->exam_paperID }}">
                                                                    <i class="bi bi-list-check"></i> Edit Questions
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
                                @php
                                    $subjectClassId = $subject->subclass
                                        ? ($subject->subclass->classID ?? null)
                                        : ($subject->classID ?? ($subject->class->classID ?? null));
                                @endphp
                                <option value="{{ $subject->class_subjectID }}" data-class-id="{{ $subjectClassId }}">
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

                    @if(strtolower($schoolType ?? 'Secondary') === 'secondary')
                        <div class="card border-primary-custom mt-3" id="question-format-modal">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="mb-1 text-primary-custom">
                                            <i class="bi bi-list-check"></i> Question Formats & Marks (Total 100)
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary add-optional-range" data-target="#question-rows-modal">
                                            <i class="bi bi-plus-circle"></i> Add optional range
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary-custom add-question-row" data-target="#question-rows-modal">
                                        <i class="bi bi-plus-circle"></i> Add Question
                                    </button>
                                </div>
                                <div class="optional-ranges-wrapper" data-wrapper-for="#question-rows-modal"></div>
                                <div id="question-rows-modal"></div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Total Marks: <span class="total-marks" data-total-for="#question-rows-modal">0</span>/100
                                    </small>
                                    <div class="text-danger small mt-1 marks-warning d-none" data-warning-for="#question-rows-modal"></div>
                                </div>
                            </div>
                        </div>
                    @endif
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

<!-- Edit Exam Paper Questions Modal -->
<div class="modal fade" id="editExamPaperQuestionsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">
                    <i class="bi bi-list-check"></i> Edit Exam Paper Questions
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editExamPaperQuestionsForm">
                    <input type="hidden" id="edit_questions_paper_id" name="exam_paperID">
                    <div class="card border-primary-custom">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-1 text-primary-custom">
                                        <i class="bi bi-list-check"></i> Question Formats & Marks (Total 100)
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-optional-range" data-target="#question-rows-edit">
                                        <i class="bi bi-plus-circle"></i> Add optional range
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary-custom add-question-row" data-target="#question-rows-edit">
                                    <i class="bi bi-plus-circle"></i> Add Question
                                </button>
                            </div>
                            <div class="optional-ranges-wrapper" data-wrapper-for="#question-rows-edit"></div>
                            <div id="question-rows-edit"></div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Total Marks: <span class="total-marks" data-total-for="#question-rows-edit">0</span>/100
                                </small>
                                <div class="text-danger small mt-1 marks-warning d-none" data-warning-for="#question-rows-edit"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="submitEditQuestionsForm">
                    <i class="bi bi-check-circle"></i> Update Questions
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const examinations = @json($examinations ?? []);
    let allowedClassIds = [];
    const isSecondarySchool = @json(strtolower($schoolType ?? 'Secondary')) === 'secondary';

    function buildQuestionRow(targetId) {
        const optionalOptions = buildOptionalRangeOptions(targetId);
        return `
            <div class="form-row align-items-end question-row">
                <div class="col-md-1 mb-2">
                    <label class="form-label">Qn</label>
                    <input type="text" class="form-control question-number" readonly>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Question Description</label>
                    <input type="text" class="form-control question-description" name="question_descriptions[]" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Marks</label>
                    <input type="number" class="form-control question-marks" name="question_marks[]" min="1" max="100" required>
                </div>
                <div class="col-md-1 mb-2">
                    <label class="form-label">Opt</label>
                    <select class="form-control question-optional" name="question_optional[]">
                        ${optionalOptions}
                    </select>
                </div>
                <div class="col-md-1 mb-2">
                    <button type="button" class="btn btn-sm btn-danger remove-question-row" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    function buildOptionalRangeOptions(targetId) {
        const ranges = getOptionalRanges(targetId);
        let options = '<option value="0">No</option>';
        ranges.forEach(function(range) {
            options += `<option value="${range.number}">Opt ${range.number}</option>`;
        });
        return options;
    }

    function getOptionalRanges(targetId) {
        const ranges = [];
        $(`.optional-range-item[data-wrapper-for="${targetId}"]`).each(function() {
            const rangeNumber = parseInt($(this).data('range-number'), 10);
            if (!isNaN(rangeNumber)) {
                ranges.push({ number: rangeNumber });
            }
        });
        return ranges;
    }

    function refreshOptionalRangeSelects(targetId) {
        const options = buildOptionalRangeOptions(targetId);
        $(`${targetId} .question-optional`).each(function() {
            const current = $(this).val();
            $(this).html(options);
            if (current && $(this).find(`option[value="${current}"]`).length > 0) {
                $(this).val(current);
            }
        });
    }

    function refreshQuestionNumbers($container) {
        $container.find('.question-row').each(function(index) {
            $(this).find('.question-number').val(index + 1);
        });
    }

    function updateTotalMarks($container) {
        const targetId = '#' + $container.attr('id');
        const $totalEl = $(`.total-marks[data-total-for="${targetId}"]`);
        const $warningEl = $(`.marks-warning[data-warning-for="${targetId}"]`);
        const optionalTotals = getOptionalTotals(targetId);
        let total = 0;
        let optionalSumByRange = {};

        $container.find('.question-row').each(function() {
            const value = parseInt($(this).find('.question-marks').val(), 10);
            const optionalRange = parseInt($(this).find('.question-optional').val(), 10);
            if (!isNaN(value)) {
                if (optionalRange > 0) {
                    optionalSumByRange[optionalRange] = (optionalSumByRange[optionalRange] || 0) + value;
                } else {
                    total += value;
                }
            }
        });

        const requiredTotal = total;
        const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
        const requiredMax = 100 - optionalTotalSum;
        const overallTotal = requiredTotal + optionalTotalSum;

        $totalEl.text(overallTotal);

        const optionalRangeMismatch = Object.keys(optionalTotals).some(function(range) {
            const rangeTotal = optionalTotals[range];
            const sum = optionalSumByRange[range] || 0;
            return sum < rangeTotal;
        });

        if (optionalTotalSum > 100) {
            $warningEl.text('Optional range totals exceed 100. Reduce optional totals.').removeClass('d-none');
        } else if (optionalRangeMismatch) {
            $warningEl.text('Optional range total is less than the range total marks.').removeClass('d-none');
        } else if (requiredTotal > requiredMax) {
            $warningEl.text('Required questions exceed allowed total. Reduce required marks.').removeClass('d-none');
        } else if (overallTotal > 100) {
            $warningEl.text('Marks exceed 100. Please reduce marks.').removeClass('d-none');
        } else if (overallTotal < 100) {
            $warningEl.text('Marks are less than 100. Add questions or update marks.').removeClass('d-none');
        } else {
            $warningEl.addClass('d-none').text('');
        }
    }

    function ensureAtLeastOneRow($container) {
        if ($container.find('.question-row').length === 0) {
            $container.append(buildQuestionRow('#' + $container.attr('id')));
        }
        refreshQuestionNumbers($container);
        updateTotalMarks($container);
        toggleRemoveButtons($container);
    }

    function toggleRemoveButtons($container) {
        const rowCount = $container.find('.question-row').length;
        $container.find('.remove-question-row').prop('disabled', rowCount <= 1);
    }

    function getOptionalTotals(targetId) {
        const totals = {};
        $(`.optional-range-item[data-wrapper-for="${targetId}"]`).each(function() {
            const rangeNumber = parseInt($(this).data('range-number'), 10);
            const total = parseInt($(this).find('.optional-total-input').val(), 10);
            if (!isNaN(rangeNumber) && !isNaN(total)) {
                totals[rangeNumber] = total;
            }
        });
        return totals;
    }

    function getOptionalRequiredCounts(targetId) {
        const counts = {};
        $(`.optional-range-item[data-wrapper-for="${targetId}"]`).each(function() {
            const rangeNumber = parseInt($(this).data('range-number'), 10);
            const requiredCount = parseInt($(this).find('.optional-required-input').val(), 10);
            if (!isNaN(rangeNumber) && !isNaN(requiredCount)) {
                counts[rangeNumber] = requiredCount;
            }
        });
        return counts;
    }

    function addOptionalRange(targetId) {
        const $wrapper = $(`.optional-ranges-wrapper[data-wrapper-for="${targetId}"]`);
        const existing = $wrapper.find('.optional-range-item').length;
        const rangeNumber = existing + 1;
        const html = `
            <div class="form-row align-items-end mb-2 optional-range-item" data-wrapper-for="${targetId}" data-range-number="${rangeNumber}">
                <div class="col-md-4">
                    <label class="form-label">Optional Range ${rangeNumber} Total Marks</label>
                    <input type="number" class="form-control optional-total-input" min="1" max="100" placeholder="e.g. 45">
                    <div class="text-danger small optional-total-error d-none">Optional totals exceed 100.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Required Questions</label>
                    <input type="number" class="form-control optional-required-input" min="1" placeholder="e.g. 2">
                    <div class="text-danger small optional-required-error d-none">Required count exceeds optional questions.</div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Questions in Opt ${rangeNumber} can exceed this total, but only this value counts toward 100.</small>
                </div>
                <div class="col-md-1 text-right">
                    <button type="button" class="btn btn-sm btn-danger remove-optional-range" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $wrapper.append(html);
        refreshOptionalRangeSelects(targetId);
        updateTotalMarks($(targetId));
    }

    if (isSecondarySchool) {
        ensureAtLeastOneRow($('#question-rows-main'));
        ensureAtLeastOneRow($('#question-rows-modal'));
    }

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


    function filterSubjectsByAllowedClasses(selectId) {
        const $select = $(selectId);
        let visibleCount = 0;

        $select.find('option').each(function(index) {
            if (index === 0) {
                $(this).prop('disabled', false).prop('hidden', false);
                return;
            }

            const classId = $(this).data('class-id');
            const isAllowed = allowedClassIds.length === 0 || (classId && allowedClassIds.includes(classId));

            $(this).prop('disabled', !isAllowed).prop('hidden', !isAllowed);
            if (isAllowed) {
                visibleCount++;
            }
        });

        if (visibleCount === 0) {
            $select.val('');
        }
    }

    function fetchAllowedClasses(examID) {
        allowedClassIds = [];

        if (!examID) {
            filterSubjectsByAllowedClasses('#class_subject');
            filterSubjectsByAllowedClasses('#modal_class_subject');
            return;
        }

        $.ajax({
            url: `/get_exam_allowed_classes/${examID}`,
            method: 'GET',
            success: function(response) {
                if (response.success && Array.isArray(response.allowed_class_ids)) {
                    allowedClassIds = response.allowed_class_ids.map(id => parseInt(id, 10));
                } else {
                    allowedClassIds = [];
                }

                filterSubjectsByAllowedClasses('#class_subject');
                filterSubjectsByAllowedClasses('#modal_class_subject');
            },
            error: function() {
                allowedClassIds = [];
                filterSubjectsByAllowedClasses('#class_subject');
                filterSubjectsByAllowedClasses('#modal_class_subject');
            }
        });
    }

    $('#selected_exam').on('change', function() {
        fetchAllowedClasses($(this).val());
    });

    $('#modal_selected_exam').on('change', function() {
        fetchAllowedClasses($(this).val());
    });

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

        if (isSecondarySchool) {
            const $questionContainer = isModal ? $('#question-rows-modal') : $('#question-rows-main');
            const descriptions = $questionContainer.find('.question-description').map(function() {
                return $(this).val().trim();
            }).get();
            const marks = $questionContainer.find('.question-marks').map(function() {
                return $(this).val();
            }).get();
            const optionals = $questionContainer.find('.question-optional').map(function() {
                return parseInt($(this).val(), 10);
            }).get();
            const targetId = isModal ? '#question-rows-modal' : '#question-rows-main';
            const optionalTotals = getOptionalTotals(targetId);
            const optionalRequiredCounts = getOptionalRequiredCounts(targetId);

            if (descriptions.length === 0) {
                Swal.fire('Error', 'Please add at least one question format', 'error');
                return;
            }

            const hasEmptyDescription = descriptions.some(desc => desc === '');
            if (hasEmptyDescription) {
                Swal.fire('Error', 'Please fill all question descriptions', 'error');
                return;
            }

            let requiredTotal = 0;
            let optionalSumByRange = {};
            for (let i = 0; i < marks.length; i++) {
                const markValue = parseInt(marks[i], 10);
                if (isNaN(markValue) || markValue <= 0) {
                    Swal.fire('Error', 'Please enter valid marks for each question', 'error');
                    return;
                }
                if (optionals[i] > 0) {
                    optionalSumByRange[optionals[i]] = (optionalSumByRange[optionals[i]] || 0) + markValue;
                } else {
                    requiredTotal += markValue;
                }
            }

            const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
            const optionalQuestionsCountByRange = {};
            optionals.forEach(function(rangeNumber) {
                if (rangeNumber > 0) {
                    optionalQuestionsCountByRange[rangeNumber] = (optionalQuestionsCountByRange[rangeNumber] || 0) + 1;
                }
            });

            const optionalRangeMismatch = Object.keys(optionalTotals).some(function(range) {
                const rangeTotal = optionalTotals[range];
                const sum = optionalSumByRange[range] || 0;
                return sum < rangeTotal;
            });

            const requiredCountInvalid = Object.keys(optionalRequiredCounts).some(function(range) {
                const requiredCount = optionalRequiredCounts[range];
                const available = optionalQuestionsCountByRange[range] || 0;
                return requiredCount > available;
            });

            if (requiredCountInvalid) {
                Swal.fire('Error', 'Required optional questions exceed available optional questions', 'error');
                return;
            }

            if (optionalRangeMismatch) {
                Swal.fire('Error', 'Optional range total must be at least the range total marks', 'error');
                return;
            }

            if (requiredTotal > (100 - optionalTotalSum)) {
                Swal.fire('Error', 'Required questions exceed allowed total', 'error');
                return;
            }

            if ((requiredTotal + optionalTotalSum) !== 100) {
                Swal.fire('Error', 'Required total + optional totals must be exactly 100', 'error');
                return;
            }
        }

        const formData = new FormData();
        formData.append('examID', examID);
        formData.append('class_subjectID', classSubjectID);
        formData.append('upload_type', 'upload');
        formData.append('file', fileInput.files[0]);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        if (isSecondarySchool) {
            const $questionContainer = isModal ? $('#question-rows-modal') : $('#question-rows-main');
            $questionContainer.find('.question-description').each(function() {
                formData.append('question_descriptions[]', $(this).val().trim());
            });
            $questionContainer.find('.question-marks').each(function() {
                formData.append('question_marks[]', $(this).val());
            });
            $questionContainer.find('.question-optional').each(function() {
                formData.append('question_optional[]', $(this).val());
            });
            const targetId = isModal ? '#question-rows-modal' : '#question-rows-main';
            const optionalTotals = getOptionalTotals(targetId);
            const optionalRequiredCounts = getOptionalRequiredCounts(targetId);
            Object.keys(optionalTotals).forEach(function(rangeNumber) {
                formData.append(`optional_ranges[${rangeNumber}]`, optionalTotals[rangeNumber]);
            });
            Object.keys(optionalRequiredCounts).forEach(function(rangeNumber) {
                formData.append(`optional_required_counts[${rangeNumber}]`, optionalRequiredCounts[rangeNumber]);
            });
        }

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

    function applyMyPapersFilters() {
        const searchTerm = $('#search_my_papers').val().toLowerCase();
        const term = $('#filter_my_papers_term').val();
        const year = $('#filter_my_papers_year').val();
        const examId = $('#filter_my_papers_exam').val();

        $('.exam-paper-card').each(function() {
            const text = $(this).text().toLowerCase();
            const matchesSearch = text.includes(searchTerm);
            const matchesTerm = !term || $(this).data('term') === term;
            const matchesYear = !year || String($(this).data('year')) === String(year);
            const matchesExam = !examId || String($(this).data('exam-id')) === String(examId);

            if (matchesSearch && matchesTerm && matchesYear && matchesExam) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    $('#search_my_papers, #filter_my_papers_term, #filter_my_papers_year, #filter_my_papers_exam').on('input change', function() {
        applyMyPapersFilters();
    });

    // Edit exam paper questions
    $(document).on('click', '.edit-questions-btn', function() {
        const paperID = $(this).data('paper-id');
        $('#edit_questions_paper_id').val(paperID);
        $('#question-rows-edit').empty();
        $('.optional-ranges-wrapper[data-wrapper-for="#question-rows-edit"]').empty();
        $('#editExamPaperQuestionsModal').modal('show');

        $.ajax({
            url: `/get_exam_paper_questions/${paperID}`,
            method: 'GET',
            success: function(response) {
                if (!response.success) {
                    Swal.fire('Error', response.error || 'Failed to load questions', 'error');
                    return;
                }

                const ranges = response.optional_ranges || [];
                ranges.forEach(function(range) {
                    addOptionalRange('#question-rows-edit');
                    const $wrapper = $('.optional-ranges-wrapper[data-wrapper-for="#question-rows-edit"] .optional-range-item').last();
                    $wrapper.find('.optional-total-input').val(range.total_marks);
                });

                (response.questions || []).forEach(function(question) {
                    $('#question-rows-edit').append(buildQuestionRow('#question-rows-edit'));
                    const $row = $('#question-rows-edit .question-row').last();
                    $row.find('.question-description').val(question.question_description);
                    $row.find('.question-marks').val(question.marks);
                    $row.find('.question-optional').val(question.optional_range_number || 0);
                });

                refreshQuestionNumbers($('#question-rows-edit'));
                refreshOptionalRangeSelects('#question-rows-edit');
                updateTotalMarks($('#question-rows-edit'));
                toggleRemoveButtons($('#question-rows-edit'));
            },
            error: function() {
                Swal.fire('Error', 'Failed to load questions', 'error');
            }
        });
    });

    $('#submitEditQuestionsForm').on('click', function() {
        const paperID = $('#edit_questions_paper_id').val();
        const $questionContainer = $('#question-rows-edit');
        const descriptions = $questionContainer.find('.question-description').map(function() {
            return $(this).val().trim();
        }).get();
        const marks = $questionContainer.find('.question-marks').map(function() {
            return $(this).val();
        }).get();
        const optionals = $questionContainer.find('.question-optional').map(function() {
            return parseInt($(this).val(), 10);
        }).get();
        const optionalTotals = getOptionalTotals('#question-rows-edit');

        if (descriptions.length === 0) {
            Swal.fire('Error', 'Please add at least one question format', 'error');
            return;
        }

        const hasEmptyDescription = descriptions.some(desc => desc === '');
        if (hasEmptyDescription) {
            Swal.fire('Error', 'Please fill all question descriptions', 'error');
            return;
        }

        let requiredTotal = 0;
        let optionalSumByRange = {};
        for (let i = 0; i < marks.length; i++) {
            const markValue = parseInt(marks[i], 10);
            if (isNaN(markValue) || markValue <= 0) {
                Swal.fire('Error', 'Please enter valid marks for each question', 'error');
                return;
            }
            if (optionals[i] > 0) {
                optionalSumByRange[optionals[i]] = (optionalSumByRange[optionals[i]] || 0) + markValue;
            } else {
                requiredTotal += markValue;
            }
        }

        const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
        const optionalRangeMismatch = Object.keys(optionalTotals).some(function(range) {
            const rangeTotal = optionalTotals[range];
            const sum = optionalSumByRange[range] || 0;
            return sum < rangeTotal;
        });

        if (optionalRangeMismatch) {
            Swal.fire('Error', 'Optional range total must be at least the range total marks', 'error');
            return;
        }

        if (requiredTotal > (100 - optionalTotalSum)) {
            Swal.fire('Error', 'Required questions exceed allowed total', 'error');
            return;
        }

        if ((requiredTotal + optionalTotalSum) !== 100) {
            Swal.fire('Error', 'Required total + optional totals must be exactly 100', 'error');
            return;
        }

        const formData = new FormData();
        descriptions.forEach(function(desc) {
            formData.append('question_descriptions[]', desc);
        });
        marks.forEach(function(mark) {
            formData.append('question_marks[]', mark);
        });
        optionals.forEach(function(opt) {
            formData.append('question_optional[]', opt);
        });
        Object.keys(optionalTotals).forEach(function(rangeNumber) {
            formData.append(`optional_ranges[${rangeNumber}]`, optionalTotals[rangeNumber]);
        });
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: `/update_exam_paper_questions/${paperID}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Success', response.success || 'Questions updated successfully', 'success').then(() => {
                    $('#editExamPaperQuestionsModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMsg = 'Failed to update questions';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });

    $(document).on('click', '.add-question-row', function() {
        const target = $(this).data('target');
        const $container = $(target);
        $container.append(buildQuestionRow(target));
        refreshQuestionNumbers($container);
        updateTotalMarks($container);
        toggleRemoveButtons($container);
    });

    $(document).on('click', '.remove-question-row', function() {
        const $container = $(this).closest('.question-row').parent();
        $(this).closest('.question-row').remove();
        ensureAtLeastOneRow($container);
    });

    $(document).on('input', '.question-marks, .question-description', function() {
        const $container = $(this).closest('[id^="question-rows"]');
        updateTotalMarks($container);
    });

    $(document).on('change', '.question-optional', function() {
        const $container = $(this).closest('[id^="question-rows"]');
        updateTotalMarks($container);
    });

    $(document).on('input', '.optional-total-input', function() {
        const $wrapper = $(this).closest('.optional-range-item');
        const target = $wrapper.data('wrapper-for');
        const optionalTotals = getOptionalTotals(target);
        const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
        const hasError = optionalTotalSum > 100;

        $(`.optional-range-item[data-wrapper-for="${target}"] .optional-total-error`).toggleClass('d-none', !hasError);
        $(`.optional-range-item[data-wrapper-for="${target}"] .optional-total-input`).toggleClass('is-invalid', hasError);
        updateTotalMarks($(target));
    });

    $(document).on('input', '.optional-required-input, .question-optional', function() {
        const $wrapper = $(this).closest('.optional-range-item');
        const target = $wrapper.length ? $wrapper.data('wrapper-for') : $(this).closest('[id^="question-rows"]').attr('id');
        const targetId = target.startsWith('#') ? target : `#${target}`;
        const optionalRequiredCounts = getOptionalRequiredCounts(targetId);
        const optionalQuestionsCountByRange = {};
        $(`${targetId} .question-optional`).each(function() {
            const rangeNumber = parseInt($(this).val(), 10);
            if (rangeNumber > 0) {
                optionalQuestionsCountByRange[rangeNumber] = (optionalQuestionsCountByRange[rangeNumber] || 0) + 1;
            }
        });

        $(`.optional-range-item[data-wrapper-for="${targetId}"]`).each(function() {
            const rangeNumber = parseInt($(this).data('range-number'), 10);
            const requiredCount = optionalRequiredCounts[rangeNumber] || 0;
            const available = optionalQuestionsCountByRange[rangeNumber] || 0;
            const isInvalid = requiredCount > available;
            $(this).find('.optional-required-error').toggleClass('d-none', !isInvalid);
            $(this).find('.optional-required-input').toggleClass('is-invalid', isInvalid);
        });
        updateTotalMarks($(targetId));
    });

    $(document).on('click', '.add-optional-range', function() {
        const target = $(this).data('target');
        addOptionalRange(target);
    });

    $(document).on('click', '.remove-optional-range', function() {
        const $item = $(this).closest('.optional-range-item');
        const target = $item.data('wrapper-for');
        const rangeNumber = $item.data('range-number');
        $item.remove();
        $(`.question-optional option[value="${rangeNumber}"]`).each(function() {
            if ($(this).closest('select').val() == rangeNumber) {
                $(this).closest('select').val('0');
            }
        });
        refreshOptionalRangeSelects(target);
        const optionalTotals = getOptionalTotals(target);
        const optionalTotalSum = Object.values(optionalTotals).reduce((sum, val) => sum + val, 0);
        const hasError = optionalTotalSum > 100;
        $(`.optional-range-item[data-wrapper-for="${target}"] .optional-total-error`).toggleClass('d-none', !hasError);
        $(`.optional-range-item[data-wrapper-for="${target}"] .optional-total-input`).toggleClass('is-invalid', hasError);
        updateTotalMarks($(target));
    });

});
</script>

@include('includes.footer')
