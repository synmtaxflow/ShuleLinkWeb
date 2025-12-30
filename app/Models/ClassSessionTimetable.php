<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSessionTimetable extends Model
{
    protected $table = 'class_session_timetables';
    protected $primaryKey = 'session_timetableID';

    protected $fillable = [
        'schoolID',
        'definitionID',
        'subclassID',
        'class_subjectID',
        'subjectID',
        'teacherID',
        'session_typeID',
        'day',
        'start_time',
        'end_time',
        'is_prepo',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'string', // Store as time string, not datetime
        'end_time' => 'string', // Store as time string, not datetime
        'is_prepo' => 'boolean',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subjectID', 'class_subjectID');
    }

    public function subject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function sessionAttendances()
    {
        return $this->hasMany(StudentSessionAttendance::class, 'session_timetableID', 'session_timetableID');
    }

    public function tasks()
    {
        return $this->hasMany(SessionTask::class, 'session_timetableID', 'session_timetableID');
    }
}
