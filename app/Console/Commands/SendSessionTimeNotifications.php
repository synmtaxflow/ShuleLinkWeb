<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\ClassSessionTimetable;
use App\Models\School;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendSessionTimeNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:notify-teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS notifications to teachers when their session time arrives';

    protected $smsService;

    public function __construct()
    {
        parent::__construct();
        $this->smsService = new SmsService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for sessions starting now...');
        
        $now = Carbon::now();
        $today = Carbon::today();
        $todayDayName = $today->format('l'); // Monday, Tuesday, etc.
        
        // Get all active session timetable definitions
        $definitions = DB::table('session_timetable_definitions')
            ->get();
        
        if ($definitions->isEmpty()) {
            $this->info('No session timetable definitions found.');
            return 0;
        }
        
        $sentCount = 0;
        $failedCount = 0;
        
        foreach ($definitions as $definition) {
            // Get all sessions for today
            $sessions = ClassSessionTimetable::with(['subject', 'subclass.class', 'teacher'])
                ->where('definitionID', $definition->definitionID)
                ->where('day', $todayDayName)
                ->get();
            
            foreach ($sessions as $session) {
                // Parse session time for today
                $startTimeParts = explode(':', $session->start_time);
                $sessionStartTime = $today->copy()->setTime((int)$startTimeParts[0], (int)($startTimeParts[1] ?? 0), 0);
                
                // Check if session is starting now (within 5 minutes window)
                $timeDiff = $now->diffInMinutes($sessionStartTime, false);
                
                // Send notification if session starts in 0-5 minutes (not in the past, not too far in future)
                if ($timeDiff >= 0 && $timeDiff <= 5) {
                    // Check if we already sent SMS for this session today
                    $cacheKey = "session_notification_sent_{$session->session_timetableID}_{$today->format('Y-m-d')}";
                    
                    if (Cache::has($cacheKey)) {
                        continue; // Already sent, skip
                    }
                    
                    // Get teacher
                    $teacher = $session->teacher;
                    if (!$teacher || !$teacher->phone_number) {
                        $failedCount++;
                        Log::warning("Cannot send session notification: Teacher {$session->teacherID} not found or has no phone number");
                        continue;
                    }
                    
                    // Get school name
                    $school = School::find($definition->schoolID);
                    $schoolName = $school ? $school->school_name : 'ShuleLink';
                    
                    // Prepare message
                    $subjectName = $session->subject->subject_name ?? 'N/A';
                    $className = $session->subclass 
                        ? ($session->subclass->class->class_name ?? '') . ' - ' . ($session->subclass->subclass_name ?? '')
                        : 'N/A';
                    
                    $startTimeFormatted = Carbon::parse($session->start_time)->format('h:i A');
                    $message = "{$schoolName}. Mudawako wa session umefika. Darasa: {$className}, Somo: {$subjectName}, Muda: {$startTimeFormatted}. Asante!";
                    
                    // Send SMS
                    try {
                        $result = $this->smsService->sendSms($teacher->phone_number, $message);
                        
                        if ($result['success']) {
                            // Mark as sent in cache (expires at end of day)
                            Cache::put($cacheKey, true, $today->copy()->endOfDay());
                            
                            $sentCount++;
                            $this->info("SMS sent to teacher {$teacher->id} ({$teacher->first_name} {$teacher->last_name}) for session at {$startTimeFormatted}");
                            Log::info("Session notification SMS sent to teacher {$teacher->id} for session {$session->session_timetableID}");
                        } else {
                            $failedCount++;
                            $this->error("Failed to send SMS to teacher {$teacher->id}: " . ($result['message'] ?? 'Unknown error'));
                            Log::error("Failed to send session notification SMS to teacher {$teacher->id}: " . ($result['message'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $this->error("Exception sending SMS to teacher {$teacher->id}: " . $e->getMessage());
                        Log::error("Exception sending session notification SMS to teacher {$teacher->id}: " . $e->getMessage());
                    }
                }
            }
        }
        
        $this->info("Notification check completed. Sent: {$sentCount}, Failed: {$failedCount}");
        return 0;
    }
}
