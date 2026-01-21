<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingResourceRecord extends Model
{
    protected $table = 'incoming_resource_records';
    protected $primaryKey = 'incoming_resourceID';

    protected $fillable = [
        'schoolID',
        'resourceID',
        'received_date',
        'quantity',
        'unit_price',
        'total_price',
        'note',
    ];
}
