<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrategicGoal extends Model
{
    use HasFactory;

    protected $primaryKey = 'strategic_goalID';

    protected $fillable = [
        'schoolID',
        'title',
        'description',
        'kpi',
        'target_value',
        'timeline_date',
        'supporting_document',
        'status',
        'created_by',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function objectives()
    {
        return $this->hasMany(DepartmentalObjective::class, 'strategic_goalID');
    }

    /**
     * Calculate overall progress of this strategic goal
     * Based on all tasks and subtasks across all departmental objectives
     */
    public function calculateProgress()
    {
        $objectives = $this->objectives;
        
        if ($objectives->isEmpty()) {
            return 0;
        }

        $totalWeight = 0;
        $totalAchieved = 0;

        foreach ($objectives as $objective) {
            // Get all action plans for this objective
            $actionPlans = $objective->actionPlans;
            
            foreach ($actionPlans as $plan) {
                // Get all tasks for this action plan
                $tasks = $plan->tasks;
                
                foreach ($tasks as $task) {
                    $totalWeight += $task->weight;
                    $totalAchieved += $task->progress; // Progress based on approved subtasks
                }
            }
        }

        if ($totalWeight == 0) {
            return 0;
        }

        return round(($totalAchieved / $totalWeight) * 100, 2);
    }

    /**
     * Get statistics for this strategic goal
     */
    public function getStatistics()
    {
        $stats = [
            'total_objectives' => 0,
            'total_action_plans' => 0,
            'total_tasks' => 0,
            'total_subtasks' => 0,
            'completed_tasks' => 0,
            'approved_subtasks' => 0,
            'overall_progress' => 0,
        ];

        $objectives = $this->objectives;
        $stats['total_objectives'] = $objectives->count();

        foreach ($objectives as $objective) {
            $actionPlans = $objective->actionPlans;
            $stats['total_action_plans'] += $actionPlans->count();
            
            foreach ($actionPlans as $plan) {
                $tasks = $plan->tasks;
                $stats['total_tasks'] += $tasks->count();
                
                foreach ($tasks as $task) {
                    if ($task->status == 'Completed' || $task->status == 'Approved') {
                        $stats['completed_tasks']++;
                    }
                    
                    $subtasks = $task->subtasks;
                    $stats['total_subtasks'] += $subtasks->count();
                    $stats['approved_subtasks'] += $subtasks->where('status', 'Approved')->count();
                }
            }
        }

        $stats['overall_progress'] = $this->calculateProgress();

        return $stats;
    }
}
