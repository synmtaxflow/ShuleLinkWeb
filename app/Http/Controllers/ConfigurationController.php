<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        return view('configuration');
    }

   public function storeSchool(Request $request)
{
    //check if username exist??
    $check_username = User::where('name',$request->registration_number)->first();
    //check if Email exist??
    $check_email = User::where('email',$request->email)->first();

    if($check_username){
        return redirect()->back()->with('error','Registration number already exist');
    }
     if($check_email){
        return redirect()->back()->with('error','Email  already exist');
    }
    $validated = $request->validate([
        'school_name' => 'required|string|max:150',
        'registration_number' => 'required|string|max:50|unique:schools,registration_number',
        'school_type' => 'required|in:Primary,Secondary',
        'ownership' => 'required|in:Public,Private',
        'region' => 'required|string|max:100',
        'district' => 'required|string|max:100',
        'ward' => 'nullable|string|max:100',
        'village' => 'nullable|string|max:100',
        'address' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:100|unique:schools,email',
        'phone' => 'nullable|string|max:20|unique:schools,phone',
        'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
        'school_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'status' => 'required|in:Active,Inactive',
    ]);
    $password = 'Admin@3345';
    if ($request->hasFile('school_logo')) {
        // Determine upload path - Prioritize public_html for cPanel
        $basePath = base_path();
        $parentDir = dirname($basePath);
        $publicHtmlPath = $parentDir . '/public_html/logos';
        $docRootPath = $_SERVER['DOCUMENT_ROOT'] . '/logos';
        $localPublicPath = public_path('logos');

        if (file_exists($parentDir . '/public_html')) {
            $uploadPath = $publicHtmlPath;
        } elseif (strpos($_SERVER['DOCUMENT_ROOT'], 'public_html') !== false) {
            $uploadPath = $docRootPath;
        } else {
            $uploadPath = $localPublicPath;
        }

        if (!file_exists($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        $logo = $request->file('school_logo');
        $filename = time() . '_' . $logo->getClientOriginalName();
        $logo->move($uploadPath, $filename);
        $validated['school_logo'] = 'logos/' . $filename;
    }

    School::create($validated);
    User::create([
        'name' => $request->registration_number,
        'email' => $request->email,
        'password' => $password,
        'user_type' => 'Admin'
    ]);

    return redirect()->back()->with('success', 'School saved successfully.');
}


}


