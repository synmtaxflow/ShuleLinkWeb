<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyDutyReport extends Model
{
    protected $primaryKey = 'reportID';

    protected $fillable = [
        'schoolID',
        'teacherID',
        'report_date',
        'attendance_data',
        'attendance_percentage',
        'school_environment',
        'pupils_cleanliness',
        'teachers_attendance',
        'timetable_status',
        'outside_activities',
        'special_events',
        'teacher_comments',
        'admin_comments',
        'signed_by',
        'signed_at',
        'approved_by_id',
        'signature_image',
        'status',
    ];

    protected $casts = [
        'attendance_data' => 'array',
        'report_date' => 'date',
        'signed_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID');
    }
}
