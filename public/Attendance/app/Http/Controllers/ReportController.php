<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Daily attendance report
     */
    public function daily(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        
        $attendances = Attendance::with('user')
            ->whereDate('punch_time', $selectedDate)
            ->orderBy('punch_time', 'desc')
            ->get();
        
        $summary = [
            'total' => $attendances->count(),
            'check_ins' => $attendances->where('status', 1)->count(),
            'check_outs' => $attendances->where('status', 0)->count(),
            'unique_users' => $attendances->pluck('user_id')->unique()->count(),
        ];
        
        return view('reports.daily', compact('attendances', 'summary', 'selectedDate'));
    }
    
    /**
     * User attendance summary
     */
    public function userSummary($userId)
    {
        $user = User::findOrFail($userId);
        
        $totalRecords = $user->attendances()->count();
        $thisMonth = $user->attendances()
            ->whereMonth('punch_time', Carbon::now()->month)
            ->whereYear('punch_time', Carbon::now()->year)
            ->count();
        
        $thisWeek = $user->attendances()
            ->where('punch_time', '>=', Carbon::now()->startOfWeek())
            ->count();
        
        $today = $user->attendances()
            ->whereDate('punch_time', Carbon::today())
            ->count();
        
        $recentAttendances = $user->attendances()
            ->orderBy('punch_time', 'desc')
            ->limit(20)
            ->get();
        
        return view('reports.user-summary', compact('user', 'totalRecords', 'thisMonth', 'thisWeek', 'today', 'recentAttendances'));
    }
}






