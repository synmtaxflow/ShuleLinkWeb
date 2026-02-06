<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPaperQuestionMark extends Model
{
    use HasFactory;

    protected $table = 'exam_paper_question_marks';
    protected $primaryKey = 'exam_paper_question_markID';

    protected $fillable = [
        'exam_paper_questionID',
        'studentID',
        'examID',
        'class_subjectID',
        'marks',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(ExamPaperQuestion::class, 'exam_paper_questionID', 'exam_paper_questionID');
    }
}
