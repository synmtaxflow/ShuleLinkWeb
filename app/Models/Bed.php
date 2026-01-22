<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;

    protected $table = 'beds';
    protected $primaryKey = 'bedID';

    protected $fillable = [
        'schoolID',
        'blockID',
        'roomID',
        'bed_number',
        'has_mattress',
        'status',
        'description',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function block()
    {
        return $this->belongsTo(Block::class, 'blockID', 'blockID');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomID', 'roomID');
    }

    public function assignments()
    {
        return $this->hasMany(BedAssignment::class, 'bedID', 'bedID');
    }
}
