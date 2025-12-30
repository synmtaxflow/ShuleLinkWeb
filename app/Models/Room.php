<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $primaryKey = 'roomID';

    protected $fillable = [
        'schoolID',
        'blockID',
        'room_name',
        'room_number',
        'capacity',
        'tables',
        'chairs',
        'cabinets',
        'wardrobes',
        'other_items',
        'other_items_description',
        'description',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function block()
    {
        return $this->belongsTo(Block::class, 'blockID', 'blockID');
    }

    public function beds()
    {
        return $this->hasMany(Bed::class, 'roomID', 'roomID');
    }
}
