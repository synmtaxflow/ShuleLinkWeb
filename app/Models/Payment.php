<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'paymentID';

    protected $fillable = [
        'schoolID',
        'studentID',
        'feeID',
        'fee_type',
        'control_number',
        'amount_required',
        'amount_paid',
        'balance',
        'payment_status',
        'sms_sent',
        'sms_sent_at',
        'payment_date',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'amount_required' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'sms_sent_at' => 'datetime',
        'payment_date' => 'datetime',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class, 'paymentID', 'paymentID');
    }
}
