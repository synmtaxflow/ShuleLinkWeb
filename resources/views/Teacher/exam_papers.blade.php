@include('includes.teacher_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Color scheme matching my_sessions.blade.php */
    .bg-primary-custom {
        background-color: #f9eeee !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    
    /* Font and Border Radius Resets */
    div, .card, .exam-widget-card, .btn, .nav-link, .alert {
        border-radius: 0 !important;
    }
    body, .container-fluid, .card, .exam-widget-card, .btn {
        font-family: "Century Gothic", "CenturyGothic", "AppleGothic", sans-serif;
    }

    .border-primary-custom {
        border-color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
        border-radius: 8px !important;
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

    /* Widget card style (matching my_sessions) */
    .exam-widget-card {
        border-radius: 12px !important;
        border: 1px solid #e7e7e7;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        padding: 18px;
        background: white;
        transition: all 0.3s ease;
        margin-bottom: 0;
    }
    .exam-widget-card:hover {
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    .exam-widget-card.status-approved {
        border-left: 4px solid #28a745;
    }
    .exam-widget-card.status-rejected {
        border-left: 4px solid #dc3545;
    }

    .exam-type-badge {
        background: #f2dede;
        color: #7a1f1f;
        padding: 4px 10px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
    }

    .exam-card-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 12px;
    }

    .btn-exam-action {
        background: white !important;
        color: #940000 !important;
        border: 1px solid #940000;
        padding: 8px 14px;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
        border-radius: 8px !important;
        width: 100%;
        text-align: center;
    }
    .btn-exam-action:hover {
        background: #f8f8f8 !important;
        color: #940000 !important;
        border-color: #940000;
    }
    .btn-exam-action.btn-danger-outline {
        color: #dc3545 !important;
        border-color: #dc3545;
    }
    .btn-exam-action.btn-danger-outline:hover {
        background: #fff5f5 !important;
    }

    /* Hero header matching my_sessions */
    .exams-hero {
        background: linear-gradient(135deg, #fff2f2 0%, #f7dede 100%);
        border: 1px solid #e8c8c8;
        color: #7a1f1f;
    }

    /* Tabs styling matching my_sessions */
    #examPapersTabs .nav-link {
        color: #940000;
        border-radius: 0 !important;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 12px 20px;
        font-weight: 600;
    }
    #examPapersTabs .nav-link:hover {
        background-color: #f9eeee;
        border-bottom-color: #e8c8c8;
    }
    #examPapersTabs .nav-link.active {
        background-color: transparent !important;
        color: #940000 !important;
        border-bottom: 3px solid #940000 !important;
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

    /* Mobile Responsiveness Improvements */
    @media (max-width: 768px) {
        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }

        /* Force all parent wrappers to full width */
        .right-panel {
            width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .right-panel .content {
            padding: 0 !important;
        }

        .container-fluid {
            padding-left: 5px !important;
            padding-right: 5px !important;
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden;
        }
        
        .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
            width: 100% !important;
        }

        .col-12, .col-md-12, .col-lg-12,
        [class*="col-"] {
            padding-left: 2px !important;
            padding-right: 2px !important;
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
        }
        
        .card-body {
            padding: 0.75rem;
        }

        .page-title {
            font-size: 1.1rem;
            word-wrap: break-word;
        }

        .nav-tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-bottom: 2px solid #ddd;
            margin-bottom: 1rem !important;
            width: 100%;
        }

        .nav-tabs .nav-item {
            white-space: nowrap;
        }

        .nav-tabs .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }

        .exam-paper-card .d-flex {
            flex-direction: column !important;
        }

        .exam-paper-card .ml-3 {
            margin-left: 0 !important;
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .exam-paper-card .btn {
            flex: 1 1 45%;
            font-size: 0.8rem;
            padding: 8px 5px;
        }

        .question-row > div {
            margin-bottom: 15px;
        }

        /* Prevent long text overflow */
        * {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        /* Table to cards for pending uploads */
        #pending-uploads .table-responsive {
            border: none;
            overflow-x: hidden;
        }

        #pending-uploads .table-responsive table thead {
            display: none;
        }
        
        #pending-uploads .table-responsive table tbody tr {
            display: block;
            margin-bottom: 1.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        #pending-uploads .table-responsive table tbody td {
            display: block;
            text-align: left !important;
            border: none;
            padding: 8px 0;
            position: relative;
        }
        
        #pending-uploads .table-responsive table tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            display: block;
            color: #940000;
            font-size: 0.75rem;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        #pending-uploads .table-responsive table tbody td .btn {
            width: 100%;
            margin-top: 10px;
        }
        
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem) !important;
        }

        /* Upload form - mobile app layout */
        .tab-content .card {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        .tab-content .card .card-body {
            padding: 0.5rem 0 !important;
        }

        .tab-content .card-body .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .tab-content .card-body .row > [class*="col-"] {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        #uploadExamPaperForm .form-control,
        #uploadExamPaperForm .form-control-file,
        #uploadExamPaperForm select,
        #uploadExamPaperForm input {
            width: 98% !important;
            margin-left: auto;
            margin-right: auto;
            display: block;
            font-size: 1rem;
            padding: 12px;
            border-radius: 8px;
        }

        #uploadExamPaperForm .form-label,
        #uploadExamPaperForm label {
            width: 98%;
            margin-left: auto;
            margin-right: auto;
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        #uploadExamPaperForm .form-text {
            width: 98%;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }

        #uploadExamPaperForm .btn {
            width: 98%;
            margin-left: auto;
            margin-right: auto;
            display: block;
            padding: 12px;
            font-size: 1rem;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        #uploadExamPaperForm .btn.ml-2 {
            margin-left: auto !important;
        }

        /* Question format card stays visible */
        #question-format-main {
            width: 98% !important;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid rgba(148,0,0,0.2) !important;
            border-radius: 8px !important;
        }
    }

    /* Touch targets */
    .btn, .form-control, .nav-link {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-control {
        display: block; /* reset flex for input */
    }

    .nav-link {
        justify-content: center;
    }

</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                <div class="card-body bg-primary-custom text-white rounded p-3 p-md-4">
                    <h4 class="mb-0 page-title">
                        <i class="bi bi-file-earmark-text"></i> Exam Papers Management
                    </h4>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="examPapersTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="upload-tab" data-toggle="tab" href="#upload" role="tab">
                        <i class="bi bi-cloud-upload"></i> Upload Exam Paper
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pending-uploads-tab" data-toggle="tab" href="#pending-uploads" role="tab">
                        <i class="bi bi-clock-history"></i> Pending Uploads
                        @if($pendingSlots->count() > 0)
                            <span class="badge badge-danger ml-1">{{ $pendingSlots->count() }}</span>
                        @endif
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
                                <input type="hidden" name="placeholder_id" id="placeholder_id">
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

                                <!-- Weekly/Monthly Test Fields (Hidden by default) -->
                                <div id="test_fields_container" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="test_year" class="form-label">
                                                <i class="bi bi-calendar"></i> Select Year <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="test_year" name="test_year">
                                                <option value="">Select Year</option>
                                                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="test_period" class="form-label">
                                                <i class="bi bi-calendar-week"></i> Select Period <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="test_period" name="test_week">
                                                <option value="">Select Period</option>
                                            </select>
                                            <small class="form-text text-muted">Periods exclude holidays</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="test_subject" class="form-label">
                                                <i class="bi bi-book"></i> Select Scheduled Subject <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" id="test_subject" name="class_subjectID">
                                                <option value="">Select a period first</option>
                                            </select>
                                            <small class="form-text text-muted">Only subjects scheduled for this test period are shown</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Regular Subject Selection (for non-test exams) -->
                                <div class="row" id="regular_subject_container">
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
                                                <option value="{{ $subject->class_subjectID }}"
                                                        data-class-id="{{ $subjectClassId }}"
                                                        data-subject-id="{{ $subject->subjectID }}"
                                                        data-subclass-id="{{ $subject->subclass ? $subject->subclass->subclassID : '' }}">
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
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="existing_upload_main" class="form-label">
                                                <i class="bi bi-files"></i> Use Existing Upload (Optional)
                                            </label>
                                            <select class="form-control" id="existing_upload_main">
                                                <option value="">Do not use existing upload</option>
                                            </select>
                                            <small class="text-muted">Only available for the same subject and class.</small>
                                        </div>
                                    </div>
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
                                            <div class="form-check mb-3 mt-3">
                                                <input class="form-check-input" type="checkbox" name="apply_to_all_subjects" id="apply_all_main" value="1">
                                                <label class="form-check-label text-primary-custom" for="apply_all_main">
                                                    <strong><i class="bi bi-megaphone"></i> Apply this question format to all subjects in this examination</strong>
                                                </label>
                                            </div>
                                            <div class="mt-2 text-muted small">
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

                <!-- Pending Uploads Tab -->
                <div class="tab-pane fade" id="pending-uploads" role="tabpanel">
                    <h5 class="text-primary-custom mb-3">
                        <i class="bi bi-clock-history"></i> Scheduled Tests (Pending Paper Upload)
                    </h5>
                    @if($pendingSlots && $pendingSlots->count() > 0)
                        <div class="row">
                            @foreach($pendingSlots as $slot)
                                @php
                                    $subjectName = $slot->classSubject->subject->subject_name ?? 'N/A';
                                    $classNameRaw = $slot->classSubject->subclass 
                                        ? ($slot->classSubject->subclass->class->class_name ?? '') . ' ' . ($slot->classSubject->subclass->subclass_name ?? '') 
                                        : ($slot->classSubject->class->class_name ?? '');
                                    $fullSubjectDisplay = $subjectName . ' - ' . trim($classNameRaw);
                                @endphp
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="exam-widget-card">
                                        <!-- Top: Week badge + Pending indicator -->
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="exam-type-badge">
                                                <i class="bi bi-calendar-week"></i> {{ $slot->test_week }}
                                            </span>
                                            <span class="badge badge-warning" style="font-size: 0.75rem;">
                                                <i class="bi bi-cloud-upload"></i> Pending Upload
                                            </span>
                                        </div>

                                        <!-- Exam Name -->
                                        <h6 class="mb-2" style="font-weight: bold;">
                                            <i class="bi bi-journal-bookmark text-primary-custom"></i>
                                            {{ $slot->examination->exam_name }}
                                            <small class="text-muted">({{ $slot->examination->year }})</small>
                                        </h6>

                                        <!-- Subject -->
                                        <p class="mb-1 text-muted" style="font-size: 0.9rem;">
                                            <i class="bi bi-book"></i> {{ $subjectName }}
                                        </p>

                                        <!-- Class -->
                                        <p class="mb-2 text-muted" style="font-size: 0.85rem;">
                                            <i class="bi bi-people"></i>
                                            @if($slot->classSubject->subclass)
                                                {{ $slot->classSubject->subclass->class->class_name ?? 'N/A' }} {{ $slot->classSubject->subclass->subclass_name }}
                                            @else
                                                {{ $slot->classSubject->class->class_name ?? 'N/A' }}
                                            @endif
                                        </p>

                                        <!-- Week Range & Test Date -->
                                        <p class="mb-1 text-muted" style="font-size: 0.8rem;">
                                            <i class="bi bi-calendar-range"></i> {{ $slot->test_week_range }}
                                        </p>
                                        <p class="mb-2" style="font-size: 0.85rem; font-weight: 600; color: #940000;">
                                            <i class="bi bi-calendar-event"></i> {{ \Carbon\Carbon::parse($slot->test_date)->format('D, d M Y') }}
                                        </p>

                                        <!-- Upload Button -->
                                        <div class="exam-card-actions">
                                            <button class="btn btn-exam-action btn-sm upload-pending-btn" 
                                                data-exam-id="{{ $slot->examID }}"
                                                data-class-subject-id="{{ $slot->class_subjectID }}"
                                                data-test-week="{{ $slot->test_week }}"
                                                data-test-week-range="{{ $slot->test_week_range }}"
                                                data-test-date="{{ $slot->test_date }}"
                                                data-full-subject-display="{{ $fullSubjectDisplay }}"
                                                data-slot-id="{{ $slot->exam_paperID }}">
                                                <i class="bi bi-cloud-upload"></i> Upload Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success display-4"></i>
                            <h5 class="mt-3">No pending uploads!</h5>
                            <p class="text-muted">All scheduled tests have had their papers uploaded or there are no upcoming scheduled tests.</p>
                        </div>
                    @endif
                </div>

                <!-- My Exam Papers Tab -->
                <div class="tab-pane fade" id="my-papers" role="tabpanel">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-6 col-md-3 mb-2">
                            <input type="text" class="form-control" id="search_my_papers" placeholder="Search...">
                        </div>
                        <div class="col-6 col-md-3 mb-2">
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
                        <div class="col-6 col-md-3 mb-2">
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
                        <div class="col-6 col-md-3 mb-2">
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

                    <!-- Papers Grid -->
                    <div id="my_exam_papers_list">
                        @if($myExamPapers && $myExamPapers->count() > 0)
                            <div class="row">
                                @foreach($myExamPapers as $paper)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="exam-widget-card {{ $paper->status == 'approved' ? 'status-approved' : ($paper->status == 'rejected' ? 'status-rejected' : '') }}"
                                             data-paper-id="{{ $paper->exam_paperID }}"
                                             data-term="{{ $paper->examination->term ?? '' }}"
                                             data-year="{{ $paper->examination->year ?? '' }}"
                                             data-exam-id="{{ $paper->examID }}">
                                            
                                            <!-- Top: Status badge -->
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="exam-type-badge">
                                                    <i class="bi bi-file-earmark-text"></i> {{ ucfirst($paper->upload_type) }}
                                                </span>
                                                <span class="badge badge-status-{{ $paper->status }}">
                                                    @if($paper->status == 'wait_approval')
                                                        <i class="bi bi-clock-history"></i> Pending
                                                    @elseif($paper->status == 'approved')
                                                        <i class="bi bi-check-circle"></i> Approved
                                                    @else
                                                        <i class="bi bi-x-circle"></i> Rejected
                                                    @endif
                                                </span>
                                            </div>

                                            <!-- Exam Name -->
                                            <h6 class="mb-2" style="font-weight: bold;">
                                                <i class="bi bi-journal-bookmark text-primary-custom"></i>
                                                {{ $paper->examination->exam_name ?? 'N/A' }}
                                            </h6>

                                            <!-- Subject & Class -->
                                            <p class="mb-1 text-muted" style="font-size: 0.9rem;">
                                                <i class="bi bi-book"></i>
                                                {{ $paper->classSubject->subject->subject_name ?? 'N/A' }}
                                            </p>
                                            <p class="mb-2 text-muted" style="font-size: 0.85rem;">
                                                <i class="bi bi-people"></i>
                                                @if($paper->classSubject->subclass)
                                                    {{ $paper->classSubject->subclass->class->class_name ?? 'N/A' }} {{ $paper->classSubject->subclass->subclass_name }}
                                                @else
                                                    {{ $paper->classSubject->class->class_name ?? 'N/A' }}
                                                @endif
                                            </p>

                                            <!-- Rejection / Approval messages -->
                                            @if($paper->status == 'rejected' && $paper->rejection_reason)
                                                <div class="alert alert-danger py-2 px-3 mb-2" style="font-size: 0.8rem; border-radius: 8px !important;">
                                                    <strong><i class="bi bi-exclamation-triangle"></i> Reason:</strong>
                                                    {{ $paper->rejection_reason }}
                                                </div>
                                            @endif
                                            @if($paper->status == 'approved' && $paper->approval_comment)
                                                <div class="alert alert-success py-2 px-3 mb-2" style="font-size: 0.8rem; border-radius: 8px !important;">
                                                    <strong><i class="bi bi-check-circle"></i> Comment:</strong>
                                                    {{ $paper->approval_comment }}
                                                </div>
                                            @endif

                                            <!-- Upload date -->
                                            <p class="mb-2 text-muted" style="font-size: 0.8rem;">
                                                <i class="bi bi-calendar"></i> {{ $paper->created_at->format('M d, Y H:i') }}
                                            </p>

                                            <!-- Action Buttons -->
                                            <div class="exam-card-actions">
                                                @if($paper->status == 'wait_approval')
                                                    <button class="btn btn-exam-action btn-sm edit-paper-btn" data-paper-id="{{ $paper->exam_paperID }}">
                                                        <i class="bi bi-pencil"></i> Edit Paper
                                                    </button>
                                                    <button class="btn btn-exam-action btn-sm edit-questions-btn" data-paper-id="{{ $paper->exam_paperID }}">
                                                        <i class="bi bi-list-check"></i> Edit Questions
                                                    </button>
                                                @endif
                                                @if($paper->status == 'rejected')
                                                    <button class="btn btn-exam-action btn-sm btn-danger-outline delete-paper-btn" data-paper-id="{{ $paper->exam_paperID }}">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                @endif
                                                @if($paper->upload_type == 'upload' && $paper->file_path)
                                                    <a href="{{ route('download_exam_paper', $paper->exam_paperID) }}" class="btn btn-exam-action btn-sm">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-x text-muted display-4"></i>
                                <h5 class="mt-3">No exam papers uploaded yet.</h5>
                                <p class="text-muted">Use the Upload tab to submit your first exam paper.</p>
                            </div>
                        @endif
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
                                <option value="{{ $subject->class_subjectID }}"
                                        data-class-id="{{ $subjectClassId }}"
                                        data-subject-id="{{ $subject->subjectID }}"
                                        data-subclass-id="{{ $subject->subclass ? $subject->subclass->subclassID : '' }}">
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
                        <div class="form-group mb-3">
                            <label for="existing_upload_modal">Use Existing Upload (Optional)</label>
                            <select class="form-control" id="existing_upload_modal">
                                <option value="">Do not use existing upload</option>
                            </select>
                            <small class="text-muted">Only available for the same subject and class.</small>
                        </div>
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
                                <div class="form-check mb-3 mt-3">
                                    <input class="form-check-input" type="checkbox" name="apply_to_all_subjects" id="apply_all_modal" value="1">
                                    <label class="form-check-label text-primary-custom" for="apply_all_modal">
                                        <strong><i class="bi bi-megaphone"></i> Apply this question format to all subjects in this examination</strong>
                                    </label>
                                </div>
                                <div class="mt-2 text-muted small">
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
    let existingExamPapers = [];
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

            $(this).data('allowed', isAllowed);
            $(this).prop('disabled', !isAllowed).prop('hidden', !isAllowed);
            if (isAllowed) {
                visibleCount++;
            }
        });

        if (visibleCount === 0) {
            $select.val('');
        }
    }

    function applyExistingUploadStatus(selectId) {
        const $select = $(selectId);
        $select.find('option').each(function(index) {
            if (index === 0) {
                return;
            }
            const $option = $(this);
            const originalText = $option.data('original-text') || $option.text();
            if (!$option.data('original-text')) {
                $option.data('original-text', originalText);
            }

            const classSubjectID = String($option.val());
            const already = existingExamPapers.find(paper => String(paper.class_subjectID) === classSubjectID && paper.status !== 'rejected');
            const allowed = $option.data('allowed') !== false;

            if (already) {
                $option.text(`${originalText} - Already uploaded`);
                $option.prop('disabled', true).addClass('text-muted');
            } else {
                $option.text(originalText);
                $option.removeClass('text-muted');
                if (allowed && !$option.prop('hidden')) {
                    $option.prop('disabled', false);
                }
            }
        });

        const selectedOption = $select.find('option:selected');
        if (selectedOption.length && selectedOption.prop('disabled')) {
            $select.val('');
        }
    }

    function refreshExistingUploadOptions(isModal) {
        const $subjectSelect = isModal ? $('#modal_class_subject') : $('#class_subject');
        const $examSelect = isModal ? $('#modal_selected_exam') : $('#selected_exam');
        const $reuseSelect = isModal ? $('#existing_upload_modal') : $('#existing_upload_main');

        if (!isSecondarySchool || $reuseSelect.length === 0) {
            return;
        }

        const examID = $examSelect.val();
        const classSubjectID = $subjectSelect.val();
        const $selectedOption = $subjectSelect.find('option:selected');
        const subjectID = $selectedOption.data('subject-id');
        const classID = $selectedOption.data('class-id');

        $reuseSelect.empty().append('<option value="">Do not use existing upload</option>');

        if (!examID || !classSubjectID || !subjectID || !classID) {
            $reuseSelect.prop('disabled', true);
            return;
        }

        const matches = existingExamPapers.filter(paper => {
            return String(paper.subjectID) === String(subjectID)
                && String(paper.class_subjectID) !== String(classSubjectID)
                && paper.status !== 'rejected';
        });

        matches.forEach(paper => {
            let weekInfo = '';
            if (paper.test_week) {
                weekInfo = ` - ${paper.test_week}`;
                if (paper.test_date) weekInfo += ` (${paper.test_date})`;
            }
            const label = `${paper.class_display}${weekInfo} (${paper.status})`;
            $reuseSelect.append(`<option value="${paper.exam_paperID}">${label}</option>`);
        });

        $reuseSelect.prop('disabled', matches.length === 0);
    }

    function applyExistingQuestions(targetId, data) {
        const $container = $(targetId);
        const $wrapper = $(`.optional-ranges-wrapper[data-wrapper-for="${targetId}"]`);

        $wrapper.empty();
        $container.empty();

        if (Array.isArray(data.optional_ranges)) {
            const sortedRanges = data.optional_ranges.slice().sort(function(a, b) {
                return (a.range_number || 0) - (b.range_number || 0);
            });
            sortedRanges.forEach(function(range) {
                addOptionalRange(targetId);
                const $lastRange = $wrapper.find('.optional-range-item').last();
                $lastRange.find('.optional-total-input').val(range.total_marks);
                if (range.required_questions !== undefined && range.required_questions !== null) {
                    $lastRange.find('.optional-required-input').val(range.required_questions);
                }
            });
        }

        if (Array.isArray(data.questions)) {
            data.questions.forEach(function(question) {
                $container.append(buildQuestionRow(targetId));
                const $row = $container.find('.question-row').last();
                $row.find('.question-description').val(question.question_description);
                $row.find('.question-marks').val(question.marks);
                $row.find('.question-optional').val(question.optional_range_number ? String(question.optional_range_number) : '0');
            });
        }

        refreshQuestionNumbers($container);
        updateTotalMarks($container);
        toggleRemoveButtons($container);
    }

    function fetchExistingPapers(examID) {
        existingExamPapers = [];

        if (!examID) {
            applyExistingUploadStatus('#class_subject');
            applyExistingUploadStatus('#modal_class_subject');
            refreshExistingUploadOptions(false);
            refreshExistingUploadOptions(true);
            return;
        }

        $.ajax({
            url: `/teacher/get-exam-paper-summary/${examID}`,
            method: 'GET',
            success: function(response) {
                if (response.success && Array.isArray(response.exam_papers)) {
                    existingExamPapers = response.exam_papers;
                } else {
                    existingExamPapers = [];
                }
                applyExistingUploadStatus('#class_subject');
                applyExistingUploadStatus('#modal_class_subject');
                refreshExistingUploadOptions(false);
                refreshExistingUploadOptions(true);
            },
            error: function() {
                existingExamPapers = [];
                applyExistingUploadStatus('#class_subject');
                applyExistingUploadStatus('#modal_class_subject');
                refreshExistingUploadOptions(false);
                refreshExistingUploadOptions(true);
            }
        });
    }

    function fetchAllowedClasses(examID) {
        allowedClassIds = [];

        if (!examID) {
            filterSubjectsByAllowedClasses('#class_subject');
            filterSubjectsByAllowedClasses('#modal_class_subject');
            applyExistingUploadStatus('#class_subject');
            applyExistingUploadStatus('#modal_class_subject');
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
                applyExistingUploadStatus('#class_subject');
                applyExistingUploadStatus('#modal_class_subject');
            },
            error: function() {
                allowedClassIds = [];
                filterSubjectsByAllowedClasses('#class_subject');
                filterSubjectsByAllowedClasses('#modal_class_subject');
                applyExistingUploadStatus('#class_subject');
                applyExistingUploadStatus('#modal_class_subject');
            }
        });
    }

    // Handle "Upload Now" from pending slots
    $(document).on('click', '.upload-pending-btn', function() {
        const examID = $(this).data('exam-id');
        const classSubjectID = $(this).data('class-subject-id');
        const testWeek = $(this).data('test-week');
        const testDate = $(this).data('test-date');
        const slotID = $(this).data('slot-id');

        // Reset form first
        $('#uploadExamPaperForm')[0].reset();
        $('#placeholder_id').val(slotID);

        // Set Exam - this will trigger the change event that shows test fields
        $('#selected_exam').val(examID);
        
        // Manually trigger the exam change logic
        const examName = $('#selected_exam').find('option:selected').text();
        const isWeeklyTest = examName.includes('Weekly Test');
        const isMonthlyTest = examName.includes('Monthly Test');
        const isTestType = isWeeklyTest || isMonthlyTest;
        
        if (isTestType) {
            // Show test fields, hide regular subject selection
            $('#test_fields_container').show();
            $('#regular_subject_container').hide();
            $('#class_subject').prop('required', false);
            $('#test_subject').prop('required', true);
            
            // Load available periods for the current year
            const currentYear = $('#test_year').val() || new Date().getFullYear();
            const fullSubjectDisplay = $(this).data('full-subject-display');
            const testWeekRange = $(this).data('test-week-range');
            const displayWeek = testWeekRange ? `${testWeek} (${testWeekRange})` : testWeek;
            
            // DIRECTLY SET VALUES FIRST (Instant Feedback)
            
            // 1. Set Period (Test Week)
            const $periodSelect = $('#test_period');
            // Ensure option exists
            if ($periodSelect.find(`option[value="${testWeek}"]`).length === 0) {
                 // Clear existing if it only has 'Select Period' to avoid clutter or duplicates if logic differs
                 if ($periodSelect.children().length <= 1) $periodSelect.html('<option value="">Select Period</option>');
                 $periodSelect.append(`<option value="${testWeek}">${displayWeek}</option>`);
            }
            $periodSelect.val(testWeek);
            
            // 2. Set Subject
            const $subjectSelect = $('#test_subject');
            if ($subjectSelect.find(`option[value="${classSubjectID}"]`).length === 0) {
                 if ($subjectSelect.children().length <= 1) $subjectSelect.html('<option value="">Select Subject</option>');
                 $subjectSelect.append(`<option value="${classSubjectID}">${fullSubjectDisplay}</option>`);
            }
            $subjectSelect.val(classSubjectID);
            
            // 3. Set Date
            $('#test_date').val(testDate);

            // BACKGROUND LOAD (To populate other options)
            // Load periods
            $.ajax({
                url: '/get_available_periods',
                method: 'GET',
                data: {
                    year: currentYear,
                    test_type: isWeeklyTest ? 'weekly_test' : 'monthly_test',
                    examID: examID
                },
                success: function(response) {
                    if (response.success && response.periods) {
                        // Remember current selection
                        const selectedPeriod = $periodSelect.val();
                        $periodSelect.html('<option value="">Select Period</option>');
                        
                        let periodExists = false;
                        response.periods.forEach(function(period) {
                            const displayText = period.range ? `${period.week} (${period.range})` : period.week;
                            $periodSelect.append(`<option value="${period.week}">${displayText}</option>`);
                            if (period.week === selectedPeriod) periodExists = true;
                        });
                        
                        // Restore selection if valid, otherwise keep what we injected if not in list (edge case)
                        if (periodExists) {
                            $periodSelect.val(selectedPeriod);
                        } else if (selectedPeriod) {
                             $periodSelect.append(`<option value="${selectedPeriod}">${displayWeek}</option>`);
                             $periodSelect.val(selectedPeriod);
                        }
                        
                        // Load subjects for this week (Background)
                        $.ajax({
                            url: '/get_scheduled_subjects',
                            method: 'GET',
                            data: {
                                examID: examID,
                                week: selectedPeriod
                            },
                            success: function(subjectResponse) {
                                if (subjectResponse.success && subjectResponse.subjects) {
                                    // Remember current subject
                                    const selectedSubject = $subjectSelect.val();
                                    $subjectSelect.html('<option value="">Select Subject</option>');
                                    
                                    let subjectExists = false;
                                    subjectResponse.subjects.forEach(function(subject) {
                                        $subjectSelect.append(`<option value="${subject.class_subjectID}">${subject.subject_name} - ${subject.class_display}</option>`);
                                        if (subject.class_subjectID == selectedSubject) subjectExists = true;
                                    });
                                    
                                    // Restore selection
                                    if (subjectExists) {
                                        $subjectSelect.val(selectedSubject);
                                    } else if (selectedSubject) {
                                        $subjectSelect.append(`<option value="${selectedSubject}">${fullSubjectDisplay}</option>`);
                                        $subjectSelect.val(selectedSubject);
                                    }
                                }
                            }
                        });
                    }
                }
            });
        }
        
        // Fetch allowed classes
        fetchAllowedClasses(examID);
        fetchExistingPapers(examID);

        // Switch Tab
        $('#upload-tab').tab('show');
    });

    $('#selected_exam').on('change', function() {
        const examID = $(this).val();
        const examName = $(this).find('option:selected').text();
        
        // Check if this is a Weekly Test or Monthly Test
        const isWeeklyTest = examName.includes('Weekly Test');
        const isMonthlyTest = examName.includes('Monthly Test');
        const isTestType = isWeeklyTest || isMonthlyTest;
        
        if (isTestType) {
            // Show test fields, hide regular subject selection
            $('#test_fields_container').show();
            $('#regular_subject_container').hide();
            $('#class_subject').prop('required', false);
            $('#test_subject').prop('required', true);
            
            // Load available periods for the current year
            const currentYear = $('#test_year').val() || new Date().getFullYear();
            loadAvailablePeriods(currentYear, isWeeklyTest ? 'weekly_test' : 'monthly_test', examID);
        } else {
            // Hide test fields, show regular subject selection
            $('#test_fields_container').hide();
            $('#regular_subject_container').show();
            $('#class_subject').prop('required', true);
            $('#test_subject').prop('required', false);
            
            // Clear test fields
            $('#test_period').html('<option value="">Select Period</option>');
            $('#test_subject').html('<option value="">Select a period first</option>');
        }
        
        fetchAllowedClasses(examID);
        fetchExistingPapers(examID);
    });

    // Handle year change for tests
    $('#test_year').on('change', function() {
        const year = $(this).val();
        const examName = $('#selected_exam').find('option:selected').text();
        const isWeeklyTest = examName.includes('Weekly Test');
        const isMonthlyTest = examName.includes('Monthly Test');
        
        if (year && (isWeeklyTest || isMonthlyTest)) {
            loadAvailablePeriods(year, isWeeklyTest ? 'weekly_test' : 'monthly_test');
        }
    });

    // Handle period change for tests
    $('#test_period').on('change', function() {
        const period = $(this).val();
        const examID = $('#selected_exam').val();
        
        if (period && examID) {
            loadScheduledSubjects(examID, period);
        } else {
            $('#test_subject').html('<option value="">Select a period first</option>');
        }
    });

    // Function to load available periods (weeks/months) excluding holidays
    function loadAvailablePeriods(year, testType, examID) {
        $.ajax({
            url: '/get_available_periods',
            method: 'GET',
            data: {
                year: year,
                test_type: testType,
                examID: examID
            },
            success: function(response) {
                if (response.success && response.periods) {
                    const $periodSelect = $('#test_period');
                    $periodSelect.html('<option value="">Select Period</option>');
                    
                    response.periods.forEach(function(period) {
                        $periodSelect.append(`<option value="${period.id}">${period.text}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Failed to load periods:', xhr);
                Swal.fire('Error', 'Failed to load available periods', 'error');
            }
        });
    }

    // Function to load scheduled subjects for a specific test period
    function loadScheduledSubjects(examID, testWeek) {
        $.ajax({
            url: '/get_scheduled_subjects',
            method: 'GET',
            data: {
                examID: examID,
                test_week: testWeek
            },
            success: function(response) {
                if (response.success && response.subjects) {
                    const $subjectSelect = $('#test_subject');
                    $subjectSelect.html('<option value="">Select Subject</option>');
                    
                    if (response.subjects.length === 0) {
                        $subjectSelect.html('<option value="">No subjects scheduled for this period</option>');
                    } else {
                        response.subjects.forEach(function(subject) {
                            $subjectSelect.append(`<option value="${subject.class_subjectID}">${subject.subject_name} (${subject.class_name}) - ${subject.day} ${subject.time}</option>`);
                        });
                    }
                }
            },
            error: function(xhr) {
                console.error('Failed to load subjects:', xhr);
                $('#test_subject').html('<option value="">Error loading subjects</option>');
            }
        });
    }

    $('#modal_selected_exam').on('change', function() {
        fetchAllowedClasses($(this).val());
        fetchExistingPapers($(this).val());
    });

    $('#class_subject').on('change', function() {
        refreshExistingUploadOptions(false);
    });

    $('#modal_class_subject').on('change', function() {
        refreshExistingUploadOptions(true);
    });

    function toggleFileRequirement(isModal, useExisting) {
        const $fileInput = isModal ? $('#modal_exam_file') : $('#exam_file');
        if (useExisting) {
            $fileInput.prop('required', false);
        } else {
            $fileInput.prop('required', true);
        }
    }

    $('#existing_upload_main').on('change', function() {
        const paperId = $(this).val();
        toggleFileRequirement(false, !!paperId);
        if (!paperId) {
            return;
        }
        $.ajax({
            url: `/get_exam_paper_questions/${paperId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    applyExistingQuestions('#question-rows-main', response);
                }
            }
        });
    });

    $('#existing_upload_modal').on('change', function() {
        const paperId = $(this).val();
        toggleFileRequirement(true, !!paperId);
        if (!paperId) {
            return;
        }
        $.ajax({
            url: `/get_exam_paper_questions/${paperId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    applyExistingQuestions('#question-rows-modal', response);
                }
            }
        });
    });

    const initialExam = $('#selected_exam').val();
    if (initialExam) {
        fetchExistingPapers(initialExam);
    }

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
        const isTestMode = !isModal && $('#test_fields_container').is(':visible');
        const classSubjectID = isModal 
            ? $('#modal_class_subject').val() 
            : (isTestMode ? $('#test_subject').val() : $('#class_subject').val());
        const existingUploadId = isModal ? $('#existing_upload_modal').val() : $('#existing_upload_main').val();

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
        if (!existingUploadId && fileInput.files.length === 0) {
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
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }
        if (existingUploadId) {
            formData.append('existing_exam_paper_id', existingUploadId);
        }
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        const applyToAll = isModal ? $('#apply_all_modal').is(':checked') : $('#apply_all_main').is(':checked');
        if (applyToAll) {
            formData.append('apply_to_all_subjects', '1');
        }

        // Add test-specific fields if applicable
        const testWeek = isModal ? $('#modal_test_week').val() : $('#test_period').val();
        const testDate = isModal ? $('#modal_test_date').val() : $('#test_date').val();
        const placeholderId = $('#placeholder_id').val();
        
        if (testWeek) {
            formData.append('test_week', testWeek);
        }
        if (testDate) {
            formData.append('test_date', testDate);
        }
        if (placeholderId) {
            formData.append('placeholder_id', placeholderId);
        }

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

        // Show loading progress bar
        Swal.fire({
            title: 'Uploading Exam Paper...',
            html: 'Please wait while we process your request.<br><br><b id="upload-percentage">0%</b><br><div class="progress mt-2" style="height: 10px; border-radius: 5px; overflow: hidden;"><div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%"></div></div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("store_exam_paper") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        percentComplete = parseInt(percentComplete * 100);
                        $('#upload-progress-bar').css('width', percentComplete + '%');
                        $('#upload-percentage').text(percentComplete + '%');
                        if (percentComplete === 100) {
                            Swal.update({
                                title: 'Processing...',
                                html: 'File uploaded. Finalizing submission, please wait...'
                            });
                        }
                    }
                }, false);
                return xhr;
            },
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

        // Show loading progress bar
        Swal.fire({
            title: 'Updating Exam Paper...',
            html: 'Please wait while we process your request.<br><br><b id="upload-percentage-edit">0%</b><br><div class="progress mt-2" style="height: 10px; border-radius: 5px; overflow: hidden;"><div id="upload-progress-bar-edit" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%"></div></div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/update_exam_paper/' + paperID,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        percentComplete = parseInt(percentComplete * 100);
                        $('#upload-progress-bar-edit').css('width', percentComplete + '%');
                        $('#upload-percentage-edit').text(percentComplete + '%');
                        if (percentComplete === 100) {
                            Swal.update({
                                title: 'Processing...',
                                html: 'File uploaded. Finalizing update, please wait...'
                            });
                        }
                    }
                }, false);
                return xhr;
            },
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
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the exam paper.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

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

        // Show loading
        Swal.fire({
            title: 'Updating Questions...',
            text: 'Please wait while we update the question formats.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

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
