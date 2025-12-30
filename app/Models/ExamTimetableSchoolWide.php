<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamTimetableSchoolWide extends Model
{
    use HasFactory;

    protected $table = 'exam_timetable';
    protected $primaryKey = 'exam_timetableID';

    protected $fillable = [
        'schoolID',
        'examID',
        'exam_date',
        'day',
        'subjectID',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }

    public function subject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function superviseTeachers()
    {
        return $this->hasMany(ExamSuperviseTeacher::class, 'exam_timetableID', 'exam_timetableID');
    }
}
