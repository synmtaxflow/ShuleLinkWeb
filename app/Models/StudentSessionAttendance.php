<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSessionAttendance extends Model
{
    protected $table = 'student_session_attendance';
    protected $primaryKey = 'session_attendanceID';

    protected $fillable = [
        'schoolID',
        'session_timetableID',
        'studentID',
        'teacherID',
        'attendance_date',
        'status',
        'remark',
    ];

    protected $casts = [
        'attendance_date' => 'date',
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

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }
}
