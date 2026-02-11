<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $table = 'fees';
    protected $primaryKey = 'feeID';

    protected $fillable = [
        'schoolID',
        'classID',
        // 'fee_type', // REMOVED - no longer using Tuition/Other separation
        'fee_name',
        'amount',
        'must_start_pay', // NEW - priority payment flag
        'payment_deadline_amount', // NEW - amount required by deadline
        'payment_deadline_date', // NEW - deadline date
        'display_order', // NEW - for sorting priority
        'duration',
        'description',
        'status',
        'allow_installments',
        'default_installment_type',
        'number_of_installments',
        'allow_partial_payment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'must_start_pay' => 'boolean',
        'payment_deadline_amount' => 'decimal:2',
        'payment_deadline_date' => 'date',
        'display_order' => 'integer',
        'allow_installments' => 'boolean',
        'allow_partial_payment' => 'boolean',
        'number_of_installments' => 'integer',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classID', 'classID');
    }

    public function installments()
    {
        return $this->hasMany(FeeInstallment::class, 'feeID', 'feeID');
    }

    public function otherFeeDetails()
    {
        return $this->hasMany(OtherFeeDetail::class, 'feeID', 'feeID');
    }
}
