<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSuperviseTeacher extends Model
{
    use HasFactory;

    protected $table = 'exam_supervise_teacher';
    protected $primaryKey = 'exam_supervise_teacherID';

    protected $fillable = [
        'schoolID',
        'examID',
        'exam_timetableID',
        'subclassID',
        'teacherID',
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

    public function examTimetable()
    {
        return $this->belongsTo(ExamTimetableSchoolWide::class, 'exam_timetableID', 'exam_timetableID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }
}
