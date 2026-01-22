<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolVisitor extends Model
{
    use HasFactory;

    protected $table = 'school_visitors';
    protected $primaryKey = 'visitorID';

    protected $fillable = [
        'schoolID',
        'visit_date',
        'name',
        'contact',
        'occupation',
        'reason',
        'signature',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];
}
