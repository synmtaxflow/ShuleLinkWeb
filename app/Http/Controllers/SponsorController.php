<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SponsorController extends Controller
{
    public function index()
    {
        $schoolID = Session::get('schoolID');
        $user_type = Session::get('user_type');
        
        $sponsors = Sponsor::where('schoolID', $schoolID)->get();
        
        return view('Admin.manage_sponsors', compact('sponsors', 'user_type'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sponsor_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $schoolID = Session::get('schoolID');
        
        // Prepend 255 to phone number if provided
        $phone = $request->phone;
        if ($phone && !str_starts_with($phone, '255')) {
            $phone = '255' . $phone;
        }

        $sponsor = Sponsor::create([
            'schoolID' => $schoolID,
            'sponsor_name' => $request->sponsor_name,
            'description' => $request->description,
            'contact_person' => $request->contact_person,
            'phone' => $phone,
            'email' => $request->email,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsor registered successfully',
            'sponsor' => $sponsor
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sponsor_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $schoolID = Session::get('schoolID');
        $sponsor = Sponsor::where('sponsorID', $id)->where('schoolID', $schoolID)->first();

        if (!$sponsor) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsor not found'
            ], 404);
        }

        // Prepend 255 to phone number if provided
        $phone = $request->phone;
        if ($phone && !str_starts_with($phone, '255')) {
            $phone = '255' . $phone;
        }

        $sponsor->update([
            'sponsor_name' => $request->sponsor_name,
            'description' => $request->description,
            'contact_person' => $request->contact_person,
            'phone' => $phone,
            'email' => $request->email,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsor updated successfully',
            'sponsor' => $sponsor
        ]);
    }

    public function destroy($id)
    {
        $schoolID = Session::get('schoolID');
        $sponsor = Sponsor::where('sponsorID', $id)->where('schoolID', $schoolID)->first();

        if (!$sponsor) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsor not found'
            ], 404);
        }

        $sponsor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sponsor deleted successfully'
        ]);
    }

    public function getSponsors()
    {
        $schoolID = Session::get('schoolID');
        $sponsors = Sponsor::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get();

        return response()->json([
            'success' => true,
            'sponsors' => $sponsors
        ]);
    }
}
