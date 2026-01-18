<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherStaff extends Model
{
    use HasFactory;

    protected $table = 'other_staff';
    protected $primaryKey = 'id';
    public $incrementing = false; // id equals fingerprint_id (non-auto-increment)
    protected $keyType = 'int'; // id is integer

    protected $fillable = [
        'id', // Primary key - must be in fillable since we set it manually (equals fingerprint_id)
        'schoolID',
        'profession_id',
        'first_name',
        'middle_name',
        'image',
        'last_name',
        'gender',
        'national_id',
        'employee_number',
        'qualification',
        'specialization',
        'experience',
        'date_of_birth',
        'date_hired',
        'address',
        'email',
        'phone_number',
        'bank_account_number',
        'position',
        'status',
        'fingerprint_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_hired' => 'date',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function profession()
    {
        return $this->belongsTo(StaffProfession::class, 'profession_id', 'id');
    }
}
