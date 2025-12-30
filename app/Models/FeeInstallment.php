<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallment extends Model
{
    use HasFactory;

    protected $table = 'fee_installments';
    protected $primaryKey = 'installmentID';

    protected $fillable = [
        'feeID',
        'installment_name',
        'installment_type',
        'installment_number',
        'amount',
        'due_date',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    // Relationships
    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }
}
