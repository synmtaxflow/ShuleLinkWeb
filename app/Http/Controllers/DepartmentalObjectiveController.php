<?php

namespace App\Http\Controllers;

use App\Models\DepartmentalObjective;
use App\Models\StrategicGoal;
use App\Models\Department;
use App\Models\SgpmActionPlan;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\User;
use App\Services\SmsService;
use App\Services\SgpmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DepartmentalObjectiveController extends Controller
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
        $userType = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');

        if (!$schoolID) return redirect()->route('login');

        // Security: Admins see all, HODs see their department, others blocked
        if ($userType !== 'Admin') {
            // Check if HOD
            $myDept = Department::where('head_teacherID', $teacherID)
                        ->whereNotNull('head_teacherID')
                        ->orWhere(function($q) use ($staffID) {
                            $q->where('head_staffID', $staffID)->whereNotNull('head_staffID');
                        })->first();

            if (!$myDept) {
                return redirect()->back()->with('error', 'Unauthorized access! Only Admins and HODs can access Objectives.');
            }

            $goals = StrategicGoal::where('schoolID', $schoolID)->where('status', 'Published')->get();
            $departments = Department::where('departmentID', $myDept->departmentID)->get();
            $objectives = DepartmentalObjective::where('departmentID', $myDept->departmentID)
                            ->with(['strategicGoal', 'department'])->get();
        } else {
            $goals = StrategicGoal::where('schoolID', $schoolID)->where('status', 'Published')->get();
            $departments = Department::where('schoolID', $schoolID)->get();
            $objectives = DepartmentalObjective::whereHas('strategicGoal', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })->with(['strategicGoal', 'department'])->get();
        }

        return view('sgpm.objectives.index', compact('objectives', 'goals', 'departments', 'userType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'strategic_goalID' => 'required|exists:strategic_goals,strategic_goalID',
            'departmentID' => 'required|exists:departments,departmentID',
            'kpi' => 'required|string',
            'target_value' => 'required|string',
            'budget' => 'nullable|numeric',
        ]);

        $obj = new DepartmentalObjective($request->all());
        $obj->status = 'Not Started';
        
        $dept = Department::find($request->departmentID);
        $obj->assigned_hod_id = ($dept->type == 'Academic') ? $dept->head_teacherID : $dept->head_staffID;
        $obj->save();

        // Notify HoD
        $hod = null;
        if ($dept->type == 'Academic') {
            $hod = Teacher::find($dept->head_teacherID);
        } else {
            $hod = OtherStaff::find($dept->head_staffID);
        }

        if ($hod) {
            $hodUser = User::where('name', $hod->employee_number)->first();
            if ($hodUser) {
                $msg = "Habari {$hod->first_name}, idara yako ya {$dept->department_name} imekabidhiwa jukumu jipya: {$obj->kpi}. Kagua shuleXpert.";
                $this->notificationService->notify(
                    $hodUser->id,
                    'New Departmental Objective',
                    $msg,
                    route('sgpm.objectives.index'),
                    'Assignment'
                );
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Objective assigned and HoD notified!']);
        }

        return redirect()->back()->with('success', 'Objective assigned to department!');
    }

    public function storeActionPlan(Request $request)
    {
        try {
            $validated = $request->validate([
                'objectiveID' => 'required|exists:departmental_objectives,objectiveID',
                'title' => 'required|string|max:255',
                'milestones' => 'required|string',
                'deadline' => 'required|date',
            ]);

            SgpmActionPlan::create($validated);

            $obj = DepartmentalObjective::find($request->objectiveID);
            if ($obj && $obj->status == 'Not Started') {
                $obj->status = 'In Progress';
                $obj->save();
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Action plan created successfully!']);
            }

            return redirect()->back()->with('success', 'Action plan created!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation error', 
                    'errors' => $e->validator->errors()->all()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error("SGPM Action Plan Error: " . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
