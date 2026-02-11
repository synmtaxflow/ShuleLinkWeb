<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'budgetID';

    protected $fillable = [
        'schoolID',
        'budget_category',
        'fiscal_year',
        'period',
        'allocated_amount',
        'spent_amount',
        'remaining_amount',
        'notes',
        'created_by',
        'status',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
