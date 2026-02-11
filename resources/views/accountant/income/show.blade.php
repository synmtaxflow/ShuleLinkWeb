@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Income Details</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="{{ route('accountant.income.index') }}">Income</a></li>
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
                    <strong class="card-title">Receipt: {{ $income->receipt_number }}</strong>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Date:</div>
                        <div class="col-md-8">{{ $income->date }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Category:</div>
                        <div class="col-md-8">{{ $income->income_category }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Payer / Student:</div>
                        <div class="col-md-8">{{ $income->payer_name ?: 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Payment Method:</div>
                        <div class="col-md-8">{{ $income->payment_method }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Payment Account:</div>
                        <div class="col-md-8">{{ $income->payment_account ?: 'Cash Handle' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Amount:</div>
                        <div class="col-md-8 text-success font-weight-bold">TZS {{ number_format($income->amount, 2) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="font-weight-bold mb-1">Description:</div>
                            <div class="p-3 bg-light border rounded">
                                {{ $income->description ?: 'No description provided.' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('accountant.income.edit', $income->incomeID) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i> Edit Record
                    </a>
                    <a href="{{ route('accountant.income.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Receipt Information</strong>
                </div>
                <div class="card-body">
                    <div class="mb-4 text-center">
                        <div class="display-4"><i class="fa fa-file-text-o text-primary"></i></div>
                        <h5 class="mt-2">{{ $income->receipt_number }}</h5>
                    </div>
                    
                    <div class="mb-3">
                        <div class="small text-muted">Recorded By:</div>
                        <div>{{ $income->enteredBy->name ?? 'System' }}</div>
                        <div class="small text-muted italic">{{ $income->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="font-weight-bold small mb-2">Attachment / Proof:</div>
                        @if($income->attachment)
                            <a href="{{ asset('storage/' . $income->attachment) }}" target="_blank" class="btn btn-outline-primary btn-block btn-sm">
                                <i class="fa fa-paperclip"></i> View Proof
                            </a>
                        @else
                            <div class="alert alert-secondary py-2 small">No proof uploaded</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
