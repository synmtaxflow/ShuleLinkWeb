<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeePayment extends Model
{
    protected $table = 'student_fee_payments';
    protected $primaryKey = 'payment_detail_id';
    
    protected $fillable = [
        'schoolID',
        'studentID',
        'paymentID',
        'feeID',
        'fee_name',
        'fee_total_amount',
        'amount_paid',
        'balance',
        'is_required',
        'display_order',
        'last_payment_date',
    ];

    protected $casts = [
        'fee_total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_required' => 'boolean',
        'last_payment_date' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }
}
