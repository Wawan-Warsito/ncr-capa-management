<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'display_name',
        'description',
        'permissions',
        'level',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'level' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all users with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Scope to get only active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Check if role is admin
     */
    public function isAdmin(): bool
    {
        return $this->role_name === 'Admin';
    }

    /**
     * Check if role is QC Manager
     */
    public function isQCManager(): bool
    {
        return $this->role_name === 'QC Manager';
    }

    /**
     * Check if role is Department Manager
     */
    public function isDepartmentManager(): bool
    {
        return $this->role_name === 'Department Manager';
    }

    /**
     * Get role level name
     */
    public function getLevelNameAttribute(): string
    {
        return match($this->level) {
            1 => 'Staff',
            2 => 'Supervisor',
            3 => 'Manager',
            4 => 'QC Manager',
            5 => 'Administrator',
            default => 'Unknown',
        };
    }
}
