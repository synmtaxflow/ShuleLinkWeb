@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>School Budget</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Accountant</a></li>
                    <li class="active">Budget</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-1">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalAllocated) }}</span>
                    </h4>
                    <p class="text-light">Total Allocated ({{ date('Y') }})</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-2">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalSpent) }}</span>
                    </h4>
                    <p class="text-light">Total Spent</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-3">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalRemaining) }}</span>
                    </h4>
                    <p class="text-light">Remaining</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-4">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ $activeBudgets }}</span>
                    </h4>
                    <p class="text-light">Active Budgets</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Budget Overview</strong>
                        <a href="{{ route('accountant.budget.create') }}" class="btn btn-primary btn-sm float-right">
                            <i class="fa fa-plus"></i> Create New Budget
                        </a>
                    </div>
                    <div class="card-body">
                        <table id="bootstrap-data-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fiscal Year</th>
                                    <th>Category</th>
                                    <th>Period</th>
                                    <th>Allocated</th>
                                    <th>Spent</th>
                                    <th>Remaining</th>
                                    <th>Utilization %</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgets as $budget)
                                @php
                                    $utilization = $budget->allocated_amount > 0 ? ($budget->spent_amount / $budget->allocated_amount) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $budget->fiscal_year }}</td>
                                    <td>{{ $budget->budget_category }}</td>
                                    <td>{{ $budget->period ?? 'Annual' }}</td>
                                    <td>{{ number_format($budget->allocated_amount, 2) }}</td>
                                    <td>{{ number_format($budget->spent_amount, 2) }}</td>
                                    <td>{{ number_format($budget->remaining_amount, 2) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $utilization > 90 ? 'danger' : ($utilization > 70 ? 'warning' : 'success') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $utilization }}%">
                                                {{ number_format($utilization, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $budget->status == 'Active' ? 'success' : ($budget->status == 'Completed' ? 'info' : 'secondary') }}">
                                            {{ $budget->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('accountant.budget.show', $budget->budgetID) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('accountant.budget.edit', $budget->budgetID) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('accountant.budget.destroy', $budget->budgetID) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this budget?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
