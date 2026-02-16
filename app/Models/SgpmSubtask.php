<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SgpmSubtask extends Model
{
    protected $primaryKey = 'subtaskID';

    protected $fillable = [
        'taskID',
        'title',
        'description',
        'weight_percentage',
        'achieved_score',
        'status',
        'due_date',
        'evidence_remarks',
        'evidence_file',
        'hod_comments',
    ];

    public function task()
    {
        return $this->belongsTo(SgpmTask::class, 'taskID');
    }
}
