<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamagedLostRecord extends Model
{
    protected $table = 'damaged_lost_records';
    protected $primaryKey = 'damaged_lostID';

    protected $fillable = [
        'schoolID',
        'resourceID',
        'record_date',
        'record_type',
        'quantity',
        'description',
    ];
}
