<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSubject extends Model
{
    use HasFactory;

    protected $table = 'school_subjects';
    protected $primaryKey = 'subjectID';

    protected $fillable = [
        'schoolID',
        'subject_name',
        'subject_code',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'subjectID', 'subjectID');
    }
}
