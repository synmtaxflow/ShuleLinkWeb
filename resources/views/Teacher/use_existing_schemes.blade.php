<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Use Existing Scheme of Work</title>
    
    <link rel="stylesheet" href="{{ asset('vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
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
        
        .header-section {
            background: linear-gradient(135deg, #940000 0%, #b30000 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .content-wrapper {
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        .scheme-card {
            border: 1px solid #e0e0e0;
            border-radius: 0;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .scheme-card:hover {
            box-shadow: 0 4px 12px rgba(148, 0, 0, 0.2);
            border-color: #940000;
        }
        
        .scheme-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #940000;
            font-weight: bold;
        }
        
        .no-border-radius {
            border-radius: 0 !important;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3><i class="fa fa-book"></i> Use Existing Scheme of Work</h3>
                    <p class="mb-0">Select a scheme to use for: <strong>{{ $classSubject->subject->subject_name ?? 'N/A' }}</strong></p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('teacher.schemeOfWork') }}" class="btn btn-light no-border-radius">
                        <i class="fa fa-arrow-left"></i> Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="content-wrapper">
            @if($existingSchemes->count() > 0)
                <div class="row">
                    @foreach($existingSchemes as $scheme)
                        <div class="col-md-6">
                            <div class="card scheme-card no-border-radius">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fa fa-calendar"></i> Year: {{ $scheme->year }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Subject:</strong> {{ $scheme->classSubject->subject->subject_name ?? 'N/A' }}</p>
                                    <p><strong>Class:</strong> 
                                        @if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class)
                                            {{ $scheme->classSubject->subclass->class->class_name }} {{ $scheme->classSubject->subclass->subclass_name }}
                                        @elseif($scheme->classSubject->class)
                                            {{ $scheme->classSubject->class->class_name }}
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                    <p><strong>Created By:</strong> {{ $scheme->createdBy->first_name ?? '' }} {{ $scheme->createdBy->last_name ?? '' }}</p>
                                    <p><strong>Status:</strong> <span class="badge badge-{{ $scheme->status === 'Active' ? 'success' : ($scheme->status === 'Draft' ? 'warning' : 'secondary') }}">{{ $scheme->status }}</span></p>
                                    <p><strong>Items:</strong> {{ $scheme->items->count() }} rows</p>
                                    <p><strong>Objectives:</strong> {{ $scheme->learningObjectives->count() }} objectives</p>
                                    
                                    <div class="mt-3">
                                        <a href="{{ route('teacher.viewSchemeOfWork', $scheme->scheme_of_workID) }}" target="_blank" class="btn btn-info btn-sm no-border-radius">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        <button type="button" class="btn btn-primary-custom btn-sm no-border-radius" onclick="useThisScheme({{ $scheme->scheme_of_workID }})">
                                            <i class="fa fa-check"></i> Use This Scheme
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info no-border-radius">
                    <i class="fa fa-info-circle"></i> No existing schemes found for this subject.
                </div>
            @endif
        </div>
    </div>

    <script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function useThisScheme(schemeID) {
            Swal.fire({
                title: 'Use This Scheme?',
                text: 'This will copy the scheme to your current subject. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Use It',
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

                    $.ajax({
                        url: '{{ route("teacher.useThisScheme", ":id") }}'.replace(':id', schemeID),
                        method: 'POST',
                        data: {
                            class_subjectID: {{ $classSubject->class_subjectID }},
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#940000',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    if (response.redirect) {
                                        window.location.href = response.redirect;
                                    } else {
                                        window.location.href = '{{ route("teacher.schemeOfWork") }}';
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message,
                                    confirmButtonColor: '#940000',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    if (response.manageUrl) {
                                        window.location.href = response.manageUrl;
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON?.message || 'Failed to use scheme';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMsg,
                                confirmButtonColor: '#940000',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>

