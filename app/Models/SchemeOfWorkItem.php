<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchemeOfWorkItem extends Model
{
    use HasFactory;

    protected $table = 'scheme_of_work_items';
    protected $primaryKey = 'itemID';

    protected $fillable = [
        'scheme_of_workID',
        'main_competence',
        'specific_competences',
        'learning_activities',
        'specific_activities',
        'month',
        'week',
        'number_of_periods',
        'teaching_methods',
        'teaching_resources',
        'assessment_tools',
        'references',
        'remarks',
        'row_order'
    ];

    protected $casts = [
        'week' => 'integer',
        'number_of_periods' => 'integer',
        'row_order' => 'integer',
    ];

    // Relationships
    public function schemeOfWork()
    {
        return $this->belongsTo(SchemeOfWork::class, 'scheme_of_workID', 'scheme_of_workID');
    }
}
