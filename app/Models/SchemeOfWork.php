<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchemeOfWork extends Model
{
    use HasFactory;

    protected $table = 'scheme_of_works';
    protected $primaryKey = 'scheme_of_workID';

    protected $fillable = [
        'class_subjectID',
        'year',
        'status',
        'created_by'
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    // Relationships
    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subjectID', 'class_subjectID');
    }

    public function createdBy()
    {
        return $this->belongsTo(Teacher::class, 'created_by', 'id');
    }

    public function items()
    {
        return $this->hasMany(SchemeOfWorkItem::class, 'scheme_of_workID', 'scheme_of_workID')->orderBy('month')->orderBy('row_order');
    }

    public function learningObjectives()
    {
        return $this->hasMany(SchemeOfWorkLearningObjective::class, 'scheme_of_workID', 'scheme_of_workID')->orderBy('order');
    }
}
