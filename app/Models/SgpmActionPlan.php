<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SgpmActionPlan extends Model
{
    use HasFactory;

    protected $primaryKey = 'action_planID';

    protected $fillable = [
        'objectiveID',
        'title',
        'milestones',
        'deadline',
        'output',
    ];

    public function objective()
    {
        return $this->belongsTo(DepartmentalObjective::class, 'objectiveID');
    }

    public function tasks()
    {
        return $this->hasMany(SgpmTask::class, 'action_planID');
    }
}
