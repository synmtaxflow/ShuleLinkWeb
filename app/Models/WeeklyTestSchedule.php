<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyTestSchedule extends Model
{
    protected $table = 'weekly_test_schedules';

    protected $fillable = [
        'schoolID',
        'examID',
        'test_type',
        'week_number',
        'day',
        'scope',
        'scope_id',
        'subjectID',
        'teacher_id',
        'start_time',
        'end_time',
        'supervisor_ids',
        'created_by'
    ];

    public function subject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
