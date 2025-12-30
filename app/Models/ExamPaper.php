<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPaper extends Model
{
    use HasFactory;

    protected $table = 'exam_papers';
    protected $primaryKey = 'exam_paperID';

    protected $fillable = [
        'examID',
        'class_subjectID',
        'teacherID',
        'file_path',
        'question_content',
        'upload_type',
        'status',
        'rejection_reason',
        'approval_comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subjectID', 'class_subjectID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }
}

