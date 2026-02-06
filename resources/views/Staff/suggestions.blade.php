@include('includes.staff_nav')
<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .text-primary-custom { color: #940000 !important; }
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
    .page-card {
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .page-header {
        background: #fff7f7;
        border-bottom: 1px solid #f0dada;
        color: #940000;
        font-weight: 600;
        padding: 12px 16px;
    }
</style>

<div class="container-fluid mt-3">
    <div class="card page-card">
        <div class="page-header">Suggestions</div>
        <div class="card-body">
            <div class="alert alert-info">
                This section will be used to submit and track staff suggestions. Implementation is in progress.
            </div>
            <a href="{{ route('staffDashboard') }}" class="btn btn-primary-custom btn-sm">
                <i class="fa fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

@include('includes.footer')
