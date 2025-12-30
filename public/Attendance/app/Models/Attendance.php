<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Http\Controllers\Api\WebhookController;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enroll_id',
        'punch_time',
        'status',
        'verify_mode',
        'device_ip',
        'check_in_time',
        'check_out_time',
        'attendance_date',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'attendance_date' => 'date',
    ];

    /**
     * Boot the model and register event listeners
     */
    protected static function booted()
    {
        // Send webhook when attendance is created
        static::created(function ($attendance) {
            // Load user relationship before sending webhook
            $attendance->load('user');
            // Send webhook asynchronously (in background)
            dispatch(function() use ($attendance) {
                WebhookController::sendAttendanceWebhook($attendance);
            })->afterResponse();
        });

        // Send webhook when attendance is updated (e.g., check-out time added)
        static::updated(function ($attendance) {
            // Only send webhook if check-in or check-out time was changed
            if ($attendance->wasChanged('check_in_time') || $attendance->wasChanged('check_out_time')) {
                // Load user relationship before sending webhook
                $attendance->load('user');
                // Send webhook asynchronously (in background)
                dispatch(function() use ($attendance) {
                    WebhookController::sendAttendanceWebhook($attendance);
                })->afterResponse();
            }
        });
    }

    /**
     * Get the user that owns the attendance record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
