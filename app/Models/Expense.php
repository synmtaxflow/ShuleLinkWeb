<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'expenseID';

    protected $fillable = [
        'schoolID',
        'voucher_number',
        'date',
        'voucher_type',
        'expense_category',
        'description',
        'amount',
        'payment_account',
        'entered_by',
        'approved_by',
        'status',
        'attachment',
    ];

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
