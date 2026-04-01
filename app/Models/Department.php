<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name',
        'department_code',
        'manager_user_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the manager of this department
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    /**
     * Get all users in this department
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * Get all NCRs where this department is the finder
     */
    public function finderNcrs(): HasMany
    {
        return $this->hasMany(NCR::class, 'finder_dept_id');
    }

    /**
     * Get all NCRs where this department is the receiver
     */
    public function receiverNcrs(): HasMany
    {
        return $this->hasMany(NCR::class, 'receiver_dept_id');
    }

    /**
     * Scope to get only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get department display name with code
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->department_code} - {$this->department_name}";
    }
}
