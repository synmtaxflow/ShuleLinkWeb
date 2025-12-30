<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetController extends Controller
{
    /**
     * Show reset confirmation page
     */
    public function index()
    {
        // OPTIMIZATION: Use single queries with conditional aggregation instead of multiple count() calls
        // This is much faster, especially for large tables
        
        try {
            // Get user statistics in one query
            $userStats = User::selectRaw('
                COUNT(*) as total_users,
                SUM(CASE WHEN registered_on_device = 1 THEN 1 ELSE 0 END) as registered_users
            ')->first();
            
            // Get attendance statistics - use simple count for total (fast)
            // For today's count, use a more efficient query
            $totalAttendances = Attendance::count();
            
            // For today's count, use date comparison that's indexed
            $todayStart = today()->startOfDay();
            $todayEnd = today()->endOfDay();
            $todayAttendances = Attendance::whereBetween('punch_time', [$todayStart, $todayEnd])->count();
            
            $stats = [
                'total_users' => $userStats->total_users ?? 0,
                'registered_users' => $userStats->registered_users ?? 0,
                'total_attendances' => $totalAttendances,
                'today_attendances' => $todayAttendances,
            ];
        } catch (\Exception $e) {
            // Fallback to simple counts if optimized query fails
            Log::warning('Reset page stats query failed, using fallback: ' . $e->getMessage());
            $stats = [
                'total_users' => User::count(),
                'registered_users' => User::where('registered_on_device', true)->count(),
                'total_attendances' => Attendance::count(),
                'today_attendances' => Attendance::whereDate('punch_time', today())->count(),
            ];
        }
        
        return view('reset.index', compact('stats'));
    }
    
    /**
     * Delete all attendance records
     */
    public function deleteAttendances(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:DELETE_ALL'
        ]);
        
        try {
            $count = Attendance::count();
            
            // Use DELETE instead of TRUNCATE to avoid foreign key constraint issues
            // TRUNCATE doesn't work with foreign keys in MySQL
            Attendance::query()->delete();
            
            Log::info("All attendance records deleted. Total deleted: {$count}");
            
            return redirect()->route('attendances.index')
                ->with('success', "Successfully deleted {$count} attendance record(s). Database is now clean!");
        } catch (\Exception $e) {
            Log::error("Error deleting attendance records: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting records: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete all users
     */
    public function deleteUsers(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:DELETE_ALL'
        ]);
        
        try {
            $count = User::count();
            
            // Delete attendances first (due to foreign key constraints)
            // Then delete users
            // Use DELETE instead of TRUNCATE to avoid foreign key constraint issues
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Attendance::query()->delete();
            User::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            Log::info("All users deleted. Total deleted: {$count}");
            
            return redirect()->route('users.index')
                ->with('success', "Successfully deleted {$count} user(s). Database is now clean!");
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $e2) {
                // Ignore
            }
            Log::error("Error deleting users: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting users: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete everything (users and attendances)
     */
    public function deleteAll(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:DELETE_EVERYTHING'
        ]);
        
        try {
            DB::beginTransaction();
            
            $attendanceCount = Attendance::count();
            $userCount = User::count();
            
            // Disable foreign key checks temporarily (MySQL requirement)
            // This allows us to delete in any order
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Delete attendances first (due to foreign key constraints)
            Attendance::query()->delete();
            
            // Delete users
            User::query()->delete();
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            DB::commit();
            
            Log::info("Complete database reset. Deleted {$userCount} users and {$attendanceCount} attendance records.");
            
            return redirect()->route('dashboard')
                ->with('success', "✅ Fresh Start Complete! Deleted {$userCount} user(s) and {$attendanceCount} attendance record(s). Database is now clean and ready for fresh data.");
        } catch (\Exception $e) {
            DB::rollBack();
            // Re-enable foreign key checks in case of error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $e2) {
                // Ignore
            }
            Log::error("Error resetting database: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error resetting database: ' . $e->getMessage());
        }
    }
}


