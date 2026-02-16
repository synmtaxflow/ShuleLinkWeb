<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentMember extends Model
{
    protected $fillable = ['departmentID', 'teacherID', 'staffID'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'departmentID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID');
    }

    public function staff()
    {
        return $this->belongsTo(OtherStaff::class, 'staffID');
    }
}
