<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $table = 'sponsors';
    protected $primaryKey = 'sponsorID';

    protected $fillable = [
        'schoolID',
        'sponsor_name',
        'description',
        'contact_person',
        'phone',
        'email',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'sponsor_id', 'sponsorID');
    }
}
