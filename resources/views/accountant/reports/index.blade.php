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
</div>

<style>
    .bg-flat-color-1 { background: #20a8d8; }
    .bg-flat-color-3 { background: #ffc107; }
    .bg-flat-color-4 { background: #e83e8c; }
    .card-body h4 { font-size: 1.5rem; font-weight: 700; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route('accountant.reports.chart_data') }}')
        .then(response => response.json())
        .then(data => {
            renderTrendsChart(data.trends);
            renderCategoryChart(data.categories);
        });

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
