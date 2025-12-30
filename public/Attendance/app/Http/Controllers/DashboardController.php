<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalUsers = User::count();
        $registeredUsers = User::where('registered_on_device', true)->count();
        $totalAttendances = Attendance::count();
        
        // Today's statistics
        $today = Carbon::today();
        $todayAttendances = Attendance::whereDate('punch_time', $today)->count();
        $todayCheckIns = Attendance::whereDate('punch_time', $today)
            ->where('status', 1)
            ->count();
        $todayCheckOuts = Attendance::whereDate('punch_time', $today)
            ->where('status', 0)
            ->count();
        
        // This week's statistics
        $weekStart = Carbon::now()->startOfWeek();
        $weekAttendances = Attendance::where('punch_time', '>=', $weekStart)->count();
        
        // Recent attendances (last 10)
        $recentAttendances = Attendance::with('user')
            ->orderBy('punch_time', 'desc')
            ->limit(10)
            ->get();
        
        // Top users by attendance count
        $topUsers = User::withCount('attendances')
            ->orderBy('attendances_count', 'desc')
            ->limit(5)
            ->get();
        
        // Attendance by day (last 7 days)
        $attendanceByDay = Attendance::select(
                DB::raw('DATE(punch_time) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('punch_time', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
        
        // Users with most recent activity
        $activeUsers = User::whereHas('attendances', function($query) {
                $query->where('punch_time', '>=', Carbon::now()->subDays(7));
            })
            ->withCount(['attendances' => function($query) {
                $query->where('punch_time', '>=', Carbon::now()->subDays(7));
            }])
            ->orderBy('attendances_count', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalUsers',
            'registeredUsers',
            'totalAttendances',
            'todayAttendances',
            'todayCheckIns',
            'todayCheckOuts',
            'weekAttendances',
            'recentAttendances',
            'topUsers',
            'attendanceByDay',
            'activeUsers'
        ));
    }
}






