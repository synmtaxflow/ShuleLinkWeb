<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;

class FixAttendanceStatus extends Command
{
    protected $signature = 'attendances:fix-status';
    protected $description = 'Fix attendance records with status=15 (should be 0 for Check Out)';

    public function handle()
    {
        $this->info('Fixing attendance records with status=15...');
        
        $records = Attendance::where('status', 15)->get();
        
        $this->info("Found {$records->count()} records with status=15");
        
        if ($records->count() > 0) {
            $updated = 0;
            foreach ($records as $record) {
                $record->status = 0; // Check Out
                $record->save();
                $updated++;
            }
            
            $this->info("✓ Updated {$updated} records: status 15 → 0 (Check Out)");
        } else {
            $this->info("No records with status=15 found.");
        }
        
        return 0;
    }
}






