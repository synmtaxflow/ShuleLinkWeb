@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
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
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-bookmark-star"></i> Grade Definitions Management
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Success/Error Messages -->
                    <div id="alertContainer"></div>

                    <!-- Add Grade Definition Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addGradeDefinitionModal">
                            <i class="bi bi-plus-circle"></i> Add Grade Definition
                        </button>
                    </div>

                    <!-- Grade Definitions Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="gradeDefinitionsTable">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>Class</th>
                                    <th>Grade</th>
                                    <th>First Mark</th>
                                    <th>Last Mark</th>
                                    <th>Range</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gradeDefinitions as $definition)
                                <tr>
                                    <td>{{ $definition->classModel->class_name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-info">{{ $definition->grade }}</span></td>
                                    <td>{{ number_format($definition->first, 2) }}</td>
                                    <td>{{ number_format($definition->last, 2) }}</td>
                                    <td>{{ number_format($definition->first, 2) }} - {{ number_format($definition->last, 2) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning edit-grade-definition" 
                                                data-id="{{ $definition->gradeDefinitionID }}"
                                                data-class-id="{{ $definition->classID }}"
                                                data-first="{{ $definition->first }}"
                                                data-last="{{ $definition->last }}"
                                                data-grade="{{ $definition->grade }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-grade-definition" 
                                                data-id="{{ $definition->gradeDefinitionID }}">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No grade definitions found. Please add one.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Grouped by Class View -->
                    @if($groupedDefinitions->count() > 0)
                    <div class="mt-4">
                        <h5 class="mb-3">Grade Definitions by Class</h5>
                        @foreach($groupedDefinitions as $classID => $definitions)
                        @php
                            $class = $definitions->first()->classModel;
                        @endphp
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><strong>{{ $class->class_name ?? 'N/A' }}</strong></h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead class="bg-primary-custom text-white">
                                            <tr>
                                                <th>Grade</th>
                                                <th>First Mark</th>
                                                <th>Last Mark</th>
                                                <th>Range</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($definitions->sortByDesc('first') as $definition)
                                            <tr>
                                                <td><span class="badge bg-info">{{ $definition->grade }}</span></td>
                                                <td>{{ number_format($definition->first, 2) }}</td>
                                                <td>{{ number_format($definition->last, 2) }}</td>
                                                <td>{{ number_format($definition->first, 2) }} - {{ number_format($definition->last, 2) }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-warning edit-grade-definition" 
                                                            data-id="{{ $definition->gradeDefinitionID }}"
                                                            data-class-id="{{ $definition->classID }}"
                                                            data-first="{{ $definition->first }}"
                                                            data-last="{{ $definition->last }}"
                                                            data-grade="{{ $definition->grade }}">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-grade-definition" 
                                                            data-id="{{ $definition->gradeDefinitionID }}">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Grade Definition Modal -->
<div class="modal fade" id="addGradeDefinitionModal" tabindex="-1" aria-labelledby="addGradeDefinitionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addGradeDefinitionModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Grade Definition
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addGradeDefinitionForm">
                @csrf
                <div class="modal-body">
                    <div id="addFormErrors" class="alert alert-danger" style="display: none;"></div>
                    
                    <div class="mb-3">
                        <label for="add_classID" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_classID" name="classID" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger" id="add_classID_error" style="display: none;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="add_grade" class="form-label">Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_grade" name="grade" placeholder="e.g., A, B, C, D, E, F" required maxlength="10">
                        <small class="text-muted">Enter grade letter (e.g., A, B, C, D, E, F)</small>
                        <div class="text-danger" id="add_grade_error" style="display: none;"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_first" class="form-label">First Mark <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="add_first" name="first" step="0.01" min="0" max="100" placeholder="e.g., 75" required>
                            <small class="text-muted">Minimum marks for this grade</small>
                            <div class="text-danger" id="add_first_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_last" class="form-label">Last Mark <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="add_last" name="last" step="0.01" min="0" max="100" placeholder="e.g., 100" required>
                            <small class="text-muted">Maximum marks for this grade</small>
                            <div class="text-danger" id="add_last_error" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Grade Definition Modal -->
<div class="modal fade" id="editGradeDefinitionModal" tabindex="-1" aria-labelledby="editGradeDefinitionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editGradeDefinitionModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Grade Definition
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGradeDefinitionForm">
                @csrf
                <input type="hidden" id="edit_gradeDefinitionID" name="gradeDefinitionID">
                <div class="modal-body">
                    <div id="editFormErrors" class="alert alert-danger" style="display: none;"></div>
                    
                    <div class="mb-3">
                        <label for="edit_classID" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_classID" name="classID" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger" id="edit_classID_error" style="display: none;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_grade" class="form-label">Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_grade" name="grade" placeholder="e.g., A, B, C, D, E, F" required maxlength="10">
                        <small class="text-muted">Enter grade letter (e.g., A, B, C, D, E, F)</small>
                        <div class="text-danger" id="edit_grade_error" style="display: none;"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_first" class="form-label">First Mark <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_first" name="first" step="0.01" min="0" max="100" placeholder="e.g., 75" required>
                            <small class="text-muted">Minimum marks for this grade</small>
                            <div class="text-danger" id="edit_first_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_last" class="form-label">Last Mark <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_last" name="last" step="0.01" min="0" max="100" placeholder="e.g., 100" required>
                            <small class="text-muted">Maximum marks for this grade</small>
                            <div class="text-danger" id="edit_last_error" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Show Alert Function
    function showAlert(message, type = 'success') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Clear Form Errors
    function clearFormErrors(formType) {
        $(`#${formType}FormErrors`).hide().html('');
        $(`#${formType}_classID_error, #${formType}_grade_error, #${formType}_first_error, #${formType}_last_error`).hide().html('');
    }

    // Add Grade Definition Form Submit
    $('#addGradeDefinitionForm').on('submit', function(e) {
        e.preventDefault();
        clearFormErrors('add');

        const formData = {
            classID: $('#add_classID').val(),
            grade: $('#add_grade').val(),
            first: $('#add_first').val(),
            last: $('#add_last').val()
        };

        $.ajax({
            url: '{{ route("grade_definitions.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#addGradeDefinitionModal').modal('hide');
                    $('#addGradeDefinitionForm')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul class="mb-0">';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value[0] + '</li>';
                        $(`#add_${key}_error`).text(value[0]).show();
                    });
                    errorHtml += '</ul>';
                    $('#addFormErrors').html(errorHtml).show();
                } else {
                    showAlert(xhr.responseJSON.message || 'Failed to create grade definition.', 'danger');
                }
            }
        });
    });

    // Edit Grade Definition Button Click
    $(document).on('click', '.edit-grade-definition', function() {
        const id = $(this).data('id');
        const classID = $(this).data('class-id');
        const first = $(this).data('first');
        const last = $(this).data('last');
        const grade = $(this).data('grade');

        $('#edit_gradeDefinitionID').val(id);
        $('#edit_classID').val(classID);
        $('#edit_grade').val(grade);
        $('#edit_first').val(first);
        $('#edit_last').val(last);

        $('#editGradeDefinitionModal').modal('show');
    });

    // Edit Grade Definition Form Submit
    $('#editGradeDefinitionForm').on('submit', function(e) {
        e.preventDefault();
        clearFormErrors('edit');

        const id = $('#edit_gradeDefinitionID').val();
        const formData = {
            classID: $('#edit_classID').val(),
            grade: $('#edit_grade').val(),
            first: $('#edit_first').val(),
            last: $('#edit_last').val()
        };

        $.ajax({
            url: `{{ url('grade_definitions') }}/${id}`,
            method: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#editGradeDefinitionModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul class="mb-0">';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value[0] + '</li>';
                        $(`#edit_${key}_error`).text(value[0]).show();
                    });
                    errorHtml += '</ul>';
                    $('#editFormErrors').html(errorHtml).show();
                } else {
                    showAlert(xhr.responseJSON.message || 'Failed to update grade definition.', 'danger');
                }
            }
        });
    });

    // Delete Grade Definition
    $(document).on('click', '.delete-grade-definition', function() {
        const id = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this grade definition?')) {
            $.ajax({
                url: `{{ url('grade_definitions') }}/${id}`,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON.message || 'Failed to delete grade definition.', 'danger');
                }
            });
        }
    });

    // Reset form when modal is closed
    $('#addGradeDefinitionModal').on('hidden.bs.modal', function() {
        $('#addGradeDefinitionForm')[0].reset();
        clearFormErrors('add');
    });

    $('#editGradeDefinitionModal').on('hidden.bs.modal', function() {
        $('#editGradeDefinitionForm')[0].reset();
        clearFormErrors('edit');
    });
});
</script>











