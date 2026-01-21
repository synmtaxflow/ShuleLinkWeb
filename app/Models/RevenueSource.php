<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueSource extends Model
{
    protected $table = 'revenue_sources';
    protected $primaryKey = 'revenue_sourceID';

    protected $fillable = [
        'schoolID',
        'source_name',
        'source_type',
        'default_amount',
        'description',
        'status',
    ];
}
