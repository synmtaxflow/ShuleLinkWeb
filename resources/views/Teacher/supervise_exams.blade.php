@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom {
        background-color: #f9eeee !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    body, .container-fluid, .card, .session-card, .btn {
        font-family: "Century Gothic", "CenturyGothic", "AppleGothic", sans-serif;
    }
    .session-card {
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: white;
    }
    .session-card.widget-card {
        border-radius: 12px !important;
        border: 1px solid #e7e7e7;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        padding: 18px;
    }
    .session-card:hover {
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    .time-badge {
        background: #f2dede;
        color: #7a1f1f;
        padding: 5px 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .sessions-hero {
        background: linear-gradient(135deg, #fff2f2 0%, #f7dede 100%);
        border: 1px solid #e8c8c8;
        color: #7a1f1f;
    }
    .btn-session-action {
        background: white !important;
        color: #940000 !important;
        border: 1px solid #940000;
        padding: 8px 14px;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        width: 100%;
        margin-bottom: 5px;
    }
    .btn-session-action:hover {
        background: #f8f8f8 !important;
        color: #940000 !important;
        border-color: #940000;
    }
    .session-card.widget-card .btn {
        border-radius: 8px !important;
    }
    .session-actions {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-top: 15px;
    }

    /* Mobile Responsiveness Improvements */
    @media (max-width: 768px) {
        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }

        .container-fluid {
            padding-left: 15px !important;
            padding-right: 15px !important;
            width: 100%;
            overflow-x: hidden;
        }

        .row {
            margin-left: -10px !important;
            margin-right: -10px !important;
        }

        .col-12, .col-md-12, .col-lg-12 {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .card-body {
            padding: 0.75rem;
        }

        .sessions-hero h4 {
            font-size: 1.15rem;
        }

        .sessions-hero small {
            font-size: 0.75rem;
            display: block;
            margin-top: 5px;
        }

        .sessions-hero .bi-person-badge {
            font-size: 2rem !important;
        }

        /* Prevent long text overflow */
        * {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .session-card {
            padding: 12px;
        }

        .btn-session-action {
            padding: 10px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body sessions-hero">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="bi bi-shield-check"></i> Exam Supervision
                            </h4>
                            <small>View and manage your assigned exam and test supervision shifts</small>
                        </div>
                        <i class="bi bi-person-badge opacity-25" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-light">
                    <form action="{{ route('supervise_exams') }}" method="GET" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">Filter by Year</label>
                                <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="">All Years</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">Exam Type</label>
                                <select name="category" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="all" {{ $selectedCategory == 'all' ? 'selected' : '' }}>All Types</option>
                                    <option value="school_exam" {{ $selectedCategory == 'school_exam' ? 'selected' : '' }}>Standard Exam</option>
                                    <option value="test" {{ $selectedCategory == 'test' ? 'selected' : '' }}>Weekly/Monthly Test</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <a href="{{ route('supervise_exams') }}" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($assignments->isEmpty())
                <div class="alert alert-info border-0 shadow-sm">
                    <i class="bi bi-info-circle"></i> No upcoming exam supervision assignments found for the selected filters.
                </div>
            @else
                <h5 class="mb-3 text-primary-custom"><i class="bi bi-calendar-event"></i> Upcoming & Active Sessions</h5>
                <div class="row">
                    @foreach($assignments as $assignment)
                        @include('Teacher.partials.supervision_card', ['assignment' => $assignment, 'isActive' => $assignment->is_active])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@include('includes.footer')
