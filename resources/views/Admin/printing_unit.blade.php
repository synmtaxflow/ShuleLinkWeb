@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Remove border-radius from all widgets */
    .card, .exam-card, .exam-paper-item, .overview-card, .alert, .btn, div, .form-control, .form-select {
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
    .exam-card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1), 0 4px 16px rgba(148, 0, 0, 0.08) !important;
        border: 1px solid rgba(148, 0, 0, 0.1) !important;
        margin-bottom: 15px;
    }
    .exam-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }
    .exam-card-header {
        cursor: pointer;
        background-color: #f8f9fa;
        border-bottom: 2px solid #940000;
        padding: 15px;
    }
    .exam-card-header:hover {
        background-color: #e9ecef;
    }
    .exam-card-body {
        display: none;
        padding: 15px;
        background-color: #ffffff;
    }
    .exam-card-body.show {
        display: block;
    }
    .exam-paper-item {
        padding: 12px;
        margin-bottom: 10px;
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
    }
    .exam-paper-item:hover {
        background-color: #e9ecef;
    }
    .overview-card {
        background: linear-gradient(135deg, #940000 0%, #b30000 100%);
        color: white;
        padding: 20px;
        margin-bottom: 20px;
    }
    .overview-stat {
        text-align: center;
        padding: 10px;
    }
    .overview-stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .overview-stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .chevron-icon {
        transition: transform 0.3s ease;
        float: right;
    }
    .chevron-icon.rotated {
        transform: rotate(180deg);
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Printing Unit</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <!-- Filters Section -->
    <div class="card">
        <div class="card-header">
            <strong>Filters</strong>
        </div>
        <div class="card-body">
            <form id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="academic_year" class="form-label">Academic Year</label>
                        <select class="form-control" id="academic_year" name="academic_year">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year }}" {{ $selectedAcademicYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="term" class="form-label">Term</label>
                        <select class="form-control" id="term" name="term">
                            <option value="">All Terms</option>
                            @foreach($terms as $termItem)
                                @php
                                    $termLabel = ucfirst(str_replace('_', ' ', $termItem));
                                @endphp
                                <option value="{{ $termItem }}" {{ $selectedTerm == $termItem ? 'selected' : '' }}>{{ $termLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="classID" class="form-label">Class</label>
                        <select class="form-control" id="classID" name="classID">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}" {{ $selectedClassID == $class->classID ? 'selected' : '' }}>{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="subclassID" class="form-label">Subclass</label>
                        <select class="form-control" id="subclassID" name="subclassID">
                            <option value="">All Subclasses</option>
                            @foreach($subclasses as $subclass)
                                <option value="{{ $subclass->subclassID }}" {{ $selectedSubclassID == $subclass->subclassID ? 'selected' : '' }}>{{ $subclass->subclass_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="examID" class="form-label">Examination</label>
                        <select class="form-control" id="examID" name="examID">
                            <option value="">All Examinations</option>
                            @foreach($examsForDropdown as $exam)
                                <option value="{{ $exam->examID }}" {{ $selectedExamID == $exam->examID ? 'selected' : '' }}>
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary-custom btn-block" id="filterBtn">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <!-- Classes List -->
    <div class="card">
        <div class="card-header">
            <strong>Classes & Subjects</strong>
        </div>
        <div class="card-body" id="classesContent">
            <div class="text-center py-5">
                <div class="spinner-border text-primary-custom" role="status"></div>
                <p class="mt-2">Loading...</p>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script>
    // Wait for jQuery to be loaded before executing jQuery code
    (function() {
        function initPrintingUnit() {
            // Check if jQuery is loaded
            if (typeof jQuery === 'undefined') {
                setTimeout(initPrintingUnit, 100);
                return;
            }
            
            // Use jQuery instead of $ to avoid conflicts
            var $ = jQuery;
            
            function toggleClassCard(key) {
                const cardBody = document.getElementById('class-body-' + key);
                const chevron = document.getElementById('chevron-' + key);
                
                if (cardBody.classList.contains('show')) {
                    cardBody.classList.remove('show');
                    chevron.classList.remove('rotated');
                } else {
                    cardBody.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
            
            // Make toggleClassCard available globally
            window.toggleClassCard = toggleClassCard;

            // Print exam paper with class name and subclass name
            $(document).on('click', '.print-exam-paper-btn', function() {
        const paperID = $(this).data('paper-id');
        const className = $(this).data('class-name') || 'N/A';
        const subclassName = $(this).data('subclass-name') || 'N/A';
        
        const downloadUrl = '{{ route("download_exam_paper", ":id") }}'.replace(':id', paperID);
        const printWindow = window.open(downloadUrl, '_blank');
        
        printWindow.onload = function() {
            // Try to inject class/subclass header into the print window
            setTimeout(function() {
                try {
                    printWindow.print();
                } catch (e) {
                    console.error('Print error:', e);
                    // Fallback: just open print dialog
                    printWindow.print();
                }
            }, 1000);
            };
        });

        // AJAX Filtering
        $(document).ready(function() {
        let filterTimeout;
        
        // Function to load filtered data via AJAX
        function filterData() {
            const formData = {
                academic_year: $('#academic_year').val(),
                term: $('#term').val(),
                classID: $('#classID').val(),
                subclassID: $('#subclassID').val(),
                examID: $('#examID').val()
            };
            
            // Show loading indicator
            $('#classesContent').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Loading...</p></div>');
            
            $.ajax({
                url: '{{ route("admin.printing_unit.filter") }}',
                method: 'GET',
                data: formData,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        // Preserve current subclass selection before updating dropdown
                        // Use formData.subclassID first (the one being sent), then current selected value
                        const currentSubclassID = formData.subclassID || $('#subclassID').val();
                        
                        // Always update subclasses from response to ensure dropdown has correct options
                        // But preserve the selected value
                        if (response.subclasses) {
                            $('#subclassID').html('<option value="">All Subclasses</option>');
                            if (response.subclasses.length > 0) {
                                response.subclasses.forEach(function(subclass) {
                                    // Preserve selection: if currentSubclassID matches this subclass, mark as selected
                                    const selected = (currentSubclassID && currentSubclassID == subclass.subclassID) ? 'selected' : '';
                                    $('#subclassID').append('<option value="' + subclass.subclassID + '" ' + selected + '>' + subclass.subclass_name + '</option>');
                                });
                            }
                        }
                        
                        // Preserve current exam selection
                        const currentExamID = $('#examID').val();
                        
                        // Update examinations dropdown
                        if (response.examsForDropdown) {
                            $('#examID').html('<option value="">All Examinations</option>');
                            response.examsForDropdown.forEach(function(exam) {
                                const selected = (currentExamID && currentExamID == exam.examID) ? 'selected' : (formData.examID == exam.examID ? 'selected' : '');
                                $('#examID').append('<option value="' + exam.examID + '" ' + selected + '>' + exam.exam_name + '</option>');
                            });
                        }
                        
                        // Render classes content
                        renderClassesContent(response.groupedByClass);
                    } else {
                        $('#classesContent').html('<div class="alert alert-danger">' + (response.error || 'Failed to load data') + '</div>');
                    }
                },
                error: function(xhr) {
                    console.error('Filter error:', xhr);
                    $('#classesContent').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
                }
            });
        }
        
        // Function to render classes content
        function renderClassesContent(groupedByClass) {
            if (!groupedByClass || Object.keys(groupedByClass).length === 0) {
                $('#classesContent').html('<div class="alert alert-info"><i class="fa fa-info-circle"></i> No classes found with approved exam papers for the selected filters.</div>');
                return;
            }
            
            let html = '';
            for (let key in groupedByClass) {
                const group = groupedByClass[key];
                
                // Format dates if available
                let startDateStr = group.start_date ? new Date(group.start_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
                let endDateStr = group.end_date ? new Date(group.end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
                
                html += '<div class="exam-card">';
                html += '<div class="exam-card-header" onclick="toggleClassCard(\'' + key + '\')">';
                html += '<div class="row align-items-center">';
                html += '<div class="col-md-10">';
                html += '<strong>' + group.className + ' ' + group.subclassName + '</strong>';
                if (group.exam_name && group.exam_name !== 'N/A') {
                    html += '<br><span class="text-muted"><i class="fa fa-file-text-o"></i> ' + group.exam_name + '</span>';
                }
                if (group.start_date || group.end_date) {
                    html += '<br><small class="text-muted"><i class="fa fa-calendar"></i> ' + startDateStr + ' - ' + endDateStr + '</small>';
                }
                html += '<span class="badge badge-info ml-2">' + group.papers.length + ' Papers</span>';
                html += '</div>';
                html += '<div class="col-md-2 text-right">';
                html += '<i class="fa fa-chevron-down chevron-icon" id="chevron-' + key + '"></i>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '<div class="exam-card-body" id="class-body-' + key + '">';
                
                group.papers.forEach(function(item) {
                    const paper = item.paper;
                    const exam = item.exam;
                    const subject = paper.class_subject && paper.class_subject.subject ? paper.class_subject.subject.subject_name : 'N/A';
                    const teacherName = paper.teacher ? (paper.teacher.first_name + ' ' + paper.teacher.last_name) : 'N/A';
                    const teacherPhone = paper.teacher ? (paper.teacher.phone_number || paper.teacher.phone || 'N/A') : 'N/A';
                    
                    html += '<div class="exam-paper-item">';
                    html += '<div class="row align-items-center">';
                    html += '<div class="col-md-3"><strong>Subject:</strong> ' + subject + '</div>';
                    html += '<div class="col-md-2"><strong>Exam:</strong> ' + exam.exam_name + '</div>';
                    html += '<div class="col-md-2"><strong>Teacher:</strong> ' + teacherName + '</div>';
                    html += '<div class="col-md-2"><strong>Phone:</strong> ' + teacherPhone + '</div>';
                    html += '<div class="col-md-3 text-right">';
                    html += '<a href="/download_exam_paper/' + paper.exam_paperID + '" class="btn btn-sm btn-success" target="_blank"><i class="fa fa-download"></i> Download</a> ';
                    html += '<button type="button" class="btn btn-sm btn-primary-custom print-exam-paper-btn" ';
                    html += 'data-paper-id="' + paper.exam_paperID + '" ';
                    html += 'data-class-name="' + group.className + '" ';
                    html += 'data-subclass-name="' + group.subclassName + '">';
                    html += '<i class="fa fa-print"></i> Print</button>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                });
                
                html += '</div>';
                html += '</div>';
            }
            
            $('#classesContent').html(html);
        }
        
        // Load subclasses when class is selected
        $('#classID').on('change', function() {
            const classID = $(this).val();
            const subclassSelect = $('#subclassID');
            
            // Reset subclass selection
            subclassSelect.val('');
            
            if (classID) {
                // Show loading in subclass dropdown
                subclassSelect.html('<option value="">Loading...</option>');
                
                // Load subclasses for selected class via AJAX
                $.ajax({
                    url: '/get_class_subclasses/' + classID,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.subclasses && response.subclasses.length > 0) {
                            subclassSelect.html('<option value="">All Subclasses</option>');
                            response.subclasses.forEach(function(subclass) {
                                subclassSelect.append('<option value="' + subclass.subclassID + '">' + subclass.subclass_name + '</option>');
                            });
                        } else {
                            subclassSelect.html('<option value="">All Subclasses</option>');
                        }
                        
                        // Trigger filter after loading subclasses
                        clearTimeout(filterTimeout);
                        filterTimeout = setTimeout(function() {
                            filterData();
                        }, 300);
                    },
                    error: function() {
                        subclassSelect.html('<option value="">All Subclasses</option>');
                        // Still trigger filter even on error
                        clearTimeout(filterTimeout);
                        filterTimeout = setTimeout(function() {
                            filterData();
                        }, 300);
                    }
                });
            } else {
                // Load all subclasses if no class selected
                loadAllSubclasses();
                // Trigger filter
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    filterData();
                }, 300);
            }
        });
        
        // Function to load all subclasses
        function loadAllSubclasses() {
            const subclassSelect = $('#subclassID');
            $.ajax({
                url: '{{ route("admin.printing_unit.filter") }}',
                method: 'GET',
                data: { load_subclasses_only: true },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.subclasses) {
                        subclassSelect.html('<option value="">All Subclasses</option>');
                        response.subclasses.forEach(function(subclass) {
                            subclassSelect.append('<option value="' + subclass.subclassID + '">' + subclass.subclass_name + '</option>');
                        });
                    } else {
                        subclassSelect.html('<option value="">All Subclasses</option>');
                    }
                },
                error: function() {
                    subclassSelect.html('<option value="">All Subclasses</option>');
                }
            });
        }
        
        // Handle filter changes (except class which has its own handler)
        $('#academic_year, #term, #subclassID, #examID').on('change', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                filterData();
            }, 300); // Debounce for 300ms
        });
        
        // Handle filter button click
        $('#filterBtn').on('click', function() {
            filterData();
        });
        
        // Initial load - load data on page load using AJAX
        filterData();
    });
        }
        
        // Start initialization
        initPrintingUnit();
    })();
</script>
