<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseRecord extends Model
{
    protected $table = 'expense_records';
    protected $primaryKey = 'expense_recordID';

    protected $fillable = [
        'schoolID',
        'expense_budgetID',
        'expense_date',
        'amount',
        'description',
    ];
}
