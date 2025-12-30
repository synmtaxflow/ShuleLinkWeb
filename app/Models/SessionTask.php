<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionTask extends Model
{
    protected $table = 'session_tasks';
    protected $primaryKey = 'session_taskID';

    protected $fillable = [
        'schoolID',
        'session_timetableID',
        'teacherID',
        'task_date',
        'topic',
        'subtopic',
        'task_description',
        'status',
        'admin_comment',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'task_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function sessionTimetable()
    {
        return $this->belongsTo(ClassSessionTimetable::class, 'session_timetableID', 'session_timetableID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
