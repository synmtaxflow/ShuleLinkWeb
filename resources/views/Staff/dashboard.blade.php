@include('includes.staff_nav')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: white;
    }
    .btn-outline-primary-custom {
        border-color: #940000;
        color: #940000;
    }
    .btn-outline-primary-custom:hover {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }
    .dashboard-hero {
        background: #fff7f7;
        border: 1px solid #f0dada;
        padding: 18px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .dashboard-hero .hero-title {
        font-size: 1.4rem;
        margin-bottom: 6px;
    }
    .dashboard-hero .hero-subtitle {
        color: #6c757d;
        margin-bottom: 0;
    }
    .dashboard-card {
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        height: 100%;
    }
    .dashboard-card .card-title {
        font-weight: 600;
        color: #940000;
        margin-bottom: 8px;
    }
    .dashboard-card .stat {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2f2f2f;
        margin-bottom: 6px;
    }
    .dashboard-card .stat-note {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .dashboard-card .card-icon {
        font-size: 2rem;
        color: rgba(148, 0, 0, 0.35);
    }
    .dashboard-card .card-actions {
        margin-top: 14px;
    }
    .section-card {
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .section-card .section-header {
        background: #fff7f7;
        border-bottom: 1px solid #f0dada;
        color: #940000;
        font-weight: 600;
        padding: 12px 16px;
    }
    .section-card .section-header small {
        color: #6c757d;
        font-weight: 400;
    }
</style>

<div class="container-fluid mt-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="dashboard-hero">
        <div>
            <div class="text-muted" style="font-size: 0.9rem;">Today · {{ \Carbon\Carbon::now()->format('l, d M Y') }}</div>
            <div class="hero-title">Hello, {{ $staff->first_name ?? 'Staff' }}</div>
            <p class="hero-subtitle">Quick access to your staff tools and requests.</p>
        </div>
        <div class="hero-actions">
            <a href="{{ route('staff.permissions') }}" class="btn btn-primary-custom btn-sm">
                <i class="fa fa-check-square-o"></i> Permission
            </a>
            <a href="{{ route('staff.leave') }}" class="btn btn-outline-primary-custom btn-sm">
                <i class="fa fa-calendar"></i> Leave
            </a>
        </div>
    </div>

    <div class="row">
        @php
            $quickCards = [
                ['title' => 'Suggestions', 'note' => 'Share new ideas', 'icon' => 'fa-lightbulb-o', 'link' => route('staff.suggestions'), 'link_label' => 'Open Suggestions'],
                ['title' => 'Incidents', 'note' => 'Report incidents', 'icon' => 'fa-exclamation-triangle', 'link' => route('staff.incidents'), 'link_label' => 'Report Incident'],
                ['title' => 'Permission', 'note' => 'Request permission', 'icon' => 'fa-check-square-o', 'link' => route('staff.permissions'), 'link_label' => 'Request Permission'],
                ['title' => 'Leave', 'note' => 'Apply for leave', 'icon' => 'fa-calendar', 'link' => route('staff.leave'), 'link_label' => 'Apply Leave'],
                ['title' => 'Payroll', 'note' => 'View payroll info', 'icon' => 'fa-money', 'link' => route('staff.payroll'), 'link_label' => 'View Payroll'],
            ];
        @endphp

        @foreach($quickCards as $card)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="card-title">{{ $card['title'] }}</div>
                                <div class="stat">{{ $card['note'] }}</div>
                            </div>
                            <div class="card-icon">
                                <i class="fa {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="{{ $card['link'] }}" class="btn btn-sm btn-outline-primary-custom w-100">
                                <i class="fa fa-arrow-right"></i> {{ $card['link_label'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card section-card mb-4">
        <div class="section-header">
            Management Access <small>(based on your assigned profession)</small>
        </div>
        <div class="card-body">
            @if(isset($staffPermissionsByCategory) && $staffPermissionsByCategory->count() > 0)
                <div class="row">
                    @foreach($staffPermissionsByCategory as $category => $permissions)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-primary-custom font-weight-bold mb-2 text-capitalize">
                                    <i class="fa fa-check-circle"></i> {{ $category ?: 'General' }}
                                </div>
                                <ul class="mb-0" style="padding-left: 18px;">
                                    @foreach($permissions as $permission)
                                        <li>{{ $permission->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info mb-0">
                    No management permissions assigned yet. Contact admin to set your role permissions.
                </div>
            @endif
        </div>
    </div>
</div>

@include('includes.footer')
