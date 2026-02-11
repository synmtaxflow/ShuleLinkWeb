<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;
use Session;
use Carbon\Carbon;

class AccountantReportController extends Controller
{
    public function index()
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        // Summary Statistics
        $totalIncome = Income::where('schoolID', $schoolID)->sum('amount');
        $totalExpenses = Expense::where('schoolID', $schoolID)->where('status', 'Approved')->sum('amount');
        $budgetBalance = Budget::where('schoolID', $schoolID)->where('status', 'Active')->sum('remaining_amount');
        $pendingExpensesCount = Expense::where('schoolID', $schoolID)->where('status', 'Pending')->count();

        return view('accountant.reports.index', compact(
            'totalIncome', 
            'totalExpenses', 
            'budgetBalance', 
            'pendingExpensesCount'
        ));
    }

    public function getChartData()
    {
        $schoolID = Session::get('schoolID');
        $currentYear = date('Y');
        
        // Last 6 months trends
        $months = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $months[] = $monthName;

            $income = Income::where('schoolID', $schoolID)
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');
            $incomeData[] = $income;

            $expense = Expense::where('schoolID', $schoolID)
                ->where('status', 'Approved')
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');
            $expenseData[] = $expense;
        }

        // Category breakdown (Current Year)
        $categoriesStats = Expense::where('schoolID', $schoolID)
            ->where('status', 'Approved')
            ->whereYear('date', $currentYear)
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->get();

        // Budget vs Expenses Comparison
        $budgets = Budget::where('schoolID', $schoolID)
            ->where('fiscal_year', $currentYear)
            ->where('status', 'Active')
            ->get();

        $budgetComparison = [];
        $budgetLabels = [];
        $budgetAllocated = [];
        $budgetSpent = [];
        $expenseDetails = [];

        // Get all unique categories from both budgets and actual expenses
        $allCategories = $budgets->pluck('budget_category')
            ->merge($categoriesStats->pluck('expense_category'))
            ->unique()
            ->values();

        foreach ($allCategories as $category) {
            $budget = $budgets->where('budget_category', $category)->first();
            $allocated = $budget ? $budget->allocated_amount : 0;
            
            $spent = Expense::where('schoolID', $schoolID)
                ->where('expense_category', $category)
                ->where('status', 'Approved')
                ->whereYear('date', $currentYear)
                ->sum('amount');

            $details = Expense::where('schoolID', $schoolID)
                ->where('expense_category', $category)
                ->where('status', 'Approved')
                ->whereYear('date', $currentYear)
                ->select('date', 'description', 'amount', 'voucher_number')
                ->orderBy('date', 'desc')
                ->get();

            $budgetLabels[] = $category;
            $budgetAllocated[] = $allocated;
            $budgetSpent[] = $spent;
            
            $expenseDetails[$category] = [
                'allocated' => $allocated,
                'spent' => $spent,
                'percentage' => $allocated > 0 ? round(($spent / $allocated) * 100, 1) : ($spent > 0 ? 100 : 0),
                'items' => $details
            ];
        }

        return response()->json([
            'trends' => [
                'labels' => $months,
                'income' => $incomeData,
                'expenses' => $expenseData
            ],
            'categories' => [
                'labels' => $categoriesStats->pluck('expense_category'),
                'data' => $categoriesStats->pluck('total')
            ],
            'budget_comparison' => [
                'labels' => $budgetLabels,
                'allocated' => $budgetAllocated,
                'spent' => $budgetSpent,
                'details' => $expenseDetails
            ]
        ]);
    }

    public function exportExpenses(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $filename = "Expenses_Export_" . date('Y-m-d') . ".csv";
        $columns = ['Date', 'Voucher Number', 'Category', 'Description', 'Amount', 'Status', 'Entered By'];

        $callback = function() use ($schoolID, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $expenses = Expense::where('schoolID', $schoolID)
                ->with('enteredBy')
                ->orderBy('date', 'desc')
                ->get();

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->date,
                    $expense->voucher_number,
                    $expense->expense_category,
                    $expense->description,
                    $expense->amount,
                    $expense->status,
                    $expense->enteredBy->name ?? 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function exportIncome(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $filename = "Income_Export_" . date('Y-m-d') . ".csv";
        $columns = ['Date', 'Receipt Number', 'Category', 'Payer', 'Description', 'Amount', 'Method'];

        $callback = function() use ($schoolID, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $incomes = Income::where('schoolID', $schoolID)
                ->orderBy('date', 'desc')
                ->get();

            foreach ($incomes as $income) {
                fputcsv($file, [
                    $income->date,
                    $income->receipt_number,
                    $income->income_category,
                    $income->payer_name,
                    $income->description,
                    $income->amount,
                    $income->payment_method
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
