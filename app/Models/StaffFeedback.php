<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffFeedback extends Model
{
    protected $table = 'staff_feedbacks';
    protected $primaryKey = 'feedbackID';

    protected $fillable = [
        'schoolID',
        'staffID',
        'type',
        'message',
        'status',
        'admin_response',
        'response_due_date',
        'responded_at',
        'is_read_by_admin',
        'is_read_by_staff',
    ];
}
