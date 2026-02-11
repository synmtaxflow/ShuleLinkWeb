@extends('layouts.vali')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Financial Reports</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <div class="btn-group mt-2">
                    <a href="{{ route('accountant.reports.export_expenses') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fa fa-download"></i> Export Expenses (CSV)
                    </a>
                    <a href="{{ route('accountant.reports.export_income') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-download"></i> Export Income (CSV)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <!-- Summary Tiles -->
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-1">
                <div class="card-body pb-0">
                    <div class="dropdown float-right">
                        <i class="fa fa-usd"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalIncome, 0) }}</span>
                    </h4>
                    <p class="text-light">Total Income</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-4">
                <div class="card-body pb-0">
                    <div class="dropdown float-right">
                        <i class="fa fa-money"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($totalExpenses, 0) }}</span>
                    </h4>
                    <p class="text-light">Total Approved Expenses</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-flat-color-3">
                <div class="card-body pb-0">
                    <div class="dropdown float-right">
                        <i class="fa fa-pie-chart"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count">{{ number_format($budgetBalance, 0) }}</span>
                    </h4>
                    <p class="text-light">Budget Balance</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-danger">
                <div class="card-body pb-0">
                    <div class="dropdown float-right">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <h4 class="mb-0">
                        <span class="count">{{ $pendingExpensesCount }}</span>
                    </h4>
                    <p class="text-light">Pending Approvals</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Income vs Expenses Trends (Last 6 Months)</strong>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Expense Categories (Current Year)</strong>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Budget vs Expense Chart -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Budget vs Actual Expenses (Current Year)</strong>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="budgetChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Expense Details Widget Area -->
    <div class="row" id="expenseDetailsValidation" style="display:none;">
        <div class="col-lg-12">
             <div class="card">
                <div class="card-header bg-dark text-white">
                    <strong class="card-title" id="selectedCategoryTitle">Expense Details</strong>
                    <button type="button" class="close text-white" onclick="document.getElementById('expenseDetailsValidation').style.display='none'">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                         <div class="col-md-4">
                             <div class="card bg-light">
                                 <div class="card-body">
                                     <small class="text-muted text-uppercase font-weight-bold">Allocated Budget</small>
                                     <h3 class="mb-0 text-primary" id="detailAllocated">0</h3>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4">
                             <div class="card bg-light">
                                 <div class="card-body">
                                     <small class="text-muted text-uppercase font-weight-bold">Total Spent</small>
                                     <h3 class="mb-0 text-danger" id="detailSpent">0</h3>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4">
                             <div class="card bg-light">
                                 <div class="card-body">
                                     <small class="text-muted text-uppercase font-weight-bold">Utilization</small>
                                     <h3 class="mb-0" id="detailPercentage">0%</h3>
                                     <div class="progress mt-2" style="height: 5px;">
                                         <div class="progress-bar" role="progressbar" style="width: 0%;" id="detailProgressBar"></div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                    </div>
                    
                    <h5 class="mb-3">Transaction History</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Description</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody">
                                <!-- filled by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>

<style>
    .bg-flat-color-1 { background: #20a8d8; }
    .bg-flat-color-3 { background: #ffc107; }
    .bg-flat-color-4 { background: #e83e8c; }
    .card-body h4 { font-size: 1.5rem; font-weight: 700; }
    #expenseDetailsValidation { transition: all 0.3s ease; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route('accountant.reports.chart_data') }}')
        .then(response => response.json())
        .then(data => {
            renderTrendsChart(data.trends);
            renderCategoryChart(data.categories);
            renderBudgetChart(data.budget_comparison);
        });
    
    // Store details for access on click
    let expenseDetailsData = {};

    function renderBudgetChart(budgetData) {
        expenseDetailsData = budgetData.details; // Store for valid access
        const labels = budgetData.labels;
        
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const budgetChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Allocated Budget',
                        data: budgetData.allocated,
                        backgroundColor: 'rgba(32, 168, 216, 0.6)',
                        borderColor: 'rgba(32, 168, 216, 1)',
                        borderWidth: 1,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Actual Expenses',
                        data: budgetData.spent,
                        backgroundColor: function(context) {
                            const value = context.raw;
                            const index = context.dataIndex;
                            const allocated = budgetData.allocated[index];
                            // Red if over budget, otherwise pink
                            return value > allocated ? 'rgba(220, 53, 69, 0.7)' : 'rgba(232, 62, 140, 0.7)';
                        },
                        borderColor: function(context) {
                             const value = context.raw;
                            const index = context.dataIndex;
                            const allocated = budgetData.allocated[index];
                            return value > allocated ? '#dc3545' : '#e83e8c';
                        },
                        borderWidth: 1,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                // Add alert in tooltip if over budget
                                if (context.dataset.label === 'Actual Expenses') {
                                    const index = context.dataIndex;
                                    const allocated = budgetData.allocated[index];
                                    const spent = context.raw;
                                    if (spent > allocated) {
                                        return '⚠️ Over Budget by ' + (spent - allocated).toLocaleString() + ' TZS';
                                    }
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                             callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                onClick: (e) => {
                    const points = budgetChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                    if (points.length) {
                        const firstPoint = points[0];
                        const label = budgetChart.data.labels[firstPoint.index];
                        showExpenseDetails(label);
                    }
                }
            }
        });
    }

    function showExpenseDetails(categoryName) {
        const details = expenseDetailsData[categoryName];
        if (!details) return;

        // Populate Widget
        document.getElementById('selectedCategoryTitle').innerText = categoryName + ' - Details';
        document.getElementById('detailAllocated').innerText = parseFloat(details.allocated).toLocaleString();
        document.getElementById('detailSpent').innerText = parseFloat(details.spent).toLocaleString();
        document.getElementById('detailPercentage').innerText = details.percentage + '%';
        
        // Progress Bar
        const pBar = document.getElementById('detailProgressBar');
        pBar.style.width = Math.min(details.percentage, 100) + '%';
        pBar.className = 'progress-bar bg-' + (details.percentage > 100 ? 'danger' : (details.percentage > 90 ? 'warning' : 'success'));
        
        // Table Items
        const tbody = document.getElementById('detailTableBody');
        tbody.innerHTML = '';
        
        if (details.items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No expenses recorded for this category yet.</td></tr>';
        } else {
            details.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.date}</td>
                    <td><small>${item.voucher_number}</small></td>
                    <td>${item.description ? item.description : '-'}</td>
                    <td class="text-right">${parseFloat(item.amount).toLocaleString()}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Show Section
        document.getElementById('expenseDetailsValidation').style.display = 'block';
        document.getElementById('expenseDetailsValidation').scrollIntoView({ behavior: 'smooth' });
    }

    function renderTrendsChart(trends) {
        const ctx = document.getElementById('trendsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: trends.labels,
                datasets: [
                    {
                        label: 'Income',
                        data: trends.income,
                        backgroundColor: 'rgba(32, 168, 216, 0.7)',
                        borderColor: '#20a8d8',
                        borderWidth: 1
                    },
                    {
                        label: 'Expenses',
                        data: trends.expenses,
                        backgroundColor: 'rgba(232, 62, 140, 0.7)',
                        borderColor: '#e83e8c',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCategoryChart(categories) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: categories.labels,
                datasets: [{
                    data: categories.data,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#C9CBCF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>
@endsection
