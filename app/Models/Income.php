<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'incomeID';

    protected $fillable = [
        'schoolID',
        'receipt_number',
        'date',
        'income_category',
        'description',
        'amount',
        'payment_method',
        'payment_account',
        'payer_name',
        'entered_by',
        'attachment',
    ];

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
