<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalMemberTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_task_id',
        'task_name',
        'description',
        'member_id',
        'member_type',
        'weight',
        'status',
    ];

    public function parentTask()
    {
        return $this->belongsTo(GoalTask::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(GoalSubtask::class, 'member_task_id');
    }

    public function member()
    {
        if ($this->member_type === 'Teacher') {
            return $this->belongsTo(Teacher::class, 'member_id');
        } else {
            return $this->belongsTo(OtherStaff::class, 'member_id');
        }
    }

    public function getProgressAttribute()
    {
        return $this->subtasks()->where('is_approved', true)->sum('marks');
    }
}
