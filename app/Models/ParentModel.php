<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';
    protected $primaryKey = 'parentID';

    protected $fillable = [
        'schoolID',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'occupation',
        'national_id',
        'phone',
        'email',
        'address',
        'photo',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parentID', 'parentID');
    }
}
