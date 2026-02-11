<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::orderBy('fiscal_year', 'desc')->get();

        // Calculate summary statistics
        $currentYear = date('Y');
        $totalAllocated = Budget::where('fiscal_year', $currentYear)->sum('allocated_amount');
        $totalSpent = Budget::where('fiscal_year', $currentYear)->sum('spent_amount');
        $totalRemaining = Budget::where('fiscal_year', $currentYear)->sum('remaining_amount');

        // Get active budgets
        $activeBudgets = Budget::where('status', 'Active')->count();

        return view('accountant.budget.index', compact('budgets', 'totalAllocated', 'totalSpent', 'totalRemaining', 'activeBudgets'));
    }

    public function create()
    {
        // Load dynamic categories from expense_categories table for this school
        $schoolID = Session::get('schoolID');
        $categories = [];
        try {
            $categories = \App\Models\ExpenseCategory::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
        } catch (\Exception $e) {
            // ignore and fallback
        }
        if (\App\Models\ExpenseCategory::where('schoolID', $schoolID)->count() > 0 && empty($categories)) {
            $categories = [
                'Academic & Learning Expenses',
                'Personnel & Staff Costs',
                'Administration & Office Expenses',
                'Utilities',
                'Maintenance & Repairs',
                'Transport & Logistics',
                'Cleaning & Sanitation',
                'Boarding & Catering',
                'Security & Safety',
                'Co-curricular & Student Welfare',
                'Marketing & Admissions',
                'Regulatory & Compliance Costs'
            ];
        }
        return view('accountant.budget.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'budget_category' => 'required',
            'fiscal_year' => 'required',
            'allocated_amount' => 'required|numeric|min:0',
        ]);

        $budget = new Budget();
        $budget->schoolID = Session::get('schoolID');
        $budget->budget_category = $request->budget_category;
        $budget->fiscal_year = $request->fiscal_year;
        $budget->period = $request->period;
        $budget->allocated_amount = $request->allocated_amount;
        $budget->spent_amount = 0;
        $budget->remaining_amount = $request->allocated_amount;
        $budget->notes = $request->notes;
        $budget->created_by = Auth::id() ?? 1;
        $budget->status = 'Active';

        $budget->save();

        return redirect()->route('accountant.budget.index')->with('success', 'Budget created successfully.');
    }

    public function show($id)
    {
        $budget = Budget::with('createdBy')->findOrFail($id);
        // Get expenses related to this budget category and fiscal year
        $expenses = Expense::where('expense_category', $budget->budget_category)
            ->whereYear('date', $budget->fiscal_year)
            ->where('status', 'Approved')
            ->orderBy('date', 'desc')
            ->get();

        return view('accountant.budget.show', compact('budget', 'expenses'));
    }

    public function edit($id)
    {
        $budget = Budget::findOrFail($id);

        // Load dynamic categories from expense_categories table for this school
        $schoolID = Session::get('schoolID');
        $categories = [];
        try {
            $categories = \App\Models\ExpenseCategory::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
        } catch (\Exception $e) {
            // ignore and fallback
        }
        if (\App\Models\ExpenseCategory::where('schoolID', $schoolID)->count() > 0 && empty($categories)) {
            $categories = [
                'Academic & Learning Expenses',
                'Personnel & Staff Costs',
                'Administration & Office Expenses',
                'Utilities',
                'Maintenance & Repairs',
                'Transport & Logistics',
                'Cleaning & Sanitation',
                'Boarding & Catering',
                'Security & Safety',
                'Co-curricular & Student Welfare',
                'Marketing & Admissions',
                'Regulatory & Compliance Costs'
            ];
        }
        return view('accountant.budget.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);

        $request->validate([
            'budget_category' => 'required',
            'fiscal_year' => 'required',
            'allocated_amount' => 'required|numeric|min:0',
        ]);

        $budget->budget_category = $request->budget_category;
        $budget->fiscal_year = $request->fiscal_year;
        $budget->period = $request->period;
        $budget->allocated_amount = $request->allocated_amount;
        $budget->remaining_amount = $request->allocated_amount - $budget->spent_amount;
        $budget->notes = $request->notes;
        $budget->status = $request->status ?? 'Active';

        $budget->save();

        return redirect()->route('accountant.budget.index')->with('success', 'Budget updated successfully.');
    }

    public function destroy($id)
    {
        $budget = Budget::findOrFail($id);
        $budget->delete();

        return redirect()->route('accountant.budget.index')->with('success', 'Budget deleted successfully.');
    }
}
