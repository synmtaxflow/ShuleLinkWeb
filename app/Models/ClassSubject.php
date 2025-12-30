<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    use HasFactory;

    protected $table = 'class_subjects';
    protected $primaryKey = 'class_subjectID';

    protected $fillable = [
        'classID',
        'subclassID',
        'subjectID',
        'teacherID',
        'status',
        'student_status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function subject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }

    public function electors()
    {
        return $this->hasMany(SubjectElector::class, 'classSubjectID', 'class_subjectID');
    }
}
