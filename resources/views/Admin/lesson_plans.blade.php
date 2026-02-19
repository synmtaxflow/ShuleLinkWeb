@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
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
    
    .signature-canvas {
        border: 2px solid #212529;
        border-radius: 4px;
        cursor: crosshair;
        background-color: white;
        width: 100%;
        max-width: 400px;
        touch-action: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    
    .signature-canvas:focus {
        outline: none;
    }
    
    .signature-preview {
        border: 2px solid #212529;
        border-radius: 4px;
        max-width: 100%;
        height: auto;
    }
    
    /* Fix modal backdrop issues */
    .modal-backdrop {
        z-index: 1040 !important;
    }
    
    .modal {
        z-index: 1050 !important;
    }
    
    /* Ensure modal content is clickable */
    .modal-content {
        pointer-events: auto !important;
    }
    
    /* Prevent backdrop from blocking clicks */
    .modal-backdrop.show {
        opacity: 0.5;
        pointer-events: auto;
    }
    
    /* Ensure body doesn't get stuck */
    body.modal-open {
        overflow: hidden;
        padding-right: 0 !important;
    }
</style>

<div class="header-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fa fa-file-text"></i> Lesson Plans Management</h3>
                <p class="mb-0">View and sign lesson plans sent by teachers</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="content-wrapper">
        <!-- Filter Section -->
        <div class="card mb-4" style="border-radius: 0;">
            <div class="card-header" style="background-color: #940000; color: white; border-radius: 0;">
                <h5 class="mb-0"><i class="fa fa-filter"></i> Filter Lesson Plans</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Subject</strong></label>
                            <select class="form-control" id="subjectFilter" style="border-radius: 0;">
                                <option value="">-- Select Subject --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->subjectID }}">{{ $subject->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Class</strong></label>
                            <select class="form-control" id="classFilter" style="border-radius: 0;">
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->subclassID }}">{{ $class->class_name }} - {{ $class->subclass_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-primary-custom" onclick="loadLessonPlans()" style="border-radius: 0;">
                            <i class="fa fa-search"></i> Load Lesson Plans
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lesson Plans List -->
        <div id="lessonPlansContainer">
            <div class="text-center text-muted py-5">
                <i class="fa fa-info-circle" style="font-size: 3rem;"></i>
                <p class="mt-3">Please select subject and class to view lesson plans</p>
            </div>
        </div>
    </div>
</div>

<!-- Lesson Plan View Modal -->
<div class="modal fade" id="lessonPlanModal" tabindex="-1" role="dialog" data-backdrop="true" data-keyboard="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header" style="background-color: #940000; color: white; border-radius: 0;">
                <h5 class="modal-title">
                    <i class="fa fa-file-text"></i> Lesson Plan Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="lessonPlanModalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let supervisorSignaturePad = null;
let currentLessonPlanID = null;

function loadLessonPlans() {
    const subjectID = $('#subjectFilter').val();
    const classID = $('#classFilter').val();
    
    if (!subjectID || !classID) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select both subject and class',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }
    
    $('#lessonPlansContainer').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading lesson plans...</p>
        </div>
    `);
    
    $.ajax({
        url: '{{ route("admin.get_lesson_plans") }}',
        method: 'GET',
        data: {
            subjectID: subjectID,
            classID: classID
        },
        success: function(response) {
            if (response.success && response.lesson_plans && response.lesson_plans.length > 0) {
                renderLessonPlansList(response.lesson_plans, response.subject_name, response.class_name);
            } else {
                $('#lessonPlansContainer').html(`
                    <div class="alert alert-info" style="border-radius: 0;">
                        <i class="fa fa-info-circle"></i> No lesson plans found for ${response.subject_name || 'selected subject'} - ${response.class_name || 'selected class'}.
                    </div>
                `);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to load lesson plans';
            $('#lessonPlansContainer').html(`
                <div class="alert alert-danger" style="border-radius: 0;">
                    <i class="fa fa-exclamation-circle"></i> ${error}
                </div>
            `);
        }
    });
}

function renderLessonPlansList(lessonPlans, subjectName, className) {
    let html = `
        <div class="card" style="border-radius: 0;">
            <div class="card-header" style="background-color: #940000; color: white; border-radius: 0;">
                <h5 class="mb-0">
                    <i class="fa fa-list"></i> ${subjectName} - ${className} (${lessonPlans.length} lesson plan(s))
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="border-radius: 0;">
                        <thead style="background-color: #f5f5f5;">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Teacher</th>
                                <th>Time</th>
                                <th>Sent At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    lessonPlans.forEach(function(plan, index) {
        const dateObj = new Date(plan.lesson_date);
        const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const sentAt = plan.sent_at ? new Date(plan.sent_at).toLocaleString('en-GB') : 'N/A';
        const formatTime = (timeStr) => {
            if (!timeStr) return 'N/A';
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return displayHours + ':' + minutes + ' ' + ampm;
        };
        
        const isSigned = plan.supervisor_signature ? true : false;
        
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${formattedDate}</td>
                <td>${plan.teacher_name || 'N/A'}</td>
                <td>${formatTime(plan.lesson_time_start)} - ${formatTime(plan.lesson_time_end)}</td>
                <td>${sentAt}</td>
                <td>
                    ${isSigned ? 
                        '<span class="badge badge-success">Signed</span>' : 
                        '<span class="badge badge-warning">Pending Signature</span>'
                    }
                </td>
                <td>
                    <button class="btn btn-sm btn-primary-custom" onclick="viewLessonPlan(${plan.lesson_planID})" style="border-radius: 0;">
                        <i class="fa fa-eye"></i> View & Sign
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    $('#lessonPlansContainer').html(html);
}

function viewLessonPlan(lessonPlanID) {
    // Prevent multiple clicks
    if ($('#lessonPlanModal').hasClass('show')) {
        return;
    }
    
    // Store current lesson plan ID
    currentLessonPlanID = lessonPlanID;
    
    // Reset signature pad
    supervisorSignaturePad = null;
    
    $.ajax({
        url: '{{ route("admin.get_lesson_plan") }}',
        method: 'GET',
        data: { lesson_planID: lessonPlanID },
        success: function(response) {
            if (response.success) {
                renderLessonPlanView(response.data);
                
                // Clean up any existing backdrops first
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
                
                // Show modal with proper settings
                $('#lessonPlanModal').modal({
                    backdrop: true,
                    keyboard: true,
                    show: true
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.error || 'Failed to load lesson plan',
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to load lesson plan',
                icon: 'error',
                confirmButtonColor: '#940000'
            });
        }
    });
}

function renderLessonPlanView(data) {
    const schoolType = '{{ $schoolTypeDisplay }}';
    const formatTime = (timeStr) => {
        if (!timeStr) return 'N/A';
        const parts = timeStr.split(':');
        const hours = parseInt(parts[0]);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return displayHours + ':' + minutes + ' ' + ampm;
    };
    
    const startTime = formatTime(data.lesson_time_start);
    const endTime = formatTime(data.lesson_time_end);
    const dateObj = new Date(data.lesson_date);
    const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    
    let html = `
        <div style="text-align: center; font-weight: bold; font-size: 18px; margin: 20px 0;">LESSON PLAN</div>
        <div style="text-align: center; font-weight: bold; font-size: 18px; margin-bottom: 20px;">${schoolType}</div>
        
        <table class="table table-bordered" style="font-size: 0.9rem;">
            <tr>
                <th style="background-color: #f5f5f5;">SUBJECT:</th>
                <td>${data.subject || 'N/A'}</td>
                <th style="background-color: #f5f5f5;">CLASS:</th>
                <td>${data.class_name || 'N/A'}</td>
                <th style="background-color: #f5f5f5;">YEAR:</th>
                <td>${data.year}</td>
            </tr>
            <tr>
                <th style="background-color: #f5f5f5;">TEACHER'S NAME</th>
                <td colspan="2">${data.teacher_name || 'N/A'}</td>
                <td colspan="3">
                    <table class="table table-bordered table-sm" style="margin: 0;">
                        <tr>
                            <th colspan="3" style="background-color: #f5f5f5; text-align: center;">NUMBER OF PUPILS</th>
                        </tr>
                        <tr>
                            <th colspan="3" style="background-color: #f5f5f5; text-align: center;">REGISTERED</th>
                            <th colspan="3" style="background-color: #f5f5f5; text-align: center;">PRESENT</th>
                        </tr>
                        <tr>
                            <th style="background-color: #f5f5f5;">GIRLS</th>
                            <th style="background-color: #f5f5f5;">BOYS</th>
                            <th style="background-color: #f5f5f5;">TOTAL</th>
                            <th style="background-color: #f5f5f5;">GIRLS</th>
                            <th style="background-color: #f5f5f5;">BOYS</th>
                            <th style="background-color: #f5f5f5;">TOTAL</th>
                        </tr>
                        <tr>
                            <td>${data.registered_girls || 0}</td>
                            <td>${data.registered_boys || 0}</td>
                            <td>${data.registered_total || 0}</td>
                            <td>${data.present_girls || 0}</td>
                            <td>${data.present_boys || 0}</td>
                            <td>${data.present_total || 0}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th style="background-color: #f5f5f5;">TIME</th>
                <td>${startTime} - ${endTime}</td>
                <th style="background-color: #f5f5f5;">DATE</th>
                <td colspan="3">${formattedDate}</td>
            </tr>
        </table>
        
        <table class="table table-bordered" style="font-size: 0.9rem;">
            <tr>
                <th style="background-color: #f5f5f5; width: 200px;">MAIN COMPETENCE</th>
                <td>${data.main_competence || ''}</td>
            </tr>
            <tr>
                <th style="background-color: #f5f5f5;">SPECIFIC COMPETENCE</th>
                <td>${data.specific_competence || ''}</td>
            </tr>
            <tr>
                <th style="background-color: #f5f5f5;">MAIN ACTIVITY</th>
                <td>${data.main_activity || ''}</td>
            </tr>
            <tr>
                <th style="background-color: #f5f5f5;">SPECIFIC ACTIVITY</th>
                <td>${data.specific_activity || ''}</td>
            </tr>
            <tr>
                <th style="background-color: #f5f5f5;">TEACHING & LEARNING RESOURCES</th>
                <td>${data.teaching_learning_resources || ''}</td>
            </tr>
            <tr>
                <th style="background-color: #f5f5f5;">REFERENCES</th>
                <td>${data.references || ''}</td>
            </tr>
        </table>
        
        <h5 class="mt-4 mb-3">LESSON DEVELOPMENT</h5>
        <table class="table table-bordered" style="font-size: 0.9rem;">
            <thead>
                <tr>
                    <th style="background-color: #f5f5f5;">STAGE</th>
                    <th style="background-color: #f5f5f5;">TIME</th>
                    <th style="background-color: #f5f5f5;">TEACHING ACTIVITIES</th>
                    <th style="background-color: #f5f5f5;">LEARNING ACTIVITIES</th>
                    <th style="background-color: #f5f5f5;">ASSESSMENT CRITERIA</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    const stages = data.lesson_stages || [];
    const stageNames = ['Introduction', 'Competence development', 'Design', 'Realization'];
    
    stageNames.forEach((stageName) => {
        const stage = stages.find(s => s.stage === stageName) || {};
        html += `
            <tr>
                <td>${stageName}</td>
                <td>${stage.time || ''}</td>
                <td>${stage.teaching_activities || ''}</td>
                <td>${stage.learning_activities || ''}</td>
                <td>${stage.assessment_criteria || ''}</td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
        
        <div class="mt-3">
            <label><strong>Remarks:</strong></label>
            <div style="border-bottom: 2px dotted #212529; min-height: 30px; padding: 5px 0;">
                <p style="margin: 0;">${data.remarks || ''}</p>
            </div>
        </div>
        
        <div class="mt-3">
            <label><strong>Reflection:</strong></label>
            <div style="border-bottom: 2px dotted #212529; min-height: 30px; padding: 5px 0;">
                <p style="margin: 0;">${data.reflection || ''}</p>
            </div>
        </div>
        
        <div class="mt-3">
            <label><strong>Evaluation:</strong></label>
            <div style="border-bottom: 2px dotted #212529; min-height: 30px; padding: 5px 0;">
                <p style="margin: 0;">${data.evaluation || ''}</p>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <label><strong>Subject Teacher's Signature:</strong></label>
                    </div>
                    ${data.teacher_signature ? 
                        `<img src="${data.teacher_signature}" class="signature-preview" style="max-width: 100%;">` :
                        `<div style="border: 2px solid #212529; border-radius: 4px; min-height: 150px; padding: 10px; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">No signature</p>
                        </div>`
                    }
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <label><strong>Academic/Supervisor's Signature:</strong></label>
                    </div>
                    ${data.supervisor_signature ? 
                        `<img src="${data.supervisor_signature}" class="signature-preview" style="max-width: 100%;">` :
                        `<div style="width: 100%;">
                            <div style="border: 2px solid #212529; border-radius: 4px; padding: 10px; background-color: white;">
                                <canvas id="supervisorSignatureCanvas" class="signature-canvas" style="width: 100%; max-width: 400px; height: 150px; display: block; margin: 0 auto; cursor: crosshair;"></canvas>
                            </div>
                            <div class="mt-2 text-center">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="clearSupervisorSignature()" style="border-radius: 0;">
                                    <i class="fa fa-eraser"></i> Clear Signature
                                </button>
                            </div>
                            <small class="text-muted d-block text-center mt-1">
                                <i class="fa fa-info-circle"></i> Draw your signature above
                            </small>
                        </div>`
                    }
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <button class="btn btn-primary-custom btn-lg" onclick="signLessonPlan(${data.lesson_planID})" style="border-radius: 0;" ${data.supervisor_signature ? 'disabled' : ''}>
                <i class="fa fa-check"></i> ${data.supervisor_signature ? 'Already Signed' : 'Sign Lesson Plan'}
            </button>
        </div>
    `;
    
    $('#lessonPlanModalContent').html(html);
    
    // Signature pad will be initialized automatically when modal is shown
    // via the 'shown.bs.modal' event handler below
}

function initializeSupervisorSignaturePad() {
    const canvas = document.getElementById('supervisorSignatureCanvas');
    if (!canvas) {
        console.error('Signature canvas not found');
        return;
    }
    
    // Clear any existing signature pad
    if (supervisorSignaturePad) {
        supervisorSignaturePad.clear();
        supervisorSignaturePad.off();
    }
    
    // Get canvas dimensions
    const rect = canvas.getBoundingClientRect();
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    
    // Set canvas size
    canvas.width = rect.width * ratio;
    canvas.height = 150 * ratio; // Fixed height
    
    // Scale context
    const ctx = canvas.getContext('2d');
    ctx.scale(ratio, ratio);
    
    // Initialize SignaturePad
    supervisorSignaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1,
        maxWidth: 3,
        throttle: 16,
    });
    
    // Handle resize
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = rect.width * ratio;
        canvas.height = 150 * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        
        // Redraw signature if it exists
        if (supervisorSignaturePad && !supervisorSignaturePad.isEmpty()) {
            supervisorSignaturePad.fromDataURL(supervisorSignaturePad.toDataURL());
        }
    }
    
    // Remove existing resize listener if any
    window.removeEventListener('resize', resizeCanvas);
    window.addEventListener('resize', resizeCanvas);
    
    console.log('Signature pad initialized successfully');
}

function clearSupervisorSignature() {
    if (supervisorSignaturePad) {
        supervisorSignaturePad.clear();
    }
}

function removeSignature(lessonPlanID) {
    Swal.fire({
        title: 'Remove Signature?',
        text: 'Are you sure you want to remove your signature? You will be able to sign again later.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove It',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.remove_lesson_plan_signature") }}',
                method: 'POST',
                data: {
                    lesson_planID: lessonPlanID,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Signature removed successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Reload the lesson plan view to show signature pad
                            if (currentLessonPlanID) {
                                viewLessonPlan(currentLessonPlanID);
                            } else {
                                // If currentLessonPlanID is not set, reload from the button's onclick
                                viewLessonPlan(lessonPlanID);
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.error || 'Failed to remove signature',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to remove signature';
                    Swal.fire({
                        title: 'Error!',
                        text: error,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        }
    });
}

function signLessonPlan(lessonPlanID) {
    if (!supervisorSignaturePad || supervisorSignaturePad.isEmpty()) {
        Swal.fire({
            title: 'Error!',
            text: 'Please provide your signature',
            icon: 'error',
            confirmButtonColor: '#940000'
        });
        return;
    }
    
    const signature = supervisorSignaturePad.toDataURL();
    
    Swal.fire({
        title: 'Sign Lesson Plan?',
        text: 'Are you sure you want to sign this lesson plan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#940000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Sign',
        cancelButtonText: 'Cancel',
        allowOutsideClick: true,
        allowEscapeKey: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.sign_lesson_plan") }}',
                method: 'POST',
                data: {
                    lesson_planID: lessonPlanID,
                    supervisor_signature: signature,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Lesson plan signed successfully',
                            icon: 'success',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Properly close modal and clean up
                            $('#lessonPlanModal').modal('hide');
                            
                            // Clean up backdrop after modal closes
                            setTimeout(function() {
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');
                                $('body').css('padding-right', '');
                            }, 300);
                            
                            loadLessonPlans();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.error || 'Failed to sign lesson plan',
                            icon: 'error',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to sign lesson plan';
                    Swal.fire({
                        title: 'Error!',
                        text: error,
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        }
    });
}

// Initialize signature pad when modal is shown
$('#lessonPlanModal').on('shown.bs.modal', function() {
    // Check if signature canvas exists and is not signed
    const canvas = document.getElementById('supervisorSignatureCanvas');
    if (canvas && !supervisorSignaturePad) {
        setTimeout(function() {
            initializeSupervisorSignaturePad();
        }, 300);
    }
});

// Clean up modal backdrop when modal is hidden
$('#lessonPlanModal').on('hidden.bs.modal', function() {
    // Remove any lingering backdrops
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('body').css('padding-right', '');
    $('body').css('overflow', '');
    
    // Reset signature pad
    if (supervisorSignaturePad) {
        supervisorSignaturePad.clear();
        supervisorSignaturePad = null;
    }
});

// Ensure modal is properly closed when clicking backdrop
$('#lessonPlanModal').on('click', function(e) {
    if ($(e.target).hasClass('modal') || $(e.target).hasClass('modal-dialog')) {
        $('#lessonPlanModal').modal('hide');
    }
});

// Ensure buttons inside modal are clickable
$(document).on('click', '#lessonPlanModal button, #lessonPlanModal a', function(e) {
    e.stopPropagation();
});
</script>
