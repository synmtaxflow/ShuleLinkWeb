<?php

namespace App\Services;

use App\Models\SgpmNotification;
use App\Models\User;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\School;
use App\Services\SmsService;

class SgpmNotificationService {
    protected $smsService;

    public function __construct() {
        $this->smsService = new SmsService();
    }

    /**
     * Notify a specific user via DB and SMS
     */
    public function notify($userId, $title, $message, $link, $type) {
        // 1. Database Notification
        SgpmNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'type' => $type,
        ]);

        // 2. SMS Notification
        $user = User::find($userId);
        if ($user) {
            $phone = $this->getUserPhone($user);
            if ($phone) {
                $this->smsService->sendSms($phone, $message);
            }
        }
    }

    /**
     * Notify Admin(s) and also send SMS to school phone
     */
    public function notifyAdmin($title, $message, $link, $schoolId) {
        $admins = User::where('schoolID', $schoolId)->where('user_type', 'Admin')->get();
        foreach($admins as $admin) {
            // Add DB notification for each admin
            SgpmNotification::create([
                'user_id' => $admin->id,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'type' => 'General',
            ]);
        }

        // Send SMS to School Phone Number
        $school = School::find($schoolId);
        if ($school && $school->phone) {
            $this->smsService->sendSms($school->phone, $message);
        }
    }

    protected function getUserPhone($user) {
        $teacher = Teacher::where('employee_number', $user->name)->first();
        if ($teacher) return $teacher->phone_number;
        
        $staff = OtherStaff::where('employee_number', $user->name)->first();
        if ($staff) return $staff->phone_number;

        return null;
    }
}
