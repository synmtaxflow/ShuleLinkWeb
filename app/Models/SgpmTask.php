<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SgpmTask extends Model
{
    use HasFactory;

    protected $primaryKey = 'taskID';

    protected $fillable = [
        'action_planID',
        'assigned_to',
        'kpi',
        'weight',
        'description',
        'status',
        'due_date',
        'completion_date',
        'score_completion',
        'score_kpi',
        'score_timeliness',
        'total_score',
        'progress',
        'hod_comments',
    ];

    public function actionPlan()
    {
        return $this->belongsTo(SgpmActionPlan::class, 'action_planID');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function evidence()
    {
        return $this->hasMany(SgpmEvidence::class, 'taskID');
    }

    public function subtasks()
    {
        return $this->hasMany(SgpmSubtask::class, 'taskID');
    }
}
