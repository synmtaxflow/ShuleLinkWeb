@include('includes.teacher_nav')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

<style>
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
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .class-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(148, 0, 0, 0.2) !important;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary-custom text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-mortarboard-fill"></i> 
                            @if(isset($isCoordinatorView) && $isCoordinatorView)
                                Main Classes (Coordinator View)
                            @else
                                My Assigned Classes
                            @endif
                        </h5>
                    </div>
                </div>
            </div>

            @if(isset($isCoordinatorView) && $isCoordinatorView)
                <!-- Main Classes Grid (Coordinator View) -->
                @if(isset($mainClasses) && count($mainClasses) > 0)
                    <div class="row g-4">
                        @foreach($mainClasses as $mainClass)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card shadow-sm h-100 class-card" style="transition: transform 0.2s;">
                                    <!-- Card Header -->
                                    <div class="card-header text-white text-center py-3" style="background: linear-gradient(135deg, #20c997 0%, #17a2b8 50%, #138496 100%);">
                                        <div class="d-flex justify-content-center align-items-center mb-2">
                                            <i class="bi bi-mortarboard-fill" style="font-size: 2.5rem;"></i>
                                        </div>
                                        <h5 class="mb-0 fw-bold text-white">
                                            {{ $mainClass['class_name'] ?? 'N/A' }}
                                        </h5>
                                    </div>

                                    <!-- Card Body -->
                                    <div class="card-body p-3">
                                        <!-- Quick Stats -->
                                        <div class="row g-2 mb-3">
                                            <!-- Students Count -->
                                            <div class="col-6">
                                                <div class="text-center p-2 rounded border" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-color: #28a745 !important;">
                                                    <i class="bi bi-people-fill text-success d-block mb-1" style="font-size: 1.5rem;"></i>
                                                    <div class="fw-bold text-dark">{{ $mainClass['student_count'] }}</div>
                                                    <small class="text-muted">Students</small>
                                                </div>
                                            </div>
                                            <!-- Subclasses Count -->
                                            <div class="col-6">
                                                <div class="text-center p-2 rounded border" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); border-color: #20c997 !important;">
                                                    <i class="bi bi-layers-fill text-info d-block mb-1" style="font-size: 1.5rem;"></i>
                                                    <div class="fw-bold text-dark">{{ $mainClass['subclasses_count'] }}</div>
                                                    <small class="text-muted">Subclasses</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hidden Details Section (View More) -->
                                        <div class="mainclass-details-{{ $mainClass['classID'] }}" style="display: none;">
                                            <!-- Subclasses List -->
                                            @if(isset($mainClass['subclasses']) && count($mainClass['subclasses']) > 0)
                                                <div class="mb-3">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-list-ul text-primary-custom me-2"></i>
                                                        <strong class="small">Subclasses:</strong>
                                                    </div>
                                                    <div class="list-group">
                                                        @foreach($mainClass['subclasses'] as $subclass)
                                                            <div class="list-group-item">
                                                                <div class="d-flex align-items-start">
                                                                    <i class="bi bi-bookmark-fill text-primary-custom me-2 mt-1"></i>
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold mb-1">{{ $subclass['subclass_name'] ?? 'N/A' }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center py-3">
                                                    <i class="bi bi-inbox text-muted" style="font-size: 1.5rem;"></i>
                                                    <p class="mt-2 mb-0 small text-muted">No subclasses</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Card Footer -->
                                    <div class="card-footer bg-light p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- View More/Less Button -->
                                            <button class="btn btn-sm btn-outline-primary view-more-btn" data-class-id="{{ $mainClass['classID'] }}" data-expanded="false">
                                                <i class="bi bi-chevron-down"></i> View More
                                            </button>

                                            <!-- Manage Button -->
                                            <a href="{{ route('ClassMangement', ['classID' => Crypt::encrypt($mainClass['classID']), 'coordinator' => 'true']) }}" class="btn btn-sm btn-primary-custom">
                                                <i class="bi bi-gear-fill"></i> Manage
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>
                            <p class="mt-3 mb-0 text-muted">No main classes assigned to you as coordinator.</p>
                        </div>
                    </div>
                @endif
            @else
                <!-- Subclasses Grid (Class Teacher View) -->
                @if(isset($subclasses) && count($subclasses) > 0)
                <div class="row g-4">
                    @foreach($subclasses as $subclass)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card shadow-sm h-100 class-card" style="transition: transform 0.2s;">
                                <!-- Card Header -->
                                <div class="card-header text-white text-center py-3" style="background: linear-gradient(135deg, #940000 0%, #b30000 50%, #d40000 100%);">
                                    <div class="d-flex justify-content-center align-items-center mb-2">
                                        <i class="bi bi-mortarboard-fill" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold text-white">
                                        {{ $subclass['class_name'] ?? 'N/A' }} - {{ $subclass['subclass_name'] }}
                                    </h5>
                                </div>

                                <!-- Card Body -->
                                <div class="card-body p-3">
                                    <!-- Quick Stats -->
                                    <div class="row g-2 mb-3">
                                        <!-- Students Count -->
                                        <div class="col-6">
                                            <div class="text-center p-2 rounded border" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-color: #28a745 !important;">
                                                <i class="bi bi-people-fill text-success d-block mb-1" style="font-size: 1.5rem;"></i>
                                                <div class="fw-bold text-dark">{{ $subclass['student_count'] }}</div>
                                                <small class="text-muted">Students</small>
                                            </div>
                                        </div>
                                        <!-- Subjects Count -->
                                        <div class="col-6">
                                            <div class="text-center p-2 rounded border" style="background: linear-gradient(135deg, #fff3f3 0%, #ffe6e6 100%); border-color: #940000 !important;">
                                                <i class="bi bi-book-fill text-primary-custom d-block mb-1" style="font-size: 1.5rem;"></i>
                                                <div class="fw-bold text-dark">{{ $subclass['subject_count'] }}</div>
                                                <small class="text-muted">Subjects</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden Details Section (View More) -->
                                    <div class="subclass-details-{{ $subclass['subclassID'] }}" style="display: none;">
                                        <!-- Subjects List -->
                                        @if(isset($subclass['subjects']) && count($subclass['subjects']) > 0)
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-list-ul text-primary-custom me-2"></i>
                                                    <strong class="small">Subject List:</strong>
                                                </div>
                                                <div class="list-group">
                                                    @foreach($subclass['subjects'] as $subject)
                                                        <div class="list-group-item">
                                                            <div class="d-flex align-items-start">
                                                                <i class="bi bi-book-fill text-primary-custom me-2 mt-1"></i>
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold mb-1">{{ $subject['subject_name'] }}</div>
                                                                    @if($subject['subject_code'])
                                                                        <div class="mb-1">
                                                                            <small class="text-muted d-block">
                                                                                <i class="bi bi-code-square text-primary-custom me-1"></i>Code: {{ $subject['subject_code'] }}
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                    @if($subject['teacher_name'] && $subject['teacher_name'] !== 'Not Assigned')
                                                                        <div>
                                                                            <small class="text-muted d-block">
                                                                                <i class="bi bi-person-badge text-info me-1"></i>Teacher: {{ $subject['teacher_name'] }}
                                                                            </small>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center py-3">
                                                <i class="bi bi-inbox text-muted" style="font-size: 1.5rem;"></i>
                                                <p class="mt-2 mb-0 small text-muted">No subjects assigned</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Card Footer -->
                                <div class="card-footer bg-light p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- View More/Less Button -->
                                        <button class="btn btn-sm btn-outline-primary view-more-btn" data-subclass-id="{{ $subclass['subclassID'] }}" data-expanded="false">
                                            <i class="bi bi-chevron-down"></i> View More
                                        </button>

                                        <!-- Manage Button -->
                                        <a href="{{ route('ClassMangement', ['subclassID' => Crypt::encrypt($subclass['subclassID'])]) }}" class="btn btn-sm btn-primary-custom">
                                            <i class="bi bi-gear-fill"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>
                            <p class="mt-3 mb-0 text-muted">No classes assigned to you yet.</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@include('includes.footer')

<script>
(function($) {
    'use strict';

    if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }

    $(document).ready(function() {
        // Handle View More/Less Button Click
        $(document).on('click', '.view-more-btn', function(e) {
            e.preventDefault();
            var subclassID = $(this).data('subclass-id');
            var classID = $(this).data('class-id');
            var isExpanded = $(this).data('expanded');
            var $btn = $(this);

            if (subclassID) {
                // Class teacher view - show subclass details
                var $details = $('.subclass-details-' + subclassID);
                if (isExpanded) {
                    // Collapse
                    $details.slideUp(300);
                    $btn.html('<i class="bi bi-chevron-down"></i> View More');
                    $btn.data('expanded', false);
                } else {
                    // Expand
                    $details.slideDown(300);
                    $btn.html('<i class="bi bi-chevron-up"></i> View Less');
                    $btn.data('expanded', true);
                }
            } else if (classID) {
                // Coordinator view - show main class details
                var $details = $('.mainclass-details-' + classID);
                if (isExpanded) {
                    // Collapse
                    $details.slideUp(300);
                    $btn.html('<i class="bi bi-chevron-down"></i> View More');
                    $btn.data('expanded', false);
                } else {
                    // Expand
                    $details.slideDown(300);
                    $btn.html('<i class="bi bi-chevron-up"></i> View Less');
                    $btn.data('expanded', true);
                }
            }
        });
    });
})(jQuery);
</script>
