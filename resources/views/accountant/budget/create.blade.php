@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Create Budget</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="{{ route('accountant.budget.index') }}">Budget</a></li>
                    <li class="active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header">
            <strong>New Budget Entry</strong>
        </div>
        <div class="card-body card-block">
            <form action="{{ route('accountant.budget.store') }}" method="post" class="form-horizontal">
                @csrf
                
                <!-- Fiscal Year -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="fiscal_year" class=" form-control-label">Fiscal Year</label></div>
                    <div class="col-12 col-md-9">
                        <select name="fiscal_year" id="fiscal_year" class="form-control" required>
                            <option value="">Select Year</option>
                            @for($year = date('Y') - 1; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
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
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Period -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="period" class=" form-control-label">Budget Period</label></div>
                    <div class="col-12 col-md-9">
                        <select name="period" id="period" class="form-control">
                            <option value="Annual">Annual</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Monthly">Monthly</option>
                        </select>
                    </div>
                </div>

                <!-- Allocated Amount -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="allocated_amount" class=" form-control-label">Allocated Amount</label></div>
                    <div class="col-12 col-md-9">
                        <input type="number" id="allocated_amount" name="allocated_amount" placeholder="0.00" class="form-control" required min="1" step="0.01">
                        <small class="form-text text-muted">Total budget allocation for this category</small>
                    </div>
                </div>

                <!-- Notes -->
                <div class="row form-group">
                    <div class="col col-md-3"><label for="notes" class=" form-control-label">Notes</label></div>
                    <div class="col-12 col-md-9">
                        <textarea name="notes" id="notes" rows="3" placeholder="Additional information or breakdown..." class="form-control"></textarea>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-dot-circle-o"></i> Create Budget
                    </button>
                    <button type="reset" class="btn btn-danger btn-sm">
                        <i class="fa fa-ban"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
