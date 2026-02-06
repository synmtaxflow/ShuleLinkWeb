<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPaperQuestion extends Model
{
    use HasFactory;

    protected $table = 'exam_paper_questions';
    protected $primaryKey = 'exam_paper_questionID';

    protected $fillable = [
        'exam_paperID',
        'question_number',
        'is_optional',
        'optional_range_number',
        'question_description',
        'marks',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function examPaper()
    {
        return $this->belongsTo(ExamPaper::class, 'exam_paperID', 'exam_paperID');
    }
}
