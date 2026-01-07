@include('includes.teacher_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Century Gothic Font Family for everything except icons */
    body, h1, h2, h3, h4, h5, h6, p, span, div, a, button, input, select, textarea, label, .card, .btn, .form-control, .alert {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }
    
    /* Exclude Font Awesome icons from font-family override */
    .fa, .fa:before, i.fa, [class*="fa-"]:before, [class^="fa-"]:before {
        font-family: 'FontAwesome' !important;
    }
    
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
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .btn-outline-primary-custom {
        border-color: #940000;
        color: #940000;
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }
    .btn-outline-primary-custom:hover {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .welcome-card {
        background: #f8f9fa;
        color: #333333;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }
    .widget-card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(148, 0, 0, 0.1);
        cursor: pointer;
    }
    .widget-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(148, 0, 0, 0.2);
        border-color: #940000;
    }
    .widget-icon {
        font-size: 2rem;
        color: #940000;
    }
    .subject-select-card {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(148, 0, 0, 0.1);
    }
    /* No border radius as requested */
    .card, .btn, .form-control, .alert {
        border-radius: 0 !important;
    }
    .widgets-container {
        display: none;
    }
    .widgets-container.show {
        display: block;
    }
    /* Reduced widget sizes */
    .widget-card .card-body {
        padding: 1.5rem !important;
    }
    .widget-card h5 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }
    .widget-card p {
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    .widget-card .btn {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Welcome Message Card -->
            <div class="card border-0 mb-4 welcome-card">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <i class="fa fa-file-text-o text-primary-custom" style="font-size: 3rem;"></i>
                        </div>
                        <div class="col-md-11">
                            <h4 class="mb-2 text-primary-custom" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
                                <i class="fa fa-hand-paper-o"></i> Welcome to the Scheme of Work
                            </h4>
                            <p class="mb-0 text-muted" style="font-size: 1rem; font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
                                Let's you simplify your work. Create, manage, and view your scheme of work for each subject you teach.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Selection Card -->
            <div class="card border-0 mb-4 subject-select-card">
                <div class="card-body p-4">
                    <h5 class="text-primary-custom mb-3">
                        <i class="fa fa-book"></i> Select Subject
                    </h5>
                    <div class="form-group">
                        <label for="subjectSelect" class="font-weight-bold">Choose a subject to manage its scheme of work:</label>
                        <select class="form-control" id="subjectSelect" name="subject_id" style="height: 50px; font-size: 1rem;">
                            <option value="">-- Select Subject --</option>
                            @if(isset($teacherSubjects) && $teacherSubjects->count() > 0)
                                @foreach($teacherSubjects as $classSubject)
                                    @if($classSubject->subject && $classSubject->subject->status === 'Active')
                                        <option value="{{ $classSubject->class_subjectID }}" 
                                                data-subject-name="{{ $classSubject->subject->subject_name }}"
                                                data-subject-code="{{ $classSubject->subject->subject_code ?? '' }}">
                                            {{ $classSubject->subject->subject_name }}
                                            @if($classSubject->subject->subject_code)
                                                ({{ $classSubject->subject->subject_code }})
                                            @endif
                                            @if($classSubject->subclass && $classSubject->subclass->class)
                                                - {{ $classSubject->subclass->class->class_name }} {{ $classSubject->subclass->subclass_name }}
                                            @elseif($classSubject->class)
                                                - {{ $classSubject->class->class_name }}
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Select a subject to view available actions for managing its scheme of work.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Widgets Container (Shown when subject is selected) -->
            <div class="widgets-container" id="widgetsContainer">
                <div class="row">
                    <!-- Create Widget -->
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 widget-card h-100" id="createWidget">
                            <div class="card-body text-center">
                                <div class="widget-icon mb-2">
                                    <i class="fa fa-plus-circle"></i>
                                </div>
                                <h5 class="text-primary-custom mb-2">Create</h5>
                                <p class="text-muted mb-2" style="font-size: 0.85rem;">Create a new scheme of work or use an existing one</p>
                                <button class="btn btn-primary-custom btn-block" onclick="handleCreate()" style="font-size: 0.9rem; padding: 0.5rem;">
                                    <i class="fa fa-plus"></i> Create Scheme of Work
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- View Widget -->
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 widget-card h-100" id="viewWidget">
                            <div class="card-body text-center">
                                <div class="widget-icon mb-2">
                                    <i class="fa fa-eye"></i>
                                </div>
                                <h5 class="text-primary-custom mb-2">View</h5>
                                <p class="text-muted mb-2" style="font-size: 0.85rem;">View your existing scheme of work</p>
                                <button class="btn btn-outline-primary-custom btn-block" onclick="handleView()" style="font-size: 0.9rem; padding: 0.5rem;">
                                    <i class="fa fa-eye"></i> View Scheme of Work
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Manage Widget -->
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 widget-card h-100" id="manageWidget">
                            <div class="card-body text-center">
                                <div class="widget-icon mb-2">
                                    <i class="fa fa-cog"></i>
                                </div>
                                <h5 class="text-primary-custom mb-2">Manage</h5>
                                <p class="text-muted mb-2" style="font-size: 0.85rem;">Update or delete your scheme of work</p>
                                <button class="btn btn-outline-primary-custom btn-block" onclick="handleManage()" style="font-size: 0.9rem; padding: 0.5rem;">
                                    <i class="fa fa-cog"></i> Manage Scheme of Work
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Options Modal (for Create widget) -->
            <div class="modal fade" id="createOptionsModal" tabindex="-1" role="dialog" aria-labelledby="createOptionsModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" style="border-radius: 0;">
                        <div class="modal-header bg-primary-custom text-white">
                            <h5 class="modal-title" id="createOptionsModalLabel" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
                                <i class="fa fa-plus-circle"></i> Create Scheme of Work
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">Choose an option:</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card border-0 widget-card h-100" onclick="createNewScheme()" style="cursor: pointer;">
                                        <div class="card-body text-center p-3">
                                            <i class="fa fa-file-o widget-icon mb-2" style="font-size: 1.8rem;"></i>
                                            <h6 class="text-primary-custom mb-2" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">Create New</h6>
                                            <p class="text-muted small mb-0" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">Create a brand new scheme of work from scratch</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card border-0 widget-card h-100" onclick="useExistingScheme()" style="cursor: pointer;">
                                        <div class="card-body text-center p-3">
                                            <i class="fa fa-files-o widget-icon mb-2" style="font-size: 1.8rem;"></i>
                                            <h6 class="text-primary-custom mb-2" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">Use Existing</h6>
                                            <p class="text-muted small mb-0" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">Use or copy from an existing scheme of work</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('vendors/popper.js/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>

<script>
    let selectedSubjectId = null;
    let selectedSubjectName = null;

    // Handle subject selection
    document.getElementById('subjectSelect').addEventListener('change', function() {
        selectedSubjectId = this.value;
        const selectedOption = this.options[this.selectedIndex];
        selectedSubjectName = selectedOption.getAttribute('data-subject-name');
        
        if (selectedSubjectId) {
            // Show widgets container
            document.getElementById('widgetsContainer').classList.add('show');
        } else {
            // Hide widgets container
            document.getElementById('widgetsContainer').classList.remove('show');
        }
    });

    // Handle Create button click
    function handleCreate() {
        if (!selectedSubjectId) {
            alert('Please select a subject first');
            return;
        }
        // Show create options modal
        $('#createOptionsModal').modal('show');
    }

    // Handle View button click
    function handleView() {
        if (!selectedSubjectId) {
            alert('Please select a subject first');
            return;
        }
        // Get scheme of work ID for this subject and year
        const year = new Date().getFullYear();
        $.ajax({
            url: '{{ route("teacher.checkExistingScheme") }}',
            method: 'POST',
            data: {
                class_subjectID: selectedSubjectId,
                year: year,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.exists && response.scheme) {
                    window.location.href = '/teacher/scheme-of-work/view/' + response.scheme.id;
                } else {
                    alert('No scheme of work found for this subject. Please create one first.');
                }
            },
            error: function() {
                alert('Error checking for scheme of work');
            }
        });
    }

    // Handle Manage button click
    function handleManage() {
        if (!selectedSubjectId) {
            alert('Please select a subject first');
            return;
        }
        // Get scheme of work ID for this subject and year
        const year = new Date().getFullYear();
        $.ajax({
            url: '{{ route("teacher.checkExistingScheme") }}',
            method: 'POST',
            data: {
                class_subjectID: selectedSubjectId,
                year: year,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.exists && response.scheme) {
                    window.location.href = '/teacher/scheme-of-work/manage/' + response.scheme.id;
                } else {
                    alert('No scheme of work found for this subject. Please create one first.');
                }
            },
            error: function() {
                alert('Error checking for scheme of work');
            }
        });
    }

    // Create new scheme of work
    function createNewScheme() {
        $('#createOptionsModal').modal('hide');
        if (!selectedSubjectId) {
            alert('Please select a subject first');
            return;
        }
        // Check if scheme already exists
        $.ajax({
            url: '{{ route("teacher.checkExistingScheme") }}',
            method: 'POST',
            data: {
                class_subjectID: selectedSubjectId,
                year: new Date().getFullYear(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.exists) {
                    if (confirm('Scheme of work already exists for this subject. Do you want to view it instead?')) {
                        handleView();
                    }
                } else {
                    window.location.href = '/teacher/scheme-of-work/create/' + selectedSubjectId;
                }
            },
            error: function() {
                // If check fails, proceed anyway
                window.location.href = '/teacher/scheme-of-work/create/' + selectedSubjectId;
            }
        });
    }

    // Use existing scheme of work
    function useExistingScheme() {
        if (!selectedSubjectId) {
            alert('Please select a subject first');
            return;
        }
        $('#createOptionsModal').modal('hide');
        // Navigate to use existing schemes page
        window.location.href = '{{ route("teacher.useExistingSchemes", ":subjectId") }}'.replace(':subjectId', selectedSubjectId);
    }
</script>

