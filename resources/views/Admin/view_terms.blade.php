@include('includes.Admin_nav')

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
        margin-bottom: 30px;
    }
    
    .term-widget {
        border: 2px solid #e0e0e0;
        border-radius: 0;
        padding: 25px;
        margin-bottom: 25px;
        transition: all 0.3s ease;
        background: white;
    }
    
    .term-widget:hover {
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.2);
        border-color: #940000;
    }
    
    .term-widget.active {
        border-color: #28a745;
    }
    
    .term-widget.closed {
        border-color: #dc3545;
        background-color: #f8f9fa;
    }
    
    .term-badge {
        padding: 5px 15px;
        border-radius: 0;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .badge-active {
        background-color: #28a745;
        color: white;
    }
    
    .badge-closed {
        background-color: #dc3545;
        color: white;
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
    
    .btn-danger-custom {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #ffffff;
        border-radius: 0;
    }
    
    .btn-danger-custom:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: #ffffff;
    }
    
    .header-section {
        background: linear-gradient(135deg, #940000 0%, #b30000 100%);
        color: white;
        padding: 20px 0;
        margin-bottom: 30px;
    }
    
    .no-border-radius {
        border-radius: 0 !important;
    }
    
    /* Progress Modal z-index - Must be above form modal */
    #closeTermProgressModal {
        z-index: 1060 !important;
    }
    
    #closeTermProgressModal .modal-dialog {
        z-index: 1061 !important;
    }
    
    #closeTermProgressModal .modal-content {
        z-index: 1062 !important;
    }
    
    #closeTermProgressModal .modal-backdrop {
        z-index: 1059 !important;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    /* Ensure form modal backdrop is below progress modal */
    #closeTermModal.show ~ .modal-backdrop {
        z-index: 1040 !important;
    }
    
    /* SweetAlert2 z-index - Must be above progress modal */
    .swal2-container {
        z-index: 1070 !important;
    }
    
    .swal2-popup {
        z-index: 1071 !important;
    }
</style>

<div class="header-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fa fa-calendar"></i> Academic Terms - {{ $currentYear }}</h3>
                <p class="mb-0">Manage terms for the current academic year</p>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('admin.academicYears') }}" class="btn btn-secondary" style="background-color: #6c757d; border-color: #6c757d; color: white; border-radius: 0;">
                    <i class="fa fa-arrow-left"></i> Back to Academic Years
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="content-wrapper">
        @if($terms->count() > 0)
            @foreach($terms as $term)
                <div class="term-widget {{ strtolower($term->status) }}" id="term-{{ $term->termID }}">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4>
                                <i class="fa fa-calendar-check-o"></i> 
                                {{ $term->term_name }}
                                <span class="term-badge badge-{{ strtolower($term->status) }}">{{ $term->status }}</span>
                            </h4>
                            <p class="mb-2">
                                <strong>Period:</strong> 
                                {{ \Carbon\Carbon::parse($term->start_date)->format('d M Y') }} - 
                                {{ \Carbon\Carbon::parse($term->end_date)->format('d M Y') }}
                            </p>
                            @if($term->closed_at)
                                <p class="mb-0 text-muted">
                                    <small>Closed on: {{ \Carbon\Carbon::parse($term->closed_at)->format('d M Y, h:i A') }}</small>
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-right">
                            @if($term->status === 'Active')
                                <button class="btn btn-danger-custom btn-lg" onclick="showCloseTermModal({{ $term->termID }}, '{{ $term->term_name }}')" style="width: 100%;">
                                    <i class="fa fa-lock"></i> Close Term
                                </button>
                            @else
                                <span class="text-muted">
                                    <i class="fa fa-check-circle"></i> Term Closed
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-info text-center">
                <i class="fa fa-info-circle"></i> No terms found for this academic year.
            </div>
        @endif
    </div>
</div>

<!-- Close Term Modal -->
<div class="modal fade modal-custom" id="closeTermModal" tabindex="-1" role="dialog" aria-labelledby="closeTermModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="closeTermModalLabel">
                    <i class="fa fa-lock"></i> Close Term
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="closeTermForm">
                <input type="hidden" id="close_term_termID" name="termID">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> 
                        <strong>Warning:</strong> Closing term will lock results editing for this term. This action cannot be undone!
                    </div>
                    
                    <p class="mb-3"><strong>Select modalities to perform when closing this term:</strong></p>
                    
                    <div class="checkbox-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 0;">
                        <label>
                            <input type="checkbox" name="modalities[]" value="send_sms_to_parents" checked>
                            <strong><i class="fa fa-send"></i> Send SMS to Parents with Term Results</strong>
                        </label>
                        <small style="display: block; margin-top: 5px; color: #666;">Send SMS notifications to parents with their children's term results summary (total marks and grade).</small>
                    </div>
                    
                    <div class="checkbox-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 0;">
                        <label>
                            <input type="checkbox" name="modalities[]" value="lock_results_editing" checked>
                            <strong><i class="fa fa-lock"></i> Lock Results Editing for This Term</strong>
                        </label>
                        <small style="display: block; margin-top: 5px; color: #666;">Prevent teachers from editing results for examinations in this term after closing.</small>
                    </div>
                    
                    <hr>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="close_term_notes"><strong>Notes (Optional):</strong></label>
                            <textarea class="form-control" id="close_term_notes" name="close_term_notes" rows="3" placeholder="Add any notes about closing this term..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger-custom" id="confirmCloseTermBtn">
                        <i class="fa fa-lock"></i> Confirm Close Term
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Term Progress Modal -->
<div class="modal fade modal-custom" id="closeTermProgressModal" tabindex="-1" role="dialog" aria-labelledby="closeTermProgressModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" style="z-index: 1060;">
    <div class="modal-dialog modal-md" role="document" style="z-index: 1061;">
        <div class="modal-content" style="border-radius: 0; z-index: 1062;">
            <div class="modal-header modal-header-custom" style="background-color: #dc3545; color: white;">
                <h5 class="modal-title" id="closeTermProgressModalLabel">
                    <i class="fa fa-cog fa-spin"></i> Closing Term
                </h5>
                <button type="button" class="close text-white" onclick="closeTermProgressModal()" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" style="padding: 40px 20px;">
                <!-- Animated Icon -->
                <div style="margin-bottom: 30px;">
                    <i class="fa fa-spinner fa-pulse" style="font-size: 60px; color: #dc3545;"></i>
                </div>
                
                <!-- Progress Bar -->
                <div class="progress" style="height: 35px; margin-bottom: 20px; border-radius: 0;">
                    <div id="closeTermProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 0%; background-color: #dc3545; transition: width 0.3s ease;"
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        <span id="closeTermProgressText" style="line-height: 35px; font-weight: bold; color: white; font-size: 14px;">0%</span>
                    </div>
                </div>
                
                <!-- Status Message -->
                <div id="closeTermProgressStatus" class="text-muted" style="font-size: 16px; margin-bottom: 15px; min-height: 24px;">
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
function showCloseTermModal(termID, termName) {
    // Set term ID and name in modal
    $('#close_term_termID').val(termID);
    $('#closeTermModalLabel').html('<i class="fa fa-lock"></i> Close Term - ' + termName);
    
    // Show modal
    $('#closeTermModal').modal('show');
}

// Handle Close Term Form Submission
$(document).ready(function() {
    $('#closeTermForm').on('submit', function(e) {
        e.preventDefault();
        
        // Close the form modal completely first - hide it completely
        $('#closeTermModal').modal('hide');
        $('#closeTermModal').css('display', 'none');
        $('#closeTermModal').removeClass('show');
        
        // Remove any backdrop from form modal
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
        
        // Wait for form modal to fully close, then show progress modal
        setTimeout(function() {
            // Ensure form modal is completely hidden
            $('#closeTermModal').css('display', 'none');
            $('#closeTermModal').removeClass('show');
            
            // Show progress modal
            $('#closeTermProgressModal').modal('show');
            updateCloseTermProgress(5, 'Preparing to close term...');
        }, 400);
        
        // Get form data
        const formData = {
            termID: $('#close_term_termID').val(),
            modalities: [],
            close_term_notes: $('#close_term_notes').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Get selected modalities
        $('input[name="modalities[]"]:checked').each(function() {
            formData.modalities.push($(this).val());
        });
        
        // Validate at least one modality is selected
        if (formData.modalities.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select at least one modality to perform.',
                confirmButtonColor: '#dc3545'
            });
            $('#closeTermProgressModal').modal('hide');
            $('#closeTermModal').modal('show');
            $('#confirmCloseTermBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Term');
            return;
        }
        
        // Send AJAX request with extended timeout (60 minutes = 3600000 milliseconds)
        $.ajax({
            url: '{{ route("admin.academicYears.closeTerm") }}',
            method: 'POST',
            data: formData,
            timeout: 3600000, // 60 minutes in milliseconds
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                // Track upload progress
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.min(5 + (evt.loaded / evt.total) * 5, 10);
                        updateCloseTermProgress(percentComplete, 'Sending request...');
                    }
                }, false);
                return xhr;
            },
            beforeSend: function() {
                updateCloseTermProgress(10, 'Request sent, processing...');
            },
            success: function(response) {
                updateCloseTermProgress(100, 'Completed successfully!');
                if (response.success) {
                    updateCloseTermProgress(100, 'Completed successfully!');
                    setTimeout(function() {
                        $('#closeTermProgressModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Term closed successfully!',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    }, 1500);
                } else {
                    $('#closeTermProgressModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to close term.',
                        confirmButtonColor: '#dc3545'
                    }).then(() => {
                        $('#closeTermModal').modal('show');
                        $('#confirmCloseTermBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Term');
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to close term.';
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
                $('#closeTermProgressModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#dc3545'
                }).then(() => {
                    $('#closeTermModal').modal('show');
                    $('#confirmCloseTermBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Term');
                });
            }
        });
    });
});

// Progress bar update function for close term
function updateCloseTermProgress(percent, status) {
    percent = Math.min(100, Math.max(0, percent));
    $('#closeTermProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);
    $('#closeTermProgressText').text(Math.round(percent) + '%');
    $('#closeTermProgressStatus').text(status);
    
    // Simulate progress if less than 90% (to show activity)
    if (percent < 90) {
        // Gradually increase progress to show activity
        setTimeout(function() {
            var currentPercent = parseFloat($('#closeTermProgressBar').attr('aria-valuenow'));
            if (currentPercent < 90) {
                var increment = Math.random() * 2; // Random increment 0-2%
                updateCloseTermProgress(currentPercent + increment, status);
            }
        }, 5000); // Update every 5 seconds
    }
}

// Close progress modal and show form modal
function closeTermProgressModal() {
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        // Fallback to regular confirm
        if (confirm('Are you sure you want to cancel closing the term? The process will be interrupted.')) {
            $('#closeTermProgressModal').modal('hide');
            $('#closeTermProgressModal').css('display', 'none');
            $('#closeTermProgressModal').removeClass('show');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            
            // Show form modal again
            setTimeout(function() {
                $('#closeTermModal').modal('show');
                $('#confirmCloseTermBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Term');
            }, 300);
        }
        return;
    }
    
    Swal.fire({
        title: 'Cancel Process?',
        text: 'Are you sure you want to cancel closing the term? The process will be interrupted.',
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
            $('#closeTermProgressModal').modal('hide');
            $('#closeTermProgressModal').css('display', 'none');
            $('#closeTermProgressModal').removeClass('show');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            
            // Show form modal again
            setTimeout(function() {
                $('#closeTermModal').modal('show');
                $('#confirmCloseTermBtn').prop('disabled', false).html('<i class="fa fa-lock"></i> Confirm Close Term');
            }, 300);
        }
    });
}
</script>

@include('includes.footer')

