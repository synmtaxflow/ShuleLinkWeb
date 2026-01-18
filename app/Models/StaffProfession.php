<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfession extends Model
{
    use HasFactory;

    protected $table = 'staff_professions';

    protected $fillable = [
        'name',
        'description',
        'schoolID',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function permissions()
    {
        return $this->hasMany(StaffPermission::class, 'profession_id', 'id');
    }

    public function staff()
    {
        return $this->hasMany(OtherStaff::class, 'profession_id', 'id');
    }
}
