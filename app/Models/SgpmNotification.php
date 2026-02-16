<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SgpmNotification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notificationID';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'link',
        'type',
        'is_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
