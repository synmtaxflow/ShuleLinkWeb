<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BedAssignment extends Model
{
    use HasFactory;

    protected $table = 'bed_assignments';
    protected $primaryKey = 'assignmentID';

    protected $fillable = [
        'schoolID',
        'bedID',
        'studentID',
        'assigned_at',
        'released_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bedID', 'bedID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }
}
