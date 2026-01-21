<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseBudget extends Model
{
    protected $table = 'expense_budgets';
    protected $primaryKey = 'expense_budgetID';

    protected $fillable = [
        'schoolID',
        'year',
        'total_amount',
        'remaining_amount',
        'status',
    ];
}
