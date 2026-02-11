@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Budget Details</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="{{ route('accountant.budget.index') }}">Budget</a></li>
                    <li class="active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">{{ $budget->budget_category }} - {{ $budget->fiscal_year }}</strong>
                    <span class="badge badge-{{ $budget->status == 'Active' ? 'success' : 'secondary' }} float-right">{{ $budget->status }}</span>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <h4 class="text-primary">{{ number_format($budget->allocated_amount, 2) }}</h4>
                            <span class="text-muted">Total Allocated</span>
                        </div>
                        <div class="col-md-4">
                            <h4 class="text-danger">{{ number_format($budget->spent_amount, 2) }}</h4>
                            <span class="text-muted">Actual Spent</span>
                        </div>
                        <div class="col-md-4">
                            <h4 class="text-success">{{ number_format($budget->remaining_amount, 2) }}</h4>
                            <span class="text-muted">Remaining Balance</span>
                        </div>
                    </div>

                    @php
                        $utilization = $budget->allocated_amount > 0 ? ($budget->spent_amount / $budget->allocated_amount) * 100 : 0;
                    @endphp
                    <div class="mb-4">
                        <label class="form-control-label">Budget Utilization ({{ number_format($utilization, 1) }}%)</label>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-{{ $utilization > 90 ? 'danger' : ($utilization > 70 ? 'warning' : 'success') }}" 
                                 role="progressbar" 
                                 style="width: {{ $utilization }}%">
                                {{ number_format($utilization, 1) }}%
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 border-right">
                            <h5>Budget Info</h5>
                            <hr>
                            <p><strong>Fiscal Year:</strong> {{ $budget->fiscal_year }}</p>
                            <p><strong>Period:</strong> {{ $budget->period }}</p>
                            <p><strong>Created By:</strong> {{ $budget->createdBy->name ?? 'N/A' }}</p>
                            <p><strong>Created At:</strong> {{ $budget->created_at->format('M d, Y') }}</p>
                            <p><strong>Notes:</strong> {{ $budget->notes ?: 'None' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Recent Approved Expenses</h5>
                            <hr>
                            @if($expenses->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($expenses->take(5) as $expense)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="font-weight-bold">{{ $expense->voucher_number }}</div>
                                                <small class="text-muted">{{ $expense->date }}</small>
                                            </div>
                                            <span class="text-danger font-weight-bold">- {{ number_format($expense->amount, 2) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                @if($expenses->count() > 5)
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Plus {{ $expenses->count() - 5 }} more expenses</small>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-info">No approved expenses found for this budget yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('accountant.budget.edit', $budget->budgetID) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i> Edit Budget
                    </a>
                    <a href="{{ route('accountant.budget.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
