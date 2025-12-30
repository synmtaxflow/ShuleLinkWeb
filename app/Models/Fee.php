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
        'fee_type',
        'fee_name',
        'amount',
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
