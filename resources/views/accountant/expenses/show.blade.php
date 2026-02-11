@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Expense Details</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="{{ route('accountant.expenses.index') }}">Expenses</a></li>
                    <li class="active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Voucher: {{ $expense->voucher_number }}</strong>
                    <span class="badge badge-{{ $expense->status == 'Approved' ? 'success' : ($expense->status == 'Rejected' ? 'danger' : 'warning') }} float-right">
                        {{ $expense->status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Date:</div>
                        <div class="col-md-8">{{ $expense->date }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Category:</div>
                        <div class="col-md-8">{{ $expense->expense_category }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Voucher Type:</div>
                        <div class="col-md-8">{{ $expense->voucher_type }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Payment Account:</div>
                        <div class="col-md-8">{{ $expense->payment_account }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Amount:</div>
                        <div class="col-md-8 text-danger font-weight-bold">TZS {{ number_format($expense->amount, 2) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="font-weight-bold mb-1">Description:</div>
                            <div class="p-3 bg-light border rounded">
                                {{ $expense->description ?: 'No description provided.' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    @if($expense->status == 'Pending')
                        <form action="{{ route('accountant.expenses.approve', $expense->expenseID) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this expense?')">
                                <i class="fa fa-check"></i> Approve
                            </button>
                        </form>
                        <form action="{{ route('accountant.expenses.reject', $expense->expenseID) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this expense?')">
                                <i class="fa fa-times"></i> Reject
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('accountant.expenses.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Audit Info</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted">Entered By:</div>
                        <div>{{ $expense->enteredBy->name ?? 'System' }}</div>
                        <div class="small text-muted italic">{{ $expense->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    
                    @if($expense->status != 'Pending')
                        <div class="mb-3">
                            <div class="small text-muted">{{ $expense->status }} By:</div>
                            <div>{{ $expense->approvedBy->name ?? 'System' }}</div>
                            <div class="small text-muted italic">{{ $expense->updated_at->format('M d, Y H:i') }}</div>
                        </div>
                    @endif
                    
                    <div class="mt-4">
                        <div class="font-weight-bold small mb-2">Attachment:</div>
                        @if($expense->attachment)
                            <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn btn-outline-primary btn-block btn-sm">
                                <i class="fa fa-paperclip"></i> View Attachment
                            </a>
                        @else
                            <div class="alert alert-secondary py-2 small">No attachment uploaded</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
