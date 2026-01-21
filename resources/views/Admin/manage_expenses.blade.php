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
    .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
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
            <div class="form-loading" id="expensesLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Saving...</span>
                <div class="form-progress"></div>
            </div>

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
                            <form method="POST" action="{{ route('expense_budgets.store') }}" class="js-show-loading">
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
                            <form method="POST" action="{{ route('expense_budgets.update') }}" class="js-show-loading">
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
                            <form method="POST" action="{{ route('expense_records.store') }}" class="js-show-loading">
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
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expenses as $expense)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}</td>
                                                <td>{{ $expense->description }}</td>
                                                <td>{{ number_format($expense->amount, 0) }}</td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-toggle="modal"
                                                        data-target="#editExpenseModal"
                                                        data-id="{{ $expense->expense_recordID }}"
                                                        data-date="{{ $expense->expense_date }}"
                                                        data-amount="{{ $expense->amount }}"
                                                        data-description="{{ $expense->description }}"
                                                    >
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('expense_records.delete') }}" style="display:inline-block;" class="js-show-loading">
                                                        @csrf
                                                        <input type="hidden" name="expense_recordID" value="{{ $expense->expense_recordID }}">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense?')">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
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

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" role="dialog" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editExpenseModalLabel"><i class="fa fa-pencil"></i> Update Expense</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('expense_records.update') }}" class="js-show-loading">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="expense_recordID" id="edit_expense_id">
                    <div class="form-group mb-3">
                        <label for="edit_expense_date">Date</label>
                        <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_expense_amount">Amount</label>
                        <input type="number" class="form-control" id="edit_expense_amount" name="amount" min="0" step="0.01" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_expense_description">Description</label>
                        <textarea class="form-control" id="edit_expense_description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const loadingBar = document.getElementById('expensesLoading');
        const loadingForms = document.querySelectorAll('.js-show-loading');
        if (loadingForms.length) {
            loadingForms.forEach(form => {
                form.addEventListener('submit', () => {
                    if (loadingBar) {
                        loadingBar.style.display = 'flex';
                    }
                });
            });
        }

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

        const editButtons = document.querySelectorAll('[data-target="#editExpenseModal"]');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_expense_id').value = this.getAttribute('data-id');
                document.getElementById('edit_expense_date').value = this.getAttribute('data-date');
                document.getElementById('edit_expense_amount').value = this.getAttribute('data-amount');
                document.getElementById('edit_expense_description').value = this.getAttribute('data-description');
            });
        });
    })();
</script>
