@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Budget</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="{{ route('accountant.budget.index') }}">Budget</a></li>
                    <li class="active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header">
            <strong>Edit Budget - {{ $budget->budget_category }} ({{ $budget->fiscal_year }})</strong>
        </div>
        <div class="card-body card-block">
            <form action="{{ route('accountant.budget.update', $budget->budgetID) }}" method="post" class="form-horizontal">
                @csrf
                @method('PUT')
                
                <!-- Fiscal Year -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="fiscal_year" class=" form-control-label">Fiscal Year</label></div>
                    <div class="col-12 col-md-9">
                        <select name="fiscal_year" id="fiscal_year" class="form-control" required>
                            <option value="">Select Year</option>
                            @for($year = date('Y') - 1; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}" {{ $budget->fiscal_year == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Budget Category -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="budget_category" class=" form-control-label">Budget Category</label></div>
                    <div class="col-12 col-md-9">
                        <select name="budget_category" id="budget_category" class="form-control" required>
                            <option value="">Please select</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ $budget->budget_category == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Period -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="period" class=" form-control-label">Budget Period</label></div>
                    <div class="col-12 col-md-9">
                        <select name="period" id="period" class="form-control">
                            <option value="Annual" {{ $budget->period == 'Annual' ? 'selected' : '' }}>Annual</option>
                            <option value="Quarterly" {{ $budget->period == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="Monthly" {{ $budget->period == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                </div>

                <!-- Allocated Amount -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="allocated_amount" class=" form-control-label">Allocated Amount</label></div>
                    <div class="col-12 col-md-9">
                        <input type="number" id="allocated_amount" name="allocated_amount" value="{{ $budget->allocated_amount }}" class="form-control" required min="1" step="0.01">
                        <small class="form-text text-muted">Total budget allocation for this category</small>
                    </div>
                </div>

                <!-- Spent Amount (Read-only on edit maybe? Or adjustable?) 
                     Usually spent amount is calculated from actual expenses. 
                     The controller update method recalculates remaining amount: 
                     $budget->remaining_amount = $request->allocated_amount - $budget->spent_amount;
                -->
                <div class="row form-group">
                    <div class="col col-md-3"><label class=" form-control-label">Spent Amount</label></div>
                    <div class="col-12 col-md-9">
                        <p class="form-control-static text-muted">{{ number_format($budget->spent_amount, 2) }}</p>
                        <small class="form-text text-muted">Calculated from actual expenses</small>
                    </div>
                </div>

                <!-- Status -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="status" class=" form-control-label">Status</label></div>
                    <div class="col-12 col-md-9">
                        <select name="status" id="status" class="form-control">
                            <option value="Active" {{ $budget->status == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Completed" {{ $budget->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Inactive" {{ $budget->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="notes" class=" form-control-label">Notes</label></div>
                    <div class="col-12 col-md-9">
                        <textarea name="notes" id="notes" rows="3" placeholder="Additional information or breakdown..." class="form-control">{{ $budget->notes }}</textarea>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Update Budget
                    </button>
                    <a href="{{ route('accountant.budget.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
