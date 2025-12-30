<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExamHall extends Model
{
    use HasFactory;

    protected $table = 'student_exam_halls';
    protected $primaryKey = 'id';

    protected $fillable = [
        'examID',
        'exam_hallID',
        'studentID',
        'subclassID',
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

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }
}






