<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $table = 'schools';
    protected $primaryKey = 'schoolID';

    protected $fillable = [
        'school_name',
        'registration_number',
        'school_type',
        'ownership',
        'region',
        'district',
        'ward',
        'village',
        'address',
        'email',
        'phone',
        'established_year',
        'school_logo',
        'status',
    ];

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'schoolID', 'schoolID');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'schoolID', 'schoolID');
    }

    public function schoolSubjects()
    {
        return $this->hasMany(SchoolSubject::class, 'schoolID', 'schoolID');
    }

    public function parents()
    {
        return $this->hasMany(ParentModel::class, 'schoolID', 'schoolID');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'schoolID', 'schoolID');
    }

    public function combies()
    {
        return $this->hasMany(Combie::class, 'schoolID', 'schoolID');
    }

    public function examinations()
    {
        return $this->hasMany(Examination::class, 'schoolID', 'schoolID');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class, 'schoolID', 'schoolID');
    }
}


