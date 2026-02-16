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
}
