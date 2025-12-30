<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $primaryKey = 'classID';

    protected $fillable = [
        'schoolID',
        'teacherID',
        'class_name',
        'description',
        'status',
        'has_subclasses',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function coordinator()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function subclasses()
    {
        return $this->hasMany(Subclass::class, 'classID', 'classID');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'classID', 'classID');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class, 'classID', 'classID');
    }

    /**
     * Get default subclass for this class (the one with empty/whitespace name)
     */
    public function defaultSubclass()
    {
        return $this->subclasses()->where('subclass_name', '')->orWhere('subclass_name', ' ')->first();
    }

    /**
     * Get visible subclasses (exclude default subclass if only one exists)
     */
    public function visibleSubclasses()
    {
        $subclasses = $this->subclasses;
        
        // If class has only one subclass and it's the default (empty name), return empty collection
        if ($subclasses->count() === 1) {
            $subclass = $subclasses->first();
            if (trim($subclass->subclass_name) === '') {
                return collect([]);
            }
        }
        
        return $subclasses;
    }
}
