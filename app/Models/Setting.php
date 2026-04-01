<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'category',
        'description',
        'is_public',
        'updated_by_user_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('setting_key', $key);
    }

    /**
     * Accessors
     */
    public function getTypedValueAttribute()
    {
        return match($this->setting_type) {
            'integer' => (int) $this->setting_value,
            'boolean' => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->setting_value, true),
            'array' => json_decode($this->setting_value, true),
            default => $this->setting_value,
        };
    }

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return $setting->typed_value;
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, ?string $type = null, ?User $user = null): self
    {
        $type = $type ?? (is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'));
        
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            $type = 'json';
        }

        return self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'setting_type' => $type,
                'updated_by_user_id' => $user?->id ?? auth()->id(),
            ]
        );
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings(): array
    {
        return self::all()->pluck('typed_value', 'setting_key')->toArray();
    }

    /**
     * Get settings by category
     */
    public static function getByCategory(string $category): array
    {
        return self::where('category', $category)
            ->get()
            ->pluck('typed_value', 'setting_key')
            ->toArray();
    }
}
