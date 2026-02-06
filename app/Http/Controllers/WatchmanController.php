<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolVisitor;
use App\Models\User;
use App\Models\Watchman;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class WatchmanController extends Controller
{
    public function manage()
    {
        $userType = Session::get('user_type');
        if ($userType !== 'Admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');
        $watchmen = Watchman::where('schoolID', $schoolID)->orderBy('id', 'desc')->get();

        return view('Admin.manage_watchman', compact('watchmen'));
    }

    public function store(Request $request)
    {
        $schoolID = Session::get('schoolID');

        $validator = Validator::make($request->all(), [
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:watchmen,phone_number|unique:users,name',
            'email'        => 'nullable|email|max:255|unique:users,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $email = $request->email ?: ($request->phone_number . '@watchman.local');

        Watchman::create([
            'schoolID'     => $schoolID,
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'phone_number' => $request->phone_number,
            'email'        => $email,
            'status'       => 'Active',
        ]);

        User::create([
            'name'      => $request->phone_number,
            'email'     => $email,
            'password'  => Hash::make($request->last_name),
            'user_type' => 'Watchman',
        ]);

        try {
            $school = School::find($schoolID);
            $smsService = new SmsService();
            $phoneNumber = $request->phone_number;
            $schoolName = $school->school_name ?? 'School';
            $username = $request->phone_number;
            $password = $request->last_name;

            $message = "{$schoolName}. Usajili umekamilika. Username: {$username}. Password: {$password}. Asante";
            $smsResult = $smsService->sendSms($phoneNumber, $message);

            if ($smsResult['success']) {
                Log::info("SMS sent successfully to watchman: {$phoneNumber}");
            } else {
                Log::error("SMS sending failed: " . ($smsResult['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('SMS sending error: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Watchman registered successfully.');
    }

    public function dashboard()
    {
        $userType = Session::get('user_type');
        if ($userType !== 'Watchman') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return redirect()->route('watchman.visitors');
    }

    public function visitors()
    {
        $userType = Session::get('user_type');
        if ($userType !== 'Watchman') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return view('Admin.manage_school_visitors', ['watchmanOnly' => true]);
    }

    public function todayVisitors()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$userType || $userType !== 'Watchman' || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $today = Carbon::today()->toDateString();
        $visitors = SchoolVisitor::where('schoolID', $schoolID)
            ->whereDate('visit_date', $today)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $visitors->map(function ($visitor) {
                return [
                    'visitorID' => $visitor->visitorID,
                    'visitDate' => $visitor->visit_date ? $visitor->visit_date->format('Y-m-d') : null,
                    'name' => $visitor->name,
                    'contact' => $visitor->contact,
                    'occupation' => $visitor->occupation,
                    'reason' => $visitor->reason,
                    'signature' => $visitor->signature,
                ];
            }),
        ]);
    }

    public function storeVisitors(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$userType || $userType !== 'Watchman' || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $names = $request->input('name', []);
        $contacts = $request->input('contact', []);
        $occupations = $request->input('occupation', []);
        $reasons = $request->input('reason', []);
        $signatures = $request->input('signature', []);

        $today = Carbon::today()->toDateString();
        $rows = [];

        foreach ($names as $index => $name) {
            $trimmed = trim((string) $name);
            if ($trimmed === '') {
                continue;
            }
            $rows[] = [
                'schoolID' => $schoolID,
                'visit_date' => $today,
                'name' => $trimmed,
                'contact' => isset($contacts[$index]) ? trim((string) $contacts[$index]) : null,
                'occupation' => isset($occupations[$index]) ? trim((string) $occupations[$index]) : null,
                'reason' => isset($reasons[$index]) ? trim((string) $reasons[$index]) : null,
                'signature' => isset($signatures[$index]) ? $signatures[$index] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($rows)) {
            return response()->json(['success' => false, 'message' => 'Please enter at least one visitor.'], 422);
        }

        DB::table('school_visitors')->insert($rows);

        $smsStatusMessage = null;
        try {
            $schoolPhone = DB::table('schools')->where('schoolID', $schoolID)->value('phone');
            if ($schoolPhone) {
                $smsService = new SmsService();
                $allSent = true;
                foreach ($rows as $row) {
                    $message = "Mgeni mpya ameingia Shuleni jina: {$row['name']}, lengo: " . ($row['reason'] ?? 'N/A') . ", simu: " . ($row['contact'] ?? 'N/A');
                    $smsResult = $smsService->sendSms($schoolPhone, $message);
                    if (!$smsResult['success']) {
                        $allSent = false;
                        Log::error("Visitor SMS failed: " . ($smsResult['message'] ?? 'Unknown error'));
                    }
                }
                if (!$allSent) {
                    $smsStatusMessage = 'SMS haikutumwa kwa baadhi ya wageni.';
                } else {
                    $smsStatusMessage = 'SMS imetumwa kwa namba ya shule: ' . $schoolPhone;
                }
            } else {
                $smsStatusMessage = 'SMS haikutumwa kwa sababu namba ya simu ya shule haipo kwenye jedwali la shule.';
                Log::warning('Visitor SMS skipped: school phone missing.');
            }
        } catch (\Exception $e) {
            Log::error('Visitor SMS error: ' . $e->getMessage());
            $smsStatusMessage = 'SMS haikutumwa kutokana na hitilafu ya mfumo.';
        }

        $message = 'Visitors recorded successfully.';
        if ($smsStatusMessage) {
            $message .= ' ' . $smsStatusMessage;
        }

        return response()->json(['success' => true, 'message' => $message]);
    }
}
