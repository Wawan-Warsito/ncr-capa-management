<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DefectCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'category_code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all NCRs with this defect category
     */
    public function ncrs(): HasMany
    {
        return $this->hasMany(NCR::class, 'defect_category_id');
    }

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get full category name with code
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->category_code} - {$this->category_name}";
    }
}
