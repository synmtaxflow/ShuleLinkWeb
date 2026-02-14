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

    /* Import Century Gothic font */
    body {
        font-family: 'Century Gothic', 'Arial', sans-serif;
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

    /* CR80 Standard Card Container - 85.6mm × 54mm */
    .id-card-container {
        perspective: 1000px;
        width: 100%;
        /* Aspect ratio for CR80: 85.6:54 = 1.585 */
        aspect-ratio: 1.585;
        max-width: 340px;
        margin: 0 auto;
    }

    .id-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.8s;
        transform-style: preserve-3d;
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        border-radius: 8px;
    }

    .id-card-container.flipped .id-card-inner {
        transform: rotateY(180deg);
    }

    /* Card Faces - CR80 Landscape */
    .id-card-front, .id-card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,0.1);
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

    /* FRONT DESIGN - Professional Layout */
    .id-header {
        background-color: var(--id-primary-color);
        color: var(--id-secondary-color);
        padding: 6px 12px;
        min-height: 48px;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
    }
    .id-header img {
        height: 36px;
        width: 36px;
        border-radius: 50%;
        background: #fff;
        padding: 2px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        object-fit: contain;
    }
    .id-header h6 {
        margin: 0;
        font-family: 'Century Gothic', sans-serif;
        font-weight: 700;
        font-size: 0.75rem;
        text-align: left;
        line-height: 1.2;
        letter-spacing: 0.3px;
        flex: 1;
    }
    .id-header small {
        font-size: 0.65rem;
        font-weight: 400;
        opacity: 0.95;
    }

    /* Body - Photo + Details */
    .id-body {
        flex: 1;
        display: flex;
        padding: 10px 12px;
        gap: 12px;
        position: relative;
        background: #fff;
    }

    /* Photo - CR80 Standard: 25mm × 30mm */
    .id-photo-container {
        position: relative;
        flex-shrink: 0;
    }
    .id-photo {
        width: 94px;   /* ~25mm at 96 DPI */
        height: 113px; /* ~30mm at 96 DPI */
        border: 2px solid #e0e0e0;
        border-radius: 4px;
        object-fit: cover;
        background: #f8f9fa;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Student Details */
    .id-details {
        flex: 1;
        text-align: left;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-right: 5px;
    }
    .id-name {
        font-family: 'Century Gothic', sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--id-accent-color);
        margin-bottom: 6px;
        line-height: 1.1;
        text-transform: uppercase;
    }
    .id-info-row {
        font-family: 'Century Gothic', sans-serif;
        font-size: 0.65rem;
        margin-bottom: 3px;
        color: #444;
        display: flex;
        align-items: flex-start;
        line-height: 1.3;
    }
    .id-info-row strong {
        color: var(--id-primary-color);
        font-weight: 600;
        min-width: 42px;
        display: inline-block;
    }
    .id-info-row span {
        flex: 1;
    }

    /* Footer Stripe */
    .id-footer {
        background: linear-gradient(90deg, var(--id-primary-color) 0%, #000 100%);
        height: 8px;
    }

    /* BACK DESIGN - Professional */
    .id-back-content {
        padding: 15px 12px;
        display: flex;
        flex-direction: column;
        height: 100%;
        text-align: left;
        font-size: 0.65rem;
        position: relative;
        background: #f8f9fa;
    }
    .id-back-title {
        font-family: 'Century Gothic', sans-serif;
        color: var(--id-primary-color);
        font-weight: 700;
        font-size: 0.75rem;
        border-bottom: 2px solid var(--id-primary-color);
        padding-bottom: 4px;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .id-back-content p {
        font-family: 'Century Gothic', sans-serif;
        margin-bottom: 5px;
        line-height: 1.4;
        font-size: 0.65rem;
    }
    .id-back-content strong {
        font-weight: 600;
        color: var(--id-accent-color);
    }

    /* QR Code - 18mm × 18mm standard */
    .id-qr {
        position: absolute;
        bottom: 12px;
        right: 12px;
        width: 68px;  /* ~18mm at 96 DPI */
        height: 68px;
        background: #fff;
        padding: 3px;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
    }
    .id-qr img {
        width: 100%;
        height: 100%;
        display: block;
    }

    /* Download button inside card at bottom */
    .download-card-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        z-index: 100;
        background: rgba(0, 0, 0, 0.85);
        border: 1px solid rgba(0,0,0,0.2);
        border-radius: 50%;
        width: 34px;
        height: 34px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        color: #fff;
    }
    .download-card-btn:hover {
        background: #000;
        transform: scale(1.15);
        box-shadow: 0 6px 10px rgba(0,0,0,0.3);
    }



    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }
    .breadcrumb-custom i { color: var(--id-primary-color); }

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
</style>

<!-- Removed jsPDF and html2canvas scripts as we now use server-side generation -->

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
                                <a href="#" id="downloadAllBtn" class="btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-download"></i> Download All Cards (PDF)
                                </a>
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
                            <div class="id-card-container" data-student-id="{{ $student->studentID }}" data-student-name="{{ $student->first_name }} {{ $student->last_name }}">
                                <a href="#" data-id="{{ $student->studentID }}" class="download-card-btn" title="Download PDF" target="_blank"><i class="fa fa-download"></i></a>
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
        // Base URL for download
        const baseUrl = "{{ route('admin.download_student_id_card', ['id' => ':id']) }}";
        const classID = "{{ $selectedClassID }}";
        const subclassID = "{{ $selectedSubclassID }}";
        
        // AJAX Download Handler
        async function handleDownload(e) {
            e.preventDefault();
            e.stopPropagation(); // Stop card flip if bubbling
            
            const btn = $(this);
            const originalContent = btn.html();
            const url = btn.attr('href');
            
            // Show loading state
            btn.html('<i class="fa fa-spinner fa-spin" style="color: #ffffff !important;"></i>').addClass('disabled');
            
            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Download failed');
                
                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                
                // Try to get filename from headers or generate one
                let filename = 'ID_Card.pdf';
                const disposition = response.headers.get('Content-Disposition');
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) { 
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }
                
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(downloadUrl);
                document.body.removeChild(a);
                
            } catch (error) {
                console.error('Download error:', error);
                alert('Failed to download PDF. Please try again.');
            } finally {
                // Restore button state
                btn.html(originalContent).removeClass('disabled');
            }
        }
        
        // Update links with colors (keep this to set base href)
        function updateDownloadLinks() {
            const primary = $('#primaryColorPicker').val().replace('#', '');
            const secondary = $('#secondaryColorPicker').val().replace('#', '');
            
            // Update "Download All" link
            let allUrl = baseUrl.replace(':id', 'all') + `?classID=${classID}&subclassID=${subclassID}&primaryColor=%23${primary}&secondaryColor=%23${secondary}`;
            $('#downloadAllBtn').attr('href', allUrl).off('click').on('click', handleDownload);
            
            // Update individual download links
            $('.download-card-btn').each(function() {
                const id = $(this).data('id');
                const url = baseUrl.replace(':id', id) + `?primaryColor=%23${primary}&secondaryColor=%23${secondary}`;
                $(this).attr('href', url).off('click').on('click', handleDownload);
            });
        }
        
        // Initial setup
        updateDownloadLinks();

        // Individual Flip
        $('.flip-btn').on('click', function(e) {
            e.stopPropagation();
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
            updateDownloadLinks();
        });

        $('#secondaryColorPicker').on('input', function() {
            document.documentElement.style.setProperty('--id-secondary-color', $(this).val());
            updateDownloadLinks();
        });
    });
</script>

@if(!isset($is_dashboard))
    {{-- Footer and scripts --}}
@endif
