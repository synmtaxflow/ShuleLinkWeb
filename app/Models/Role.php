<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'role_name',
        'guard_name',
        'schoolID',
    ];

    // Accessor for backward compatibility
    public function getRoleNameAttribute()
    {
        return $this->name ?? $this->attributes['role_name'] ?? null;
    }

    // Mutator for backward compatibility
    public function setRoleNameAttribute($value)
    {
        $this->attributes['role_name'] = $value;
        // Set name if column exists (after migration)
        if (isset($this->attributes['name']) || \Illuminate\Support\Facades\Schema::hasColumn('roles', 'name')) {
            $this->attributes['name'] = $value;
        }
    }

    // Keep old relationship for backward compatibility
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id')->withTimestamps();
    }

    // Permissions relationship - one role has many permissions
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'role_id');
    }

    /**
     * Sync permissions for this role
     * Deletes existing permissions and creates new ones
     */
    public function syncPermissions($permissions)
    {
        // Delete existing permissions
        $this->permissions()->delete();
        
        // Create new permissions
        if (is_array($permissions) && count($permissions) > 0) {
            foreach ($permissions as $permission) {
                if (is_string($permission)) {
                    // If it's a string (permission name), create it
                    $this->permissions()->create([
                        'name' => $permission,
                        'guard_name' => 'web',
                    ]);
                } elseif (is_object($permission) && isset($permission->name)) {
                    // If it's an object with name property
                    $this->permissions()->create([
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name ?? 'web',
                    ]);
                } elseif (is_array($permission) && isset($permission['name'])) {
                    // If it's an array with name key
                    $this->permissions()->create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'] ?? 'web',
                    ]);
                }
            }
        }
        
        return $this;
    }
}


