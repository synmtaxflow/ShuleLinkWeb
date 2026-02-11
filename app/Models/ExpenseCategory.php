<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $primaryKey = 'expense_categoryID';

    protected $fillable = [
        'schoolID',
        'name',
        'description',
        'status',
    ];
}
