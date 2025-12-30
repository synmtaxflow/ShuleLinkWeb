<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamHall extends Model
{
    use HasFactory;

    protected $table = 'exam_halls';
    protected $primaryKey = 'exam_hallID';

    protected $fillable = [
        'examID',
        'classID',
        'hall_name',
        'capacity',
        'gender_allowed',
        'schoolID',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    // Relationships
    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function students()
    {
        return $this->hasMany(StudentExamHall::class, 'exam_hallID', 'exam_hallID');
    }

    public function supervisors()
    {
        return $this->hasMany(ExamHallSupervisor::class, 'exam_hallID', 'exam_hallID');
    }
}






