@if($user_type == 'Admin')
@include('includes.Admin_nav')
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
        padding: 20px;
        margin-bottom: 20px;
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
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #495057;
        font-size: 24px;
        margin-bottom: 12px;
    }

    .stat-card:hover .stat-icon {
        background: var(--primary-color);
        color: white;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #212529;
        margin: 8px 0;
    }

    .stat-label {
        font-size: 0.9rem;
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
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead class="bg-primary-custom text-white">
                <tr>
                    <th>Total Books</th>
                    <th>Available Books</th>
                    <th>Issued Books</th>
                    <th>Students with Borrowed Books</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="totalBooks">{{ $totalBooks ?? 0 }}</td>
                    <td id="availableBooks">{{ $availableBooks ?? 0 }}</td>
                    <td id="issuedBooks">{{ $issuedBooks ?? 0 }}</td>
                    <td id="borrowedBooks">{{ $borrowedBooks ?? 0 }}</td>
                </tr>
            </tbody>
        </table>
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
                    </ul>

                    <div class="tab-content">
        <!-- Books Tab -->
        <div class="tab-pane fade show active" id="booksTab">
            <div class="library-card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom">
                        <i class="fa fa-book"></i> Books List
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Total</th>
                                <th>Available</th>
                                <th>Issued</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="booksTableBody">
                            <tr>
                                <td colspan="10" class="text-center">Loading...</td>
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
                    </div>
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
                            <label class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="bookISBN">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Publisher</label>
                            <input type="text" class="form-control" id="bookPublisher">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Publication Year</label>
                            <input type="number" class="form-control" id="bookPublicationYear" min="1900" max="{{ date('Y') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="bookTotalQuantity" required min="1">
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="saveBookBtn" onclick="saveBook()">
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
                        <label class="form-label">Select Book <span class="text-danger">*</span></label>
                        <select class="form-control" id="borrowBookID" required>
                            <option value="">Select Book</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Search Student <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="searchStudent" placeholder="Search by name or admission number...">
                        <input type="hidden" id="borrowStudentID">
                        <div id="studentResults" class="mt-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expected Return Date (Optional)</label>
                        <input type="date" class="form-control" id="expectedReturnDate">
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
        $('#booksTableBody').html('<tr><td colspan="10" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading books...</td></tr>');
        
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
                        response.books.forEach((book, index) => {
                            const bookTitle = (book.book_title || '').replace(/'/g, "\\'");
                            const author = (book.author || '-').replace(/'/g, "\\'");
                            const className = book.class ? (book.class.class_name || '-') : '-';
                            const subjectName = book.subject ? (book.subject.subject_name || '-') : '-';
                            
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${bookTitle}</td>
                                    <td>${author}</td>
                                    <td>${className}</td>
                                    <td>${subjectName}</td>
                                    <td>${book.total_quantity || 0}</td>
                                    <td><span class="badge badge-custom badge-available">${book.available_quantity || 0}</span></td>
                                    <td><span class="badge badge-custom badge-issued">${book.issued_quantity || 0}</span></td>
                                    <td><span class="badge badge-custom ${book.status === 'Active' ? 'badge-available' : 'badge-issued'}">${book.status === 'Active' ? 'Active' : 'Inactive'}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editBook(${book.bookID})" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteBook(${book.bookID})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="10" class="text-center">No books found</td></tr>';
                    }
                    $('#booksTableBody').html(html);
                } else {
                    $('#booksTableBody').html('<tr><td colspan="10" class="text-center">Error loading books</td></tr>');
                    console.error('Error in response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading books:', error);
                console.error('Response:', xhr.responseText);
                $('#booksTableBody').html('<tr><td colspan="10" class="text-center">Error loading books. Please try again.</td></tr>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load books: ' + (xhr.responseJSON?.error || error)
                });
            }
        });
    }

    // DataTable instance for borrows
    let borrowsDataTable = null;

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
                status: $('#filterBorrowStatus').val()
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
                                            <button class="btn btn-sm btn-success" onclick="returnBook(${borrow.borrowID})" title="Return Book">
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
                    
                    // Initialize or reinitialize DataTable
                    if (borrowsDataTable) {
                        borrowsDataTable.destroy();
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

    function showAddBookModal() {
        if (typeof jQuery === 'undefined') {
            alert('jQuery is not loaded. Please refresh the page.');
            console.error('jQuery not loaded');
            return;
        }
        var $ = jQuery;
        
        console.log('showAddBookModal called');
        try {
            $('#bookModalTitle').html('<i class="fa fa-book"></i> Add New Book');
            $('#bookForm')[0].reset();
            $('#bookID').val('');
            $('#bookStatusDiv').hide();
            $('#bookSubjectID').html('<option value="">Select Subject</option>');
            $('#bookClassID').val(''); // Reset class selection
            
            // Reset save button state
            $('#saveBookBtn').prop('disabled', false);
            $('#saveBookBtnText').text('Save');
            
            // Re-attach event handler for class change (in case modal was recreated)
            $('#bookClassID').off('change').on('change', function() {
                const classID = $(this).val();
                console.log('Class changed in modal:', classID);
                if (classID) {
                    loadSubjectsByClass(classID);
                } else {
                    $('#bookSubjectID').html('<option value="">Select Subject</option>');
                }
            });
            
            // Check if modal element exists
            var $modal = $('#bookModal');
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
            $modal.modal('show');
            
            // Verify modal is shown
            setTimeout(function() {
                if ($modal.hasClass('show')) {
                    console.log('Modal is now visible');
                } else {
                    console.error('Modal failed to show');
                    // Try alternative method
                    $modal.css('display', 'block');
                    $modal.addClass('show');
                    $('body').addClass('modal-open');
                    $('.modal-backdrop').remove();
                    $('body').append('<div class="modal-backdrop fade show"></div>');
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
                        $('#bookModalTitle').html('<i class="fa fa-edit"></i> Edit Book');
                        $('#bookID').val(book.bookID);
                        $('#bookClassID').val(book.classID);
                        loadSubjectsByClass(book.classID);
                        setTimeout(() => {
                            $('#bookSubjectID').val(book.subjectID);
                        }, 500);
                        $('#bookTitle').val(book.book_title);
                        $('#bookAuthor').val(book.author || '');
                        $('#bookISBN').val(book.isbn || '');
                        $('#bookPublisher').val(book.publisher || '');
                        $('#bookPublicationYear').val(book.publication_year || '');
                        $('#bookTotalQuantity').val(book.total_quantity);
                        $('#bookDescription').val(book.description || '');
                        $('#bookStatus').val(book.status);
                        $('#bookStatusDiv').show();
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
        
        if (!$('#bookTotalQuantity').val() || parseInt($('#bookTotalQuantity').val()) < 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter a valid quantity (minimum 1)'
            });
            return;
        }
        
        const formData = {
            classID: $('#bookClassID').val(),
            subjectID: $('#bookSubjectID').val(),
            book_title: $('#bookTitle').val().trim(),
            author: $('#bookAuthor').val().trim(),
            isbn: $('#bookISBN').val().trim(),
            publisher: $('#bookPublisher').val().trim(),
            publication_year: $('#bookPublicationYear').val(),
            total_quantity: $('#bookTotalQuantity').val(),
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
        
        // Load available books
        console.log('Loading available books for borrow modal...');
        $('#borrowBookID').html('<option value="">Loading books...</option>');
        
        $.ajax({
            url: '{{ route("get_books") }}',
            type: 'GET',
            data: { status: 'Active' },
            success: function(response) {
                console.log('Books for borrow response:', response);
                if (response.success && response.books) {
                    let html = '<option value="">Select Book</option>';
                    let hasAvailableBooks = false;
                    response.books.forEach(book => {
                        if (book.available_quantity > 0) {
                            hasAvailableBooks = true;
                            const bookTitle = (book.book_title || 'Untitled').replace(/"/g, '&quot;');
                            html += `<option value="${book.bookID}">${bookTitle} (${book.available_quantity} available)</option>`;
                        }
                    });
                    if (!hasAvailableBooks) {
                        html += '<option value="" disabled>No available books</option>';
                    }
                    $('#borrowBookID').html(html);
                } else {
                    $('#borrowBookID').html('<option value="">No books available</option>');
                    console.error('No books in response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading books for borrow:', error);
                console.error('Response:', xhr.responseText);
                $('#borrowBookID').html('<option value="">Error loading books</option>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load books: ' + (xhr.responseJSON?.error || error)
                });
            }
        });
        
        $('#borrowModal').modal('show');
    }

    function saveBorrow() {
        if (typeof jQuery === 'undefined') {
            alert('jQuery is not loaded. Please refresh the page.');
            return;
        }
        var $ = jQuery;
        const formData = {
            bookID: $('#borrowBookID').val(),
            studentID: $('#borrowStudentID').val(),
            expected_return_date: $('#expectedReturnDate').val(),
            notes: $('#borrowNotes').val()
        };

        if (!formData.bookID || !formData.studentID) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a book and student'
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

    function returnBook(borrowID) {
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
            title: 'Return Book?',
            text: 'Are you sure the student has returned the book?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, return it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
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
                    url: `{{ url('return_book') }}/${borrowID}`,
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Returned!',
                                text: response.message || 'Book returned successfully',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
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
