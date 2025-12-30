<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combie extends Model
{
    use HasFactory;

    protected $table = 'combies';
    protected $primaryKey = 'combieID';

    protected $fillable = [
        'schoolID',
        'combie_name',
        'combie_code',
        'description',
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

    public function subclasses()
    {
        return $this->hasMany(Subclass::class, 'combieID', 'combieID');
    }
}
