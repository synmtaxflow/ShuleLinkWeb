<?php

namespace App\Http\Controllers;

use App\Models\StrategicGoal;
use App\Models\Department;
use App\Models\DepartmentalObjective;
use App\Models\SgpmTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SgpmPerformanceController extends Controller
{
    public function index()
    {
        $userType = Session::get('user_type');
        
        // Custom logic to redirect based on role or show a general overview
        if ($userType == 'Admin') {
            return $this->headDashboard();
        } elseif ($userType == 'Board') {
            return $this->boardDashboard();
        } elseif (Session::has('staffID') || Session::has('teacherID')) {
             // Check if they are HoD
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
        
        // Calculate Goal Progress % (Simplistic average of objectives)
        foreach($goals as $goal) {
            $objCount = $goal->objectives->count();
            if ($objCount > 0) {
                $completed = $goal->objectives->where('status', 'Completed')->count();
                $goal->progress_percent = ($completed / $objCount) * 100;
            } else {
                $goal->progress_percent = 0;
            }
        }

        return view('sgpm.dashboards.board', compact('goals'));
    }

    public function headDashboard()
    {
        $schoolID = Session::get('schoolID');
        $departments = Department::where('schoolID', $schoolID)->get();
        
        foreach($departments as $dept) {
            // Department Score = (Average Staff Score × 50%) + (Department KPI Achievement × 50%)
            $avgStaffScore = SgpmTask::whereHas('actionPlan.objective', function($q) use ($dept) {
                $q->where('departmentID', $dept->departmentID);
            })->avg('total_score') ?? 0;
            
            $deptKpiAchievement = DepartmentalObjective::where('departmentID', $dept->departmentID)
                                    ->where('status', 'Completed')->count() > 0 ? 100 : 0; // Simplified
                                    
            $dept->performance_score = ($avgStaffScore * 0.5) + ($deptKpiAchievement * 0.5);
        }

        return view('sgpm.dashboards.head', compact('departments'));
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
