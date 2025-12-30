<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subclass extends Model
{
    use HasFactory;

    protected $table = 'subclasses';
    protected $primaryKey = 'subclassID';

    protected $fillable = [
        'classID',
        'teacherID',
        'combieID',
        'subclass_name',
        'stream_code',
        'status',
        'first_grade',
        'final_grade',
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function classTeacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    // Alias for classTeacher - for convenience
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function combie()
    {
        return $this->belongsTo(Combie::class, 'combieID', 'combieID');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'subclassID', 'subclassID');
    }

    // Get subjects through class
    public function subjects()
    {
        return $this->hasManyThrough(
            ClassSubject::class,
            ClassModel::class,
            'classID', // Foreign key on classes table
            'classID', // Foreign key on class_subjects table
            'classID', // Local key on subclasses table
            'classID'  // Local key on classes table
        )->with('subject', 'teacher');
    }

    /**
     * Get display name (class_name + subclass_name)
     */
    public function getDisplayNameAttribute()
    {
        $className = $this->class ? $this->class->class_name : '';
        $subclassName = trim($this->subclass_name);
        
        if (empty($subclassName)) {
            return $className;
        }
        
        return $className . ' ' . $subclassName;
    }

    /**
     * Check if this is a default subclass (empty name)
     */
    public function isDefault()
    {
        return trim($this->subclass_name) === '';
    }
}
