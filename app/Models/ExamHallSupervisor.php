<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamHallSupervisor extends Model
{
    use HasFactory;

    protected $table = 'exam_hall_supervisors';
    protected $primaryKey = 'exam_hall_supervisorID';

    protected $fillable = [
        'examID',
        'exam_hallID',
        'teacherID',
        'subjectID',
        'exam_timetableID',
        'schoolID',
    ];

    // Relationships
    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }

    public function examHall()
    {
        return $this->belongsTo(ExamHall::class, 'exam_hallID', 'exam_hallID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function subject()
    {
        return $this->belongsTo(\App\Models\SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function examTimetable()
    {
        return $this->belongsTo(\App\Models\ExamTimetableSchoolWide::class, 'exam_timetableID', 'exam_timetableID');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}


