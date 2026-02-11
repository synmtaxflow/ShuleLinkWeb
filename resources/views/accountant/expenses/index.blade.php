@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>School Expenses</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Accountant</a></li>
                    <li class="active">Expenses</li>
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
                    <p class="text-light">Today's Expenses</p>
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

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Expense Records</strong>
                        <div class="float-right d-flex gap-2">
                            <a href="{{ route('accountant.expense_categories.index') }}" class="btn btn-outline-primary btn-sm mr-2">
                                <i class="fa fa-cogs"></i> Manage Expense Categories
                            </a>
                            <a href="{{ route('accountant.expenses.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add New Expense
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="bootstrap-data-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher No</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Account</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->date }}</td>
                                    <td>{{ $expense->voucher_number }}</td>
                                    <td>{{ $expense->voucher_type }}</td>
                                    <td>{{ $expense->expense_category }}</td>
                                    <td>{{ Str::limit($expense->description, 30) }}</td>
                                    <td>{{ number_format($expense->amount, 2) }}</td>
                                    <td>{{ $expense->payment_account }}</td>
                                    <td>
                                        <span class="badge badge-{{ $expense->status == 'Approved' ? 'success' : ($expense->status == 'Rejected' ? 'danger' : 'warning') }}">
                                            {{ $expense->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('accountant.expenses.show', $expense->expenseID) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @if($expense->status == 'Pending')
                                                <form action="{{ route('accountant.expenses.approve', $expense->expenseID) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Approve this expense?')">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('accountant.expenses.reject', $expense->expenseID) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Reject" onclick="return confirm('Reject this expense?')">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('accountant.expenses.edit', $expense->expenseID) }}" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif

                                            <form action="{{ route('accountant.expenses.destroy', $expense->expenseID) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this expense?')">
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
