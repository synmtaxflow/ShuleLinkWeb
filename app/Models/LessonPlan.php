<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    protected $table = 'lesson_plans';
    protected $primaryKey = 'lesson_planID';

    protected $fillable = [
        'schoolID',
        'session_timetableID',
        'teacherID',
        'lesson_date',
        'lesson_time_start',
        'lesson_time_end',
        'subject',
        'class_name',
        'year',
        'registered_girls',
        'registered_boys',
        'registered_total',
        'present_girls',
        'present_boys',
        'present_total',
        'main_competence',
        'specific_competence',
        'main_activity',
        'specific_activity',
        'teaching_learning_resources',
        'references',
        'lesson_stages',
        'remarks',
        'reflection',
        'evaluation',
        'teacher_signature',
        'supervisor_signature',
        'status',
        'sent_to_admin',
        'sent_at',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'lesson_time_start' => 'string',
        'lesson_time_end' => 'string',
        'year' => 'integer',
        'registered_girls' => 'integer',
        'registered_boys' => 'integer',
        'registered_total' => 'integer',
        'present_girls' => 'integer',
        'present_boys' => 'integer',
        'present_total' => 'integer',
        'lesson_stages' => 'array',
        'sent_to_admin' => 'boolean',
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function sessionTimetable()
    {
        return $this->belongsTo(ClassSessionTimetable::class, 'session_timetableID', 'session_timetableID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }
}
