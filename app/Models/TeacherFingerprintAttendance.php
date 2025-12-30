<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherFingerprintAttendance extends Model
{
    use HasFactory;

    protected $table = 'teacher_fingerprint_attendance';

    protected $fillable = [
        'teacherID',
        'user_id',
        'user_name',
        'enroll_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'verify_mode',
        'device_ip',
        'external_id',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // Relationship to Teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }
}

