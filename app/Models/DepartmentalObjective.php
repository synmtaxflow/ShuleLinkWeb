<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentalObjective extends Model
{
    use HasFactory;

    protected $primaryKey = 'objectiveID';

    protected $fillable = [
        'strategic_goalID',
        'departmentID',
        'kpi',
        'target_value',
        'budget',
        'status',
        'assigned_hod_id',
    ];

    public function strategicGoal()
    {
        return $this->belongsTo(StrategicGoal::class, 'strategic_goalID');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'departmentID');
    }

    public function actionPlans()
    {
        return $this->hasMany(SgpmActionPlan::class, 'objectiveID');
    }
}
