<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalSubtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_task_id',
        'direct_task_id',
        'subtask_name',
        'description',
        'weight',
        'marks',
        'status',
        'is_sent_to_hod',
        'is_approved',
        'approved_by',
    ];

    public function memberTask()
    {
        return $this->belongsTo(GoalMemberTask::class, 'member_task_id');
    }

    public function steps()
    {
        return $this->hasMany(GoalSubtaskStep::class, 'subtask_id');
    }

    public function directTask()
    {
        return $this->belongsTo(GoalTask::class, 'direct_task_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
