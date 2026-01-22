<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolVisitorSmsLog extends Model
{
    use HasFactory;

    protected $table = 'school_visitor_sms_logs';

    protected $fillable = [
        'schoolID',
        'message',
        'recipient_count',
        'recipient_ids',
    ];

    protected $casts = [
        'recipient_ids' => 'array',
    ];
}
