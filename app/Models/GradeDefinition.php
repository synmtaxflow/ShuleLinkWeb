<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GradeDefinition extends Model
{
    use HasFactory;

    protected $table = 'grade_definitions';
    protected $primaryKey = 'gradeDefinitionID';

    protected $fillable = [
        'classID',
        'first',
        'last',
        'grade',
    ];

    // Relationships
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }
}
