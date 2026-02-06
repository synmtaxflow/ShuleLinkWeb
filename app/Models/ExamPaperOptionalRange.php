<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPaperOptionalRange extends Model
{
    use HasFactory;

    protected $table = 'exam_paper_optional_ranges';
    protected $primaryKey = 'exam_paper_optional_rangeID';

    protected $fillable = [
        'exam_paperID',
        'range_number',
        'total_marks',
        'required_questions',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
