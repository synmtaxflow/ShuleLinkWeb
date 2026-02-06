@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    * {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }
    
    .fa, .fa:before, i.fa, [class*="fa-"]:before, [class^="fa-"]:before {
        font-family: 'FontAwesome' !important;
    }
    
    body {
        background-color: #f5f5f5;
    }
    
    .content-wrapper {
        background: white;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 0;
    }
    
    .header-section {
        background: linear-gradient(135deg, #940000 0%, #b30000 100%);
        color: white;
        padding: 20px 0;
        margin-bottom: 30px;
    }
    
    .year-widget {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        background: white;
    }
    
    .year-widget:hover {
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.2);
        border-color: #940000;
    }
    
    .year-widget.current {
        border-color: #940000;
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
    }
    
    .year-widget.past {
        border-color: #6c757d;
        background: #f8f9fa;
    }
    
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
        border-radius: 0;
    }
    
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    
    .btn-secondary-custom {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #ffffff;
        border-radius: 0;
    }
    
    .btn-secondary-custom:hover {
        background-color: #5a6268;
        border-color: #5a6268;
        color: #ffffff;
    }
    
    .year-select-wrapper {
        margin-bottom: 30px;
    }
    
    /* Progress Modal z-index - Must be above form modal */
    #closeYearProgressModal {
        z-index: 1060 !important;
    }
    
    #closeYearProgressModal .modal-dialog {
        z-index: 1061 !important;
    }
    
    #closeYearProgressModal .modal-content {
        z-index: 1062 !important;
    }
    
    #closeYearProgressModal .modal-backdrop {
        z-index: 1059 !important;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    /* Ensure form modal backdrop is below progress modal */
    #closeYearModal.show ~ .modal-backdrop {
        z-index: 1040 !important;
    }
    
    /* SweetAlert2 z-index - Must be above progress modal */
    .swal2-container {
        z-index: 1070 !important;
    }
    
    .swal2-popup {
        z-index: 1071 !important;
    }
    
    .year-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-active {
        background-color: #28a745;
        color: white;
    }
    
    .badge-closed {
        background-color: #6c757d;
        color: white;
    }
    
    .badge-draft {
        background-color: #ffc107;
        color: #000;
    }
    
    /* Modal Styles */
    .modal-custom {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }
    
    .modal-header-custom {
        background: linear-gradient(135deg, #940000 0%, #b30000 100%);
        color: white;
        border-radius: 0;
    }
    
    .modal-header-custom .close {
        color: white;
        opacity: 1;
        text-shadow: none;
    }
    
    .modal-header-custom .close:hover {
        color: #f0f0f0;
    }
    
    .checkbox-item {
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .checkbox-item:hover {
        background-color: #f8f9fa;
        border-color: #940000;
    }
    
    .checkbox-item input[type="checkbox"] {
        margin-right: 10px;
        transform: scale(1.2);
    }
    
    .checkbox-item label {
        cursor: pointer;
        font-weight: 500;
        margin: 0;
    }
    
    .checkbox-item small {
        display: block;
        margin-top: 5px;
        color: #6c757d;
        margin-left: 30px;
    }
</style>

<div class="header-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fa fa-calendar-check-o"></i> Academic Years Management</h3>
                <p class="mb-0">Manage and view academic years data</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="content-wrapper">
        <!-- Year Selector -->
        <div class="year-select-wrapper">
            <div class="row">
                <div class="col-md-4">
                    <label for="yearSelect" class="form-label"><strong>Select Academic Year:</strong></label>
                    <select class="form-control" id="yearSelect" onchange="filterYear(this.value)">
                        <option value="current" selected>Current Year ({{ $currentYear->year_name ?: $currentYear->year }})</option>
                        <option value="">All Years</option>
                        @foreach($pastYears as $year)
                            <option value="{{ $year->academic_yearID }}">
                                {{ $year->year_name ?: $year->year }} (Closed)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Current Year Widget -->
        <div class="year-widget current" id="year-current">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4>
                        <i class="fa fa-calendar"></i> 
                        {{ $currentYear->year_name ?: $currentYear->year }}
                        <span class="year-badge badge-active">Current Year</span>
                    </h4>
                    <p class="mb-2">
                        <strong>Period:</strong> 
                        {{ \Carbon\Carbon::parse($currentYear->start_date)->format('d M Y') }} - 
                        {{ \Carbon\Carbon::parse($currentYear->end_date)->format('d M Y') }}
                    </p>
                    <p class="mb-0 text-muted">
                        <small>Status: <span class="year-badge badge-active">Active</span></small>
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.academicYears.viewTerms') }}" class="btn btn-info btn-lg mb-2" style="width: 100%; background-color: #17a2b8; border-color: #17a2b8; color: white;">
                        <i class="fa fa-calendar"></i> View Terms
                    </a>
                    <button class="btn btn-primary-custom btn-lg mb-2" onclick="closeYear()" style="width: 100%;">
                        <i class="fa fa-lock"></i> Close Year
                    </button>
                    <button class="btn btn-secondary-custom btn-lg" onclick="openNewYear()" style="width: 100%;">
                        <i class="fa fa-plus-circle"></i> Open New Year
                    </button>
                </div>
            </div>
        </div>

        <!-- Past Years Widgets -->
        <h5 class="mt-4 mb-3" id="pastYearsHeader" style="display: none;"><i class="fa fa-history"></i> Past Academic Years</h5>
        <div id="pastYearsContainer">
            @foreach($pastYears as $year)
                <div class="year-widget past" id="year-{{ $year->academic_yearID }}" style="display: none;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5>
                                <i class="fa fa-calendar"></i> 
                                {{ $year->year_name ?: $year->year }}
                                <span class="year-badge badge-closed">Closed</span>
                            </h5>
                            <p class="mb-2">
                                <strong>Period:</strong> 
                                {{ \Carbon\Carbon::parse($year->start_date)->format('d M Y') }} - 
                                {{ \Carbon\Carbon::parse($year->end_date)->format('d M Y') }}
                            </p>
                            @if($year->closed_at)
                                <p class="mb-0 text-muted">
                                    <small>Closed on: {{ \Carbon\Carbon::parse($year->closed_at)->format('d M Y, h:i A') }}</small>
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('admin.academicYears.view', $year->academic_yearID) }}" class="btn btn-secondary-custom btn-lg" style="width: 100%;">
                                <i class="fa fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($pastYears->count() == 0)
            <div class="alert alert-info text-center" id="noPastYears" style="display: none;">
                <i class="fa fa-info-circle"></i> No past academic years found.
            </div>
        @endif
    </div>
</div>

<!-- Close Year Progress Modal -->
<div class="modal fade modal-custom" id="closeYearProgressModal" tabindex="-1" role="dialog" aria-labelledby="closeYearProgressModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" style="z-index: 1060;">
    <div class="modal-dialog modal-md" role="document" style="z-index: 1061;">
        <div class="modal-content" style="border-radius: 0; z-index: 1062;">
            <div class="modal-header modal-header-custom" style="background-color: #940000; color: white;">
                <h5 class="modal-title" id="closeYearProgressModalLabel">
                    <i class="fa fa-cog fa-spin"></i> Closing Academic Year
                </h5>
                <button type="button" class="close text-white" onclick="closeProgressModal()" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" style="padding: 40px 20px;">
                <!-- Animated Icon -->
                <div style="margin-bottom: 30px;">
                    <i class="fa fa-spinner fa-pulse" style="font-size: 60px; color: #940000;"></i>
                </div>
                
                <!-- Progress Bar -->
                <div class="progress" style="height: 35px; margin-bottom: 20px; border-radius: 0;">
                    <div id="closeYearProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 0%; background-color: #940000; transition: width 0.3s ease;"
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        <span id="closeYearProgressText" style="line-height: 35px; font-weight: bold; color: white; font-size: 14px;">0%</span>
                    </div>
                </div>
                
                <!-- Status Message -->
                <div id="closeYearProgressStatus" class="text-muted" style="font-size: 16px; margin-bottom: 15px; min-height: 24px;">
                    Initializing...
                </div>
                
                <!-- Info Message -->
                <div class="alert alert-info" style="margin-top: 20px; border-radius: 0; background-color: #e7f3ff; border-color: #b3d9ff;">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Please wait...</strong> This process may take 1-60 minutes to complete. Do not close this window.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Close Year Modal -->
<div class="modal fade modal-custom" id="closeYearModal" tabindex="-1" role="dialog" aria-labelledby="closeYearModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="closeYearModalLabel">
                    <i class="fa fa-lock"></i> Close Academic Year - {{ $currentYear->year_name ?: $currentYear->year }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="closeYearForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> 
                        <strong>Warning:</strong> Closing academic year will save all current data to history. This action cannot be undone!
                    </div>
                    
                    <p class="mb-3"><strong>Select modalities to perform when closing this academic year:</strong></p>
                    
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="modalities[]" value="send_sms_to_parents" checked>
                            <strong><i class="fa fa-send"></i> Send SMS to Parents</strong>
                        </label>
                        <small>Send SMS notifications to parents informing them about school closure dates and reopening dates.</small>
                    </div>
                    
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="modalities[]" value="promote_students" checked>
                            <strong><i class="fa fa-arrow-up"></i> Promote/Shift Students to Next Classes</strong>
                        </label>
                        <small>Automatically promote students to next classes based on grade definitions. Students in final classes will be marked as graduated.</small>
                    </div>
                    
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="modalities[]" value="shift_by_grade" id="shift_by_grade_checkbox">
                            <strong><i class="fa fa-graduation-cap"></i> Shifting Student from One Class to Another by Grade</strong>
                        </label>
                        <small>Check student's grade from final exam of the year and match with subclass grade range (first_grade to final_grade). If no subclass matches the grade, student will repeat (kariri darasa). If unchecked, all students will be shifted without checking grades.</small>
                    </div>
                    
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="modalities[]" value="save_scheme_of_work" checked>
                            <strong><i class="fa fa-book"></i> Save Scheme of Work to History</strong>
                        </label>
                        <small>Save all scheme of works to history. Teachers will need to create new schemes or use existing ones for the new academic year.</small>
                    </div>
                    
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="modalities[]" value="lock_results_editing" checked>
                            <strong><i class="fa fa-lock"></i> Lock Results Editing for Teachers</strong>
                        </label>
                        <small>Prevent teachers from editing results for this academic year after closing.</small>
                    </div>
                    
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="modalities[]" value="lock_exams_editing" checked>
                            <strong><i class="fa fa-lock"></i> Lock Examinations Editing</strong>
                        </label>
                        <small>Prevent editing of examinations for this academic year after closing.</small>
                    </div>
                    
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="modalities[]" value="save_to_history" checked disabled>
                            <strong><i class="fa fa-database"></i> Save All Data to History (Required)</strong>
                        </label>
                        <small>All classes, subclasses, subjects, students, results, attendance, and other data will be saved to history tables. This is mandatory and cannot be disabled.</small>
                    </div>
                    
                    <hr>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="school_closing_date"><strong>School Closing Date:</strong></label>
                            <input type="date" class="form-control" id="school_closing_date" name="school_closing_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="school_reopening_date"><strong>School Reopening Date:</strong></label>
                            <input type="date" class="form-control" id="school_reopening_date" name="school_reopening_date" required>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="close_notes"><strong>Notes (Optional):</strong></label>
                            <textarea class="form-control" id="close_notes" name="close_notes" rows="3" placeholder="Add any notes about closing this academic year..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom" id="confirmCloseYearBtn">
                        <i class="fa fa-lock"></i> Confirm Close Year
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- jQuery and Bootstrap - Load FIRST before any JavaScript code -->
<script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// On page load, show only current year by default
document.addEventListener('DOMContentLoaded', function() {
    // Hide all past years by default
    document.querySelectorAll('.year-widget.past').forEach(widget => {
        widget.style.display = 'none';
    });
    
    // Hide past years header by default
    document.getElementById('pastYearsHeader').style.display = 'none';
    const noPastYears = document.getElementById('noPastYears');
    if (noPastYears) {
        noPastYears.style.display = 'none';
    }
    
    // Show current year widget
    const currentYearWidget = document.getElementById('year-current');
    if (currentYearWidget) {
        currentYearWidget.style.display = 'block';
    }
});

function filterYear(yearID) {
    const pastYearsHeader = document.getElementById('pastYearsHeader');
    const noPastYears = document.getElementById('noPastYears');
    
    if (!yearID || yearID === 'current') {
        // Show current year only
        document.querySelectorAll('.year-widget').forEach(widget => {
            widget.style.display = 'none';
        });
        const currentYearWidget = document.getElementById('year-current');
        if (currentYearWidget) {
            currentYearWidget.style.display = 'block';
        }
        pastYearsHeader.style.display = 'none';
        if (noPastYears) {
            noPastYears.style.display = 'none';
        }
    } else if (yearID === '') {
        // Show all years
        document.querySelectorAll('.year-widget').forEach(widget => {
            widget.style.display = 'block';
        });
        pastYearsHeader.style.display = 'block';
        if (noPastYears) {
            noPastYears.style.display = 'none';
        }
    } else {
        // Show only selected past year
        document.querySelectorAll('.year-widget').forEach(widget => {
            widget.style.display = 'none';
        });
        const selectedWidget = document.getElementById('year-' + yearID);
        if (selectedWidget) {
            selectedWidget.style.display = 'block';
            pastYearsHeader.style.display = 'block';
        }
        if (noPastYears) {
            noPastYears.style.display = 'none';
        }
    }
}

function closeYear() {
    // Show modal using jQuery (should be loaded by now)
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
        jQuery('#closeYearModal').modal('show');
    } else {
        // Fallback if jQuery/Bootstrap not loaded
        console.error('jQuery or Bootstrap not loaded. Please refresh the page.');
        alert('Please wait for page to fully load, then try again.');
    }
}

function openNewYear() {
    Swal.fire({
        title: 'Open New Year',
        html: 'Do you want to open a new academic year?<br><br><small>This will create a new active academic year and you can start fresh.</small>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, open new year',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Opening New Year...',
                text: 'Please wait while we create the new academic year.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("admin.academicYears.open") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Reload page to show new year
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to open new academic year',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to open new academic year';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        }
    });
}

// Handle Close Year Form Submission
$(document).ready(function() {
    $('#closeYearForm').on('submit', function(e) {
        e.preventDefault();
        
        // Close the form modal completely first - hide it completely
        $('#closeYearModal').modal('hide');
        $('#closeYearModal').css('display', 'none');
        $('#closeYearModal').removeClass('show');
        
        // Remove any backdrop from form modal
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
        
        // Wait for form modal to fully close, then show progress modal
        setTimeout(function() {
            // Ensure form modal is completely hidden
            $('#closeYearModal').css('display', 'none');
            $('#closeYearModal').removeClass('show');
            
            // Show progress modal
            $('#closeYearProgressModal').modal('show');
            updateCloseYearProgress(5, 'Preparing to close academic year...');
        }, 400);
        
        // Get form data
        const formData = {
            modalities: [],
            school_closing_date: $('#school_closing_date').val(),
            school_reopening_date: $('#school_reopening_date').val(),
            close_notes: $('#close_notes').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Get selected modalities
        $('input[name="modalities[]"]:checked').each(function() {
            formData.modalities.push($(this).val());
        });
        
        // Add save_to_history as mandatory
        if (formData.modalities.indexOf('save_to_history') === -1) {
            formData.modalities.push('save_to_history');
        }
        
        // Validate dates
        if (!formData.school_closing_date || !formData.school_reopening_date) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please fill in both closing and reopening dates.',
                confirmButtonColor: '#940000'
            });
            $('#closeYearProgressModal').modal('hide');
            $('#closeYearModal').modal('show');
            $('#confirmCloseYearBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Year');
            return;
        }
        
        // Validate at least one modality is selected
        if (formData.modalities.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select at least one modality to perform.',
                confirmButtonColor: '#940000'
            });
            $('#closeYearProgressModal').modal('hide');
            $('#closeYearModal').modal('show');
            $('#confirmCloseYearBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Year');
            return;
        }
        
        // Send AJAX request with extended timeout (60 minutes = 3600000 milliseconds)
        $.ajax({
            url: '{{ route("admin.academicYears.close") }}',
            method: 'POST',
            data: formData,
            timeout: 3600000, // 60 minutes in milliseconds
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                // Track upload progress
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.min(5 + (evt.loaded / evt.total) * 5, 10);
                        updateCloseYearProgress(percentComplete, 'Sending request...');
                    }
                }, false);
                return xhr;
            },
            beforeSend: function() {
                updateCloseYearProgress(10, 'Request sent, processing...');
            },
            success: function(response) {
                updateCloseYearProgress(100, 'Completed successfully!');
                if (response.success) {
                    updateCloseYearProgress(100, 'Completed successfully!');
                    setTimeout(function() {
                        $('#closeYearProgressModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Academic year closed successfully!',
                            confirmButtonColor: '#940000',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    }, 1500);
                } else {
                    $('#closeYearProgressModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to close academic year.',
                        confirmButtonColor: '#940000'
                    }).then(() => {
                        $('#closeYearModal').modal('show');
                        $('#confirmCloseYearBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Year');
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to close academic year.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const error = JSON.parse(xhr.responseText);
                        if (error.message) {
                            errorMessage = error.message;
                        }
                    } catch(e) {
                        errorMessage = xhr.responseText.substring(0, 200);
                    }
                }
                $('#closeYearProgressModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#940000'
                }).then(() => {
                    $('#closeYearModal').modal('show');
                    $('#confirmCloseYearBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Year');
                });
            }
        });
    });
});

// Progress bar update function
function updateCloseYearProgress(percent, status) {
    percent = Math.min(100, Math.max(0, percent));
    $('#closeYearProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);
    $('#closeYearProgressText').text(Math.round(percent) + '%');
    $('#closeYearProgressStatus').text(status);
    
    // Simulate progress if less than 90% (to show activity)
    if (percent < 90) {
        // Gradually increase progress to show activity
        setTimeout(function() {
            var currentPercent = parseFloat($('#closeYearProgressBar').attr('aria-valuenow'));
            if (currentPercent < 90) {
                var increment = Math.random() * 2; // Random increment 0-2%
                updateCloseYearProgress(currentPercent + increment, status);
            }
        }, 5000); // Update every 5 seconds
    }
}

// Close progress modal and show form modal
function closeProgressModal() {
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        // Fallback to regular confirm
        if (confirm('Are you sure you want to cancel closing the academic year? The process will be interrupted.')) {
            $('#closeYearProgressModal').modal('hide');
            $('#closeYearProgressModal').css('display', 'none');
            $('#closeYearProgressModal').removeClass('show');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            
            // Show form modal again
            setTimeout(function() {
                $('#closeYearModal').modal('show');
                $('#confirmCloseYearBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Year');
            }, 300);
        }
        return;
    }
    
    Swal.fire({
        title: 'Cancel Process?',
        text: 'Are you sure you want to cancel closing the academic year? The process will be interrupted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel',
        cancelButtonText: 'No, continue',
        allowOutsideClick: false,
        allowEscapeKey: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Hide progress modal completely
            $('#closeYearProgressModal').modal('hide');
            $('#closeYearProgressModal').css('display', 'none');
            $('#closeYearProgressModal').removeClass('show');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            
            // Show form modal again
            setTimeout(function() {
                $('#closeYearModal').modal('show');
                $('#confirmCloseYearBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Year');
            }, 300);
        }
    });
}
</script>

@include('includes.footer')

<!-- jQuery - Load first -->
<script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

@include('includes.footer')

