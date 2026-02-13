<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPaperNotification extends Model
{
    use HasFactory;

    protected $table = 'exam_paper_notifications';
    protected $primaryKey = 'exam_paper_notificationID';

    protected $fillable = [
        'schoolID',
        'exam_paperID',
        'teacherID',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function examPaper()
    {
        return $this->belongsTo(ExamPaper::class, 'exam_paperID', 'exam_paperID');
    }
}
