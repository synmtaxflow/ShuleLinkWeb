@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>School Income</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Accountant</a></li>
                    <li class="active">Income</li>
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
                        <span class="count">{{ number_format($totalToday) }}</span>
                    </h4>
                    <p class="text-light">Today's Income</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-2">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalWeek) }}</span>
                    </h4>
                    <p class="text-light">This Week</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-3">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalMonth) }}</span>
                    </h4>
                    <p class="text-light">This Month</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-4">
                <div class="card-body pb-0">
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalYear) }}</span>
                    </h4>
                    <p class="text-light">This Year</p>
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
                        <strong class="card-title">Income Records</strong>
                        <div class="row float-right">
                            <a href="{{ route('accountant.income_categories.index') }}" class="btn btn-secondary btn-sm mr-2">
                                <i class="fa fa-list"></i> Manage Categories
                            </a>
                            <a href="{{ route('accountant.income.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Record New Income
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="bootstrap-data-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Receipt No</th>
                                    <th>Category</th>
                                    <th>Payer</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incomes as $income)
                                <tr>
                                    <td>{{ $income->date }}</td>
                                    <td>{{ $income->receipt_number }}</td>
                                    <td>{{ $income->income_category }}</td>
                                    <td>{{ $income->payer_name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($income->description, 25) }}</td>
                                    <td>{{ number_format($income->amount, 2) }}</td>
                                    <td>{{ $income->payment_method }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('accountant.income.show', $income->incomeID) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('accountant.income.edit', $income->incomeID) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('accountant.income.destroy', $income->incomeID) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this income record?')">
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
