<?php

namespace App\Http\Controllers;

use App\Models\OtherStaff;
use App\Models\StaffPermission;
use App\Models\StaffProfession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class StaffController extends Controller
{
    private function getStaffContext()
    {
        $userType = Session::get('user_type');
        if (!$userType || $userType !== 'Staff') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $staffID = Session::get('staffID');
        $staff = $staffID ? OtherStaff::find($staffID) : null;
        $userID = Session::get('userID');
        $user = $userID ? \App\Models\User::find($userID) : null;

        if (!$staff) {
            Session::flush();
            return redirect()->route('login')->with('error', 'Staff account not found.');
        }

        $staffProfession = $staff->profession_id
            ? StaffProfession::find($staff->profession_id)
            : null;

        $staffPermissionsByCategory = collect();
        if ($staff->profession_id) {
            $staffPermissionsByCategory = StaffPermission::where('profession_id', $staff->profession_id)
                ->get()
                ->groupBy('permission_category');
        }

        return [
            'staff' => $staff,
            'user' => $user,
            'staffProfession' => $staffProfession,
            'staffPermissionsByCategory' => $staffPermissionsByCategory,
        ];
    }

    public function staffDashboard()
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Staff.dashboard', $context);
    }

    public function suggestions()
    {
        return $this->manageStaffFeedback(request());
    }

    public function incidents()
    {
        return $this->manageStaffFeedback(request());
    }

    public function permissions()
    {
        return $this->manageStaffPermissions(request());
    }

    public function leave()
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Staff.leave', $context);
    }

    public function payroll()
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Staff.payroll', $context);
    }

    public function manageStaffPermissions(Request $request)
    {
        $user = Session::get('user_type');
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Staff' || !$staffID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $activeTab = $request->get('tab', 'request');

        $permissions = \App\Models\PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'staff')
            ->where('staffID', $staffID)
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingPermissions = $permissions->where('status', 'pending');
        $approvedPermissions = $permissions->where('status', 'approved');
        $rejectedPermissions = $permissions->where('status', 'rejected');

        if (in_array($activeTab, ['pending', 'approved', 'rejected'], true)) {
            \App\Models\PermissionRequest::where('schoolID', $schoolID)
                ->where('requester_type', 'staff')
                ->where('staffID', $staffID)
                ->where('status', $activeTab)
                ->update(['is_read_by_requester' => true]);
        }

        $unreadPendingCount = \App\Models\PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'staff')
            ->where('staffID', $staffID)
            ->where('status', 'pending')
            ->where('is_read_by_requester', false)
            ->count();
        $unreadApprovedCount = \App\Models\PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'staff')
            ->where('staffID', $staffID)
            ->where('status', 'approved')
            ->where('is_read_by_requester', false)
            ->count();
        $unreadRejectedCount = \App\Models\PermissionRequest::where('schoolID', $schoolID)
            ->where('requester_type', 'staff')
            ->where('staffID', $staffID)
            ->where('status', 'rejected')
            ->where('is_read_by_requester', false)
            ->count();

        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Teacher.manage_permissions', array_merge($context, [
            'activeTab' => $activeTab,
            'pendingPermissions' => $pendingPermissions,
            'approvedPermissions' => $approvedPermissions,
            'rejectedPermissions' => $rejectedPermissions,
            'unreadPendingCount' => $unreadPendingCount,
            'unreadApprovedCount' => $unreadApprovedCount,
            'unreadRejectedCount' => $unreadRejectedCount,
            'permissionContext' => 'staff',
        ]));
    }

    public function storeStaffPermission(Request $request)
    {
        $user = Session::get('user_type');
        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$user || $user !== 'Staff' || !$staffID || !$schoolID) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'time_mode' => 'required|in:days,hours',
            'days_count' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'reason_type' => 'required|in:medical,official,professional,emergency,other',
            'reason_description' => 'required|string|min:5',
        ]);

        if ($validated['time_mode'] === 'days') {
            if (empty($validated['days_count']) || empty($validated['start_date']) || empty($validated['end_date'])) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please provide days count and date range.'], 422);
                }
                return redirect()->back()->with('error', 'Please provide days count and date range.')->withInput();
            }
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($validated['end_date']);
            $daysCount = $startDate->diffInDays($endDate) + 1;
            if ($daysCount > 7) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Days exceed limit. Use 7 days or less.'], 422);
                }
                return redirect()->back()->with('error', 'Days exceed limit. Use 7 days or less.')->withInput();
            }
        }

        if ($validated['time_mode'] === 'hours') {
            if (empty($validated['start_time']) || empty($validated['end_time'])) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please provide start and end time.'], 422);
                }
                return redirect()->back()->with('error', 'Please provide start and end time.')->withInput();
            }
            $start = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
            $end = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
            if ($end->lessThanOrEqualTo($start)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'End time must be after start time.'], 422);
                }
                return redirect()->back()->with('error', 'End time must be after start time.')->withInput();
            }
        }

        $computedDays = null;
        if ($validated['time_mode'] === 'days') {
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($validated['end_date']);
            $computedDays = $startDate->diffInDays($endDate) + 1;
        }

        \App\Models\PermissionRequest::create([
            'schoolID' => $schoolID,
            'requester_type' => 'staff',
            'staffID' => $staffID,
            'time_mode' => $validated['time_mode'],
            'days_count' => $validated['time_mode'] === 'days' ? $computedDays : null,
            'start_date' => $validated['time_mode'] === 'days' ? $validated['start_date'] : null,
            'end_date' => $validated['time_mode'] === 'days' ? $validated['end_date'] : null,
            'start_time' => $validated['time_mode'] === 'hours' ? $validated['start_time'] : null,
            'end_time' => $validated['time_mode'] === 'hours' ? $validated['end_time'] : null,
            'reason_type' => $validated['reason_type'],
            'reason_description' => $validated['reason_description'],
            'status' => 'pending',
            'is_read_by_admin' => false,
            'is_read_by_requester' => true,
        ]);

        $staff = \App\Models\OtherStaff::where('id', $staffID)->first();
        $school = \App\Models\School::where('schoolID', $schoolID)->first();
        $smsService = new \App\Services\SmsService();

        if ($staff && $staff->phone_number) {
            $smsService->sendSms($staff->phone_number, 'Your permission request has been submitted to Admin. Please wait for approval.');
        }

        if ($school && $school->phone) {
            $staffName = $staff ? trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')) : 'Staff';
            $reasonLabel = $validated['reason_description'];
            $schoolName = $school->school_name ?? 'School';
            $periodLabel = 'N/A';
            if ($validated['time_mode'] === 'days') {
                $periodLabel = ($validated['start_date'] ?? '') . ' to ' . ($validated['end_date'] ?? '');
            } else {
                $periodLabel = ($validated['start_time'] ?? '') . ' to ' . ($validated['end_time'] ?? '');
            }
            $smsService->sendSms($school->phone, "{$schoolName}: New staff permission request by {$staffName}. Period: {$periodLabel}. Reason: {$reasonLabel}. Please review.");
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Permission request submitted successfully.']);
        }
        return redirect()->route('staff.permissions')->with('success', 'Permission request submitted successfully.');
    }

    public function manageStaffFeedback(Request $request)
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Staff') {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$staffID || !$schoolID) {
            return redirect()->route('login')->with('error', 'Session expired');
        }

        $tab = $request->query('tab');
        $section = $request->query('section', 'send');
        if (!$tab) {
            $routeName = optional($request->route())->getName();
            $tab = $routeName === 'staff.incidents' ? 'incidents' : 'suggestions';
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $feedbackQuery = \App\Models\StaffFeedback::where('staffID', $staffID)
            ->where('schoolID', $schoolID);

        if ($dateFrom) {
            $feedbackQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $feedbackQuery->whereDate('created_at', '<=', $dateTo);
        }

        $feedback = $feedbackQuery->orderBy('created_at', 'desc')->get();

        $suggestions = $feedback->where('type', 'suggestion')->values();
        $incidents = $feedback->where('type', 'incident')->values();

        $suggestionStats = [
            'total' => $suggestions->count(),
            'pending' => $suggestions->where('status', 'pending')->count(),
            'approved' => $suggestions->where('status', 'approved')->count(),
            'rejected' => $suggestions->where('status', 'rejected')->count(),
        ];

        $incidentStats = [
            'total' => $incidents->count(),
            'pending' => $incidents->where('status', 'pending')->count(),
            'approved' => $incidents->where('status', 'approved')->count(),
            'rejected' => $incidents->where('status', 'rejected')->count(),
        ];

        $readType = $tab === 'incidents' ? 'incident' : 'suggestion';
        \App\Models\StaffFeedback::where('staffID', $staffID)
            ->where('schoolID', $schoolID)
            ->where('type', $readType)
            ->update(['is_read_by_staff' => true]);

        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Teacher.manage_feedback', array_merge($context, [
            'activeTab' => $tab,
            'activeSection' => $section,
            'suggestions' => $suggestions,
            'incidents' => $incidents,
            'suggestionStats' => $suggestionStats,
            'incidentStats' => $incidentStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'feedbackContext' => 'staff',
        ]));
    }

    public function storeStaffFeedback(Request $request)
    {
        $user = Session::get('user_type');
        if (!$user || $user !== 'Staff') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $staffID = Session::get('staffID');
        $schoolID = Session::get('schoolID');

        if (!$staffID || !$schoolID) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired'], 403);
            }
            return redirect()->route('login')->with('error', 'Session expired');
        }

        $validated = $request->validate([
            'type' => 'required|in:suggestion,incident',
            'message' => 'required|string',
        ]);

        \App\Models\StaffFeedback::create([
            'schoolID' => $schoolID,
            'staffID' => $staffID,
            'type' => $validated['type'],
            'message' => $validated['message'],
            'status' => 'pending',
            'is_read_by_admin' => false,
            'is_read_by_staff' => true,
        ]);

        $staff = \App\Models\OtherStaff::where('id', $staffID)->first();
        $school = \App\Models\School::where('schoolID', $schoolID)->first();
        $adminPhone = $school->phone ?? null;

        if ($adminPhone) {
            $typeLabel = $validated['type'] === 'incident' ? 'Incident' : 'Suggestion';
            $staffName = $staff ? trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')) : 'Staff';
            $message = "New {$typeLabel} from {$staffName}. Please review in the system.";
            $smsService = new \App\Services\SmsService();
            $smsService->sendSms($adminPhone, $message);
        }

        $routeName = $validated['type'] === 'incident' ? 'staff.incidents' : 'staff.suggestions';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Message sent successfully.']);
        }

        return redirect()->route($routeName)->with('success', 'Message sent successfully.');
    }

    public function profile()
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('Staff.profile', $context);
    }

    public function updateProfile(Request $request)
    {
        $context = $this->getStaffContext();
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        /** @var \App\Models\OtherStaff $staff */
        $staff = $context['staff'];
        /** @var \App\Models\User|null $user */
        $user = $context['user'];

        $request->validate([
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'current_password' => 'nullable|string|min:4',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->filled('new_password')) {
            if (!$user || !$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->with('error', 'Current password is incorrect.');
            }
            $user->password = Hash::make($request->new_password);
            $user->save();
        }

        if ($request->hasFile('profile_image')) {
            $uploadPath = public_path('userImages');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            if ($staff->image && file_exists($uploadPath . '/' . $staff->image)) {
                @unlink($uploadPath . '/' . $staff->image);
            }

            $imageName = time() . '_' . $request->file('profile_image')->getClientOriginalName();
            $request->file('profile_image')->move($uploadPath, $imageName);
            $staff->image = $imageName;
            $staff->save();
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
