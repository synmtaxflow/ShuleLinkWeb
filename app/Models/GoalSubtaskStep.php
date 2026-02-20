<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalSubtaskStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtask_id',
        'date',
        'step_description',
    ];

    public function subtask()
    {
        return $this->belongsTo(GoalSubtask::class, 'subtask_id');
    }
}
