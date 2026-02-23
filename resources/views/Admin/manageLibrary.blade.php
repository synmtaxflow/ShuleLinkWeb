@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
    }

    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item {
        font-family: "Century Gothic", Arial, sans-serif;
    }

    body {
        background-color: #ffffff;
    }

    .card, .alert, .btn, div, .form-control, .form-select {
        border-radius: 0 !important;
    }

    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
    }

    /* Statistics Cards */
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 12px 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
        border-top: 3px solid #e9ecef;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-top-color: var(--primary-color);
    }

    .stat-icon {
        width: 35px;
        height: 35px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #495057;
        font-size: 18px;
        margin-bottom: 8px;
    }

    .stat-card:hover .stat-icon {
        background: var(--primary-color);
        color: white;
    }

    .stat-number {
        font-size: 1.4rem;
        font-weight: 700;
        color: #212529;
        margin: 4px 0;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 500;
        margin: 0;
    }

    /* Card Styles */
    .library-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 25px;
    }

    .card-header-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .card-title-custom {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .btn-primary-custom:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
    }

    .table-responsive {
        border-radius: 8px;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        min-height: 0.01%;
    }

    /* Ensure table doesn't break on smaller screens */
    @media screen and (max-width: 1200px) {
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }
        
        .table-responsive > .table {
            margin-bottom: 0;
            width: 100%;
            min-width: 1000px; /* Minimum width to force horizontal scroll */
        }
    }

    /* Desktop and laptop - ensure horizontal scroll is visible */
    @media screen and (min-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
            max-width: 100%;
        }
        
        #borrowsTable {
            min-width: 1200px; /* Force horizontal scroll on desktop/laptop */
            width: 100%;
        }
        
        /* Make scrollbar more visible */
        .table-responsive::-webkit-scrollbar {
            height: 12px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    }

    /* Ensure borrows table container is responsive */
    #borrowsTab .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .table thead {
        background-color: #f8f9fa;
    }

    .badge-custom {
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
    }

    .badge-available {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-issued {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-borrowed {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-returned {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #856404;
        font-size: 0.75rem;
        padding: 3px 8px;
        margin-left: 5px;
    }

    /* DataTable custom styles */
    #borrowsTable_wrapper .dataTables_filter input {
        margin-left: 10px;
        padding: 5px 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    #borrowsTable_wrapper .dataTables_length select {
        padding: 5px 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    .search-filter-section {
        background: white;
        border-radius: 0;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 8px;
    }

    .library-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .library-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
    }

    .library-management-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .book-widget {
        border-radius: 10px;
        padding: 15px;
        border: 2px solid #e9ecef;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .book-widget.available {
        border-color: #28a745;
        background: repeating-linear-gradient(
            45deg,
            rgba(40, 167, 69, 0.08),
            rgba(40, 167, 69, 0.08) 8px,
            rgba(255, 255, 255, 0.6) 8px,
            rgba(255, 255, 255, 0.6) 16px
        );
    }

    .book-widget.borrowed {
        border-color: #ffc107;
        background: repeating-linear-gradient(
            45deg,
            rgba(255, 193, 7, 0.08),
            rgba(255, 193, 7, 0.08) 8px,
            rgba(255, 255, 255, 0.6) 8px,
            rgba(255, 255, 255, 0.6) 16px
        );
    }

    .book-widget.overdue {
        border-color: #dc3545;
        background: repeating-linear-gradient(
            45deg,
            rgba(220, 53, 69, 0.08),
            rgba(220, 53, 69, 0.08) 8px,
            rgba(255, 255, 255, 0.6) 8px,
            rgba(255, 255, 255, 0.6) 16px
        );
    }

    .pattern-key {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 12px;
    }

    .pattern-swatch {
        width: 22px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid #e9ecef;
        display: inline-block;
    }

    .pattern-swatch.available {
        border-color: #28a745;
        background: repeating-linear-gradient(
            45deg,
            rgba(40, 167, 69, 0.12),
            rgba(40, 167, 69, 0.12) 6px,
            rgba(255, 255, 255, 0.6) 6px,
            rgba(255, 255, 255, 0.6) 12px
        );
    }

    .pattern-swatch.borrowed {
        border-color: #ffc107;
        background: repeating-linear-gradient(
            45deg,
            rgba(255, 193, 7, 0.12),
            rgba(255, 193, 7, 0.12) 6px,
            rgba(255, 255, 255, 0.6) 6px,
            rgba(255, 255, 255, 0.6) 12px
        );
    }

    .pattern-swatch.overdue {
        border-color: #dc3545;
        background: repeating-linear-gradient(
            45deg,
            rgba(220, 53, 69, 0.12),
            rgba(220, 53, 69, 0.12) 6px,
            rgba(255, 255, 255, 0.6) 6px,
            rgba(255, 255, 255, 0.6) 12px
        );
    }

    .book-widget.lost {
        border-color: #6f42c1;
        background: repeating-linear-gradient(
            45deg,
            rgba(111, 66, 193, 0.08),
            rgba(111, 66, 193, 0.08) 8px,
            rgba(255, 255, 255, 0.6) 8px,
            rgba(255, 255, 255, 0.6) 16px
        );
    }

    .book-widget.damaged {
        border-color: #fd7e14;
        background: repeating-linear-gradient(
            45deg,
            rgba(253, 126, 20, 0.08),
            rgba(253, 126, 20, 0.08) 8px,
            rgba(255, 255, 255, 0.6) 8px,
            rgba(255, 255, 255, 0.6) 16px
        );
    }

    .pattern-swatch.lost {
        border-color: #6f42c1;
        background: repeating-linear-gradient(
            45deg,
            rgba(111, 66, 193, 0.12),
            rgba(111, 66, 193, 0.12) 6px,
            rgba(255, 255, 255, 0.6) 6px,
            rgba(255, 255, 255, 0.6) 12px
        );
    }

    .pattern-swatch.damaged {
        border-color: #fd7e14;
        background: repeating-linear-gradient(
            45deg,
            rgba(253, 126, 20, 0.12),
            rgba(253, 126, 20, 0.12) 6px,
            rgba(255, 255, 255, 0.6) 6px,
            rgba(255, 255, 255, 0.6) 12px
        );
    }

    .student-widget {
        width: 260px;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        font-family: Arial, sans-serif;
        margin: 0 auto;
    }

    #studentsWidgetContainer {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    #studentsWidgetContainer .student-widget-col {
        flex: 0 0 260px;
    }

    .student-header {
        background: #940000;
        padding: 12px;
        text-align: center;
    }

    .student-widget.status-occupied .student-header,
    .student-widget.status-overdue .student-header,
    .student-widget.status-not-due .student-header {
        background: #940000;
    }

    .student-widget.status-lost .student-header {
        background: #6f42c1;
        background-image: repeating-linear-gradient(
            45deg,
            rgba(255, 255, 255, 0.08),
            rgba(255, 255, 255, 0.08) 8px,
            rgba(255, 255, 255, 0.0) 8px,
            rgba(255, 255, 255, 0.0) 16px
        );
    }

    .student-widget.status-damaged .student-header {
        background: #fd7e14;
        background-image: repeating-linear-gradient(
            45deg,
            rgba(255, 255, 255, 0.08),
            rgba(255, 255, 255, 0.08) 8px,
            rgba(255, 255, 255, 0.0) 8px,
            rgba(255, 255, 255, 0.0) 16px
        );
    }

    .student-widget.status-overdue .student-body .return-date {
        color: #dc3545;
    }

    .student-header img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid #fff;
        object-fit: cover;
    }

    .student-body {
        padding: 10px;
        text-align: center;
    }

    .student-body h3 {
        color: #940000;
        margin-bottom: 5px;
        font-size: 18px;
        font-weight: 700;
    }

    .student-body .form {
        font-weight: bold;
        color: #444;
        margin-bottom: 10px;
    }

    .student-body .info p {
        margin: 6px 0;
        font-size: 14px;
    }

    .return-date {
        color: #940000;
        font-weight: bold;
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Manage Library</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-1">
                <div class="card-body">
                    <div class="float-right mt-1">
                        <i class="fa fa-book fa-2x"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count" id="totalBooks">{{ $totalBooks ?? 0 }}</span>
                    </h4>
                    <p class="text-light">Total Books</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="float-right mt-1">
                        <i class="fa fa-check fa-2x"></i>
                    </div>
                    <h4 class="mb-0 text-white">
                        <span class="count" id="availableBooks">{{ $availableBooks ?? 0 }}</span>
                    </h4>
                    <p class="text-white opacity-8">Available Books</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-2">
                <div class="card-body">
                    <div class="float-right mt-1">
                        <i class="fa fa-hand-paper-o fa-2x"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count" id="issuedBooks">{{ $issuedBooks ?? 0 }}</span>
                    </h4>
                    <p class="text-light">Borrowed Books</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="float-right mt-1">
                        <i class="fa fa-exclamation-triangle fa-2x text-white"></i>
                    </div>
                    <h4 class="mb-0 text-white">
                        <span class="count" id="overdueBooks">{{ $overdueBooks ?? 0 }}</span>
                    </h4>
                    <p class="text-white opacity-8">Overdue Books</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-5">
                <div class="card-body">
                    <div class="float-right mt-1">
                        <i class="fa fa-ban fa-2x text-white"></i>
                    </div>
                    <h4 class="mb-0 text-white">
                        <span class="count" id="lostBooks">{{ $lostBooks ?? 0 }}</span>
                    </h4>
                    <p class="text-white opacity-8">Lost Books</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="float-right mt-1">
                        <i class="fa fa-wrench fa-2x text-white"></i>
                    </div>
                    <h4 class="mb-0 text-white">
                        <span class="count" id="damagedBooks">{{ $damagedBooks ?? 0 }}</span>
                    </h4>
                    <p class="text-white opacity-8">Damaged Books</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Library Management</strong>
        </div>
        <div class="card-body library-management-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group library-menu">
                        <a class="list-group-item active" data-target="#booksTab">
                            <i class="fa fa-book"></i> Books
                        </a>
                        <a class="list-group-item" data-target="#borrowsTab">
                            <i class="fa fa-hand-paper-o"></i> Borrow Records
                        </a>
                        <a class="list-group-item" data-target="#statisticsTab">
                            <i class="fa fa-bar-chart"></i> Statistics
                        </a>
                        <a class="list-group-item" data-target="#lostTab">
                            <i class="fa fa-ban"></i> Lost Books
                        </a>
                        <a class="list-group-item" data-target="#damagedTab">
                            <i class="fa fa-wrench"></i> Damaged Books
                        </a>
                        <a class="list-group-item" data-target="#studentsTab">
                            <i class="fa fa-users"></i> Students
                        </a>
                        <a class="list-group-item" data-action="add-book">
                            <i class="fa fa-plus"></i> Add Book
                        </a>
                        <a class="list-group-item" data-action="borrow-book">
                            <i class="fa fa-hand-paper-o"></i> Borrow Book
                        </a>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Search and Filter Section -->
                    <div class="search-filter-section">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label"><i class="fa fa-filter"></i> Select Class</label>
                                <select class="form-control" id="filterClass">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fa fa-bookmark"></i> Select Subject</label>
                                <select class="form-control" id="filterSubject">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->subjectID }}">{{ $subject->subject_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fa fa-search"></i> Search Book</label>
                                <input type="text" class="form-control" id="searchBook" placeholder="Search by title, author, or ISBN...">
                            </div>
                            <div class="col-md-12 mt-2">
                                <button class="btn btn-primary-custom w-100" onclick="loadBooks()">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Tabs -->
                    <ul class="nav nav-tabs mb-3 d-none" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#booksTab">
                                <i class="fa fa-book"></i> Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#borrowsTab">
                                <i class="fa fa-hand-paper-o"></i> Borrow Records
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#statisticsTab">
                                <i class="fa fa-bar-chart"></i> Statistics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#lostTab">
                                <i class="fa fa-ban"></i> Lost Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#damagedTab">
                                <i class="fa fa-wrench"></i> Damaged Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#studentsTab">
                                <i class="fa fa-users"></i> Students
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
        <!-- Books Tab -->
        <div class="tab-pane fade show active" id="booksTab">
            <div class="library-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom">
                        <i class="fa fa-book"></i> Books Summary
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Total Books</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody id="booksTableBody">
                            <tr>
                                <td colspan="4" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Borrows Tab -->
        <div class="tab-pane fade" id="borrowsTab">
            <div class="library-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom">
                        <i class="fa fa-hand-paper-o"></i> Book Borrow Records
                    </h5>
                    <button class="btn btn-primary-custom" onclick="showBorrowBookModal()">
                        <i class="fa fa-plus"></i> Borrow Book
                    </button>
                </div>
                <div class="mb-3">
                    <select class="form-control" id="filterBorrowStatus" style="width: 200px; display: inline-block;">
                        <option value="">All Status</option>
                        <option value="borrowed">Borrowed</option>
                        <option value="returned">Returned</option>
                    </select>
                    <select class="form-control ml-2" id="filterBorrowDue" style="width: 240px; display: inline-block;">
                        <option value="">All Due Status</option>
                        <option value="overdue">Return Date Passed</option>
                        <option value="not_due">Return Date Not Reached</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="borrowsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Admission No.</th>
                                <th>Book</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Borrow Date</th>
                                <th>Expected Return</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="borrowsTableBody">
                            <tr>
                                <td colspan="11" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Statistics Tab -->
        <div class="tab-pane fade" id="statisticsTab">
            <div class="library-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom">
                        <i class="fa fa-bar-chart"></i> Book Statistics by Class and Subject
                    </h5>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Select Class</label>
                        <select class="form-control" id="statClass">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Select Subject</label>
                        <select class="form-control" id="statSubject">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->subjectID }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary-custom w-100" onclick="loadStatistics()">
                            <i class="fa fa-refresh"></i> Get Statistics
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Total Books</th>
                                <th>Available</th>
                                <th>Issued</th>
                                <th>Book Types</th>
                            </tr>
                        </thead>
                        <tbody id="statisticsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">Select class and/or subject to view statistics</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Lost Books Tab -->
        <div class="tab-pane fade" id="lostTab">
            <div class="library-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom">
                        <i class="fa fa-ban"></i> Lost Books
                    </h5>
                    <button class="btn btn-primary-custom" onclick="showLostBookModal()">
                        <i class="fa fa-plus"></i> Record Lost Book
                    </button>
                    </div>
                <div class="mb-3 d-flex flex-wrap align-items-center">
                    <span class="me-2"><strong>Pattern Key:</strong></span>
                    <span class="pattern-key">
                        <span class="pattern-swatch lost"></span> Lost
                    </span>
                </div>
                <div class="mb-3">
                    <select class="form-control" id="filterLostBy" style="width: 220px; display: inline-block;">
                        <option value="">All</option>
                        <option value="student">Lost by Student</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="row" id="lostBooksContainer">
                    <div class="col-12 text-center">Loading...</div>
                </div>
            </div>
        </div>
        <!-- Damaged Books Tab -->
        <div class="tab-pane fade" id="damagedTab">
            <div class="library-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom">
                        <i class="fa fa-wrench"></i> Damaged Books
                    </h5>
                    <button class="btn btn-primary-custom" onclick="showDamagedBookModal()">
                        <i class="fa fa-plus"></i> Record Damaged Book
                    </button>
                </div>
                <div class="mb-3 d-flex flex-wrap align-items-center">
                    <span class="me-2"><strong>Pattern Key:</strong></span>
                    <span class="pattern-key">
                        <span class="pattern-swatch damaged"></span> Damaged
                    </span>
                </div>
                <div class="mb-3">
                    <select class="form-control" id="filterDamagedBy" style="width: 220px; display: inline-block;">
                        <option value="">All</option>
                        <option value="student">Damaged by Student</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="row" id="damagedBooksContainer">
                    <div class="col-12 text-center">Loading...</div>
                </div>
            </div>
        </div>
        <!-- Students Tab -->
        <div class="tab-pane fade" id="studentsTab">
            <div class="library-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom">
                        <i class="fa fa-users"></i> Students
                    </h5>
                </div>
                <div class="mb-3">
                    <label class="form-label">Filter</label>
                    <select class="form-control" id="studentFilter" style="width: 280px; display: inline-block;">
                        <option value="occupied">Borrowed (Not Returned)</option>
                        <option value="overdue">Borrowed (Return Date Passed)</option>
                        <option value="not_due">Borrowed (Return Date Not Reached)</option>
                        <option value="lost">Lost Books</option>
                        <option value="damaged">Damaged Books</option>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" id="studentSearch" placeholder="Search student, class, book title, ISBN...">
                </div>
                <div class="mb-3">
                    <span><strong>Total:</strong> <span id="studentFilterTotal">0</span></span>
                </div>
                <div class="row" id="studentsWidgetContainer">
                    <div class="col-12 text-center">Loading...</div>
                </div>
            </div>
        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Books Modal -->
<div class="modal fade" id="viewBooksModal" tabindex="-1">
    <div class="modal-dialog modal-xl" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewBooksModalTitle">
                    <i class="fa fa-th"></i> View Books
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3 d-flex flex-wrap align-items-center">
                    <span class="me-2"><strong>Pattern Key:</strong></span>
                    <span class="pattern-key">
                        <span class="pattern-swatch available"></span> Free
                    </span>
                    <span class="pattern-key">
                        <span class="pattern-swatch borrowed"></span> Occupied
                    </span>
                    <span class="pattern-key">
                        <span class="pattern-swatch overdue"></span> Overdue
                    </span>
                    <span class="pattern-key">
                        <span class="pattern-swatch lost"></span> Lost
                    </span>
                    <span class="pattern-key">
                        <span class="pattern-swatch damaged"></span> Damaged
                    </span>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" id="viewBooksSearch" placeholder="Search by ISBN, title, author...">
                </div>
                <div class="mb-3">
                    <select class="form-control" id="viewBooksFilter" style="width: 240px; display: inline-block;">
                        <option value="">All</option>
                        <option value="free">Free</option>
                        <option value="occupied">Occupied</option>
                        <option value="overdue">Overdue</option>
                        <option value="lost">Lost</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>
                <div class="row" id="viewBooksWidgetContainer">
                    <div class="col-12 text-center">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Book Modal -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookModalTitle">
                    <i class="fa fa-book"></i> Add New Book
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bookForm">
                    <input type="hidden" id="bookID">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-control" id="bookClassID" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-control" id="bookSubjectID" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Book Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bookTitle" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Author</label>
                            <input type="text" class="form-control" id="bookAuthor">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Publisher</label>
                            <input type="text" class="form-control" id="bookPublisher">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Publication Year</label>
                            <input type="number" class="form-control" id="bookPublicationYear" min="1900" max="{{ date('Y') }}">
                        </div>
                        <div class="col-md-6 mb-3" id="bookStatusDiv" style="display: none;">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="bookStatus">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="bookDescription" rows="3"></textarea>
                        </div>
                    </div>
                </form>
                <div id="bookEntriesSection" class="mt-3">
                    <h6 class="mb-2">ISBN List</h6>
                    <div id="isbnList">
                        <div class="row align-items-end isbn-row mb-2">
                            <div class="col-md-8">
                                <label class="form-label">ISBN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control isbn-input" placeholder="Enter ISBN">
                                <small class="text-danger d-none isbn-error">ISBN is already taken.</small>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-danger remove-isbn-btn d-none">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addIsbnRowBtn">
                        <i class="fa fa-plus"></i> Add ISBN
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="saveBooksBtn" onclick="saveBooks()">
                    <i class="fa fa-save"></i> Save All
                </button>
                <button type="button" class="btn btn-primary-custom d-none" id="saveBookBtn" onclick="saveBook()">
                    <i class="fa fa-save"></i> <span id="saveBookBtnText">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Borrow Book Modal -->
<div class="modal fade" id="borrowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-hand-paper-o"></i> Azima Kitabu
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="borrowForm">
                    <div class="mb-3">
                        <label class="form-label">Book ISBN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="borrowISBN" required placeholder="Enter book ISBN">
                        <small id="borrowIsbnError" class="text-danger d-none">Book not found or already borrowed.</small>
                    </div>
                    <div class="mb-3" id="borrowBookInfo" style="display: none;">
                        <div class="p-2 border rounded bg-light">
                            <div><strong>Title:</strong> <span id="borrowBookTitle"></span></div>
                            <div><strong>Class:</strong> <span id="borrowBookClass"></span></div>
                            <div><strong>Subject:</strong> <span id="borrowBookSubject"></span></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Search Student <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="searchStudent" placeholder="Search by name or admission number...">
                        <input type="hidden" id="borrowStudentID">
                        <div id="studentResults" class="mt-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expectedReturnDate" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="borrowNotes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="saveBorrowBtn" onclick="saveBorrow()">
                    <i class="fa fa-save"></i> <span id="saveBorrowBtnText">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lost Book Modal -->
<div class="modal fade" id="lostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-ban"></i> Record Lost Book
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="lostForm">
                    <div class="mb-3">
                        <label class="form-label">Book ISBN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lostIsbn" required>
                    </div>
                    <div class="mb-3" id="lostBookInfo" style="display:none;">
                        <div class="p-2 border rounded bg-light">
                            <div><strong>Title:</strong> <span id="lostBookTitle"></span></div>
                            <div><strong>Class:</strong> <span id="lostBookClass"></span></div>
                            <div><strong>Subject:</strong> <span id="lostBookSubject"></span></div>
                            <div><strong>Status:</strong> <span id="lostBookStatus"></span></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lost By <span class="text-danger">*</span></label>
                        <select class="form-control" id="lostBy">
                            <option value="student">Student</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3" id="lostStudentSection">
                        <label class="form-label">Search Student <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lostStudentSearch" placeholder="Search by name or admission number...">
                        <input type="hidden" id="lostStudentID">
                        <div id="lostStudentResults" class="mt-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="lostDescription" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveLostBook()">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Damaged Book Modal -->
<div class="modal fade" id="damagedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-wrench"></i> Record Damaged Book
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="damagedForm">
                    <div class="mb-3">
                        <label class="form-label">Book ISBN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="damagedIsbn" required>
                    </div>
                    <div class="mb-3" id="damagedBookInfo" style="display:none;">
                        <div class="p-2 border rounded bg-light">
                            <div><strong>Title:</strong> <span id="damagedBookTitle"></span></div>
                            <div><strong>Class:</strong> <span id="damagedBookClass"></span></div>
                            <div><strong>Subject:</strong> <span id="damagedBookSubject"></span></div>
                            <div><strong>Status:</strong> <span id="damagedBookStatus"></span></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Damaged By <span class="text-danger">*</span></label>
                        <select class="form-control" id="damagedBy">
                            <option value="student">Student</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3" id="damagedStudentSection">
                        <label class="form-label">Search Student <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="damagedStudentSearch" placeholder="Search by name or admission number...">
                        <input type="hidden" id="damagedStudentID">
                        <div id="damagedStudentResults" class="mt-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="damagedDescription" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveDamagedBook()">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Parent Message Modal -->
<div class="modal fade" id="parentMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-envelope"></i> Message Parent
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="parentMessageStudentID">
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" id="parentMessageText" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="sendParentMessage()">
                    <i class="fa fa-paper-plane"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Return Book Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-check"></i> Return Book
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="returnBorrowID">
                <div class="mb-2"><strong>Expected Return:</strong> <span id="returnExpectedDate"></span></div>
                <div class="mb-3">
                    <label class="form-label">Actual Return Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="actualReturnDate" onchange="handleReturnDateChange()">
                </div>
                <div class="mb-3" id="lateReasonSection" style="display:none;">
                    <label class="form-label">Why is it late?</label>
                    <textarea class="form-control" id="lateReason" rows="2" placeholder="Reason for late return..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="submitReturn()">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<script>
    // Wait for jQuery to be loaded
    (function() {
        function initLibrary() {
            // Check if jQuery is loaded
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded');
                setTimeout(initLibrary, 100);
                return;
            }
            
            // Use jQuery.noConflict if needed, or just use jQuery
            var $ = jQuery;
            if (typeof window.$ !== 'function') {
                window.$ = jQuery;
            }
            
            $(document).ready(function() {
                console.log('Document ready, jQuery version:', $.fn.jquery);

                // Sidebar menu to control tabs
                $('.library-menu .list-group-item').on('click', function() {
                    const action = $(this).data('action');
                    if (action === 'add-book') {
                        showAddBookModal();
                        return;
                    }
                    if (action === 'borrow-book') {
                        showBorrowBookModal();
                        return;
                    }
                    $('.library-menu .list-group-item').removeClass('active');
                    $(this).addClass('active');
                    const target = $(this).data('target');
                    if (target) {
                        const tabLink = $('.nav-tabs a[href="' + target + '"]');
                        if (tabLink.length && typeof tabLink.tab === 'function') {
                            tabLink.tab('show');
                        }
                    }
                });
                
                // Load books and borrows after a short delay to ensure DOM is ready
                setTimeout(function() {
                    loadBooks();
                    loadBorrows();
            loadLostBooks();
            loadDamagedBooks();
            loadLibraryStudents();
                }, 100);
        
        // Load subjects when class changes in modal
        $('#bookClassID').on('change', function() {
            const classID = $(this).val();
            console.log('Class changed in modal:', classID);
            if (classID) {
                loadSubjectsByClass(classID);
            } else {
                $('#bookSubjectID').html('<option value="">Select Subject</option>');
            }
        });
        
        // Load subjects when class changes in filter
        $('#filterClass').on('change', function() {
            // This is for filtering books, not for modal
            loadBooks();
        });
        
        // Test modal on page load
        console.log('Modal element exists:', $('#bookModal').length > 0);

        // Search students - use event delegation for dynamically created elements
        // Declare searchTimeout in window scope so it's accessible everywhere
        window.searchTimeout = null;
        $(document).on('input', '#searchStudent', function() {
            if (window.searchTimeout) {
                clearTimeout(window.searchTimeout);
            }
            const search = $(this).val();
            console.log('Student search input:', search);
            if (search && search.trim().length >= 2) {
                window.searchTimeout = setTimeout(() => searchStudents(search.trim()), 300);
            } else {
                $('#studentResults').hide();
            }
        });

        // Filter borrows
        $('#filterBorrowStatus').on('change', function() {
            loadBorrows();
        });
        $('#filterBorrowDue').on('change', function() {
            loadBorrows();
        });

        $('#filterLostBy').on('change', function() {
            loadLostBooks();
        });
        $('#filterDamagedBy').on('change', function() {
            loadDamagedBooks();
        });
        $('#studentFilter').on('change', function() {
            loadLibraryStudents();
        });
        $('#studentSearch').on('input', function() {
            applyStudentSearch();
        });

        $('#viewBooksFilter').on('change', function() {
            applyViewBooksFilters();
        });

        // View books in modal
        $(document).on('click', '.view-books-btn', function() {
            const classID = $(this).data('class-id');
            const className = $(this).data('class-name');
            openBooksView(classID, className);
        });

        // ISBN realtime check for add/edit book
        $(document).on('input', '.isbn-input', function() {
            const isbn = $(this).val().trim();
            const bookID = $('#bookID').val();
            const $input = $(this);
            if (isbnCheckTimeout) {
                clearTimeout(isbnCheckTimeout);
            }
            isbnCheckTimeout = setTimeout(() => checkIsbnAvailability($input, isbn, bookID), 300);
        });

        // Add/remove ISBN rows
        $('#addIsbnRowBtn').on('click', function() {
            addIsbnRow();
        });
        $(document).on('click', '.remove-isbn-btn', function() {
            $(this).closest('.isbn-row').remove();
            updateIsbnRemoveButtons();
        });

        // Filter books in view modal
        $(document).on('input', '#viewBooksSearch', function() {
            applyViewBooksFilters();
        });

        // Toggle occupied details
        $(document).on('click', '.toggle-occupied', function() {
            const target = $(this).data('target');
            $(target).toggleClass('d-none');
        });

        $(document).on('change', '.payment-method', function() {
            const $wrapper = $(this).closest('div').parent();
            const method = $(this).val();
            const $amount = $wrapper.find('.payment-amount');
            if (method === 'cash') {
                $amount.show();
            } else {
                $amount.hide();
                $amount.find('input').val('');
            }
        });

        $('#lostBy').on('change', function() {
            const val = $(this).val();
            if (val === 'student') {
                $('#lostStudentSection').show();
            } else {
                $('#lostStudentSection').hide();
                $('#lostStudentID').val('');
                $('#lostStudentSearch').val('');
                $('#lostStudentResults').hide();
            }
        });

        $('#damagedBy').on('change', function() {
            const val = $(this).val();
            if (val === 'student') {
                $('#damagedStudentSection').show();
            } else {
                $('#damagedStudentSection').hide();
                $('#damagedStudentID').val('');
                $('#damagedStudentSearch').val('');
                $('#damagedStudentResults').hide();
            }
        });

        $(document).on('input', '#lostStudentSearch', function() {
            const search = $(this).val();
            if (window.searchTimeout) {
                clearTimeout(window.searchTimeout);
            }
            if (search && search.trim().length >= 2) {
                window.searchTimeout = setTimeout(() => searchStudentsForLoss(search.trim()), 300);
            } else {
                $('#lostStudentResults').hide();
            }
        });

        $(document).on('input', '#damagedStudentSearch', function() {
            const search = $(this).val();
            if (window.searchTimeout) {
                clearTimeout(window.searchTimeout);
            }
            if (search && search.trim().length >= 2) {
                window.searchTimeout = setTimeout(() => searchStudentsForDamage(search.trim()), 300);
            } else {
                $('#damagedStudentResults').hide();
            }
        });

        $(document).on('input', '#lostIsbn', function() {
            const isbn = $(this).val().trim();
            if (isbnCheckTimeout) {
                clearTimeout(isbnCheckTimeout);
            }
            isbnCheckTimeout = setTimeout(() => lookupLossBookByIsbn(isbn), 300);
        });

        $(document).on('input', '#damagedIsbn', function() {
            const isbn = $(this).val().trim();
            if (isbnCheckTimeout) {
                clearTimeout(isbnCheckTimeout);
            }
            isbnCheckTimeout = setTimeout(() => lookupDamageBookByIsbn(isbn), 300);
        });

        // Lookup book by ISBN when borrowing
        $(document).on('input', '#borrowISBN', function() {
            const isbn = $(this).val().trim();
            if (isbnCheckTimeout) {
                clearTimeout(isbnCheckTimeout);
            }
            isbnCheckTimeout = setTimeout(() => lookupBookByIsbn(isbn), 300);
        });
            });
        }
        
        // Start initialization
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLibrary);
        } else {
            initLibrary();
        }
    })();

    function loadBooks() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        
        // Show loading state
        $('#booksTableBody').html('<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading books...</td></tr>');
        
        $.ajax({
            url: '{{ route("get_books") }}',
            type: 'GET',
            data: {
                classID: $('#filterClass').val(),
                subjectID: $('#filterSubject').val(),
                search: $('#searchBook').val()
            },
            success: function(response) {
                console.log('Books response:', response);
                if (response.success) {
                    let html = '';
                    if (response.books && response.books.length > 0) {
                        const grouped = {};
                        response.books.forEach(book => {
                            const classID = book.class ? book.class.classID : 'no-class';
                            const className = book.class ? (book.class.class_name || '-') : '-';
                            if (!grouped[classID]) {
                                grouped[classID] = { classID: classID, className: className, total: 0 };
                            }
                            grouped[classID].total += 1;
                        });

                        const rows = Object.values(grouped);
                        rows.forEach((row, index) => {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${row.className}</td>
                                    <td>${row.total}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-books-btn" data-class-id="${row.classID}" data-class-name="${row.className}">
                                            <i class="fa fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center">No books found</td></tr>';
                    }
                    $('#booksTableBody').html(html);
                } else {
                    $('#booksTableBody').html('<tr><td colspan="4" class="text-center">Error loading books</td></tr>');
                    console.error('Error in response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading books:', error);
                console.error('Response:', xhr.responseText);
                $('#booksTableBody').html('<tr><td colspan="4" class="text-center">Error loading books. Please try again.</td></tr>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load books: ' + (xhr.responseJSON?.error || error)
                });
            }
        });
    }

    function openBooksView(classID, className) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        $('#viewBooksModalTitle').html('<i class="fa fa-th"></i> ' + (className || 'View Books'));
        $('#viewBooksSearch').val('');
        $('#viewBooksFilter').val('');
        $('#viewBooksWidgetContainer').html('<div class="col-12 text-center">Loading...</div>');
        const $modal = $('#viewBooksModal');
        if ($modal.length === 0) {
            console.error('View Books modal not found');
            return;
        }
        if (typeof $modal.modal === 'function') {
            $modal.modal('show');
        } else {
            $modal.css('display', 'block');
            $modal.addClass('show');
            $('body').addClass('modal-open');
            $('.modal-backdrop').remove();
            $('body').append('<div class="modal-backdrop fade show"></div>');
        }

        $.ajax({
            url: '{{ route("get_books") }}',
            type: 'GET',
            data: {
                classID: classID !== 'no-class' ? classID : '',
                subjectID: $('#filterSubject').val(),
                search: $('#searchBook').val()
            },
            success: function(response) {
                if (response.success) {
                    renderBooksWidgets(response.books || []);
                } else {
                    $('#viewBooksWidgetContainer').html('<div class="col-12 text-center">Error loading books</div>');
                }
            },
            error: function() {
                $('#viewBooksWidgetContainer').html('<div class="col-12 text-center">Error loading books</div>');
            }
        });
    }

    function renderBooksWidgets(books) {
        let html = '';
        if (!books || books.length === 0) {
            html = '<div class="col-12 text-center">No books found</div>';
        } else {
            books.forEach(book => {
                const widgetId = `occupied-${book.bookID}`;
                const statusClass = book.is_lost ? 'lost' : (book.is_damaged ? 'damaged' : (book.is_overdue ? 'overdue' : (book.is_available ? 'available' : 'borrowed')));
                const statusText = book.is_lost ? 'Lost' : (book.is_damaged ? 'Damaged' : (book.is_overdue ? 'Overdue' : (book.is_available ? 'Free' : 'Occupied')));
                const occupiedBy = book.occupied_by
                    ? `${book.occupied_by.name} (${book.occupied_by.admission_number}) - ${book.occupied_by.class_name}`
                    : '-';
                const expectedReturn = book.borrow_expected_return_date ? formatDate(book.borrow_expected_return_date) : '-';
                const searchKey = `${book.book_title || ''} ${book.author || ''} ${book.isbn || ''}`.trim();
                const filterStatus = book.is_lost ? 'lost' : (book.is_damaged ? 'damaged' : (book.is_overdue ? 'overdue' : (book.is_available ? 'free' : 'occupied')));
                const lossInfo = book.loss_info || null;
                const damageInfo = book.damage_info || null;
                const lossId = `loss-${book.bookID}`;
                const damageId = `damage-${book.bookID}`;
                const lossPayId = `loss-pay-${book.bookID}`;
                const damagePayId = `damage-pay-${book.bookID}`;

                html += `
                    <div class="col-md-4 mb-3 book-widget-col">
                        <div class="book-widget ${statusClass} ${book.is_lost ? 'lost' : ''} ${book.is_damaged ? 'damaged' : ''}" data-search="${searchKey}" data-filter="${filterStatus}">
                            <div class="d-flex align-items-center mb-2">
                                <div class="stat-icon"><i class="fa fa-book"></i></div>
                                <div>
                                    <div class="fw-bold">${book.book_title || '-'}</div>
                                    <small class="text-muted">${book.author || '-'}</small>
                                </div>
                            </div>
                            <div class="mb-1"><strong>ISBN:</strong> ${book.isbn || '-'}</div>
                            <div class="mb-1"><strong>Status:</strong> ${statusText}</div>
                            ${book.is_lost ? `<span class="badge badge-custom" style="background:#6f42c1;color:#fff;">Lost</span>` : ''}
                            ${book.is_damaged ? `<span class="badge badge-custom" style="background:#fd7e14;color:#fff;">Damaged</span>` : ''}
                            <div class="mt-2">
                                <button class="btn btn-sm btn-primary" onclick="editBook(${book.bookID})">
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                            </div>
                            ${book.is_lost ? `
                                <span class="badge badge-custom toggle-occupied" style="background:#6f42c1;color:#fff;cursor:pointer;" data-target="#${lossId}">
                                    <i class="fa fa-caret-down"></i> Lost
                                </span>
                                <div id="${lossId}" class="mt-2 d-none">
                                    <div class="mb-1"><strong>Lost By:</strong> ${lossInfo && lossInfo.lost_by ? lossInfo.lost_by : '-'}</div>
                                    ${lossInfo && lossInfo.student ? `
                                        <div class="mb-1"><strong>Student:</strong> ${lossInfo.student.name} (${lossInfo.student.admission_number}) - ${lossInfo.student.class_name}</div>
                                    ` : ''}
                                    <div class="mb-1"><strong>Description:</strong> ${lossInfo && lossInfo.description ? lossInfo.description : '-'}</div>
                                    <div class="mb-1"><strong>Return:</strong>
                                        ${lossInfo && lossInfo.payment_status === 'paid' ? `
                                            <span class="badge badge-custom badge-available">Paid</span>
                                            <small class="text-muted">(${lossInfo.payment_method === 'cash' ? 'Cash' : 'Replace'}${lossInfo.payment_method === 'cash' && lossInfo.payment_amount ? ': ' + lossInfo.payment_amount : ''})</small>
                                        ` : `
                                            <span class="badge badge-custom badge-issued">Unpaid</span>
                                        `}
                                    </div>
                                    ${lossInfo && lossInfo.payment_status !== 'paid' ? `
                                    <div id="${lossPayId}" class="mt-2">
                                        <div class="mb-2">
                                            <label class="form-label">Return Method</label>
                                            <select class="form-control payment-method">
                                                <option value="replace">Replace New</option>
                                                <option value="cash">Cash</option>
                                            </select>
                                        </div>
                                        <div class="mb-2 payment-amount" style="display:none;">
                                            <label class="form-label">Amount</label>
                                            <input type="number" class="form-control payment-amount-input" min="0" step="0.01">
                                        </div>
                                        <button class="btn btn-sm btn-primary" onclick="submitPayment('lost', ${lossInfo ? lossInfo.lossID : 'null'}, '#${lossPayId}')">
                                            <i class="fa fa-save"></i> Save
                                        </button>
                                    </div>
                                    ` : ''}
                                </div>
                            ` : ''}
                            ${book.is_damaged ? `
                                <span class="badge badge-custom toggle-occupied" style="background:#fd7e14;color:#fff;cursor:pointer;" data-target="#${damageId}">
                                    <i class="fa fa-caret-down"></i> Damaged
                                </span>
                                <div id="${damageId}" class="mt-2 d-none">
                                    <div class="mb-1"><strong>Damaged By:</strong> ${damageInfo && damageInfo.damaged_by ? damageInfo.damaged_by : '-'}</div>
                                    ${damageInfo && damageInfo.student ? `
                                        <div class="mb-1"><strong>Student:</strong> ${damageInfo.student.name} (${damageInfo.student.admission_number}) - ${damageInfo.student.class_name}</div>
                                    ` : ''}
                                    <div class="mb-1"><strong>Description:</strong> ${damageInfo && damageInfo.description ? damageInfo.description : '-'}</div>
                                    <div class="mb-1"><strong>Return:</strong>
                                        ${damageInfo && damageInfo.payment_status === 'paid' ? `
                                            <span class="badge badge-custom badge-available">Paid</span>
                                            <small class="text-muted">(${damageInfo.payment_method === 'cash' ? 'Cash' : 'Replace'}${damageInfo.payment_method === 'cash' && damageInfo.payment_amount ? ': ' + damageInfo.payment_amount : ''})</small>
                                        ` : `
                                            <span class="badge badge-custom badge-issued">Unpaid</span>
                                        `}
                                    </div>
                                    ${damageInfo && damageInfo.payment_status !== 'paid' ? `
                                    <div id="${damagePayId}" class="mt-2">
                                        <div class="mb-2">
                                            <label class="form-label">Return Method</label>
                                            <select class="form-control payment-method">
                                                <option value="replace">Replace New</option>
                                                <option value="cash">Cash</option>
                                            </select>
                                        </div>
                                        <div class="mb-2 payment-amount" style="display:none;">
                                            <label class="form-label">Amount</label>
                                            <input type="number" class="form-control payment-amount-input" min="0" step="0.01">
                                        </div>
                                        <button class="btn btn-sm btn-primary" onclick="submitPayment('damaged', ${damageInfo ? damageInfo.damageID : 'null'}, '#${damagePayId}')">
                                            <i class="fa fa-save"></i> Save
                                        </button>
                                    </div>
                                    ` : ''}
                                </div>
                            ` : ''}
                            ${(!book.is_lost && !book.is_damaged && !book.is_available) ? `
                                <span class="badge badge-custom badge-issued toggle-occupied" data-target="#${widgetId}" style="cursor:pointer;">Occupied</span>
                                <div id="${widgetId}" class="mt-2 d-none">
                                    <div class="mb-1"><strong>Occupied By:</strong> ${occupiedBy}</div>
                                    <div class="mb-1"><strong>Return Date:</strong> ${expectedReturn}</div>
                                    <button class="btn btn-sm btn-success mt-1" onclick="openReturnModalByIsbn('${book.isbn || ''}', '${book.borrow_expected_return_date || ''}')">
                                        <i class="fa fa-check"></i> Return
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
        }
        $('#viewBooksWidgetContainer').html(html);
    }

    function applyViewBooksFilters() {
        const term = $('#viewBooksSearch').val().trim().toLowerCase();
        const filter = $('#viewBooksFilter').val();
        $('#viewBooksWidgetContainer .book-widget').each(function() {
            const hay = ($(this).data('search') || '').toLowerCase();
            const status = $(this).data('filter') || '';
            const matchesSearch = hay.indexOf(term) !== -1;
            const matchesFilter = !filter || status === filter;
            $(this).closest('.book-widget-col').toggle(matchesSearch && matchesFilter);
        });
    }

    function loadLibraryStudents() {
        const filter = $('#studentFilter').val();
        $('#studentSearch').val('');
        $('#studentsWidgetContainer').html('<div class="col-12 text-center">Loading...</div>');
        $.ajax({
            url: '{{ route("get_library_students") }}',
            type: 'GET',
            data: { filter: filter },
            success: function(response) {
                if (response.success) {
                    $('#studentFilterTotal').text(response.total || 0);
                    renderStudentWidgets(response.items || []);
                } else {
                    const msg = response.message || 'Error loading students';
                    console.error(msg);
                    $('#studentsWidgetContainer').html('<div class="col-12 text-center">' + msg + '</div>');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || xhr.responseText || 'Error loading students';
                console.error(msg);
                $('#studentsWidgetContainer').html('<div class="col-12 text-center">' + msg + '</div>');
            }
        });
    }

    function renderStudentWidgets(items) {
        let html = '';
        if (!items || items.length === 0) {
            html = '<div class="col-12 text-center">No records found</div>';
        } else {
            items.forEach(item => {
                const student = item.student || {};
                const book = item.book || {};
                const gender = (student.gender || '').toLowerCase();
                const placeholder = gender === 'female'
                    ? '{{ asset('images/female.png') }}'
                    : '{{ asset('images/male.png') }}';
                const photo = student.photo || placeholder;
                const expected = item.expected_return_date ? formatDate(item.expected_return_date) : '-';
                const type = item.type || '';
                const payId = `student-pay-${type}-${item.lossID || item.damageID || item.borrowID}`;
                const searchKey = `${student.name || ''} ${student.class_name || ''} ${book.title || ''} ${book.isbn || ''}`.toLowerCase();
                const statusClass = type === 'lost'
                    ? 'status-lost'
                    : type === 'damaged'
                        ? 'status-damaged'
                        : type === 'overdue'
                            ? 'status-overdue'
                            : type === 'not_due'
                                ? 'status-not-due'
                                : 'status-occupied';
                html += `
                    <div class="student-widget-col">
                        <div class="student-widget ${statusClass}" data-search="${searchKey}">
                            <div class="student-header">
                                <img src="${photo}" alt="Student Photo" onerror="this.src='${placeholder}'">
                            </div>
                            <div class="student-body">
                                <h3>${student.name || '-'}</h3>
                                <p class="form">${student.class_name || '-'}</p>
                                <div class="info">
                                    <p><strong>Book:</strong> ${book.title || '-'}</p>
                                    <p><strong>ISBN:</strong> ${book.isbn || '-'}</p>
                                    ${type === 'occupied' || type === 'overdue' || type === 'not_due' ? `
                                        <p class="return-date"><strong>Return Date:</strong> ${expected}</p>
                                        <button class="btn btn-sm btn-success" onclick="openReturnModal(${item.borrowID}, '${item.expected_return_date || ''}')">
                                            <i class="fa fa-check"></i> Return
                                        </button>
                                    ` : `
                                        <p><strong>Return:</strong>
                                            ${item.payment_status === 'paid' ? `
                                                <span class="badge badge-custom badge-available">Paid</span>
                                                <small class="text-muted">(${item.payment_method === 'cash' ? 'Cash' : 'Replace'}${item.payment_method === 'cash' && item.payment_amount ? ': ' + item.payment_amount : ''})</small>
                                            ` : `
                                                <span class="badge badge-custom badge-issued">Unpaid</span>
                                                <button class="btn btn-sm btn-success ml-2 toggle-occupied" data-target="#${payId}">
                                                    <i class="fa fa-caret-down"></i> Record Return
                                                </button>
                                            `}
                                        </p>
                                        <div id="${payId}" class="mt-2 d-none">
                                            <div class="mb-2">
                                                <label class="form-label">Return Method</label>
                                                <select class="form-control payment-method">
                                                    <option value="replace">Replace New</option>
                                                    <option value="cash">Cash</option>
                                                </select>
                                            </div>
                                            <div class="mb-2 payment-amount" style="display:none;">
                                                <label class="form-label">Amount</label>
                                                <input type="number" class="form-control payment-amount-input" min="0" step="0.01">
                                            </div>
                                            <button class="btn btn-sm btn-primary" onclick="submitPayment('${type}', ${item.lossID || item.damageID}, '#${payId}')">
                                                <i class="fa fa-save"></i> Save
                                            </button>
                                        </div>
                                    `}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        $('#studentsWidgetContainer').html(html);
    }

    function applyStudentSearch() {
        const term = $('#studentSearch').val().trim().toLowerCase();
        $('#studentsWidgetContainer .student-widget').each(function() {
            const hay = ($(this).data('search') || '').toLowerCase();
            $(this).closest('.student-widget-col').toggle(hay.indexOf(term) !== -1);
        });
    }

    // DataTable instance for borrows
    let borrowsDataTable = null;

    // Book add/edit state
    let bookMode = 'add';
    let isbnCheckTimeout = null;

    // Function to format date (e.g., "17 November 2025")
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        return `${day} ${month} ${year}`;
    }

    function loadBorrows() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        
        // Show loading state
        $('#borrowsTableBody').html('<tr><td colspan="11" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading borrow records...</td></tr>');
        
        $.ajax({
            url: '{{ route("get_book_borrows") }}',
            type: 'GET',
            data: {
                status: $('#filterBorrowStatus').val(),
                dueFilter: $('#filterBorrowDue').val()
            },
            success: function(response) {
                if (response.success) {
                    let html = '';
                    if (response.borrows && response.borrows.length > 0) {
                        response.borrows.forEach((borrow, index) => {
                            const student = borrow.student;
                            const book = borrow.book;
                            const statusClass = borrow.status === 'borrowed' ? 'badge-borrowed' : 'badge-returned';
                            const statusText = borrow.status === 'borrowed' ? 'Borrowed' : 'Returned';
                            
                            // Format dates
                            const borrowDate = formatDate(borrow.borrow_date);
                            const expectedReturnDate = formatDate(borrow.expected_return_date);
                            const returnDate = formatDate(borrow.return_date);
                            
                            // Get student name
                            const studentName = student ? 
                                (student.first_name + ' ' + (student.middle_name || '') + ' ' + student.last_name).trim() : 
                                '-';
                            
                            // Get admission number
                            const admissionNo = student ? (student.admission_number || '-') : '-';
                            
                            // Check if student is graduated (status not Active)
                            const studentStatus = student ? (student.status || 'Unknown') : 'Unknown';
                            const isGraduated = studentStatus !== 'Active';
                            const studentNameDisplay = isGraduated ? 
                                `${studentName} <span class="badge badge-warning" title="Graduated">Graduated</span>` : 
                                studentName;
                            
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${studentNameDisplay}</td>
                                    <td>${admissionNo}</td>
                                    <td>${book ? book.book_title : '-'}</td>
                                    <td>${book && book.class ? book.class.class_name : '-'}</td>
                                    <td>${book && book.subject ? book.subject.subject_name : '-'}</td>
                                    <td>${borrowDate}</td>
                                    <td>${expectedReturnDate}</td>
                                    <td>${returnDate}</td>
                                    <td><span class="badge badge-custom ${statusClass}">${statusText}</span></td>
                                    <td>
                                        ${borrow.status === 'borrowed' ? `
                                            <button class="btn btn-sm btn-success" onclick="openReturnModal(${borrow.borrowID}, '${borrow.expected_return_date || ''}')" title="Return Book">
                                                <i class="fa fa-check"></i> Return
                                            </button>
                                        ` : ''}
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="11" class="text-center">No borrow records found</td></tr>';
                    }
                    $('#borrowsTableBody').html(html);
                    
                    // New approach to fix the DataTables re-initialization warning
                    if ($.fn.DataTable.isDataTable('#borrowsTable')) {
                        $('#borrowsTable').DataTable().destroy();
                    }
                    
                    borrowsDataTable = $('#borrowsTable').DataTable({
                        "order": [[6, "desc"]], // Sort by borrow date descending
                        "pageLength": 25,
                        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        "autoWidth": false, // Let CSS control width
                        "responsive": false, // Disable responsive mode to allow horizontal scroll
                        "language": {
                            "search": "Search:",
                            "lengthMenu": "Show _MENU_ records per page",
                            "info": "Showing _START_ to _END_ of _TOTAL_ records",
                            "infoEmpty": "No records available",
                            "infoFiltered": "(filtered from _MAX_ total records)",
                            "zeroRecords": "No matching records found"
                        },
                        "columnDefs": [
                            { "orderable": false, "targets": [10] } // Disable sorting on Actions column
                        ]
                    });
                } else {
                    $('#borrowsTableBody').html('<tr><td colspan="11" class="text-center">Error loading borrow records</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading borrows:', error);
                $('#borrowsTableBody').html('<tr><td colspan="11" class="text-center">Error loading borrow records. Please try again.</td></tr>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load borrow records: ' + (xhr.responseJSON?.error || error)
                });
            }
        });
    }

    function loadLostBooks() {
        $.ajax({
            url: '{{ route("get_book_losses") }}',
            type: 'GET',
            data: {
                lost_by: $('#filterLostBy').val()
            },
            success: function(response) {
                if (response.success) {
                    renderLossDamageWidgets('#lostBooksContainer', response.losses || [], 'lost');
                } else {
                    $('#lostBooksContainer').html('<div class="col-12 text-center">Error loading lost books</div>');
                }
            },
            error: function() {
                $('#lostBooksContainer').html('<div class="col-12 text-center">Error loading lost books</div>');
            }
        });
    }

    function loadDamagedBooks() {
        $.ajax({
            url: '{{ route("get_book_damages") }}',
            type: 'GET',
            data: {
                damaged_by: $('#filterDamagedBy').val()
            },
            success: function(response) {
                if (response.success) {
                    renderLossDamageWidgets('#damagedBooksContainer', response.damages || [], 'damaged');
                } else {
                    $('#damagedBooksContainer').html('<div class="col-12 text-center">Error loading damaged books</div>');
                }
            },
            error: function() {
                $('#damagedBooksContainer').html('<div class="col-12 text-center">Error loading damaged books</div>');
            }
        });
    }

    function renderLossDamageWidgets(container, items, type) {
        let html = '';
        if (!items || items.length === 0) {
            html = '<div class="col-12 text-center">No records found</div>';
        } else {
            items.forEach(item => {
                const book = item.book || {};
                const student = item.student || null;
                const studentName = student
                    ? `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.trim()
                    : '-';
                const className = student && student.subclass && student.subclass.class
                    ? student.subclass.class.class_name
                    : '-';
                const toggleId = `${type}-${item[type === 'lost' ? 'lossID' : 'damageID']}`;
                const byLabel = type === 'lost' ? 'Lost By' : 'Damaged By';
                const byValue = item[type === 'lost' ? 'lost_by' : 'damaged_by'] === 'student' ? 'Student' : 'Other';
                const paymentStatus = item.payment_status || 'unpaid';
                const paymentMethod = item.payment_method || '';
                const paymentAmount = item.payment_amount || '';
                const paymentId = `${type}-pay-${item[type === 'lost' ? 'lossID' : 'damageID']}`;
                html += `
                    <div class="col-md-4 mb-3 book-widget-col">
                        <div class="book-widget ${type}">
                            <div class="d-flex align-items-center mb-2">
                                <div class="stat-icon"><i class="fa fa-book"></i></div>
                                <div>
                                    <div class="fw-bold">${book.book_title || '-'}</div>
                                    <small class="text-muted">${book.author || '-'}</small>
                                </div>
                            </div>
                            <div class="mb-1"><strong>ISBN:</strong> ${book.isbn || '-'}</div>
                            <div class="mb-1"><strong>${byLabel}:</strong> ${byValue}</div>
                            <span class="badge badge-custom badge-issued toggle-occupied" data-target="#${toggleId}" style="cursor:pointer;">
                                <i class="fa fa-caret-down"></i> Details
                            </span>
                            <div id="${toggleId}" class="mt-2 d-none">
                                ${item[type === 'lost' ? 'lost_by' : 'damaged_by'] === 'student' ? `
                                    <div class="mb-1"><strong>Name:</strong> ${studentName}</div>
                                    <div class="mb-1"><strong>Class:</strong> ${className}</div>
                                    <button class="btn btn-sm btn-primary" onclick="showParentMessageModal(${student ? student.studentID : 'null'})">
                                        <i class="fa fa-envelope"></i> Message Parent
                                    </button>
                                ` : ''}
                                <div class="mt-2"><strong>Description:</strong> ${item.description || '-'}</div>
                                <div class="mt-2">
                                    <strong>Return:</strong>
                                    ${paymentStatus === 'paid' ? `
                                        <span class="badge badge-custom badge-available">Paid</span>
                                        <small class="text-muted">(${paymentMethod === 'cash' ? 'Cash' : 'Replace'}${paymentMethod === 'cash' && paymentAmount ? ': ' + paymentAmount : ''})</small>
                                    ` : `
                                        <span class="badge badge-custom badge-issued">Unpaid</span>
                                    `}
                                </div>
                                ${paymentStatus !== 'paid' ? `
                                    <div id="${paymentId}" class="mt-2">
                                        <div class="mb-2">
                                            <label class="form-label">Return Method</label>
                                            <select class="form-control payment-method" data-target="#${paymentId}">
                                                <option value="replace">Replace New</option>
                                                <option value="cash">Cash</option>
                                            </select>
                                        </div>
                                        <div class="mb-2 payment-amount" style="display:none;">
                                            <label class="form-label">Amount</label>
                                            <input type="number" class="form-control payment-amount-input" min="0" step="0.01">
                                        </div>
                                        <button class="btn btn-sm btn-primary" onclick="submitPayment('${type}', ${item[type === 'lost' ? 'lossID' : 'damageID']}, '#${paymentId}')">
                                            <i class="fa fa-save"></i> Save
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        $(container).html(html);
    }

    function loadSubjectsByClass(classID) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        
        console.log('Loading subjects for class:', classID);
        
        // Show loading state
        $('#bookSubjectID').html('<option value="">Loading subjects...</option>');
        
        $.ajax({
            url: '{{ route("get_subjects_by_class") }}',
            type: 'GET',
            data: { classID: classID },
            success: function(response) {
                console.log('Subjects response:', response);
                if (response.success && response.subjects) {
                    let html = '<option value="">Select Subject</option>';
                    if (response.subjects.length > 0) {
                        response.subjects.forEach(subject => {
                            if (subject && subject.subjectID) {
                                html += `<option value="${subject.subjectID}">${subject.subject_name}</option>`;
                            }
                        });
                    } else {
                        html += '<option value="">No subjects found for this class</option>';
                    }
                    $('#bookSubjectID').html(html);
                } else {
                    $('#bookSubjectID').html('<option value="">No subjects found</option>');
                    console.error('No subjects in response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading subjects:', error);
                console.error('Response:', xhr.responseText);
                $('#bookSubjectID').html('<option value="">Error loading subjects</option>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading subjects: ' + (xhr.responseJSON?.error || error)
                });
            }
        });
    }

    function searchStudents(search) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        
        if (!search || search.trim().length < 2) {
            $('#studentResults').hide();
            return;
        }
        
        console.log('Searching students:', search);
        
        $.ajax({
            url: '{{ route("get_students") }}',
            type: 'GET',
            data: { search: search.trim() },
            success: function(response) {
                console.log('Students response:', response);
                if (response.success && response.students) {
                    let html = '';
                    if (response.students.length > 0) {
                        response.students.forEach(student => {
                            const subclass = student.subclass;
                            const className = subclass && subclass.class ? subclass.class.class_name : '';
                            const fullName = `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.trim();
                            const studentName = fullName.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            
                            html += `
                                <div class="p-2 border-bottom student-option" 
                                     onclick="selectStudent(${student.studentID}, '${studentName}')"
                                     style="cursor: pointer; background-color: #f8f9fa;"
                                     onmouseover="this.style.backgroundColor='#e9ecef'"
                                     onmouseout="this.style.backgroundColor='#f8f9fa'">
                                    <strong>${fullName}</strong><br>
                                    <small class="text-muted">${student.admission_number || 'N/A'} - ${className || 'N/A'}</small>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="p-2 text-muted">No students found</div>';
                    }
                    $('#studentResults').html(html).show();
                } else {
                    $('#studentResults').html('<div class="p-2 text-muted">No students found</div>').show();
                    console.error('No students in response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error searching students:', error);
                console.error('Response:', xhr.responseText);
                $('#studentResults').html('<div class="p-2 text-danger">Error searching students</div>').show();
            }
        });
    }

    function selectStudent(studentID, name) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        $('#borrowStudentID').val(studentID);
        $('#searchStudent').val(name);
        $('#studentResults').hide();
    }

    function setBookMode(mode) {
        bookMode = mode;
        if (mode === 'edit') {
            $('#bookEntriesSection').show();
            $('#saveBooksBtn').hide();
            $('#saveBookBtn').removeClass('d-none');
            $('#addIsbnRowBtn').hide();
            $('#isbnList .remove-isbn-btn').addClass('d-none');
        } else {
            $('#bookEntriesSection').show();
            $('#saveBooksBtn').show();
            $('#saveBookBtn').addClass('d-none');
            $('#addIsbnRowBtn').show();
            updateIsbnRemoveButtons();
        }
    }

    function resetBookForm() {
        $('#bookTitle').val('');
        $('#bookAuthor').val('');
        $('#bookPublisher').val('');
        $('#bookPublicationYear').val('');
        $('#bookDescription').val('');
        $('#isbnList').html(getIsbnRowHtml());
        updateIsbnRemoveButtons();
    }

    function addIsbnRow() {
        $('#isbnList').append(getIsbnRowHtml());
        updateIsbnRemoveButtons();
    }

    function getIsbnRowHtml() {
        return `
            <div class="row align-items-end isbn-row mb-2">
                <div class="col-md-8">
                    <label class="form-label">ISBN <span class="text-danger">*</span></label>
                    <input type="text" class="form-control isbn-input" placeholder="Enter ISBN">
                    <small class="text-danger d-none isbn-error">ISBN is already taken.</small>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-danger remove-isbn-btn">
                        <i class="fa fa-trash"></i> Remove
                    </button>
                </div>
            </div>
        `;
    }

    function updateIsbnRemoveButtons() {
        const rows = $('#isbnList .isbn-row');
        if (rows.length <= 1) {
            rows.find('.remove-isbn-btn').addClass('d-none');
            return;
        }
        rows.find('.remove-isbn-btn').removeClass('d-none');
    }

    function checkIsbnAvailability($input, isbn, bookID) {
        const $error = $input.closest('.isbn-row').find('.isbn-error');
        if (!isbn) {
            $error.addClass('d-none');
            $input.data('isbn-available', true);
            return;
        }
        $.ajax({
            url: '{{ route("check_isbn") }}',
            type: 'GET',
            data: { isbn: isbn, bookID: bookID || '' },
            success: function(response) {
                if (response.success && response.available) {
                    $error.addClass('d-none');
                    $input.data('isbn-available', true);
                } else {
                    $error.removeClass('d-none').text('ISBN is already taken.');
                    $input.data('isbn-available', false);
                }
            },
            error: function() {
                $error.removeClass('d-none').text('Failed to verify ISBN.');
                $input.data('isbn-available', false);
            }
        });
    }

    function saveBooks() {
        if (!$('#bookClassID').val()) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please select a class' });
            return;
        }
        if (!$('#bookSubjectID').val()) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please select a subject' });
            return;
        }
        if (!$('#bookTitle').val().trim()) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please enter book title' });
            return;
        }

        const isbnInputs = $('#isbnList .isbn-input');
        const isbns = [];
        let hasInvalid = false;
        isbnInputs.each(function() {
            const isbn = $(this).val().trim();
            if (!isbn) {
                hasInvalid = true;
                return;
            }
            const available = $(this).data('isbn-available');
            if (available === false) {
                hasInvalid = true;
                return;
            }
            isbns.push(isbn);
        });

        const duplicates = isbns.filter((v, i, a) => a.indexOf(v) !== i);
        if (duplicates.length > 0) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Duplicate ISBN in list' });
            return;
        }

        if (hasInvalid || isbns.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please enter valid ISBN(s)' });
            return;
        }

        const $saveBooksBtn = $('#saveBooksBtn');
        const originalHtml = $saveBooksBtn.html();
        $saveBooksBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $.ajax({
            url: '{{ route("store_book") }}',
            type: 'POST',
            data: {
                classID: $('#bookClassID').val(),
                subjectID: $('#bookSubjectID').val(),
                book_title: $('#bookTitle').val().trim(),
                author: $('#bookAuthor').val().trim(),
                publisher: $('#bookPublisher').val().trim(),
                publication_year: $('#bookPublicationYear').val(),
                description: $('#bookDescription').val().trim(),
                isbns: isbns
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Books added successfully',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        $('#bookModal').modal('hide');
                        loadBooks();
                        location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'An error occurred' });
                    $saveBooksBtn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while saving books.';
                if (xhr.responseJSON) {
                    errorMessage = xhr.responseJSON.message || xhr.responseJSON.error || errorMessage;
                }
                Swal.fire({ icon: 'error', title: 'Error', text: errorMessage });
                $saveBooksBtn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    function showAddBookModal() {
        if (typeof jQuery === 'undefined') {
            alert('jQuery is not loaded. Please refresh the page.');
            console.error('jQuery not loaded');
            return;
        }
        
        console.log('showAddBookModal called');
        try {
            setBookMode('add');
            jQuery('#bookModalTitle').html('<i class="fa fa-book"></i> Add New Book');
            jQuery('#bookForm')[0].reset();
            jQuery('#bookID').val('');
            jQuery('#bookStatusDiv').hide();
            jQuery('#bookSubjectID').html('<option value="">Select Subject</option>');
            jQuery('#bookClassID').val(''); // Reset class selection
            jQuery('#isbnList').html(getIsbnRowHtml());
            updateIsbnRemoveButtons();
            
            // Reset save button state
            jQuery('#saveBookBtn').prop('disabled', false);
            jQuery('#saveBookBtnText').text('Save');
            
            // Re-attach event handler for class change (in case modal was recreated)
            jQuery('#bookClassID').off('change').on('change', function() {
                const classID = jQuery(this).val();
                console.log('Class changed in modal:', classID);
                if (classID) {
                    loadSubjectsByClass(classID);
                } else {
                    jQuery('#bookSubjectID').html('<option value="">Select Subject</option>');
                }
            });
            
            // Check if modal element exists
            var $modal = jQuery('#bookModal');
            if ($modal.length === 0) {
                console.error('Modal element not found!');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Modal element not found. Please refresh the page.'
                });
                return;
            }
            
            console.log('Showing modal...');
            // Show modal using Bootstrap 4
            if (typeof $modal.modal === 'function') {
            $modal.modal('show');
            } else {
                $modal.css('display', 'block');
                $modal.addClass('show');
                jQuery('body').addClass('modal-open');
                jQuery('.modal-backdrop').remove();
                jQuery('body').append('<div class="modal-backdrop fade show"></div>');
            }
            
            // Verify modal is shown
            setTimeout(function() {
                if ($modal.hasClass('show')) {
                    console.log('Modal is now visible');
                } else {
                    console.error('Modal failed to show');
                    // Try alternative method
                    $modal.css('display', 'block');
                    $modal.addClass('show');
                    jQuery('body').addClass('modal-open');
                    jQuery('.modal-backdrop').remove();
                    jQuery('body').append('<div class="modal-backdrop fade show"></div>');
                }
            }, 200);
        } catch (error) {
            console.error('Error showing modal:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error opening modal: ' + error.message
            });
        }
    }

    function editBook(bookID) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        $.ajax({
            url: '{{ route("get_books") }}',
            type: 'GET',
            data: {},
            success: function(response) {
                if (response.success) {
                    const book = response.books.find(b => b.bookID == bookID);
                    if (book) {
                        setBookMode('edit');
                        $('#bookModalTitle').html('<i class="fa fa-edit"></i> Edit Book');
                        $('#bookID').val(book.bookID);
                        $('#bookClassID').val(book.classID);
                        loadSubjectsByClass(book.classID);
                        setTimeout(() => {
                            $('#bookSubjectID').val(book.subjectID);
                        }, 500);
                        $('#bookTitle').val(book.book_title);
                        $('#bookAuthor').val(book.author || '');
                        $('#bookPublisher').val(book.publisher || '');
                        $('#bookPublicationYear').val(book.publication_year || '');
                        $('#bookDescription').val(book.description || '');
                        $('#bookStatus').val(book.status);
                        $('#bookStatusDiv').show();
                        $('#isbnList').html(getIsbnRowHtml());
                        const $firstIsbn = $('#isbnList .isbn-input').first();
                        $firstIsbn.val(book.isbn || '');
                        $firstIsbn.data('isbn-available', true);
                        updateIsbnRemoveButtons();
                        $('#isbnList .remove-isbn-btn').addClass('d-none');
                        $('#bookModal').modal('show');
                    }
                }
            }
        });
    }

    function saveBook() {
        if (typeof jQuery === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'jQuery is not loaded. Please refresh the page.'
            });
            return;
        }
        var $ = jQuery;
        
        // Validate required fields
        if (!$('#bookClassID').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a class'
            });
            return;
        }
        
        if (!$('#bookSubjectID').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a subject'
            });
            return;
        }
        
        if (!$('#bookTitle').val().trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter book title'
            });
            return;
        }
        
        const editIsbn = $('#isbnList .isbn-input').first().val().trim();
        if (!editIsbn) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter ISBN'
            });
            return;
        }

        const available = $('#isbnList .isbn-input').first().data('isbn-available');
        if (available === false) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'ISBN is already taken'
            });
            return;
        }
        
        const formData = {
            classID: $('#bookClassID').val(),
            subjectID: $('#bookSubjectID').val(),
            book_title: $('#bookTitle').val().trim(),
            author: $('#bookAuthor').val().trim(),
            isbn: editIsbn,
            publisher: $('#bookPublisher').val().trim(),
            publication_year: $('#bookPublicationYear').val(),
            description: $('#bookDescription').val().trim(),
        };

        const bookID = $('#bookID').val();
        const url = bookID ? `{{ url('update_book') }}/${bookID}` : '{{ route("store_book") }}';
        const method = 'POST';
        const isEdit = !!bookID;

        if (bookID) {
            formData.status = $('#bookStatus').val();
        }

        // Show loading state
        const $saveBtn = $('#saveBookBtn');
        const $saveBtnText = $('#saveBookBtnText');
        const originalText = $saveBtnText.text();
        $saveBtn.prop('disabled', true);
        $saveBtnText.html('<i class="fa fa-spinner fa-spin"></i> ' + (isEdit ? 'Updating...' : 'Saving...'));

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || (isEdit ? 'Book updated successfully' : 'Book added successfully'),
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        $('#bookModal').modal('hide');
                        loadBooks();
                        location.reload(); // Reload to update statistics
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'An error occurred'
                    });
                    $saveBtn.prop('disabled', false);
                    $saveBtnText.text(originalText);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while saving the book.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.errors) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        const errorList = Object.values(errors).flat().join('<br>');
                        errorMessage = errorList;
                    }
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || response.error || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.responseText || errorMessage;
                    }
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage,
                    confirmButtonColor: '#940000'
                });
                $saveBtn.prop('disabled', false);
                $saveBtnText.text(originalText);
            }
        });
    }

    function deleteBook(bookID) {
        if (typeof jQuery === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'jQuery is not loaded. Please refresh the page.'
            });
            return;
        }
        var $ = jQuery;
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: `{{ url('delete_book') }}/${bookID}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Book deleted successfully',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                loadBooks();
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'An error occurred';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error
                        });
                    }
                });
            }
        });
    }

    function showBorrowBookModal() {
        if (typeof jQuery === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'jQuery is not loaded. Please refresh the page.'
            });
            return;
        }
        var $ = jQuery;
        $('#borrowForm')[0].reset();
        $('#borrowStudentID').val('');
        $('#studentResults').hide();
        $('#borrowBookInfo').hide();
        $('#borrowIsbnError').addClass('d-none');
        $('#borrowISBN').val('');
        
        // Reset save button state
        $('#saveBorrowBtn').prop('disabled', false);
        $('#saveBorrowBtnText').text('Save');
        
        // Clear student search
        $('#searchStudent').val('');
        $('#borrowStudentID').val('');
        $('#studentResults').html('').hide();
        
        // Ensure search event handler is attached (in case modal was recreated)
        $('#searchStudent').off('input').on('input', function() {
            if (window.searchTimeout) {
                clearTimeout(window.searchTimeout);
            }
            const search = $(this).val();
            console.log('Student search input in modal:', search);
            if (search && search.trim().length >= 2) {
                window.searchTimeout = setTimeout(() => searchStudents(search.trim()), 300);
            } else {
                $('#studentResults').hide();
            }
        });
        
        $('#borrowModal').modal('show');
    }

    function lookupBookByIsbn(isbn) {
        if (!isbn || isbn.length < 2) {
            $('#borrowBookInfo').hide();
            $('#borrowIsbnError').addClass('d-none');
            return;
        }
        
        $.ajax({
            url: '{{ route("get_book_by_isbn") }}',
            type: 'GET',
            data: { isbn: isbn },
            success: function(response) {
                if (response.success && response.book) {
                    if (!response.is_available) {
                        $('#borrowBookInfo').hide();
                        const borrower = response.borrower ? response.borrower.name : 'another student';
                        $('#borrowIsbnError').removeClass('d-none').text('Already taken by ' + borrower + '.');
                        return;
                    }
                    $('#borrowIsbnError').addClass('d-none');
                    $('#borrowBookTitle').text(response.book.book_title || '-');
                    $('#borrowBookClass').text(response.book.class ? response.book.class.class_name : '-');
                    $('#borrowBookSubject').text(response.book.subject ? response.book.subject.subject_name : '-');
                    $('#borrowBookInfo').show();
                } else {
                    $('#borrowBookInfo').hide();
                    $('#borrowIsbnError').removeClass('d-none').text('Book not found.');
                }
            },
            error: function() {
                $('#borrowBookInfo').hide();
                $('#borrowIsbnError').removeClass('d-none').text('Failed to lookup book.');
            }
        });
    }

    function showLostBookModal() {
        $('#lostForm')[0].reset();
        $('#lostStudentID').val('');
        $('#lostStudentResults').html('').hide();
        $('#lostBy').val('student');
        $('#lostStudentSection').show();
        $('#lostBookInfo').hide();
        $('#lostModal').modal('show');
    }

    function showDamagedBookModal() {
        $('#damagedForm')[0].reset();
        $('#damagedStudentID').val('');
        $('#damagedStudentResults').html('').hide();
        $('#damagedBy').val('student');
        $('#damagedStudentSection').show();
        $('#damagedBookInfo').hide();
        $('#damagedModal').modal('show');
    }

    function lookupLossBookByIsbn(isbn) {
        if (!isbn) {
            $('#lostBookInfo').hide();
            return;
        }
        $.ajax({
            url: '{{ route("get_book_by_isbn") }}',
            type: 'GET',
            data: { isbn: isbn },
            success: function(response) {
                if (response.success && response.book) {
                    $('#lostBookTitle').text(response.book.book_title || '-');
                    $('#lostBookClass').text(response.book.class ? response.book.class.class_name : '-');
                    $('#lostBookSubject').text(response.book.subject ? response.book.subject.subject_name : '-');
                    $('#lostBookStatus').text(response.is_available ? 'Free' : 'Occupied');
                    $('#lostBookInfo').show();

                    if ($('#lostBy').val() === 'student' && response.borrower) {
                        $('#lostStudentID').val(response.borrower.studentID);
                        $('#lostStudentSearch').val(response.borrower.name);
                        $('#lostStudentResults').hide();
                    }
                } else {
                    $('#lostBookInfo').hide();
                }
            },
            error: function() {
                $('#lostBookInfo').hide();
            }
        });
    }

    function lookupDamageBookByIsbn(isbn) {
        if (!isbn) {
            $('#damagedBookInfo').hide();
            return;
        }
        $.ajax({
            url: '{{ route("get_book_by_isbn") }}',
            type: 'GET',
            data: { isbn: isbn },
            success: function(response) {
                if (response.success && response.book) {
                    $('#damagedBookTitle').text(response.book.book_title || '-');
                    $('#damagedBookClass').text(response.book.class ? response.book.class.class_name : '-');
                    $('#damagedBookSubject').text(response.book.subject ? response.book.subject.subject_name : '-');
                    $('#damagedBookStatus').text(response.is_available ? 'Free' : 'Occupied');
                    $('#damagedBookInfo').show();

                    if ($('#damagedBy').val() === 'student' && response.borrower) {
                        $('#damagedStudentID').val(response.borrower.studentID);
                        $('#damagedStudentSearch').val(response.borrower.name);
                        $('#damagedStudentResults').hide();
                    }
                } else {
                    $('#damagedBookInfo').hide();
                }
            },
            error: function() {
                $('#damagedBookInfo').hide();
            }
        });
    }

    function searchStudentsForLoss(search) {
        if (!search || search.trim().length < 2) {
            $('#lostStudentResults').hide();
            return;
        }
        $.ajax({
            url: '{{ route("get_students") }}',
            type: 'GET',
            data: { search: search.trim() },
            success: function(response) {
                if (response.success && response.students) {
                    let html = '';
                    if (response.students.length > 0) {
                        response.students.forEach(student => {
                            const subclass = student.subclass;
                            const className = subclass && subclass.class ? subclass.class.class_name : '';
                            const fullName = `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.trim();
                            const safeName = fullName.replace(/'/g, "\\'");
                            html += `
                                <div class="p-2 border-bottom student-option"
                                     onclick="selectLostStudent(${student.studentID}, '${safeName}')"
                                     style="cursor: pointer; background-color: #f8f9fa;"
                                     onmouseover="this.style.backgroundColor='#e9ecef'"
                                     onmouseout="this.style.backgroundColor='#f8f9fa'">
                                    <strong>${fullName}</strong><br>
                                    <small class="text-muted">${student.admission_number || 'N/A'} - ${className || 'N/A'}</small>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="p-2 text-muted">No students found</div>';
                    }
                    $('#lostStudentResults').html(html).show();
                }
            }
        });
    }

    function searchStudentsForDamage(search) {
        if (!search || search.trim().length < 2) {
            $('#damagedStudentResults').hide();
            return;
        }
        $.ajax({
            url: '{{ route("get_students") }}',
            type: 'GET',
            data: { search: search.trim() },
            success: function(response) {
                if (response.success && response.students) {
                    let html = '';
                    if (response.students.length > 0) {
                        response.students.forEach(student => {
                            const subclass = student.subclass;
                            const className = subclass && subclass.class ? subclass.class.class_name : '';
                            const fullName = `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.trim();
                            const safeName = fullName.replace(/'/g, "\\'");
                            html += `
                                <div class="p-2 border-bottom student-option"
                                     onclick="selectDamagedStudent(${student.studentID}, '${safeName}')"
                                     style="cursor: pointer; background-color: #f8f9fa;"
                                     onmouseover="this.style.backgroundColor='#e9ecef'"
                                     onmouseout="this.style.backgroundColor='#f8f9fa'">
                                    <strong>${fullName}</strong><br>
                                    <small class="text-muted">${student.admission_number || 'N/A'} - ${className || 'N/A'}</small>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="p-2 text-muted">No students found</div>';
                    }
                    $('#damagedStudentResults').html(html).show();
                }
            }
        });
    }

    function selectLostStudent(studentID, name) {
        $('#lostStudentID').val(studentID);
        $('#lostStudentSearch').val(name);
        $('#lostStudentResults').hide();
    }

    function selectDamagedStudent(studentID, name) {
        $('#damagedStudentID').val(studentID);
        $('#damagedStudentSearch').val(name);
        $('#damagedStudentResults').hide();
    }

    function saveLostBook() {
        const isbn = $('#lostIsbn').val().trim();
        const lostBy = $('#lostBy').val();
        const studentID = $('#lostStudentID').val();
        const description = $('#lostDescription').val().trim();

        if (!isbn) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please enter ISBN' });
            return;
        }
        if (lostBy === 'student' && !studentID) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please select student' });
            return;
        }

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $.ajax({
            url: '{{ route("store_book_loss") }}',
            type: 'POST',
            data: { isbn: isbn, lost_by: lostBy, studentID: studentID, description: description },
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message || 'Saved' });
                    $('#lostModal').modal('hide');
                    loadLostBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Failed' });
                }
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed' });
            }
        });
    }

    function saveDamagedBook() {
        const isbn = $('#damagedIsbn').val().trim();
        const damagedBy = $('#damagedBy').val();
        const studentID = $('#damagedStudentID').val();
        const description = $('#damagedDescription').val().trim();

        if (!isbn) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please enter ISBN' });
            return;
        }
        if (damagedBy === 'student' && !studentID) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please select student' });
            return;
        }

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $.ajax({
            url: '{{ route("store_book_damage") }}',
            type: 'POST',
            data: { isbn: isbn, damaged_by: damagedBy, studentID: studentID, description: description },
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message || 'Saved' });
                    $('#damagedModal').modal('hide');
                    loadDamagedBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Failed' });
                }
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed' });
            }
        });
    }

    function showParentMessageModal(studentID) {
        if (!studentID) return;
        $('#parentMessageStudentID').val(studentID);
        $('#parentMessageText').val('');
        $('#parentMessageModal').modal('show');
    }

    function sendParentMessage() {
        const studentID = $('#parentMessageStudentID').val();
        const message = $('#parentMessageText').val().trim();
        if (!message) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please enter message' });
            return;
        }
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
        $.ajax({
            url: '{{ route("send_parent_message") }}',
            type: 'POST',
            data: { studentID: studentID, message: message },
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Sent', text: response.message || 'Message sent' });
                    $('#parentMessageModal').modal('hide');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Failed' });
                }
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed' });
            }
        });
    }

    function submitPayment(type, id, containerSelector) {
        const $container = $(containerSelector);
        const method = $container.find('.payment-method').val();
        const amount = $container.find('.payment-amount-input').val();

        if (method === 'cash' && (!amount || parseFloat(amount) <= 0)) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please enter payment amount' });
            return;
        }

        const url = type === 'lost'
            ? `{{ url('update_book_loss_payment') }}/${id}`
            : `{{ url('update_book_damage_payment') }}/${id}`;

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: { payment_method: method, payment_amount: amount },
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Saved', text: response.message || 'Payment recorded' });
                    loadLostBooks();
                    loadDamagedBooks();
                    loadBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Failed' });
                }
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed' });
            }
        });
    }

    function saveBorrow() {
        if (typeof jQuery === 'undefined') {
            alert('jQuery is not loaded. Please refresh the page.');
            return;
        }
        var $ = jQuery;
        const formData = {
            isbn: $('#borrowISBN').val().trim(),
            studentID: $('#borrowStudentID').val(),
            expected_return_date: $('#expectedReturnDate').val(),
            notes: $('#borrowNotes').val()
        };

        if (!formData.isbn || !formData.studentID) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter ISBN and select a student'
            });
            return;
        }

        if (!formData.expected_return_date) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select expected return date'
            });
            return;
        }

        if (!$('#borrowBookInfo').is(':visible')) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter a valid ISBN'
            });
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Show loading state
        const $saveBorrowBtn = $('#saveBorrowBtn');
        const $saveBorrowBtnText = $('#saveBorrowBtnText');
        const originalBorrowBtnText = $saveBorrowBtnText.text();
        $saveBorrowBtn.prop('disabled', true);
        $saveBorrowBtnText.html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: '{{ route("borrow_book") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Book borrowed successfully',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        $('#borrowModal').modal('hide');
                        loadBorrows();
                        loadBooks();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'An error occurred'
                    });
                    $saveBorrowBtn.prop('disabled', false);
                    $saveBorrowBtnText.text(originalBorrowBtnText);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while borrowing the book.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.errors) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        const errorList = Object.values(errors).flat().join('<br>');
                        errorMessage = errorList;
                    }
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || response.error || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.responseText || errorMessage;
                    }
                }
                
                console.error('Borrow error:', xhr.responseJSON || xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage,
                    confirmButtonColor: '#940000'
                });
                $saveBorrowBtn.prop('disabled', false);
                $saveBorrowBtnText.text(originalBorrowBtnText);
            }
        });
    }

    function openReturnModal(borrowID, expectedReturnDate) {
        $('#returnBorrowID').val(borrowID);
        const today = new Date().toISOString().slice(0, 10);
        $('#actualReturnDate').val(today);
        $('#lateReason').val('');
        $('#lateReasonSection').hide();
        $('#returnExpectedDate').text(expectedReturnDate ? formatDate(expectedReturnDate) : 'N/A');
        $('#returnExpectedDate').data('expected-raw', expectedReturnDate || '');
        $('#returnModal').modal('show');
    }

    function handleReturnDateChange() {
        const expectedText = $('#returnExpectedDate').text();
        const expectedRaw = $('#returnExpectedDate').data('expected-raw');
        const actual = $('#actualReturnDate').val();
        if (!actual || !expectedRaw) {
            $('#lateReasonSection').hide();
            return;
        }
        const expected = new Date(expectedRaw);
        const actualDate = new Date(actual);
        if (actualDate > expected) {
            $('#lateReasonSection').show();
        } else {
            $('#lateReasonSection').hide();
            $('#lateReason').val('');
        }
    }

    function submitReturn() {
        const borrowID = $('#returnBorrowID').val();
        const returnDate = $('#actualReturnDate').val();
        const lateReason = $('#lateReason').val().trim();

        if (!borrowID) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Borrow record not found' });
            return;
        }
        if (!returnDate) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please select return date' });
            return;
        }
                
                $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                });

                $.ajax({
                    url: `{{ url('return_book') }}/${borrowID}`,
                    type: 'POST',
            data: {
                return_date: returnDate,
                late_reason: lateReason
            },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Returned!',
                                text: response.message || 'Book returned successfully',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                        $('#returnModal').modal('hide');
                                loadBorrows();
                                loadBooks();
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'An error occurred';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error
                        });
                    }
                });
    }

    function openReturnModalByIsbn(isbn, expectedReturnDate) {
        if (!isbn) {
            Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Invalid ISBN' });
            return;
        }
        $.ajax({
            url: '{{ route("get_book_borrows") }}',
            type: 'GET',
            data: { status: 'borrowed' },
            success: function(response) {
                if (response.success && response.borrows) {
                    const borrow = response.borrows.find(b => b.book && b.book.isbn === isbn && b.status === 'borrowed');
                    if (!borrow) {
                        Swal.fire({ icon: 'warning', title: 'Not Found', text: 'Active borrow not found for this ISBN' });
                        return;
                    }
                    openReturnModal(borrow.borrowID, expectedReturnDate || borrow.expected_return_date || '');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load borrow records' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load borrow records' });
            }
        });
    }

    function loadStatistics() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        
        const classID = $('#statClass').val();
        const subjectID = $('#statSubject').val();
        
        console.log('Loading statistics with filters - ClassID:', classID, 'SubjectID:', subjectID);
        
        // Show loading state
        $('#statisticsTableBody').html('<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading statistics...</td></tr>');
        
        $.ajax({
            url: '{{ route("get_book_statistics") }}',
            type: 'GET',
            data: {
                classID: classID || '',
                subjectID: subjectID || ''
            },
            success: function(response) {
                console.log('Statistics response:', response);
                if (response.success) {
                    let html = '';
                    if (response.books_by_class_subject && response.books_by_class_subject.length > 0) {
                        response.books_by_class_subject.forEach(item => {
                            const className = item.class ? item.class.class_name : '-';
                            const subjectName = item.subject ? item.subject.subject_name : '-';
                            
                            html += `
                                <tr>
                                    <td>${className}</td>
                                    <td>${subjectName}</td>
                                    <td>${item.total || 0}</td>
                                    <td><span class="badge badge-custom badge-available">${item.available || 0}</span></td>
                                    <td><span class="badge badge-custom badge-issued">${item.issued || 0}</span></td>
                                    <td>${item.book_count || 0}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center">No statistics found for the selected filters</td></tr>';
                    }
                    $('#statisticsTableBody').html(html);
                } else {
                    $('#statisticsTableBody').html('<tr><td colspan="6" class="text-center">Error loading statistics</td></tr>');
                    console.error('Error in response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading statistics:', error);
                console.error('Response:', xhr.responseText);
                $('#statisticsTableBody').html('<tr><td colspan="6" class="text-center">Error loading statistics. Please try again.</td></tr>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load statistics: ' + (xhr.responseJSON?.error || error)
                });
            }
        });
    }
</script>
