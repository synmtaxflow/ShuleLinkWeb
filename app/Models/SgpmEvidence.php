<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SgpmEvidence extends Model
{
    use HasFactory;

    protected $primaryKey = 'evidenceID';

    protected $fillable = [
        'taskID',
        'file_path',
        'remarks',
    ];

    public function task()
    {
        return $this->belongsTo(SgpmTask::class, 'taskID');
    }
}
