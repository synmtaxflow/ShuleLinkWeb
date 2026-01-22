<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionRequest extends Model
{
    use HasFactory;

    protected $table = 'permission_requests';
    protected $primaryKey = 'permissionID';

    protected $fillable = [
        'schoolID',
        'requester_type',
        'teacherID',
        'studentID',
        'parentID',
        'time_mode',
        'days_count',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'reason_type',
        'reason_description',
        'attachment_path',
        'status',
        'admin_response',
        'admin_attachment_path',
        'reviewed_at',
        'is_read_by_admin',
        'is_read_by_requester',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
        'is_read_by_admin' => 'boolean',
        'is_read_by_requester' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parentID', 'parentID');
    }
}
