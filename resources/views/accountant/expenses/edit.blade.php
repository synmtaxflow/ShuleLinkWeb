@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Expense</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="{{ route('accountant.expenses.index') }}">Expenses</a></li>
                    <li class="active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header">
            <strong>Edit Expense - {{ $expense->voucher_number }}</strong>
        </div>
        <div class="card-body card-block">
            <form action="{{ route('accountant.expenses.update', $expense->expenseID) }}" method="post" enctype="multipart/form-data" class="form-horizontal">
                @csrf
                @method('PUT')
                
                <!-- Date -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="date" class=" form-control-label">Date</label></div>
                    <div class="col-12 col-md-9">
                        <input type="date" id="date" name="date" value="{{ $expense->date }}" class="form-control" required>
                    </div>
                </div>

                <!-- Expense Category -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="expense_category" class=" form-control-label">Expense Category</label></div>
                    <div class="col-12 col-md-9">
                        <select name="expense_category" id="expense_category" class="form-control" required>
                            <option value="">Please select</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ $expense->expense_category == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Voucher Type -->
                <div class="row form-group">
                    <div class="col col-md-3"><label class=" form-control-label">Voucher Type</label></div>
                    <div class="col col-md-9">
                        <div class="form-check-inline form-check">
                            <label for="voucher_petty" class="form-check-label mr-4">
                                <input type="radio" id="voucher_petty" name="voucher_type" value="Petty Cash Voucher" class="form-check-input" {{ $expense->voucher_type == 'Petty Cash Voucher' ? 'checked' : '' }} onchange="toggleAccountOptions()"> Petty Cash Voucher
                            </label>
                            <label for="voucher_payment" class="form-check-label">
                                <input type="radio" id="voucher_payment" name="voucher_type" value="Payment Voucher" class="form-check-input" {{ $expense->voucher_type == 'Payment Voucher' ? 'checked' : '' }} onchange="toggleAccountOptions()"> Payment Voucher
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Payment Account -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="payment_account" class=" form-control-label">Payment Account</label></div>
                    <div class="col-12 col-md-9">
                        <select name="payment_account" id="payment_account" class="form-control" required>
                            <option value="{{ $expense->payment_account }}" selected>{{ $expense->payment_account }}</option>
                        </select>
                    </div>
                </div>

                <!-- Voucher Number -->
                <div class="row form-group">
                    <div class="col col-md-3"><label class=" form-control-label">Voucher Number</label></div>
                    <div class="col-12 col-md-9">
                        <p class="form-control-static text-muted">{{ $expense->voucher_number }}</p>
                        <small class="form-text text-muted">Cannot be changed</small>
                    </div>
                </div>

                <!-- Description -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="description" class=" form-control-label">Description</label></div>
                    <div class="col-12 col-md-9">
                        <textarea name="description" id="description" rows="3" placeholder="Enter expense details..." class="form-control">{{ $expense->description }}</textarea>
                    </div>
                </div>

                <!-- Amount -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="amount" class=" form-control-label">Amount</label></div>
                    <div class="col-12 col-md-9">
                        <input type="number" id="amount" name="amount" value="{{ $expense->amount }}" class="form-control" required min="1" step="0.01">
                    </div>
                </div>

                <!-- Attachment -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="attachment" class=" form-control-label">Attachment</label></div>
                    <div class="col-12 col-md-9">
                        @if($expense->attachment)
                            <p class="form-text text-muted">Current: {{ basename($expense->attachment) }}</p>
                        @endif
                        <input type="file" id="attachment" name="attachment" class="form-control-file">
                        <small class="form-text text-muted">Upload new file to replace existing</small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Update
                    </button>
                    <a href="{{ route('accountant.expenses.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleAccountOptions() {
        var voucherType = document.querySelector('input[name="voucher_type"]:checked').value;
        var accountSelect = document.getElementById('payment_account');
        var currentValue = '{{ $expense->payment_account }}';
        
        // Clear options
        accountSelect.innerHTML = '';
        
        if (voucherType === 'Petty Cash Voucher') {
            // Add Cash option
            var option = document.createElement('option');
            option.value = 'Cash';
            option.text = 'Cash';
            option.selected = (currentValue == 'Cash');
            accountSelect.add(option);
        } else {
            // Add Bank options
            var accounts = ['CRDB', 'NMB', 'NBC', 'Mobile Money'];
            accounts.forEach(function(acc) {
                var option = document.createElement('option');
                option.value = 'Bank - ' + acc;
                option.text = 'Bank - ' + acc;
                option.selected = (currentValue == 'Bank - ' + acc);
                accountSelect.add(option);
            });
        }
    }
    
    // Initialize on load
    toggleAccountOptions();
</script>
@endsection
