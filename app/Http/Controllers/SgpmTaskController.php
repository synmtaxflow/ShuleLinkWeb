<?php

namespace App\Http\Controllers;

use App\Models\SgpmTask;
use App\Models\SgpmActionPlan;
use App\Models\SgpmEvidence;
use App\Models\SgpmSubtask;
use App\Models\User;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\Department;
use App\Services\SmsService;
use App\Services\SgpmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SgpmTaskController extends Controller
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
        $userID = Session::get('userID');
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');

        if (!$schoolID) return redirect()->route('login');

        // Check if HOD
        $myDept = Department::where('head_teacherID', $teacherID)
                    ->whereNotNull('head_teacherID')
                    ->orWhere(function($q) use ($staffID) {
                        $q->where('head_staffID', $staffID)->whereNotNull('head_staffID');
                    })->first();

        $isHod = (bool)$myDept;

        if ($userType == 'Admin') {
            $tasks = SgpmTask::with('subtasks')->whereHas('actionPlan.objective.strategicGoal', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })->get();
            $actionPlans = SgpmActionPlan::whereHas('objective.strategicGoal', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })->get();
            $users = User::with(['teacher', 'staff'])->where(function($query) use ($schoolID) {
                $query->whereHas('teacher', function($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                })->orWhereHas('staff', function($q) use ($schoolID) {
                    $q->where('schoolID', $schoolID);
                });
            })->get();
        } elseif ($myDept) {
            // HOD sees all tasks assigned in their department
            $tasks = SgpmTask::with('subtasks')->whereHas('actionPlan.objective', function($q) use ($myDept) {
                $q->where('departmentID', $myDept->departmentID);
            })->get();
            // HOD can only assign tasks to their department's action plans
            $actionPlans = SgpmActionPlan::whereHas('objective', function($q) use ($myDept) {
                $q->where('departmentID', $myDept->departmentID);
            })->get();
            // HOD can only assign tasks to their department's members
            $users = User::with(['teacher', 'staff'])->where(function($query) use ($myDept) {
                $query->whereHas('teacher', function($q) use ($myDept) {
                    $q->whereIn('id', function($sq) use ($myDept) {
                        $sq->select('teacherID')->from('department_members')->where('departmentID', $myDept->departmentID);
                    });
                })->orWhereHas('staff', function($q) use ($myDept) {
                    $q->whereIn('id', function($sq) use ($myDept) {
                        $sq->select('staffID')->from('department_members')->where('departmentID', $myDept->departmentID);
                    });
                });
            })->get();
        } else {
            // Regular Staff/Teacher sees only tasks assigned to them
            $tasks = SgpmTask::with('subtasks')->where('assigned_to', $userID)->get();
            $actionPlans = collect();
            $users = collect();
        }

        return view('sgpm.tasks.index', compact('tasks', 'actionPlans', 'users', 'userType', 'isHod'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'action_planID' => 'required',
            'assigned_to' => 'required',
            'kpi' => 'required',
            'weight' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        $task = SgpmTask::create($request->all());
        
        // Notify the assigned user
        $user = User::find($request->assigned_to);
        if ($user) {
            $msg = "Habari {$user->name}, umepangiwa jukumu jipya (Task): {$task->kpi}. Kikomo ni {$task->due_date}. Kagua shuleXpert.";
            $this->notificationService->notify(
                $user->id,
                'New Task Assigned',
                $msg,
                route('sgpm.tasks.index'),
                'Assignment'
            );
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task assigned and notification sent!']);
        }

        return redirect()->back()->with('success', 'Task assigned!');
    }

    public function submitProgress(Request $request, $id)
    {
        $task = SgpmTask::findOrFail($id);
        
        $request->validate([
            'remarks' => 'nullable|string',
            'evidence_file' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('evidence_file')) {
            $path = $request->file('evidence_file')->store('sgpm/evidence', 'public');
            SgpmEvidence::create([
                'taskID' => $task->taskID,
                'file_path' => $path,
                'remarks' => $request->remarks
            ]);
        }

        $task->status = 'Completed';
        $task->completion_date = now();
        $task->save();

        // Notify HoD
        $plan = SgpmActionPlan::find($task->action_planID);
        $obj = $plan->objective;
        $dept = Department::find($obj->departmentID);
        $hodId = ($dept->type == 'Academic') ? $dept->head_teacherID : $dept->head_staffID;
        
        $hod = ($dept->type == 'Academic') ? Teacher::find($hodId) : OtherStaff::find($hodId);

        if ($hod) {
            $hodUser = User::where('name', $hod->employee_number)->first();
            if ($hodUser) {
                $msg = "Habari HoD {$hod->first_name}, kazi imeshawasilishwa na {$task->assignee->name} kwa uhakiki: {$task->kpi}.";
                $this->notificationService->notify(
                    $hodUser->id,
                    'Task Submitted for Review',
                    $msg,
                    route('sgpm.tasks.index'),
                    'Submission'
                );
            }
        }

        // Notify Admin of the milestone
        $schoolID = Session::get('schoolID');
        $this->notificationService->notifyAdmin(
            'Task Progress Reported',
            "Kazi imewasilishwa na {$task->assignee->name} (Idara: {$dept->department_name}): {$task->kpi}",
            route('sgpm.performance.index'),
            $schoolID
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Work submitted for review!']);
        }

        return redirect()->back()->with('success', 'Task progress submitted for review!');
    }

    public function evaluate(Request $request, $id)
    {
        $userType = Session::get('user_type');
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');

        $task = SgpmTask::findOrFail($id);
        $plan = SgpmActionPlan::findOrFail($task->action_planID);
        $dept = Department::findOrFail($plan->objective->departmentID);

        // Authorization check: Must be Admin OR the HOD of this department
        $isHodOfDept = ($dept->head_teacherID == $teacherID && $teacherID) || ($dept->head_staffID == $staffID && $staffID);
        
        if ($userType !== 'Admin' && !$isHodOfDept) {
            return response()->json(['success' => false, 'message' => 'Unauthorized! Only Admins or the Department HoD can evaluate tasks.'], 403);
        }
        
        $request->validate([
            'score_completion' => 'required|numeric|min:0|max:100',
            'score_kpi' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:Approved,Rejected',
        ]);

        $task->score_completion = $request->score_completion;
        $task->score_kpi = $request->score_kpi;
        
        $dueDate = Carbon::parse($task->due_date);
        $compDate = Carbon::parse($task->completion_date);
        $task->score_timeliness = $compDate->lte($dueDate) ? 100 : 50;

        $task->total_score = ($task->score_completion * 0.4) + ($task->score_kpi * 0.4) + ($task->score_timeliness * 0.2);
        $task->status = $request->status;
        $task->hod_comments = $request->hod_comments;
        $task->save();

        // Notify staff/teacher
        $user = $task->assignee;
        if ($user) {
            $statusText = $task->status == 'Approved' ? "Imepitishwa kwa alama {$task->total_score}%" : "Imekataliwa. Tafadhali rudia.";
            $msg = "Habari {$user->name}, kazi yako ({$task->kpi}) imehakikiwa: {$statusText}.";
            $this->notificationService->notify(
                $user->id,
                'Task Evaluated',
                $msg,
                route('sgpm.tasks.index'),
                'Approval'
            );
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task evaluated and staff notified!']);
        }

        return redirect()->back()->with('success', 'Task evaluated and scored!');
    }

    public function storeSubtask(Request $request)
    {
        $request->validate([
            'taskID' => 'required|exists:sgpm_tasks,taskID',
            'title' => 'required|string|max:255',
            'weight_percentage' => 'required|numeric|min:1|max:100',
            'due_date' => 'required|date',
        ]);

        $task = SgpmTask::find($request->taskID);
        $totalSubWeight = $task->subtasks()->sum('weight_percentage');

        if (($totalSubWeight + $request->weight_percentage) > $task->weight) {
            $remaining = $task->weight - $totalSubWeight;
            return response()->json(['success' => false, 'message' => "Total subtask weight cannot exceed the parent task weight ({$task->weight}%). Remaining: {$remaining}%."], 422);
        }

        SgpmSubtask::create($request->all());

        return response()->json(['success' => true, 'message' => 'Subtask created successfully!']);
    }

    public function submitSubtask($id)
    {
        $subtask = SgpmSubtask::findOrFail($id);
        $subtask->status = 'Submitted';
        $subtask->achieved_score = 0;     // Reset on resubmission
        $subtask->hod_comments = null;    // Clear previous rejection comments
        $subtask->save();

        // Notify HOD
        $task = $subtask->task;
        $dept = $task->actionPlan->objective->department;
        $hodId = ($dept->type == 'Academic') ? $dept->head_teacherID : $dept->head_staffID;
        $hod = ($dept->type == 'Academic') ? Teacher::find($hodId) : OtherStaff::find($hodId);

        if ($hod) {
            $hodUser = User::where('name', $hod->employee_number)->first();
            if ($hodUser) {
                $msg = "Habari HoD {$hod->first_name}, kuna sub-task mpya ya kuhakiki kutoka kwa {$task->assignee->name}: {$subtask->title}.";
                $this->notificationService->notify($hodUser->id, 'Sub-task Submitted', $msg, route('sgpm.tasks.index'), 'Submission');
            }
        }

        return response()->json(['success' => true, 'message' => 'Sub-task sent to HOD!']);
    }

    public function approveSubtask(Request $request, $id)
    {
        $request->validate([
            'achieved_score' => 'required|numeric|min:0',
            'hod_comments' => 'nullable|string',
        ]);

        $subtask = SgpmSubtask::findOrFail($id);
        
        // Validate that achieved score doesn't exceed weight
        if ($request->achieved_score > $subtask->weight_percentage) {
            return response()->json([
                'success' => false, 
                'message' => "Achieved score cannot exceed subtask weight ({$subtask->weight_percentage}%)."
            ], 422);
        }

        $subtask->status = 'Approved';
        $subtask->achieved_score = $request->achieved_score;
        $subtask->hod_comments = $request->hod_comments;
        $subtask->save();

        // Update progress
        $this->updateTaskProgress($subtask->task);

        $task = $subtask->fresh()->task; // Refresh to get updated progress

        // Notify the staff/teacher
        $user = $task->assignee;
        if ($user) {
            $msg = "Habari {$user->name}, sub-task yako '{$subtask->title}' imekubaliwa na kupata {$subtask->achieved_score}/{$subtask->weight_percentage}%.";
            $this->notificationService->notify($user->id, 'Sub-task Approved', $msg, route('sgpm.tasks.index'), 'Approval');
            
            // Send SMS notification
            $profile = $user->teacher ?? $user->staff;
            if ($profile && $profile->phone_number) {
                try {
                    $smsMsg = "ShuleLink SGPM: Sub-task yako '{$subtask->title}' imekubaliwa. Alama: {$subtask->achieved_score}/{$subtask->weight_percentage}%. Progress ya task yako: " . round(($task->progress / $task->weight) * 100) . "%.";
                    $this->smsService->sendSMS($profile->phone_number, $smsMsg);
                } catch (\Exception $e) {
                    \Log::error("Failed to send SMS for subtask approval: " . $e->getMessage());
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Sub-task approved and progress updated!']);
    }

    public function rejectSubtask(Request $request, $id)
    {
        $subtask = SgpmSubtask::findOrFail($id);
        $subtask->status = 'Rejected';
        $subtask->achieved_score = 0; // Reset score if rejected
        $subtask->hod_comments = $request->hod_comments ?? null;
        $subtask->save();

        // Update task progress
        $this->updateTaskProgress($subtask->task);

        return response()->json(['success' => true, 'message' => 'Sub-task rejected and progress updated.']);
    }

    private function updateTaskProgress($task)
    {
        $totalApprovedProgress = $task->subtasks()->where('status', 'Approved')->sum('achieved_score');
        $task->progress = $totalApprovedProgress;
        
        // If progress reaches task weight, mark as completed
        if ($task->progress >= $task->weight) {
            $task->status = 'Completed';
            $task->completion_date = now();
        } else {
            $task->status = 'In Progress';
        }
        $task->save();

        // Update Departmental Objective Progress
        $this->updateObjectiveProgress($task);
    }

    private function updateObjectiveProgress($task)
    {
        $objective = $task->actionPlan->objective;
        $allTasks = SgpmTask::whereHas('actionPlan', function($q) use ($objective) {
            $q->where('objectiveID', $objective->objectiveID);
        })->get();

        $totalWeight = $allTasks->sum('weight');
        $totalProgress = $allTasks->sum('progress');

        if ($totalWeight > 0 && $totalProgress >= $totalWeight) {
            $objective->status = 'Completed';
            $objective->save();
        } else {
            $objective->status = 'In Progress';
            $objective->save();
        }
    }
}
