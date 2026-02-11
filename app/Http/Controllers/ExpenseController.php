<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::orderBy('date', 'desc')->get();
        // Calculate totals for dashboard cards
        $today = date('Y-m-d');
        $totalToday = Expense::whereDate('date', $today)->sum('amount');
        $totalWeek = Expense::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $totalMonth = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $totalYear = Expense::whereYear('date', now()->year)->sum('amount');

        return view('accountant.expenses.index', compact('expenses', 'totalToday', 'totalWeek', 'totalMonth', 'totalYear'));
    }

    public function create()
    {
        $schoolID = Session::get('schoolID');
        $categoryNames = [];
        try {
            if (DB::getSchemaBuilder()->hasTable('expense_categories')) {
                $categoryNames = DB::table('expense_categories')
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->orderBy('name')
                    ->pluck('name')
                    ->toArray();
            }
        } catch (\Exception $e) {
            // ignore and fallback below
        }
        // Fallback ONLY if table exists and has no data
        if (DB::getSchemaBuilder()->hasTable('expense_categories') && empty($categoryNames)) {
            $categoryNames = [
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

        $year = date('Y');
        $categories = [];
        foreach ($categoryNames as $name) {
            $budget = \App\Models\Budget::where('schoolID', $schoolID)
                ->where('budget_category', $name)
                ->where('fiscal_year', $year)
                ->where('status', 'Active')
                ->first();

            $categories[] = [
                'name' => $name,
                'remaining' => $budget ? $budget->remaining_amount : 0
            ];
        }

        $lastExpense = Expense::latest()->first();
        $sequence = $lastExpense ? ($lastExpense->id + 1) : 1;
        $voucherPreview = "PCV-{$year}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        return view('accountant.expenses.create', compact('categories', 'voucherPreview'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'voucher_type' => 'required',
            'expense_category' => 'required',
            'amount' => 'required|numeric|min:0',
            'payment_account' => 'required',
        ]);

        $year = date('Y', strtotime($request->date));
        $schoolID = Session::get('schoolID');
        $category = $request->expense_category;
        $amount = $request->amount;

        // Check for active budget for this category and year
        $budget = \App\Models\Budget::where('schoolID', $schoolID)
            ->where('budget_category', $category)
            ->where('fiscal_year', $year)
            ->where('status', 'Active')
            ->first();

        if (!$budget) {
            return redirect()->back()->withInput()->withErrors(['expense_category' => 'No budget defined for this category and year. Please define a budget first.']);
        }
        if ($budget->remaining_amount < $amount) {
            return redirect()->back()->withInput()->withErrors(['amount' => 'Insufficient budget for this category. Remaining: ' . number_format($budget->remaining_amount, 2) . ' TZS']);
        }

        // Simple sequence generation based on count + 1 for now.
        $count = Expense::whereYear('date', $year)->count() + 1;
        $prefix = $request->voucher_type == 'Petty Cash Voucher' ? 'PCV' : 'PV';
        $voucherNumber = "{$prefix}-{$year}-" . str_pad($count, 3, '0', STR_PAD_LEFT);

        $expense = new Expense();
        $expense->schoolID = $schoolID;
        $expense->voucher_number = $voucherNumber;
        $expense->date = $request->date;
        $expense->voucher_type = $request->voucher_type;
        $expense->expense_category = $category;
        $expense->description = $request->description;
        $expense->amount = $amount;
        $expense->payment_account = $request->payment_account;
        $expense->entered_by = Auth::id() ?? 1; // Fallback to 1 if not logged in (testing)
        $expense->status = 'Pending';

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('expenses', 'public');
            $expense->attachment = $path;
        }

        $expense->save();

        return redirect()->route('accountant.expenses.index')->with('success', 'Expense recorded successfully. Voucher: ' . $voucherNumber);
    }

    public function show($id)
    {
        $expense = Expense::with(['enteredBy', 'approvedBy'])->findOrFail($id);
        return view('accountant.expenses.show', compact('expense'));
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $schoolID = Session::get('schoolID');
        $categories = [];
        try {
            if (DB::getSchemaBuilder()->hasTable('expense_categories')) {
                $categories = DB::table('expense_categories')
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->orderBy('name')
                    ->pluck('name')
                    ->toArray();
            }
        } catch (\Exception $e) {
            // ignore and fallback
        }
        if (DB::getSchemaBuilder()->hasTable('expense_categories') && empty($categories)) {
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
        return view('accountant.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'voucher_type' => 'required',
            'expense_category' => 'required',
            'amount' => 'required|numeric|min:0',
            'payment_account' => 'required',
        ]);

        $expense->date = $request->date;
        $expense->voucher_type = $request->voucher_type;
        $expense->expense_category = $request->expense_category;
        $expense->description = $request->description;
        $expense->amount = $request->amount;
        $expense->payment_account = $request->payment_account;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('expenses', 'public');
            $expense->attachment = $path;
        }

        $expense->save();

        return redirect()->route('accountant.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('accountant.expenses.index')->with('success', 'Expense deleted successfully.');
    }

    public function approve($id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'Pending') {
            return redirect()->back()->with('error', 'This expense is already ' . $expense->status);
        }

        DB::beginTransaction();
        try {
            $expense->status = 'Approved';
            $expense->approved_by = Auth::id() ?? 1;
            $expense->save();

            // Update Budget
            $year = date('Y', strtotime($expense->date));
            $budget = \App\Models\Budget::where('budget_category', $expense->expense_category)
                ->where('fiscal_year', $year)
                ->where('schoolID', Session::get('schoolID'))
                ->first();

            if ($budget) {
                $budget->spent_amount += $expense->amount;
                $budget->remaining_amount = $budget->allocated_amount - $budget->spent_amount;
                $budget->save();
            }

            DB::commit();
            return redirect()->route('accountant.expenses.index')->with('success', 'Expense approved successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error approving expense: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'Pending') {
            return redirect()->back()->with('error', 'This expense is already ' . $expense->status);
        }

        $expense->status = 'Rejected';
        $expense->approved_by = Auth::id() ?? 1;
        $expense->save();

        return redirect()->route('accountant.expenses.index')->with('success', 'Expense rejected.');
    }
}
