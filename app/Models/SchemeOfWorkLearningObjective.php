<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchemeOfWorkLearningObjective extends Model
{
    use HasFactory;

    protected $table = 'scheme_of_work_learning_objectives';
    protected $primaryKey = 'objectiveID';

    protected $fillable = [
        'scheme_of_workID',
        'objective_text',
        'order'
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // Relationships
    public function schemeOfWork()
    {
        return $this->belongsTo(SchemeOfWork::class, 'scheme_of_workID', 'scheme_of_workID');
    }
}
