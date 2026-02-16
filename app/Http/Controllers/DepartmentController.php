<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\DepartmentMember;
use App\Models\User;
use App\Services\SmsService;
use App\Services\SgpmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DepartmentController extends Controller
{
    protected $smsService;
    protected $notificationService;

    public function __construct()
    {
        $this->smsService = new SmsService();
        $this->notificationService = new SgpmNotificationService();
    }

    public function index()
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Please login again.');
        }

        $userType = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');

        $query = Department::where('schoolID', $schoolID)->with(['headTeacher', 'headStaff', 'members']);

        // Security: If not Admin, only show departments where user is HOD
        if ($userType !== 'Admin') {
            $query->where(function($q) use ($teacherID, $staffID) {
                if ($teacherID) $q->orWhere('head_teacherID', $teacherID);
                if ($staffID) $q->orWhere('head_staffID', $staffID);
            });
        }

        $departments = $query->get();
        $teachers = Teacher::where('schoolID', $schoolID)->where('status', 'Active')->get();
        $staff = OtherStaff::where('schoolID', $schoolID)->where('status', 'Active')->get();

        return view('sgpm.departments.index', compact('departments', 'teachers', 'staff'));
    }

    public function store(Request $request)
    {
        $schoolID = Session::get('schoolID');
        
        $request->validate([
            'department_name' => 'required|string|max:255',
            'type' => 'required|in:Academic,Administrative',
            'head_id' => 'nullable',
        ]);

        $dept = new Department();
        $dept->schoolID = $schoolID;
        $dept->department_name = $request->department_name;
        $dept->type = $request->type;

        $head = null;
        $dept->head_teacherID = null;
        $dept->head_staffID = null;

        if ($request->head_id) {
            if (str_contains($request->head_id, 't_')) {
                $teacherID = str_replace('t_', '', $request->head_id);
                $dept->head_teacherID = $teacherID;
                $head = Teacher::find($teacherID);
            } elseif (str_contains($request->head_id, 's_')) {
                $staffID = str_replace('s_', '', $request->head_id);
                $dept->head_staffID = $staffID;
                $head = OtherStaff::find($staffID);
            } else {
                // Fallback for legacy data or if no prefix
                if ($request->type == 'Academic') {
                    $dept->head_teacherID = $request->head_id;
                    $head = Teacher::find($request->head_id);
                } else {
                    $dept->head_staffID = $request->head_id;
                    $head = OtherStaff::find($request->head_id);
                }
            }
        }

        $dept->save();

        // Notify the new HoD
        if ($head) {
            $hodUser = User::where('name', $head->employee_number)->first();
            if ($hodUser) {
                $msg = "Habari {$head->first_name}, umeteuliwa kuwa Mkuu wa Idara (HoD) ya {$dept->department_name}. Kagua shuleXpert kwa maelezo.";
                $this->notificationService->notify(
                    $hodUser->id,
                    'HOD Appointment',
                    $msg,
                    route('sgpm.departments.index'),
                    'Assignment'
                );
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Department created and HoD notified!']);
        }

        return redirect()->back()->with('success', 'Department created successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            'type' => 'required|in:Academic,Administrative',
            'head_id' => 'nullable',
        ]);

        $dept = Department::findOrFail($id);
        $oldHeadID = ($dept->head_teacherID) ? 't_'.$dept->head_teacherID : (($dept->head_staffID) ? 's_'.$dept->head_staffID : null);
        
        $dept->department_name = $request->department_name;
        $dept->type = $request->type;

        $head = null;
        $dept->head_teacherID = null;
        $dept->head_staffID = null;

        if ($request->head_id) {
            if (str_contains($request->head_id, 't_')) {
                $teacherID = str_replace('t_', '', $request->head_id);
                $dept->head_teacherID = $teacherID;
                $head = Teacher::find($teacherID);
            } elseif (str_contains($request->head_id, 's_')) {
                $staffID = str_replace('s_', '', $request->head_id);
                $dept->head_staffID = $staffID;
                $head = OtherStaff::find($staffID);
            } else {
                if ($request->type == 'Academic') {
                    $dept->head_teacherID = $request->head_id;
                    $head = Teacher::find($request->head_id);
                } else {
                    $dept->head_staffID = $request->head_id;
                    $head = OtherStaff::find($request->head_id);
                }
            }
        }

        $dept->save();

        // Notify if head changed
        if ($request->head_id != $oldHeadID && $head) {
            $hodUser = User::where('name', $head->employee_number)->first();
            if ($hodUser) {
                $msg = "Habari {$head->first_name}, maelezo yako ya Ukuu wa Idara ya {$dept->department_name} yamehuishwa. Kagua shuleXpert.";
                $this->notificationService->notify(
                    $hodUser->id,
                    'HOD Assignment Updated',
                    $msg,
                    route('sgpm.departments.index'),
                    'Assignment'
                );
            }
        }


        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Department updated successfully!']);
        }

        return redirect()->back()->with('success', 'Department updated successfully!');
    }

    public function destroy($id)
    {
        $dept = Department::findOrFail($id);
        $dept->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Department deleted successfully!']);
        }

        return redirect()->back()->with('success', 'Department deleted successfully!');
    }

    public function manageMembers($id)
    {
        $dept = Department::with(['members.teacher', 'members.staff'])->findOrFail($id);
        $schoolID = Session::get('schoolID');
        
        $teachers = Teacher::where('schoolID', $schoolID)->where('status', 'Active')->get();
        $staff = OtherStaff::where('schoolID', $schoolID)->where('status', 'Active')->get();
        
        return response()->json([
            'success' => true,
            'html' => view('sgpm.departments.members_partial', compact('dept', 'teachers', 'staff'))->render()
        ]);
    }

    public function addMember(Request $request, $id)
    {
        if (Session::get('user_type') !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized! Only Admins can assign members.'], 403);
        }

        $request->validate([
            'members' => 'required|array',
        ]);

        foreach ($request->members as $member_id) {
            $member = null;
            if (str_contains($member_id, 't_')) {
                $teacherID = str_replace('t_', '', $member_id);
                $exists = DepartmentMember::where('departmentID', $id)->where('teacherID', $teacherID)->exists();
                if (!$exists) {
                    DepartmentMember::create(['departmentID' => $id, 'teacherID' => $teacherID]);
                    $member = Teacher::find($teacherID);
                }
            } else {
                $staffID = str_replace('s_', '', $member_id);
                $exists = DepartmentMember::where('departmentID', $id)->where('staffID', $staffID)->exists();
                if (!$exists) {
                    DepartmentMember::create(['departmentID' => $id, 'staffID' => $staffID]);
                    $member = OtherStaff::find($staffID);
                }
            }

            // Notify assigned member
            if ($member) {
                $dept = Department::find($id);
                $memberUser = User::where('name', $member->employee_number)->first();
                if ($memberUser) {
                    $msg = "Habari {$member->first_name}, umepangiwa kwenye Idara ya {$dept->department_name}. Kagua shuleXpert kwa maelezo.";
                    $this->notificationService->notify(
                        $memberUser->id,
                        'Department Assignment',
                        $msg,
                        route('sgpm.departments.index'),
                        'Assignment'
                    );
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Members added and notified successfully!']);
    }

    public function removeMember($memberId)
    {
        if (Session::get('user_type') !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized! Only Admins can remove members.'], 403);
        }

        $member = DepartmentMember::findOrFail($memberId);
        $member->delete();
        return response()->json(['success' => true, 'message' => 'Member removed successfully!']);
    }

    public function sendSmsToMembers(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $dept = Department::with(['members.teacher', 'members.staff'])->findOrFail($id);
        $userType = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');

        // Security: Must be Admin OR the HOD of this department
        $isHOD = ($dept->head_teacherID == $teacherID && $teacherID) || ($dept->head_staffID == $staffID && $staffID);
        
        if ($userType !== 'Admin' && !$isHOD) {
            return response()->json(['success' => false, 'message' => 'Unauthorized! Only Admins or the HOD can send SMS.'], 403);
        }

        set_time_limit(120); // 2 minutes max for SMS loop
        $count = 0;
        $memberIds = $request->member_ids; // Array of DepartmentMember IDs
        
        foreach ($dept->members as $dm) {
            // If specific members are selected, only send to them
            if ($memberIds && !in_array($dm->id, $memberIds)) {
                continue;
            }

            $member = $dm->teacher ?? $dm->staff;
            if ($member && $member->phone_number) {
                $fullMsg = "Idara ya {$dept->department_name}:\n{$request->message}";
                $this->smsService->sendSms($member->phone_number, $fullMsg);
                $count++;
            }
        }

        return response()->json([
            'success' => true, 
            'message' => "SMS sent successfully to {$count} members!"
        ]);
    }

    public function sendSmsToHODs(Request $request)
    {
        if (Session::get('user_type') !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized!'], 403);
        }

        set_time_limit(180); // 3 minutes max for large schools
        $request->validate([
            'message' => 'required|string',
            'department_ids' => 'nullable|array', // If null, send to all HODs
        ]);

        $query = Department::with(['headTeacher', 'headStaff']);
        if ($request->department_ids) {
            $query->whereIn('departmentID', $request->department_ids);
        }
        $departments = $query->get();

        $count = 0;
        $sentNumbers = []; // Avoid duplicate SMS if one person is HOD of multiple departments

        foreach ($departments as $dept) {
            $head = $dept->headTeacher ?? $dept->headStaff;
            if ($head && $head->phone_number && !in_array($head->phone_number, $sentNumbers)) {
                $fullMsg = "ShuleXpert SMS kwa HODs:\n{$request->message}";
                $this->smsService->sendSms($head->phone_number, $fullMsg);
                $sentNumbers[] = $head->phone_number;
                $count++;
            }
        }

        return response()->json([
            'success' => true, 
            'message' => "SMS sent successfully to {$count} HODs!"
        ]);
    }
}
