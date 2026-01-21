@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    /* Remove border-radius from all widgets */
    .card, .alert, .btn, div, .form-control, .form-select {
        border-radius: 0 !important;
    }
    
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }

    .expenses-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .expenses-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
    }
    .section-title {
        font-weight: 600;
        margin-bottom: 12px;
    }
    .muted-help {
        color: #6c757d;
        font-size: 0.9rem;
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Manage Expenses</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Expenses Management</strong>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group expenses-menu">
                        <a class="list-group-item active" data-target="#section-budgets">
                            <i class="fa fa-pie-chart"></i> Budgets
                        </a>
                        <a class="list-group-item" data-target="#section-record-expenses">
                            <i class="fa fa-pencil-square-o"></i> Record Expenses
                        </a>
                        <a class="list-group-item" data-target="#section-view-expenses">
                            <i class="fa fa-list"></i> View Expenses
                        </a>
                    </div>
                    <div class="card border-primary-custom mt-3">
                        <div class="card-body">
                            <div class="section-title">Quick Notes</div>
                            <div class="muted-help">
                                - Budget cannot exceed revenue for selected year.<br>
                                - Recording expenses reduces the budget.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div id="section-budgets" class="expenses-section">
                        <div class="section-title">Budget Setup</div>
                        <form method="GET" action="{{ route('manage_expenses') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year" onchange="this.form.submit()">
                                        <option value="{{ date('Y') }}" {{ ($year ?? date('Y')) == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                                        <option value="{{ date('Y') - 1 }}" {{ ($year ?? date('Y')) == date('Y') - 1 ? 'selected' : '' }}>{{ date('Y') - 1 }}</option>
                                        <option value="{{ date('Y') - 2 }}" {{ ($year ?? date('Y')) == date('Y') - 2 ? 'selected' : '' }}>{{ date('Y') - 2 }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="alert alert-info w-100 mb-0">
                                        Revenue: <strong>{{ number_format($totalRevenue ?? 0, 0) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @if(!$budget)
                            <form method="POST" action="{{ route('expense_budgets.store') }}">
                                @csrf
                                <input type="hidden" name="year" value="{{ $year ?? date('Y') }}">
                                <div class="form-group mb-3">
                                    <label for="total_amount">Budget Amount</label>
                                    <input type="number" class="form-control" id="total_amount" name="total_amount" min="0" step="0.01" required>
                                </div>
                                <button class="btn btn-primary-custom" type="submit">
                                    <i class="fa fa-save"></i> Save Budget
                                </button>
                            </form>
                        @else
                            <div class="alert alert-info">
                                Total Budget: <strong>{{ number_format($budget->total_amount, 0) }}</strong><br>
                                Remaining Budget: <strong>{{ number_format($budget->remaining_amount, 0) }}</strong>
                            </div>
                            <form method="POST" action="{{ route('expense_budgets.update') }}">
                                @csrf
                                <input type="hidden" name="expense_budgetID" value="{{ $budget->expense_budgetID }}">
                                <div class="form-group mb-3">
                                    <label for="update_total_amount">Update Budget Amount</label>
                                    <input type="number" class="form-control" id="update_total_amount" name="total_amount" min="0" step="0.01" value="{{ $budget->total_amount }}" required>
                                </div>
                                <button class="btn btn-primary-custom" type="submit">
                                    <i class="fa fa-save"></i> Update Budget
                                </button>
                            </form>
                        @endif
                    </div>

                    <div id="section-record-expenses" class="expenses-section d-none">
                        <div class="section-title">Record Expenses</div>
                        @if(!$budget)
                            <div class="alert alert-warning">
                                Please create a budget for this year before recording expenses.
                            </div>
                        @else
                            <form method="POST" action="{{ route('expense_records.store') }}">
                                @csrf
                                <input type="hidden" name="expense_budgetID" value="{{ $budget->expense_budgetID }}">
                                <div class="form-group mb-3">
                                    <label for="expense_date">Date</label>
                                    <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="amount">Amount Spent</label>
                                    <input type="number" class="form-control" id="amount" name="amount" min="0" step="0.01" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required placeholder="What was this expense for?"></textarea>
                                </div>
                                <button class="btn btn-primary-custom" type="submit">
                                    <i class="fa fa-check"></i> Record Expense
                                </button>
                            </form>
                        @endif

                    </div>

                    <div id="section-view-expenses" class="expenses-section d-none">
                        <div class="section-title">Expenses List ({{ $year ?? date('Y') }})</div>
                        @if(isset($expenses) && $expenses->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expenses as $expense)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}</td>
                                                <td>{{ $expense->description }}</td>
                                                <td>{{ number_format($expense->amount, 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">No expenses recorded for this year.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script>
    (function() {
        const menuItems = document.querySelectorAll('.expenses-menu .list-group-item');
        const sections = document.querySelectorAll('.expenses-section');

        function showSection(targetId) {
            sections.forEach(section => {
                if (section.id === targetId) {
                    section.classList.remove('d-none');
                } else {
                    section.classList.add('d-none');
                }
            });
        }

        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                const target = this.getAttribute('data-target');
                if (target) {
                    showSection(target.replace('#', ''));
                }
            });
        });
    })();
</script>
