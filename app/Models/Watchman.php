<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchman extends Model
{
    protected $table = 'watchmen';

    protected $fillable = [
        'schoolID',
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'status',
    ];
}
