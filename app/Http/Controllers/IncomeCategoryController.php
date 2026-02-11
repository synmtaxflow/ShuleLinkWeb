<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IncomeCategory;
use Session;
use Auth;

class IncomeCategoryController extends Controller
{
    public function index()
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $categories = IncomeCategory::where('schoolID', $schoolID)
            ->where('status', '!=', 'Deleted')
            ->orderBy('name')
            ->get();

        return view('accountant.income.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $schoolID = Session::get('schoolID');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $exists = IncomeCategory::where('schoolID', $schoolID)
            ->where('name', $request->name)
            ->where('status', '!=', 'Deleted')
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Income Category already exists!');
        }

        $category = new IncomeCategory();
        $category->schoolID = $schoolID;
        $category->name = $request->name;
        $category->description = $request->description;
        $category->status = 'Active';
        $category->save();

        return redirect()->back()->with('success', 'Income Category added successfully!');
    }

    public function update(Request $request, $id)
    {
        $schoolID = Session::get('schoolID');
        
        $category = IncomeCategory::where('id', $id)->where('schoolID', $schoolID)->first();
        if (!$category) {
            return redirect()->back()->with('error', 'Category not found!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive'
        ]);

        $category->name = $request->name;
        $category->description = $request->description;
        $category->status = $request->status;
        $category->save();

        return redirect()->back()->with('success', 'Income Category updated successfully!');
    }

    public function destroy($id)
    {
        $schoolID = Session::get('schoolID');
        
        $category = IncomeCategory::where('id', $id)->where('schoolID', $schoolID)->first();
        if (!$category) {
            return redirect()->back()->with('error', 'Category not found!');
        }

        // Soft delete by changing status
        $category->status = 'Deleted';
        $category->save();

        return redirect()->back()->with('success', 'Income Category deleted successfully!');
    }
}
