<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;

class DeleteAllAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:delete-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete all attendance records from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Attendance::count();
        
        if ($count === 0) {
            $this->info('✓ No attendance records to delete.');
            return 0;
        }
        
        $this->warn("⚠️  WARNING: This will permanently delete {$count} attendance record(s)!");
        $this->warn('   This action CANNOT be undone!');
        
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all attendance records?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        try {
            $this->info('Deleting all attendance records...');
            
            // For SQLite, use DELETE FROM (TRUNCATE doesn't work the same way)
            // Disable foreign key checks temporarily if needed
            try {
                \DB::statement('PRAGMA foreign_keys = OFF');
            } catch (\Exception $e) {
                // Not SQLite or foreign keys not an issue
            }
            
            // Permanently delete all records - use multiple methods to ensure deletion
            \DB::table('attendances')->delete();
            \DB::statement('DELETE FROM attendances');
            
            // Re-enable foreign keys
            try {
                \DB::statement('PRAGMA foreign_keys = ON');
            } catch (\Exception $e) {
                // Not SQLite
            }
            
            // Clear any model cache
            Attendance::flushEventListeners();
            
            // Verify deletion
            $remaining = \DB::table('attendances')->count();
            
            Log::info("Deleted all {$count} attendance records via Artisan command. Remaining: {$remaining}");
            
            if ($remaining === 0) {
                $this->info("✓ Successfully deleted {$count} attendance record(s) from database.");
                $this->info('✓ Database is now clean and ready for fresh data.');
                $this->warn('⚠️  Note: If auto-sync or Push SDK is active, records may be recreated automatically.');
            } else {
                $this->warn("⚠️  Deleted records, but {$remaining} record(s) still remain.");
                $this->warn('   Try disabling auto-sync or Push SDK before deleting.');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Error deleting attendance records: ' . $e->getMessage());
            Log::error('Delete all attendances command error: ' . $e->getMessage());
            return 1;
        }
    }
}
