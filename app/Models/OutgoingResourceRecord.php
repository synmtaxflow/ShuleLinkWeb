<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutgoingResourceRecord extends Model
{
    protected $table = 'outgoing_resource_records';
    protected $primaryKey = 'outgoing_resourceID';

    protected $fillable = [
        'schoolID',
        'resourceID',
        'outgoing_date',
        'outgoing_type',
        'is_returned',
        'returned_at',
        'returned_quantity',
        'return_description',
        'quantity',
        'unit_price',
        'total_price',
        'description',
    ];
}
