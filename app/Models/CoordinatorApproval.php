<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoordinatorApproval extends Model
{
    use HasFactory;

    protected $table = 'coordinator_approvals';
    protected $primaryKey = 'coordinator_approvalID';

    protected $fillable = [
        'result_approvalID',
        'mainclassID',
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

    public function mainclass()
    {
        return $this->belongsTo(ClassModel::class, 'mainclassID', 'classID');
    }

    public function approver()
    {
        return $this->belongsTo(Teacher::class, 'approved_by', 'id');
    }
}
