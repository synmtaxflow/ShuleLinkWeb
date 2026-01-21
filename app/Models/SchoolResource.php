<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolResource extends Model
{
    protected $table = 'school_resources';
    protected $primaryKey = 'resourceID';

    protected $fillable = [
        'schoolID',
        'resource_name',
        'resource_type',
        'requires_quantity',
        'quantity',
        'unit_price',
    ];
}
