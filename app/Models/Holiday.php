<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 'holidays';
    protected $primaryKey = 'holidayID';
    
    protected $fillable = [
        'schoolID',
        'holiday_name',
        'start_date',
        'end_date',
        'description',
        'type'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
