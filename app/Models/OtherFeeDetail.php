<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherFeeDetail extends Model
{
    use HasFactory;

    protected $table = 'other_fees_details';
    protected $primaryKey = 'detailID';

    protected $fillable = [
        'feeID',
        'fee_detail_name',
        'amount',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }
}
