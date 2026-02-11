@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Income</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="{{ route('accountant.income.index') }}">Income</a></li>
                    <li class="active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header">
            <strong>Edit Income Record - {{ $income->receipt_number }}</strong>
        </div>
        <div class="card-body card-block">
            <form action="{{ route('accountant.income.update', $income->incomeID) }}" method="post" enctype="multipart/form-data" class="form-horizontal">
                @csrf
                @method('PUT')
                
                <!-- Date -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="date" class=" form-control-label">Date</label></div>
                    <div class="col-12 col-md-9">
                        <input type="date" id="date" name="date" value="{{ $income->date }}" class="form-control" required>
                    </div>
                </div>

                <!-- Income Category -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="income_category" class=" form-control-label">Income Category</label></div>
                    <div class="col-12 col-md-9">
                        <select name="income_category" id="income_category" class="form-control" required>
                            <option value="">Please select</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ $income->income_category == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Payer Name -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="payer_name" class=" form-control-label">Payer Name / Student</label></div>
                    <div class="col-12 col-md-9">
                        <input type="text" id="payer_name" name="payer_name" value="{{ $income->payer_name }}" placeholder="Enter student name or payer..." class="form-control">
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="payment_method" class=" form-control-label">Payment Method</label></div>
                    <div class="col-12 col-md-9">
                        <select name="payment_method" id="payment_method" class="form-control" required onchange="toggleAccountOptions()">
                            <option value="Cash" {{ $income->payment_method == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Bank" {{ $income->payment_method == 'Bank' ? 'selected' : '' }}>Bank Deposit</option>
                            <option value="Mobile Money" {{ $income->payment_method == 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="Cheque" {{ $income->payment_method == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>
                </div>

                <!-- Payment Account -->
                <div class="row form-group" id="account_div" style="{{ $income->payment_method == 'Cash' ? 'display:none;' : '' }}">
                    <div class="col col-md-3"><label for="payment_account" class=" form-control-label">Deposit To Account</label></div>
                    <div class="col-12 col-md-9">
                        <select name="payment_account" id="payment_account" class="form-control">
                            <option value="">Select Account</option>
                            <option value="CRDB Bank" {{ $income->payment_account == 'CRDB Bank' ? 'selected' : '' }}>CRDB Bank</option>
                            <option value="NMB Bank" {{ $income->payment_account == 'NMB Bank' ? 'selected' : '' }}>NMB Bank</option>
                            <option value="NBC Bank" {{ $income->payment_account == 'NBC Bank' ? 'selected' : '' }}>NBC Bank</option>
                            <option value="M-Pesa" {{ $income->payment_account == 'M-Pesa' ? 'selected' : '' }}>M-Pesa</option>
                            <option value="Tigo Pesa" {{ $income->payment_account == 'Tigo Pesa' ? 'selected' : '' }}>Tigo Pesa</option>
                            <option value="Airtel Money" {{ $income->payment_account == 'Airtel Money' ? 'selected' : '' }}>Airtel Money</option>
                        </select>
                    </div>
                </div>

                <!-- Receipt Number -->
                <div class="row form-group">
                    <div class="col col-md-3"><label class=" form-control-label">Receipt Number</label></div>
                    <div class="col-12 col-md-9">
                        <p class="form-control-static text-muted">{{ $income->receipt_number }}</p>
                        <small class="form-text text-muted">Cannot be changed</small>
                    </div>
                </div>

                <!-- Description -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="description" class=" form-control-label">Description</label></div>
                    <div class="col-12 col-md-9">
                        <textarea name="description" id="description" rows="3" placeholder="Enter income details..." class="form-control">{{ $income->description }}</textarea>
                    </div>
                </div>

                <!-- Amount -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="amount" class=" form-control-label">Amount</label></div>
                    <div class="col-12 col-md-9">
                        <input type="number" id="amount" name="amount" value="{{ $income->amount }}" class="form-control" required min="1" step="0.01">
                    </div>
                </div>

                <!-- Attachment -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="attachment" class=" form-control-label">Attachment</label></div>
                    <div class="col-12 col-md-9">
                        @if($income->attachment)
                            <p class="form-text text-muted">Current: {{ basename($income->attachment) }}</p>
                        @endif
                        <input type="file" id="attachment" name="attachment" class="form-control-file">
                        <small class="form-text text-muted">Upload new file to replace existing</small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Update
                    </button>
                    <a href="{{ route('accountant.income.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleAccountOptions() {
        var method = document.getElementById('payment_method').value;
        var accountDiv = document.getElementById('account_div');
        
        if (method === 'Cash') {
            accountDiv.style.display = 'none';
        } else {
            accountDiv.style.display = 'flex';
        }
    }
</script>
@endsection
