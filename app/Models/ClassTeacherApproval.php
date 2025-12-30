<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassTeacherApproval extends Model
{
    use HasFactory;

    protected $table = 'class_teacher_approvals';
    protected $primaryKey = 'class_teacher_approvalID';

    protected $fillable = [
        'result_approvalID',
        'subclassID',
        'status',
        'approved_by',
        'approved_at',
        'approval_comment',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function resultApproval()
    {
        return $this->belongsTo(ResultApproval::class, 'result_approvalID', 'result_approvalID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }

    public function approver()
    {
        return $this->belongsTo(Teacher::class, 'approved_by', 'id');
    }
}
