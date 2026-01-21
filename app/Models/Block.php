<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $table = 'blocks';
    protected $primaryKey = 'blockID';

    protected $fillable = [
        'schoolID',
        'block_name',
        'location',
        'block_type',
        'block_sex',
        'block_items',
        'description',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'blockID', 'blockID');
    }

    public function beds()
    {
        return $this->hasMany(Bed::class, 'blockID', 'blockID');
    }
}
