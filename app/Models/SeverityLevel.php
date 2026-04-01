<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeverityLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_name',
        'level_code',
        'priority',
        'color_code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all NCRs with this severity level
     */
    public function ncrs(): HasMany
    {
        return $this->hasMany(NCR::class, 'severity_level_id');
    }

    /**
     * Scope to get only active severity levels
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by priority
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Check if this is critical severity
     */
    public function isCritical(): bool
    {
        return $this->level_code === 'CRITICAL';
    }

    /**
     * Get full severity name with code
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->level_code} - {$this->level_name}";
    }
}
