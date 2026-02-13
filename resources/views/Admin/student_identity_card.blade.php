@php
    $user_type = Session::get('user_type');
@endphp
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
        --id-primary-color: #940000;
        --id-secondary-color: #ffffff;
        --id-accent-color: #2f2f2f;
    }

    .id-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    @media (max-width: 1200px) {
        .id-cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .id-cards-grid {
            grid-template-columns: 1fr;
        }
    }

    .id-card-container {
        perspective: 1000px;
        width: 100%;
        height: 220px;
    }

    .id-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.8s;
        transform-style: preserve-3d;
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        border-radius: 15px;
    }

    .id-card-container.flipped .id-card-inner {
        transform: rotateY(180deg);
    }

    .id-card-front, .id-card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 15px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,0.1);
        background-image: radial-gradient(circle at 20% 20%, rgba(var(--id-primary-color-rgb), 0.05) 0%, transparent 40%),
                          radial-gradient(circle at 80% 80%, rgba(var(--id-primary-color-rgb), 0.05) 0%, transparent 40%);
    }

    .id-card-back {
        transform: rotateY(180deg);
    }

    /* Flip Button */
    .flip-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 100;
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 50%;
        width: 34px;
        height: 34px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        color: var(--id-primary-color);
    }
    .flip-btn:hover {
        background: var(--id-primary-color);
        color: #fff;
        transform: rotate(180deg) scale(1.1);
    }

    /* Front Design */
    .id-header {
        background-color: var(--id-primary-color);
        color: var(--id-secondary-color);
        padding: 5px 15px;
        height: 60px;
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
    }
    .id-header::after {
        content: '';
        position: absolute;
        bottom: -15px;
        right: -15px;
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .id-header h6 {
        margin: 0;
        font-weight: 800;
        font-size: 0.9rem;
        text-align: left;
        line-height: 1.2;
        letter-spacing: 0.5px;
        z-index: 1;
    }
    .id-header img {
        height: 42px;
        width: 42px;
        border-radius: 50%;
        background: #fff;
        padding: 2px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 1;
    }

    .id-body {
        flex: 1;
        display: flex;
        padding: 12px;
        gap: 18px;
        position: relative;
    }
    .id-photo-container {
        position: relative;
        z-index: 1;
    }
    .id-photo {
        width: 95px;
        height: 115px;
        border: 3px solid #fff;
        border-radius: 10px;
        object-fit: cover;
        background: #f8f9fa;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .id-details {
        flex: 1;
        text-align: left;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .id-name {
        font-weight: 900;
        font-size: 1.1rem;
        color: var(--id-accent-color);
        margin-bottom: 8px;
        line-height: 1.1;
        font-family: 'Century Gothic', sans-serif;
    }
    .id-info-row {
        font-size: 0.7rem;
        margin-bottom: 4px;
        color: #444;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .id-info-row i {
        color: var(--id-primary-color);
        width: 12px;
        text-align: center;
    }
    .id-info-row strong {
        color: var(--id-primary-color);
        min-width: 45px;
        display: inline-block;
    }

    .id-footer {
        background: linear-gradient(90deg, var(--id-primary-color) 0%, #000 100%);
        height: 10px;
    }

    /* Back Design */
    .id-back-content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        height: 100%;
        text-align: left;
        font-size: 0.72rem;
        position: relative;
    }
    .id-back-title {
        color: var(--id-primary-color);
        font-weight: 800;
        border-bottom: 2px solid var(--id-primary-color);
        padding-bottom: 5px;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .id-back-content p {
        margin-bottom: 6px;
        line-height: 1.4;
    }
    .id-qr {
        position: absolute;
        bottom: 15px;
        right: 15px;
        width: 55px;
        height: 55px;
        background: #fff;
        padding: 3px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Controls Area */
    .controls-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid rgba(148,0,0,0.1);
    }
    .color-picker-group {
        display: flex;
        align-items: center;
        gap: 20px;
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
    }
    .color-input-wrapper {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    input[type="color"] {
        border: none;
        width: 30px;
        height: 30px;
        cursor: pointer;
        background: none;
    }

    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }
    .breadcrumb-custom i { color: var(--id-primary-color); }
</style>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                
                <div class="breadcrumb-custom">
                    <i class="fa fa-id-card"></i> 
                    <span>Student Management</span> / 
                    <strong>Identity Cards</strong>
                </div>

                <div class="controls-card">
                    <form action="{{ route('admin.student_id_cards', $selectedClassID) }}" method="GET" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small font-weight-bold">Custom Primary Color</label>
                                <div class="color-picker-group">
                                    <div class="color-input-wrapper">
                                        <input type="color" id="primaryColorPicker" value="#940000">
                                        <span class="small">Primary</span>
                                    </div>
                                    <div class="color-input-wrapper">
                                        <input type="color" id="secondaryColorPicker" value="#ffffff">
                                        <span class="small">Text/BG</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="small font-weight-bold">Filter by Subclass</label>
                                <select name="subclassID" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="">All Subclasses</option>
                                    @foreach($subclasses as $sub)
                                        <option value="{{ $sub->subclassID }}" {{ $selectedSubclassID == $sub->subclassID ? 'selected' : '' }}>
                                            {{ $sub->subclass_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                    <i class="fa fa-print"></i> Print ID Cards
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" id="flipAllBtn">
                                    <i class="fa fa-refresh"></i> Flip All
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                @if($students->isEmpty())
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No active students found for the selected criteria.
                    </div>
                @else
                    <div class="id-cards-grid">
                        @foreach($students as $student)
                            <div class="id-card-container">
                                <button class="flip-btn"><i class="fa fa-rotate-right"></i></button>
                                <div class="id-card-inner">
                                    <!-- Front Side -->
                                    <div class="id-card-front">
                                        <div class="id-header">
                                            @php
                                                // Dynamic school logo
                                                $schoolLogo = asset('images/shuleXpert.jpg'); // Default
                                                if ($student->school) {
                                                    if (!empty($student->school->logo)) {
                                                        $schoolLogo = asset('schoolLogos/' . $student->school->logo);
                                                    } elseif (!empty($student->school->school_logo)) {
                                                        $schoolLogo = asset($student->school->school_logo);
                                                    }
                                                }
                                            @endphp
                                            <img src="{{ $schoolLogo }}" alt="Logo">
                                            <h6>{{ $student->school->school_name ?? 'ShuleLink Academy' }}<br><small>Student Identity Card</small></h6>
                                        </div>
                                        <div class="id-body">
                                            @php
                                                // Gender-based placeholder
                                                $defaultPhoto = strtolower($student->gender ?? 'male') === 'female' 
                                                    ? asset('images/female.png') 
                                                    : asset('images/male.png');
                                                $photo = $student->photo ? asset('userImages/' . $student->photo) : $defaultPhoto;
                                            @endphp
                                            <img src="{{ $photo }}" class="id-photo" alt="Student">
                                            <div class="id-details">
                                                <div class="id-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                <div class="id-info-row"><strong>ID:</strong> {{ $student->admission_number ?? $student->studentID }}</div>
                                                <div class="id-info-row"><strong>Class:</strong> {{ $student->subclass->class->class_name ?? 'N/A' }} {{ $student->subclass->subclass_name ?? '' }}</div>
                                                <div class="id-info-row"><strong>Gender:</strong> {{ ucfirst($student->gender) }}</div>
                                                <div class="id-info-row"><strong>DOB:</strong> {{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div class="id-footer"></div>
                                    </div>

                                    <!-- Back Side -->
                                    <div class="id-card-back">
                                        <div class="id-back-content">
                                            <div class="id-back-title">IMPORTANT INFORMATION</div>
                                            <p class="mb-1"><strong>Parent:</strong> {{ $student->parent->first_name ?? '' }} {{ $student->parent->last_name ?? '' }}</p>
                                            <p class="mb-1"><strong>Contact:</strong> {{ $student->parent->phone ?? 'N/A' }}</p>
                                            <p class="mb-1"><strong>Address:</strong> {{ $student->address ?? 'N/A' }}</p>
                                            <p class="mt-2 small text-muted">This card is the property of {{ $student->school->school_name ?? 'ShuleLink' }}. If found, please return it to the nearest school office or call {{ $student->school->phone ?? 'the office' }}.</p>
                                            
                                            <div class="id-qr">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $student->studentID }}" alt="QR" style="width: 100%; height: 100%;">
                                            </div>
                                        </div>
                                        <div class="id-footer"></div>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Individual Flip
        $('.flip-btn').on('click', function() {
            $(this).parent('.id-card-container').toggleClass('flipped');
        });

        // Flip All
        $('#flipAllBtn').on('click', function() {
            $('.id-card-container').toggleClass('flipped');
            const icon = $(this).find('i');
            if ($('.id-card-container').first().hasClass('flipped')) {
                $(this).html('<i class="fa fa-eye"></i> Show Front');
            } else {
                $(this).html('<i class="fa fa-refresh"></i> Flip All');
            }
        });

        // Color Customization
        $('#primaryColorPicker').on('input', function() {
            document.documentElement.style.setProperty('--id-primary-color', $(this).val());
        });

        $('#secondaryColorPicker').on('input', function() {
            document.documentElement.style.setProperty('--id-secondary-color', $(this).val());
        });
    });
</script>

@if(!isset($is_dashboard))
    {{-- Footer and scripts --}}
@endif
