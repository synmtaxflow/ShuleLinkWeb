<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';
    protected $primaryKey = 'studentID';
    public $incrementing = false; // studentID is not auto-incrementing (it equals fingerprintID)
    protected $keyType = 'int'; // studentID is an integer (number)

    protected $fillable = [
        'studentID', // Primary key - must be in fillable since we set it manually (equals fingerprintID)
        'schoolID',
        'subclassID',
        'old_subclassID',
        'parentID',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'admission_number',
        'fingerprint_id',
        'sent_to_device',
        'device_sent_at',
        'fingerprint_capture_count',
        'admission_date',
        'address',
        'photo',
        'status',
        'is_disabled',
        'has_epilepsy',
        'has_allergies',
        'allergies_details',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'status' => 'string',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function subclass()
    {
        return $this->belongsTo(Subclass::class, 'subclassID', 'subclassID');
    }

    public function oldSubclass()
    {
        return $this->belongsTo(Subclass::class, 'old_subclassID', 'subclassID');
    }

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parentID', 'parentID');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'studentID', 'studentID');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'studentID', 'studentID');
    }

    public function bookBorrows()
    {
        return $this->hasMany(BookBorrow::class, 'studentID', 'studentID');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'studentID', 'studentID');
    }
}
