<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueRecord extends Model
{
    protected $table = 'revenue_records';
    protected $primaryKey = 'revenue_recordID';

    protected $fillable = [
        'schoolID',
        'revenue_sourceID',
        'record_date',
        'unit_amount',
        'quantity',
        'total_amount',
        'note',
    ];
}
