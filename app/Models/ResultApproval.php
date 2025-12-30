<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultApproval extends Model
{
    use HasFactory;

    protected $table = 'result_approvals';
    protected $primaryKey = 'result_approvalID';

    protected $fillable = [
        'examID',
        'role_id',
        'special_role_type',
        'approval_order',
        'status',
        'approved_by',
        'approved_at',
        'approval_comment',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'approval_order' => 'integer',
    ];

    // Relationships
    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examID', 'examID');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(Teacher::class, 'approved_by', 'id');
    }

    // Relationships for special roles
    public function classTeacherApprovals()
    {
        return $this->hasMany(ClassTeacherApproval::class, 'result_approvalID', 'result_approvalID');
    }

    public function coordinatorApprovals()
    {
        return $this->hasMany(CoordinatorApproval::class, 'result_approvalID', 'result_approvalID');
    }
}
