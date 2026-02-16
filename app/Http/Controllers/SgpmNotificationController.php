<?php

namespace App\Http\Controllers;

use App\Models\SgpmNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SgpmNotificationController extends Controller
{
    public function markAsRead(Request $request)
    {
        $notification = SgpmNotification::find($request->notificationID);
        if ($notification && $notification->user_id == Session::get('userID')) {
            $notification->is_read = true;
            $notification->save();
        }
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        SgpmNotification::where('user_id', Session::get('userID'))
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }
}
