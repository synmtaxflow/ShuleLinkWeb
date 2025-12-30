<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckDuplicateUsers extends Command
{
    protected $signature = 'users:check-duplicates';
    protected $description = 'Check for duplicate users with same enroll_id';

    public function handle()
    {
        $users = User::all(['id', 'name', 'enroll_id', 'email']);
        
        $this->info("Total users: " . $users->count());
        
        $duplicates = $users->groupBy('enroll_id')->filter(function($group) {
            return $group->count() > 1;
        });
        
        if ($duplicates->count() > 0) {
            $this->warn("Found duplicate enroll_ids:");
            foreach ($duplicates as $enrollId => $group) {
                $this->warn("Enroll ID {$enrollId}:");
                foreach ($group as $user) {
                    $this->line("  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}");
                }
            }
        } else {
            $this->info("No duplicate enroll_ids found.");
        }
        
        return 0;
    }
}






