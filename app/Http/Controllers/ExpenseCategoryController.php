<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $schoolID = Session::get('schoolID');
        $categories = ExpenseCategory::where('schoolID', $schoolID)
            ->orderBy('status', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        return view('accountant.expense_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $request->merge(['schoolID' => $schoolID]);

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Enforce uniqueness per school manually to control message
        $exists = ExpenseCategory::where('schoolID', $schoolID)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Category name already exists for this school.')->withInput();
        }

        ExpenseCategory::create([
            'schoolID' => $schoolID,
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'Active',
        ]);

        return redirect()->route('accountant.expense_categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $schoolID = Session::get('schoolID');
        $category = ExpenseCategory::where('schoolID', $schoolID)->findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $exists = ExpenseCategory::where('schoolID', $schoolID)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->where('expense_categoryID', '!=', $id)
            ->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Category name already exists for this school.')->withInput();
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('accountant.expense_categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $schoolID = Session::get('schoolID');
        $category = ExpenseCategory::where('schoolID', $schoolID)->findOrFail($id);

        // Soft guard: if linked in budgets/expenses, prefer Inactive instead of delete
        $inUse = \App\Models\Expense::where('schoolID', $schoolID)
            ->where('expense_category', $category->name)
            ->exists()
            || \App\Models\Budget::where('schoolID', $schoolID)
            ->where('budget_category', $category->name)
            ->exists();

        if ($inUse) {
            $category->update(['status' => 'Inactive']);
            return redirect()->route('accountant.expense_categories.index')->with('success', 'Category in use; marked as Inactive.');
        }

        $category->delete();
        return redirect()->route('accountant.expense_categories.index')->with('success', 'Category deleted successfully.');
    }
}
