<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @method static static create(array $attributes = [])
 */
class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'action_type',
        'action_description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'performed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByEntity($query, $entityType, $entityId)
    {
        return $query->where('entity_type', $entityType)
                     ->where('entity_id', $entityId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('performed_at', 'desc')->limit($limit);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('performed_at', today());
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    /**
     * Accessors
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->performed_at->diffForHumans();
    }

    public function getActionBadgeColorAttribute(): string
    {
        return match($this->action_type) {
            'Create', 'Created' => 'green',
            'Update', 'Updated', 'Edit', 'Edited' => 'blue',
            'Delete', 'Deleted' => 'red',
            'Approve', 'Approved' => 'green',
            'Reject', 'Rejected' => 'red',
            'Submit', 'Submitted' => 'indigo',
            'Assign', 'Assigned' => 'purple',
            'Close', 'Closed' => 'gray',
            'Verify', 'Verified' => 'green',
            default => 'gray',
        };
    }

    public static function getStatusColor(string $status): string
    {
        $key = strtolower(trim($status));
        if ($key === 'draft') return 'blue';
        if (str_starts_with($key, 'pending')) return 'orange';
        if (in_array($key, ['verified', 'closed'], true)) return 'green';
        if (in_array($key, ['cancelled', 'canceled', 'rejected'], true)) return 'red';
        return 'gray';
    }

    /**
     * Get changes summary
     */
    public function getChangesSummaryAttribute(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $key,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Log activity helper
     * 
     * @return ActivityLog
     */
    public static function logActivity(
        string $entityType,
        ?int $entityId,
        string $actionType,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null
    ) {
        $userId = $user ? $user->getKey() : Auth::id();

        return static::create([
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action_type' => $actionType,
            'action_description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}
