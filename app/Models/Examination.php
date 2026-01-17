<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examination extends Model
{
    use HasFactory;

    protected $table = 'examinations';
    protected $primaryKey = 'examID';

    protected $fillable = [
        'exam_name',
        'exam_category',
        'term',
        'except_class_ids',
        'start_date',
        'end_date',
        'status',
        'approval_status',
        'rejection_reason',
        'exam_type',
        'student_shifting_status',
        'schoolID',
        'year',
        'details',
        'created_by',
        'enter_result',
        'publish_result',
        'upload_paper',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'year' => 'integer',
        'except_class_ids' => 'array',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'examID', 'examID');
    }

    public function resultApprovals()
    {
        return $this->hasMany(ResultApproval::class, 'examID', 'examID')->orderBy('approval_order');
    }

    public function examPapers()
    {
        return $this->hasMany(ExamPaper::class, 'examID', 'examID');
    }
}
