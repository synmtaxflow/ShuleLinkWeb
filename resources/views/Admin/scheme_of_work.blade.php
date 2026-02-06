@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

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
    
    .progress-bar-custom {
        background-color: #940000;
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
    
    .header-section {
        background: linear-gradient(135deg, #940000 0%, #b30000 100%);
        color: white;
        padding: 20px 0;
        margin-bottom: 30px;
    }
    
    .no-border-radius {
        border-radius: 0 !important;
    }
</style>

<div class="header-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fa fa-book"></i> Scheme of Work Progress</h3>
                <p class="mb-0">View progress of all schemes of work for teachers</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="content-wrapper">
        @if($classSubjectsWithSchemes->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover no-border-radius">
                    <thead style="background-color: #940000; color: white;">
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>Year</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classSubjectsWithSchemes as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $item['subject_name'] }}</strong></td>
                                <td>{{ $item['class_name'] }}</td>
                                <td>{{ $item['teacher_name'] }}</td>
                                <td>{{ $item['year'] }}</td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar progress-bar-custom" role="progressbar" 
                                             style="width: {{ $item['progress'] }}%;" 
                                             aria-valuenow="{{ $item['progress'] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $item['progress'] }}% ({{ $item['doneItems'] }}/{{ $item['totalItems'] }})
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($item['scheme'])
                                        <span class="badge badge-{{ $item['scheme']->status === 'Active' ? 'success' : ($item['scheme']->status === 'Draft' ? 'warning' : 'secondary') }}">
                                            {{ $item['scheme']->status }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">No Scheme</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item['scheme'])
                                        <a href="{{ route('admin.viewSchemeOfWork', $item['scheme']->scheme_of_workID) }}" 
                                           target="_blank" 
                                           class="btn btn-primary-custom btn-sm no-border-radius">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted">No scheme available</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info no-border-radius">
                <i class="fa fa-info-circle"></i> No schemes of work found for this school.
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>

