<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'schoolID',
        'goal_name',
        'target_percentage',
        'deadline',
        'created_by',
        'status',
    ];

    public function tasks()
    {
        return $this->hasMany(GoalTask::class, 'goal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function getProgressAttribute()
    {
        $tasks = $this->tasks;
        if ($tasks->isEmpty()) return 0;

        $totalGoalStatusProgress = 0;
        $totalWeight = 0;

        foreach ($tasks as $task) {
            $totalGoalStatusProgress += ($task->progress / 100) * $task->weight;
            $totalWeight += $task->weight;
        }

        return $totalWeight > 0 ? ($totalGoalStatusProgress / $totalWeight) * 100 : 0;
    }
}
