<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CacheEntry extends Model
{
    use HasFactory;

    protected $table = 'cache';
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'expiration',
    ];
}



