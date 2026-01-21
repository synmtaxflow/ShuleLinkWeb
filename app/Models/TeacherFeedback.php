<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherFeedback extends Model
{
    protected $table = 'teacher_feedbacks';
    protected $primaryKey = 'feedbackID';

    protected $fillable = [
        'schoolID',
        'teacherID',
        'type',
        'message',
        'status',
        'admin_response',
        'response_due_date',
        'responded_at',
        'is_read_by_admin',
        'is_read_by_teacher',
    ];
}
