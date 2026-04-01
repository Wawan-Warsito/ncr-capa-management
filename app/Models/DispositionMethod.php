<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DispositionMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'method_name',
        'method_code',
        'description',
        'requires_approval',
        'is_active',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all NCRs using this disposition method
     */
    public function ncrs(): HasMany
    {
        return $this->hasMany(NCR::class, 'disposition_method_id');
    }

    /**
     * Scope to get only active methods
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get full method name with code
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->method_code} - {$this->method_name}";
    }
}
