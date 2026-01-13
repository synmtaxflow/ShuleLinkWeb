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
    }
    
    .header-section {
        background: linear-gradient(135deg, #940000 0%, #b30000 100%);
        color: white;
        padding: 20px 0;
        margin-bottom: 30px;
    }
    
    .data-widget {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        background: white;
        text-align: center;
    }
    
    .data-widget:hover {
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.2);
        border-color: #940000;
        transform: translateY(-2px);
    }
    
    .data-widget i {
        font-size: 48px;
        color: #940000;
        margin-bottom: 15px;
    }
    
    .data-widget h3 {
        color: #940000;
        font-size: 36px;
        font-weight: bold;
        margin: 10px 0;
    }
    
    .data-widget p {
        color: #6c757d;
        margin: 0;
        font-size: 14px;
    }
    
    .btn-back {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #ffffff;
        border-radius: 0;
        margin-bottom: 20px;
    }
    
    .btn-back:hover {
        background-color: #5a6268;
        border-color: #5a6268;
        color: #ffffff;
    }
    
    .year-info-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 2px solid #940000;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
    }
</style>

<div class="header-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fa fa-calendar-check-o"></i> Academic Year: {{ $academicYear->year_name ?: $academicYear->year }}</h3>
                <p class="mb-0">View historical data for this academic year</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="content-wrapper">
        <!-- Back Button -->
        <a href="{{ route('admin.academicYears') }}" class="btn btn-back">
            <i class="fa fa-arrow-left"></i> Back to Academic Years
        </a>

        <!-- Year Information Card -->
        <div class="year-info-card">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fa fa-info-circle"></i> Year Information</h5>
                    <p><strong>Year Name:</strong> {{ $academicYear->year_name ?: $academicYear->year }}</p>
                    <p><strong>Period:</strong> 
                        {{ \Carbon\Carbon::parse($academicYear->start_date)->format('d M Y') }} - 
                        {{ \Carbon\Carbon::parse($academicYear->end_date)->format('d M Y') }}
                    </p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-{{ strtolower($academicYear->status) }}">
                            {{ $academicYear->status }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    @if($academicYear->closed_at)
                        <p><strong>Closed On:</strong> 
                            {{ \Carbon\Carbon::parse($academicYear->closed_at)->format('d M Y, h:i A') }}
                        </p>
                    @endif
                    @if($academicYear->notes)
                        <p><strong>Notes:</strong> {{ $academicYear->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Data Widgets -->
        <h5 class="mb-4"><i class="fa fa-database"></i> Historical Data Summary</h5>
        <div class="row">
            <!-- Students Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-users"></i>
                    <h3>{{ number_format($yearData['students_count']) }}</h3>
                    <p>Students</p>
                </div>
            </div>

            <!-- Classes Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-columns"></i>
                    <h3>{{ number_format($yearData['classes_count']) }}</h3>
                    <p>Classes</p>
                </div>
            </div>

            <!-- Subclasses Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-th"></i>
                    <h3>{{ number_format($yearData['subclasses_count']) }}</h3>
                    <p>Subclasses</p>
                </div>
            </div>

            <!-- Subjects Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-bookmark"></i>
                    <h3>{{ number_format($yearData['subjects_count']) }}</h3>
                    <p>Subjects</p>
                </div>
            </div>

            <!-- Examinations Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-pencil-square-o"></i>
                    <h3>{{ number_format($yearData['examinations_count']) }}</h3>
                    <p>Examinations</p>
                </div>
            </div>

            <!-- Results Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-trophy"></i>
                    <h3>{{ number_format($yearData['results_count']) }}</h3>
                    <p>Results</p>
                </div>
            </div>

            <!-- Attendance Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-check-square-o"></i>
                    <h3>{{ number_format($yearData['attendances_count']) }}</h3>
                    <p>Attendance Records</p>
                </div>
            </div>

            <!-- Scheme of Work Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-book"></i>
                    <h3>{{ number_format($yearData['scheme_of_works_count']) }}</h3>
                    <p>Scheme of Works</p>
                </div>
            </div>

            <!-- Lesson Plans Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-file-text-o"></i>
                    <h3>{{ number_format($yearData['lesson_plans_count']) }}</h3>
                    <p>Lesson Plans</p>
                </div>
            </div>

            <!-- Payments Widget -->
            <div class="col-md-3 col-sm-6">
                <div class="data-widget">
                    <i class="fa fa-money"></i>
                    <h3>{{ number_format($yearData['payments_count']) }}</h3>
                    <p>Payments</p>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="alert alert-info mt-4">
            <i class="fa fa-info-circle"></i> 
            <strong>Note:</strong> This page displays historical data that was saved when the academic year was closed. 
            All data from this year has been archived and can be viewed here.
        </div>
    </div>
</div>


