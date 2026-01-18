<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffPermission extends Model
{
    use HasFactory;

    protected $table = 'staff_permissions';

    protected $fillable = [
        'profession_id',
        'name',
        'guard_name',
        'permission_category',
    ];

    // Relationships
    public function profession()
    {
        return $this->belongsTo(StaffProfession::class, 'profession_id', 'id');
    }
}
