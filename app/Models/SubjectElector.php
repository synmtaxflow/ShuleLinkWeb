<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectElector extends Model
{
    use HasFactory;

    protected $table = 'subject_electors';
    protected $primaryKey = 'electorID';

    protected $fillable = [
        'studentID',
        'classSubjectID',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'classSubjectID', 'class_subjectID');
    }
}





