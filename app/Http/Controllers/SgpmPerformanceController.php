<?php

namespace App\Http\Controllers;

use App\Models\StrategicGoal;
use App\Models\Department;
use App\Models\DepartmentalObjective;
use App\Models\SgpmTask;
use App\Models\SgpmSubtask;
use App\Models\SgpmActionPlan;
use App\Models\User;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Services\SmsService;
use App\Services\SgpmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SgpmPerformanceController extends Controller
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
        $userType = Session::get('user_type');
        
        if ($userType == 'Admin') {
            return $this->headDashboard();
        } elseif ($userType == 'Board') {
            return $this->boardDashboard();
        } elseif (Session::has('staffID') || Session::has('teacherID')) {
             $userID = Session::get('userID');
             $isHod = Department::where('head_teacherID', Session::get('teacherID'))
                        ->orWhere('head_staffID', Session::get('staffID'))
                        ->exists();
             if ($isHod) return $this->hodDashboard();
             return $this->staffDashboard();
        }
        
        return redirect()->route('AdminDashboard');
    }

    public function boardDashboard()
    {
        $schoolID = Session::get('schoolID');
        $goals = StrategicGoal::where('schoolID', $schoolID)->with('objectives.department')->get();
        
        foreach($goals as $goal) {
            $goal->progress_percent = $goal->calculateProgress();
            $goal->stats = $goal->getStatistics();
        }

        return view('sgpm.dashboards.board', compact('goals'));
    }

    public function headDashboard()
    {
        $schoolID = Session::get('schoolID');
        
        // Load Strategic Goals with all nested relationships
        $goals = StrategicGoal::where('schoolID', $schoolID)
            ->with(['objectives.department', 'objectives.actionPlans.tasks.subtasks', 'objectives.actionPlans.tasks.assignee'])
            ->get();
        
        foreach ($goals as $goal) {
            $goal->progress_percent = $goal->calculateProgress();
            $goal->stats = $goal->getStatistics();
        }

        // Load Departments with performance scores
        $departments = Department::where('schoolID', $schoolID)->get();
        
        foreach ($departments as $dept) {
            $deptTasks = SgpmTask::with('subtasks')
                ->whereHas('actionPlan.objective', function($q) use ($dept) {
                    $q->where('departmentID', $dept->departmentID);
                })->get();
            
            $totalWeight = $deptTasks->sum('weight');
            $totalProgress = $deptTasks->sum('progress');
            
            $dept->performance_score = $totalWeight > 0 ? round(($totalProgress / $totalWeight) * 100, 1) : 0;
            $dept->total_tasks = $deptTasks->count();
            $dept->completed_tasks = $deptTasks->whereIn('status', ['Completed', 'Approved'])->count();
            $dept->pending_review = $deptTasks->flatMap->subtasks->where('status', 'Submitted')->count();
            
            // Objectives count
            $dept->total_objectives = DepartmentalObjective::where('departmentID', $dept->departmentID)->count();
        }

        // Overall institutional score
        $institutionalScore = $departments->count() > 0 ? $departments->avg('performance_score') : 0;

        return view('sgpm.dashboards.head', compact('goals', 'departments', 'institutionalScore'));
    }

    /**
     * Admin reviews a specific strategic goal with all its drill-down data
     */
    public function adminReviewGoal($goalId)
    {
        $goal = StrategicGoal::with([
            'objectives.department',
            'objectives.actionPlans.tasks.subtasks',
            'objectives.actionPlans.tasks.assignee.teacher',
            'objectives.actionPlans.tasks.assignee.staff',
        ])->findOrFail($goalId);

        $goal->progress_percent = $goal->calculateProgress();
        $goal->stats = $goal->getStatistics();

        return view('sgpm.dashboards.admin_review_goal', compact('goal'));
    }

    /**
     * Admin approves a subtask (same as HOD but from admin level)
     */
    public function adminApproveSubtask(Request $request, $id)
    {
        $request->validate([
            'achieved_score' => 'required|numeric|min:0',
            'hod_comments' => 'nullable|string',
        ]);

        $subtask = SgpmSubtask::findOrFail($id);
        
        if ($request->achieved_score > $subtask->weight_percentage) {
            return response()->json([
                'success' => false, 
                'message' => "Score cannot exceed subtask weight ({$subtask->weight_percentage}%)."
            ], 422);
        }

        $subtask->status = 'Approved';
        $subtask->achieved_score = $request->achieved_score;
        $subtask->hod_comments = $request->hod_comments;
        $subtask->save();

        // Update progress
        $this->updateTaskProgress($subtask->task);

        // Update departmental objective progress
        $task = $subtask->task;

        // Notify & SMS
        $user = $task->assignee;
        if ($user) {
            $msg = "Sub-task yako '{$subtask->title}' imekubaliwa na Admin. Alama: {$subtask->achieved_score}/{$subtask->weight_percentage}%.";
            $this->notificationService->notify($user->id, 'Sub-task Approved by Admin', $msg, route('sgpm.tasks.index'), 'Approval');
            
            $profile = $user->teacher ?? $user->staff;
            if ($profile && $profile->phone_number) {
                try {
                    $smsMsg = "ShuleLink SGPM: Sub-task '{$subtask->title}' imekubaliwa na Admin. Alama: {$subtask->achieved_score}/{$subtask->weight_percentage}%.";
                    $this->smsService->sendSMS($profile->phone_number, $smsMsg);
                } catch (\Exception $e) {
                    \Log::error("SMS error: " . $e->getMessage());
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Sub-task approved and progress updated!']);
    }

    /**
     * Admin rejects a subtask
     */
    public function adminRejectSubtask(Request $request, $id)
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

    /**
     * Update objective progress based on its tasks
     */
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

    public function hodDashboard()
    {
        $userID = Session::get('userID');
        $teacherID = Session::get('teacherID');
        $staffID = Session::get('staffID');

        $department = Department::where('head_teacherID', $teacherID)
                        ->orWhere('head_staffID', $staffID)
                        ->first();

        if (!$department) return redirect()->back()->with('error', 'HoD record not found.');

        $tasks = SgpmTask::whereHas('actionPlan.objective', function($q) use ($department) {
            $q->where('departmentID', $department->departmentID);
        })->with('assignee')->get();

        return view('sgpm.dashboards.hod', compact('department', 'tasks'));
    }

    public function staffDashboard()
    {
        $userID = Session::get('userID');
        $tasks = SgpmTask::where('assigned_to', $userID)->get();
        $avgScore = $tasks->avg('total_score') ?? 0;

        return view('sgpm.dashboards.staff', compact('tasks', 'avgScore'));
    }
}
