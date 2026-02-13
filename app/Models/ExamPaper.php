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
        'weekly_test_schedule_id',
        'class_subjectID',
        'teacherID',
        'file_path',
        'question_content',
        'optional_question_total',
        'upload_type',
        'test_week',
        'test_week_range',
        'test_date',
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

    public function weeklyTestSchedule()
    {
        return $this->belongsTo(WeeklyTestSchedule::class, 'weekly_test_schedule_id');
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subjectID', 'class_subjectID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function questions()
    {
        return $this->hasMany(ExamPaperQuestion::class, 'exam_paperID', 'exam_paperID');
    }

    public function optionalRanges()
    {
        return $this->hasMany(ExamPaperOptionalRange::class, 'exam_paperID', 'exam_paperID');
    }
}

