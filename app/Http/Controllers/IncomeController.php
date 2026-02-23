<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function index()
    {
        $schoolID = Session::get('schoolID');
        $incomes = Income::where('schoolID', $schoolID)->orderBy('date', 'desc')->get();
        
        // Calculate totals for dashboard cards
        $today = date('Y-m-d');
        $totalToday = Income::where('schoolID', $schoolID)->whereDate('date', $today)->sum('amount');
        $totalWeek = Income::where('schoolID', $schoolID)->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $totalMonth = Income::where('schoolID', $schoolID)->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $totalYear = Income::where('schoolID', $schoolID)->whereYear('date', now()->year)->sum('amount');

        return view('accountant.income.index', compact('incomes', 'totalToday', 'totalWeek', 'totalMonth', 'totalYear'));
    }

    public function create()
    {
        $schoolID = Session::get('schoolID');
        $categories = \App\Models\IncomeCategory::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('name')
            ->pluck('name');

        // Always include 'School Fees' as it's a core system category
        if (!$categories->contains('School Fees')) {
            $categories->prepend('School Fees');
        }

        if ($categories->count() <= 1 && $categories->first() === 'School Fees') { 
             // If only School Fees is present (meaning no DB categories), add other defaults
            $defaults = [
                'Tuition & Fees',
                'Boarding & Accommodation',
                'Co-Curricular & Extracurricular',
                'Fundraising & Donations',
                'Rentals & Leasing',
                'Sales & Services',
                'Investment & Interest Income',
                'Grants & Government Support',
                'Other Miscellaneous Income'
            ];
            $categories = $categories->merge($defaults)->unique();
        }
        
        $year = date('Y');
        $lastIncome = Income::where('schoolID', $schoolID)->latest()->first();
        $sequence = $lastIncome ? ($lastIncome->id + 1) : 1;
        $receiptPreview = "REC-{$year}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return view('accountant.income.create', compact('categories', 'receiptPreview'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'income_category' => 'required',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required',
        ]);

        $year = date('Y');
        $schoolID = Session::get('schoolID');
        $count = Income::where('schoolID', $schoolID)->whereYear('date', $year)->count() + 1;
        $receiptNumber = "REC-{$year}-" . str_pad($count, 3, '0', STR_PAD_LEFT);

        $income = new Income();
        $income->schoolID = Session::get('schoolID');
        $income->receipt_number = $receiptNumber;
        $income->date = $request->date;
        $income->income_category = $request->income_category;
        $income->description = $request->description;
        $income->amount = $request->amount;
        $income->payment_method = $request->payment_method;
        $income->payment_account = $request->payment_account;
        $income->payer_name = $request->payer_name;
        $income->entered_by = Auth::id() ?? 1;
        
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('income', 'public');
            $income->attachment = $path;
        }

        $income->save();

        return redirect()->route('accountant.income.index')->with('success', 'Income recorded successfully. Receipt: ' . $receiptNumber);
    }

    public function show($id)
    {
        $schoolID = Session::get('schoolID');
        $income = Income::where('schoolID', $schoolID)->with('enteredBy')->findOrFail($id);
        return view('accountant.income.show', compact('income'));
    }

    public function edit($id)
    {
        $schoolID = Session::get('schoolID');
        $income = Income::where('schoolID', $schoolID)->findOrFail($id);
        
        $categories = \App\Models\IncomeCategory::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('name')
            ->pluck('name');

        // Always include 'School Fees' as it's a core system category
        if (!$categories->contains('School Fees')) {
            $categories->prepend('School Fees');
        }

        if ($categories->count() <= 1 && $categories->first() === 'School Fees') {
             // If only School Fees is present (meaning no DB categories), add other defaults
            $defaults = [
                'Tuition & Fees',
                'Boarding & Accommodation',
                'Co-Curricular & Extracurricular',
                'Fundraising & Donations',
                'Rentals & Leasing',
                'Sales & Services',
                'Investment & Interest Income',
                'Grants & Government Support',
                'Other Miscellaneous Income'
            ];
            $categories = $categories->merge($defaults)->unique();
        }

        return view('accountant.income.edit', compact('income', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $schoolID = Session::get('schoolID');
        $income = Income::where('schoolID', $schoolID)->findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'income_category' => 'required',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required',
        ]);

        $income->date = $request->date;
        $income->income_category = $request->income_category;
        $income->description = $request->description;
        $income->amount = $request->amount;
        $income->payment_method = $request->payment_method;
        $income->payment_account = $request->payment_account;
        $income->payer_name = $request->payer_name;
        
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('income', 'public');
            $income->attachment = $path;
        }

        $income->save();

        return redirect()->route('accountant.income.index')->with('success', 'Income updated successfully.');
    }

    public function destroy($id)
    {
        $schoolID = Session::get('schoolID');
        $income = Income::where('schoolID', $schoolID)->findOrFail($id);
        $income->delete();

        return redirect()->route('accountant.income.index')->with('success', 'Income deleted successfully.');
    }
}
