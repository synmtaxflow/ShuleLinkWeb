<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $primaryKey = 'departmentID';

    protected $fillable = [
        'schoolID',
        'department_name',
        'type',
        'head_teacherID',
        'head_staffID',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID');
    }

    public function headTeacher()
    {
        return $this->belongsTo(Teacher::class, 'head_teacherID');
    }

    public function headStaff()
    {
        return $this->belongsTo(OtherStaff::class, 'head_staffID');
    }

    public function objectives()
    {
        return $this->hasMany(DepartmentalObjective::class, 'departmentID');
    }

    public function members()
    {
        return $this->hasMany(DepartmentMember::class, 'departmentID');
    }
}
