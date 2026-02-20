<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmartGoal;
use App\Models\GoalTask;
use App\Models\GoalMemberTask;
use App\Models\GoalSubtask;
use App\Models\GoalSubtaskStep;
use App\Models\GoalNotification;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\Department;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoalManagementController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new SmsService();
    }

    // --- Admin Views ---

    public function createGoal()
    {
        return view('Admin.goals.create');
    }

    public function storeGoal(Request $request)
    {
        $request->validate([
            'goal_name' => 'required|string',
            'target_percentage' => 'required|numeric|min:0|max:100',
            'deadline' => 'required|date',
        ]);

        SmartGoal::create([
            'schoolID' => Session::get('schoolID'),
            'goal_name' => $request->goal_name,
            'target_percentage' => $request->target_percentage,
            'deadline' => $request->deadline,
            'created_by' => auth()->id() ?? 1, // Fallback if auth is not set up correctly in this session context
        ]);

        return redirect()->route('admin.goals.index')->with('success', 'Goal created successfully');
    }

    public function goalList()
    {
        $goals = SmartGoal::where('schoolID', Session::get('schoolID'))->get();
        return view('Admin.goals.index', compact('goals'));
    }

    public function showGoal($id)
    {
        $goal = SmartGoal::with(['tasks.memberTasks.subtasks', 'tasks.subtasks', 'tasks.department.headTeacher', 'tasks.department.headStaff', 'tasks.teacher', 'tasks.staff'])->findOrFail($id);
        
        foreach ($goal->tasks as $task) {
            $task->assignee_phone = 'N/A';
            // Get assignee name and contact
            if ($task->assigned_to_type === 'Department') {
                $task->assignee_name = $task->department->department_name ?? 'N/A';
                if ($task->department) {
                    $head = $task->department->headTeacher ?? $task->department->headStaff;
                    if ($head) {
                        $task->assignee_name = ($head->first_name ?? '') . ' ' . ($head->last_name ?? '') . " (HOD - " . ($task->department->department_name ?? '') . ")";
                        $task->assignee_phone = $head->phone_number ?? 'N/A';
                    }
                }
            } elseif ($task->assigned_to_type === 'Teacher') {
                $task->assignee_name = ($task->teacher->first_name ?? '') . ' ' . ($task->teacher->last_name ?? '');
                $task->assignee_phone = $task->teacher->phone_number ?? 'N/A';
            } else {
                $task->assignee_name = ($task->staff->first_name ?? '') . ' ' . ($task->staff->last_name ?? '');
                $task->assignee_phone = $task->staff->phone_number ?? 'N/A';
            }
        }

        return view('Admin.goals.show', compact('goal'));
    }

    public function editGoal($id)
    {
        $goal = SmartGoal::findOrFail($id);
        return response()->json($goal);
    }

    public function updateGoal(Request $request, $id)
    {
        $request->validate([
            'goal_name' => 'required|string',
            'target_percentage' => 'required|numeric|min:0|max:100',
            'deadline' => 'required|date',
        ]);

        $goal = SmartGoal::findOrFail($id);
        $goal->update($request->all());

        return response()->json(['success' => true, 'message' => 'Goal updated successfully']);
    }

    public function deleteGoal($id)
    {
        $goal = SmartGoal::findOrFail($id);
        $goal->delete();

        return response()->json(['success' => true, 'message' => 'Goal deleted successfully']);
    }

    // --- Task Assignment (Admin) ---

    public function assignTask(Request $request)
    {
        $request->validate([
            'goal_id' => 'required|exists:smart_goals,id',
            'tasks' => 'required|array',
            'tasks.*.task_name' => 'required|string',
            'tasks.*.assigned_to_type' => 'required|in:Department,Teacher,Staff',
            'tasks.*.assigned_to_id' => 'required',
            'tasks.*.weight' => 'required|numeric',
        ]);

        $goal = SmartGoal::find($request->goal_id);
        $incomingWeight = collect($request->tasks)->sum('weight');
        $currentWeight = $goal->tasks()->sum('weight');

        if (($currentWeight + $incomingWeight) > $goal->target_percentage) {
            return response()->json(['success' => false, 'message' => 'Total tasks weight exceeds goal target percentage (' . $goal->target_percentage . '%)']);
        }

        foreach ($request->tasks as $taskData) {
            $taskData['goal_id'] = $request->goal_id;
            $task = GoalTask::create($taskData);
            $this->sendAssignmentNotification($task);
        }

        return response()->json(['success' => true, 'message' => 'Tasks assigned successfully']);
    }

    public function fetchTaskDetails($id)
    {
        $task = GoalTask::findOrFail($id);
        return response()->json($task);
    }

    public function fetchGoalTasks($goal_id)
    {
        $goal = SmartGoal::findOrFail($goal_id);
        $tasks = GoalTask::where('goal_id', $goal_id)->get()->map(function($task) {
            $name = 'Unknown';
            if ($task->assigned_to_type === 'Department') {
                $d = Department::find($task->assigned_to_id);
                $name = $d ? $d->department_name : 'Deleted Dept';
            } elseif ($task->assigned_to_type === 'Teacher') {
                $t = Teacher::find($task->assigned_to_id);
                $name = $t ? $t->first_name . ' ' . $t->last_name : 'Deleted Teacher';
            } elseif ($task->assigned_to_type === 'Staff') {
                $s = OtherStaff::find($task->assigned_to_id);
                $name = $s ? $s->first_name . ' ' . $s->last_name : 'Deleted Staff';
            }
            $task->assigned_name = $name;
            return $task;
        });

        return response()->json([
            'goal' => $goal,
            'tasks' => $tasks,
            'total_assigned_weight' => $tasks->sum('weight')
        ]);
    }

    public function updateTask(Request $request, $id)
    {
        $request->validate([
            'task_name' => 'required|string',
            'weight' => 'required|numeric',
        ]);

        $task = GoalTask::findOrFail($id);
        $goal = $task->goal;
        
        $otherTasksWeight = $goal->tasks()->where('id', '!=', $id)->sum('weight');
        if (($otherTasksWeight + $request->weight) > $goal->target_percentage) {
            return response()->json(['success' => false, 'message' => 'Total weight exceeds goal target (' . $goal->target_percentage . '%)']);
        }

        $task->update($request->all());
        return response()->json(['success' => true, 'message' => 'Task updated successfully']);
    }

    public function deleteTask($id)
    {
        $task = GoalTask::findOrFail($id);
        $task->delete();
        return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
    }

    // --- HOD/Member Logic (Teachers/Staff) ---

    public function hodAssignedTasks()
    {
        // Get tasks assigned to the HOD's department
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');
        
        $department = null;
        if ($teacherID) {
            $department = Department::where('head_teacherID', $teacherID)->first();
        } elseif ($staffID) {
            $department = Department::where('head_staffID', $staffID)->first();
        }

        if (!$department) {
            return back()->with('error', 'You are not assigned as HOD of any department');
        }

        $tasks = GoalTask::where('assigned_to_type', 'Department')
            ->where('assigned_to_id', $department->departmentID)
            ->get();

        return view('Teacher.goals.hod_assigned', compact('tasks', 'department'));
    }

    public function memberTasks()
    {
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');

        $query = GoalMemberTask::with(['parentTask.goal', 'subtasks.steps']);

        if ($teacherID) {
            $query->where('member_id', $teacherID)->where('member_type', 'Teacher');
        } elseif ($staffID) {
            $query->where('member_id', $staffID)->where('member_type', 'Staff');
        } else {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $myTasks = $query->get();

        foreach ($myTasks as $task) {
            // Member task progress is simply the sum of approved marks (since max sum of marks is 100)
            $task->progress = $task->subtasks->where('is_approved', true)->sum('marks');
            
            // Total subtasks weight budget (target 100%)
            $task->subtasks_weight_sum = $task->subtasks->sum('weight');
        }

        return view('Teacher.goals.my_tasks', compact('myTasks'));
    }

    private function sendAssignmentNotification($task)
    {
        $title = "New Task Assigned";
        $message = "You have been assigned a new task: " . $task->task_name;
        $link = "";

        if ($task->assigned_to_type === 'Teacher') {
            $teacher = Teacher::find($task->assigned_to_id);
            if ($teacher) {
                $this->createSystemNotification($teacher->email, $title, $message, $link);
                $this->sendSmsNotification($teacher->phone_number, $message);
            }
        } elseif ($task->assigned_to_type === 'Staff') {
            $staff = OtherStaff::find($task->assigned_to_id);
            if ($staff) {
                $this->createSystemNotification($staff->email, $title, $message, $link);
                $this->sendSmsNotification($staff->phone_number, $message);
            }
        } elseif ($task->assigned_to_type === 'Department') {
            $dept = Department::find($task->assigned_to_id);
            if ($dept) {
                // Notify HOD
                if ($dept->head_teacherID) {
                    $hod = Teacher::find($dept->head_teacherID);
                    if ($hod) {
                        $this->createSystemNotification($hod->email, $title, $message, $link);
                        $this->sendSmsNotification($hod->phone_number, $message);
                    }
                } elseif ($dept->head_staffID) {
                    $hod = OtherStaff::find($dept->head_staffID);
                    if ($hod) {
                        $this->createSystemNotification($hod->email, $title, $message, $link);
                        $this->sendSmsNotification($hod->phone_number, $message);
                    }
                }
            }
        }
    }

    private function createSystemNotification($email, $title, $message, $link)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            GoalNotification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'link' => $link,
            ]);
        }
    }

    private function sendSmsNotification($phone, $message)
    {
        if ($phone) {
            try {
                $this->smsService->sendSms($phone, $message);
            } catch (\Exception $e) {
                Log::error("SMS failed to $phone: " . $e->getMessage());
            }
        }
    }

    public function getNotifications()
    {
        $notifications = GoalNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->latest()
            ->get();
        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notif = GoalNotification::find($id);
        if ($notif && $notif->user_id == auth()->id()) {
            $notif->update(['is_read' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        GoalNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function fetchTargets($type)
    {
        $schoolID = Session::get('schoolID');
        $targets = [];

        if ($type === 'Department') {
            $targets = Department::where('schoolID', $schoolID)
                ->get()
                ->map(fn($d) => ['id' => $d->departmentID, 'name' => $d->department_name]);
        } elseif ($type === 'Teacher') {
            $targets = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->get()
                ->map(fn($t) => ['id' => $t->id, 'name' => $t->first_name . ' ' . $t->last_name]);
        } elseif ($type === 'Staff') {
            $targets = OtherStaff::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->get()
                ->map(fn($s) => ['id' => $s->id, 'name' => $s->first_name . ' ' . $s->last_name]);
        }

        return response()->json($targets);
    }

    public function fetchDeptMembers()
    {
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');
        
        $department = null;
        if ($teacherID) {
            $department = Department::where('head_teacherID', $teacherID)->first();
        } elseif ($staffID) {
            $department = Department::where('head_staffID', $staffID)->first();
        }

        if (!$department) return response()->json([]);

        // Get members of this department
        $members = DB::table('department_members')
            ->where('departmentID', $department->departmentID)
            ->get();

        $result = [];
        foreach ($members as $m) {
            if ($m->teacherID) {
                $t = Teacher::find($m->teacherID);
                if ($t) $result[] = ['id' => $t->id, 'name' => $t->first_name . ' ' . $t->last_name, 'type' => 'Teacher'];
            } elseif ($m->staffID) {
                $s = OtherStaff::find($m->staffID);
                if ($s) $result[] = ['id' => $s->id, 'name' => $s->first_name . ' ' . $s->last_name, 'type' => 'Staff'];
            }
        }

        return response()->json($result);
    }

    public function assignMemberStore(Request $request)
    {
        $request->validate([
            'parent_task_id' => 'required|exists:goal_tasks,id',
            'tasks' => 'required|array',
            'tasks.*.task_name' => 'required|string',
            'tasks.*.member_id' => 'required',
            'tasks.*.member_type' => 'required|in:Teacher,Staff',
            'tasks.*.weight' => 'required|numeric',
            'tasks.*.description' => 'nullable|string',
        ]);

        $parentTask = GoalTask::findOrFail($request->parent_task_id);
        $incomingWeight = collect($request->tasks)->sum('weight');
        $currentWeight = GoalMemberTask::where('parent_task_id', $request->parent_task_id)->sum('weight');

        if (($currentWeight + $incomingWeight) > $parentTask->weight) {
            return response()->json(['success' => false, 'message' => 'Total member tasks weight exceeds your task weight (' . $parentTask->weight . '%)']);
        }

        foreach ($request->tasks as $taskData) {
            $taskData['parent_task_id'] = $request->parent_task_id;
            $memberTask = GoalMemberTask::create($taskData);
            $this->sendMemberAssignmentNotification($memberTask);
        }

        return response()->json(['success' => true, 'message' => 'Tasks assigned to members successfully']);
    }

    public function showHODTaskDetails($id)
    {
        $task = GoalTask::with(['memberTasks.subtasks', 'subtasks', 'goal'])->findOrFail($id);
        
        foreach ($task->memberTasks as $mTask) {
            // Task progress is sum of approved marks (max 100)
            $mTask->progress = $mTask->subtasks->where('is_approved', true)->sum('marks');
            
            // Member name
            $member = $mTask->member;
            $mTask->member_name = ($member->first_name ?? '') . ' ' . ($member->last_name ?? '');
        }

        // overall_progress_percent is now available via accessor task->progress
        $task->overall_progress_percent = $task->progress;

        return view('Teacher.goals.hod_task_show', compact('task'));
    }

    public function fetchTaskFullStructure($id)
    {
        $task = GoalTask::with([
            'subtasks.steps',
            'memberTasks.subtasks.steps'
        ])->findOrFail($id);

        foreach ($task->memberTasks as $mTask) {
            $member = $mTask->member;
            $mTask->member_name = ($member->first_name ?? '') . ' ' . ($member->last_name ?? '');
            
            // Calculate detailed stats for Admin
            $mTask->progress_percent = (float)$mTask->progress; // from accessor
            $mTask->weight_earned = ($mTask->progress_percent / 100) * $mTask->weight;
            $mTask->total_subtasks = $mTask->subtasks->count();
            $mTask->approved_count = $mTask->subtasks->where('is_approved', true)->count();
            $mTask->pending_count = $mTask->total_subtasks - $mTask->approved_count;
        }

        return response()->json($task);
    }

    public function fetchMemberTasks($parent_task_id)
    {
        $parentTask = GoalTask::findOrFail($parent_task_id);
        $memberTasks = GoalMemberTask::with('subtasks')->where('parent_task_id', $parent_task_id)->get()->map(function($mt) {
            $member = $mt->member;
            $mt->member_name = ($member->first_name ?? '') . ' ' . ($member->last_name ?? '');
            return $mt;
        });

        return response()->json([
            'parent_task' => $parentTask,
            'member_tasks' => $memberTasks,
            'total_assigned_weight' => $memberTasks->sum('weight')
        ]);
    }

    public function fetchSubtasks(Request $request, $member_task_id)
    {
        $query = GoalSubtask::with('steps')
            ->where('member_task_id', $member_task_id);

        if ($request->get('role') === 'hod') {
            // HOD only sees submitted or approved
            $query->whereIn('status', ['Submitted', 'Approved']);
        }

        $subtasks = $query->get();
        return response()->json($subtasks);
    }

    private function sendMemberAssignmentNotification($memberTask)
    {
        $title = "New Department Task Assigned";
        $message = "You have been assigned a task by your HOD: " . $memberTask->task_name;
        $link = route('member.goals.myTasks');

        if ($memberTask->member_type === 'Teacher') {
            $teacher = Teacher::find($memberTask->member_id);
            if ($teacher) {
                $this->createSystemNotification($teacher->email, $title, $message, $link);
                $this->sendSmsNotification($teacher->phone_number, $message);
            }
        } elseif ($memberTask->member_type === 'Staff') {
            $staff = OtherStaff::find($memberTask->member_id);
            if ($staff) {
                $this->createSystemNotification($staff->email, $title, $message, $link);
                $this->sendSmsNotification($staff->phone_number, $message);
            }
        }
    }
    public function subtaskStore(Request $request)
    {
        $request->validate([
            'member_task_id' => 'required_without:direct_task_id|exists:goal_member_tasks,id',
            'direct_task_id' => 'required_without:member_task_id|exists:goal_tasks,id',
            'subtasks' => 'required|array',
            'subtasks.*.subtask_name' => 'required|string',
            'subtasks.*.weight' => 'required|numeric|min:0.1',
        ]);

        $incomingWeight = collect($request->subtasks)->sum('weight');
        $currentWeight = 0;

        if ($request->member_task_id) {
            $currentWeight = GoalSubtask::where('member_task_id', $request->member_task_id)->sum('weight');
            if (($currentWeight + $incomingWeight) > 100) {
                return response()->json(['success' => false, 'message' => 'Total subtasks weight exceeds 100%']);
            }
        } else {
            $currentWeight = GoalSubtask::where('direct_task_id', $request->direct_task_id)->sum('weight');
            if (($currentWeight + $incomingWeight) > 100) {
                return response()->json(['success' => false, 'message' => 'Total subtasks weight exceeds 100%']);
            }
        }

        foreach ($request->subtasks as $subData) {
            $subData['member_task_id'] = $request->member_task_id;
            $subData['direct_task_id'] = $request->direct_task_id;
            GoalSubtask::create($subData);
        }

        return response()->json(['success' => true, 'message' => 'Subtasks created successfully.']);
    }

    public function saveSubtaskStep(Request $request)
    {
        try {
            $validated = $request->validate([
                'subtask_id'             => 'required|exists:goal_subtasks,id',
                'steps'                  => 'required|array|min:1',
                'steps.*.date'           => 'required|date',
                'steps.*.step_description' => 'required|string|max:1000',
            ]);

            foreach ($request->steps as $stepData) {
                GoalSubtaskStep::create([
                    'subtask_id'       => $request->subtask_id,
                    'date'             => $stepData['date'],
                    'step_description' => $stepData['step_description'],
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Steps saved successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => implode(' ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function submitSubtask(Request $request, $id)
    {
        $sub = GoalSubtask::with(['memberTask.parentTask.department', 'directTask'])->findOrFail($id);

        $sub->update([
            'status'        => 'Submitted',
            'is_sent_to_hod'=> true
        ]);

        $memberTask  = $sub->memberTask;
        $directTask  = $sub->directTask;
        
        // ---- Get performer info ----
        $member      = null;
        $memberPhone = null;
        $memberName  = 'Staff';
        $submissionTitle = 'Subtask Submitted';

        if ($memberTask) {
            if ($memberTask->member_type === 'Teacher') {
                $member = Teacher::find($memberTask->member_id);
            } else {
                $member = OtherStaff::find($memberTask->member_id);
            }
        } elseif ($directTask) {
            if ($directTask->assigned_to_type === 'Teacher') {
                $member = Teacher::find($directTask->assigned_to_id);
            } else {
                $member = OtherStaff::find($directTask->assigned_to_id);
            }
        }

        if ($member) {
            $memberPhone = $member->phone_number ?? null;
            $memberName  = trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
        }

        // ---- Determine Reviewer and Send Notifications ----
        $reviewerPhone = null;
        $subtaskName = $sub->subtask_name;
        $taskName    = ($memberTask->task_name ?? ($directTask->task_name ?? 'Task'));

        if ($memberTask) {
            // Delegated Task -> Goes to HOD
            $parentTask  = $memberTask->parentTask;
            $department  = $parentTask?->department;
            if ($department) {
                $hod = $department->headTeacher ?? $department->headStaff ?? null;
                if ($hod) {
                    $reviewerPhone = $hod->phone_number ?? null;
                }
            }
        } else {
            // Direct Task -> Goes to Admin
            // For now, we assume the reviewer is the school admin (who doesn't have a single phone in settings typically, but we can notify via system)
            // If there's a specific admin phone, you'd fetch it here.
        }

        // SMS to Performer
        if ($memberPhone) {
            $this->smsService->sendSms($memberPhone, "ShuleLink: Dear {$memberName}, your subtask \"{$subtaskName}\" has been submitted for review. - ShuleLink");
        }

        // SMS to Reviewer (if HOD)
        if ($reviewerPhone) {
            $this->smsService->sendSms($reviewerPhone, "ShuleLink: {$memberName} ametuma subtask \"{$subtaskName}\" kwa ajili ya ukaguzi. - ShuleLink");
        }

        return response()->json(['success' => true, 'message' => 'Subtask imetumwa kwa ajili ya ukaguzi.']);
    }

    public function fetchSubtaskDetails($id)
    {
        $sub = GoalSubtask::with(['steps', 'memberTask'])->findOrFail($id);
        return response()->json($sub);
    }

    public function approveSubtask(Request $request)
    {
        $request->validate([
            'id'    => 'required|exists:goal_subtasks,id',
            'marks' => 'required|numeric',
        ]);

        $sub = GoalSubtask::with(['memberTask.parentTask.department'])->findOrFail($request->id);

        if ($request->marks > $sub->weight) {
            return response()->json(['success' => false, 'message' => 'Marks cannot exceed subtask weight (' . $sub->weight . ')']);
        }

        $sub->update([
            'marks'       => $request->marks,
            'is_approved' => true,
            'status'      => 'Approved',
            'approved_by' => auth()->id() ?? 1
        ]);

        // ---- Get member phone ----
        $memberTask  = $sub->memberTask;
        $memberPhone = null;
        $memberName  = 'Staff';

        if ($memberTask) {
            $member = $memberTask->member_type === 'Teacher'
                ? Teacher::find($memberTask->member_id)
                : OtherStaff::find($memberTask->member_id);

            if ($member) {
                $memberPhone = $member->phone_number ?? null;
                $memberName  = trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
            }
        }

        // ---- SMS to Member ----
        if ($memberPhone) {
            $this->smsService->sendSms(
                $memberPhone,
                "ShuleLink: Dear {$memberName}, your subtask \"{$sub->subtask_name}\" has been reviewed and approved. Score: {$request->marks}/{$sub->weight}. Well done! - ShuleLink"
            );
        }

        return response()->json(['success' => true, 'message' => 'Subtask approved and marks assigned.']);
    }

    public function deleteSubtask($id)
    {
        $sub = GoalSubtask::findOrFail($id);
        if ($sub->is_approved || $sub->status === 'Submitted') {
            return response()->json(['success' => false, 'message' => 'Cannot delete an approved or submitted subtask.']);
        }
        $sub->delete();
        return response()->json(['success' => true, 'message' => 'Subtask deleted.']);
    }

    public function updateSubtask(Request $request, $id)
    {
        $request->validate([
            'subtask_name' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
        ]);

        $sub = GoalSubtask::findOrFail($id);
        if ($sub->is_approved || $sub->status === 'Submitted') {
            return response()->json(['success' => false, 'message' => 'Cannot edit an approved or submitted subtask.']);
        }

        // Check total weight excluding this subtask
        $currentWeight = GoalSubtask::where('member_task_id', $sub->member_task_id)
            ->where('id', '!=', $id)
            ->sum('weight');

        if (($currentWeight + $request->weight) > 100) {
            return response()->json(['success' => false, 'message' => 'Total weight exceeds 100%. Current other subtasks sum: ' . $currentWeight . '%']);
        }

        $sub->update([
            'subtask_name' => $request->subtask_name,
            'description' => $request->description,
            'weight' => $request->weight
        ]);

        return response()->json(['success' => true, 'message' => 'Subtask updated successfully.']);
    }

    public function updateSubtaskStep(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'step_description' => 'required|string',
        ]);

        $step = GoalSubtaskStep::findOrFail($id);
        $step->update($request->all());

        return response()->json(['success' => true, 'message' => 'Step updated successfully.']);
    }

    public function deleteSubtaskStep($id)
    {
        $step = GoalSubtaskStep::findOrFail($id);
        $step->delete();
        return response()->json(['success' => true, 'message' => 'Step deleted successfully.']);
    }

    public function resetSubtaskMarks($id)
    {
        $sub = GoalSubtask::findOrFail($id);
        $sub->update([
            'marks' => 0,
            'is_approved' => false,
            'status' => 'Submitted'
        ]);
        return response()->json(['success' => true, 'message' => 'Marks reset successfully.']);
    }
}
