<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Spatie\Permission\Traits\HasRoles; // Temporarily commented - will enable after package installation

class Teacher extends Model
{
    use HasFactory; // HasRoles will be added after Spatie package installation

    protected $table = 'teachers';
    protected $primaryKey = 'id';
    public $incrementing = false; // teacherID is not auto-incrementing (it equals fingerprintID)
    protected $keyType = 'int'; // teacherID is now an integer

    protected $fillable = [
        'id', // Primary key - must be in fillable since we set it manually (equals fingerprintID)
        'schoolID',
        'first_name',
        'middle_name',
        'image',
        'last_name',
        'gender',
        'national_id',
        'employee_number',
        'qualification',
        'specialization',
        'experience',
        'date_of_birth',
        'date_hired',
        'address',
        'email',
        'phone_number',
        'position',
        'status',
        'fingerprint_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_hired' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'teacherID', 'id');
    }

    public function subclasses()
    {
        return $this->hasMany(Subclass::class, 'teacherID', 'id');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'teacherID', 'id');
    }
}


