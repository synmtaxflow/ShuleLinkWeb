<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'task_name',
        'assigned_to_type',
        'assigned_to_id',
        'weight',
        'description',
        'status',
    ];

    public function goal()
    {
        return $this->belongsTo(SmartGoal::class, 'goal_id');
    }

    public function memberTasks()
    {
        return $this->hasMany(GoalMemberTask::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(GoalSubtask::class, 'direct_task_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'assigned_to_id', 'departmentID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'assigned_to_id');
    }

    public function staff()
    {
        return $this->belongsTo(OtherStaff::class, 'assigned_to_id');
    }
    public function getProgressAttribute()
    {
        $taskEarnedScore = 0;

        // 1. Roll up from Member Tasks (Delegated to Dept Members)
        foreach ($this->memberTasks()->with('subtasks')->get() as $mTask) {
            $mTaskProgress = $mTask->subtasks->where('is_approved', true)->sum('marks');
            $taskEarnedScore += ($mTaskProgress / 100) * $mTask->weight;
        }

        // 2. Roll up from Direct Subtasks
        $directSubProgress = $this->subtasks()->where('is_approved', true)->sum('marks');
        $taskEarnedScore += $directSubProgress;

        return $taskEarnedScore;
    }
}
