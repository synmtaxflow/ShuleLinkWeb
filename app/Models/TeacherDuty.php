<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Teacher;
use App\Models\School;
use App\Models\Term;
use App\Models\AcademicYear;

class TeacherDuty extends Model
{
    use HasFactory;

    protected $table = 'teacher_duties';
    protected $primaryKey = 'teacher_dutyID';

    protected $fillable = [
        'schoolID',
        'teacherID',
        'termID',
        'academic_yearID',
        'start_date',
        'end_date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'termID', 'termID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }
}
