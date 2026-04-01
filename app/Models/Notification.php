<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'recipient_user_id',
        'notification_type',
        'title',
        'message',
        'related_entity_type',
        'related_entity_id',
        'action_url',
        'priority',
        'is_read',
        'read_at',
        'is_email_sent',
        'email_sent_at',
        'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Accessors
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'Urgent' => 'red',
            'High' => 'orange',
            'Normal' => 'blue',
            'Low' => 'gray',
            default => 'gray',
        };
    }

    public function getIconAttribute(): string
    {
        return match($this->notification_type) {
            'NCR_Created' => 'document-plus',
            'Approval_Required' => 'clipboard-check',
            'CAPA_Assigned' => 'user-plus',
            'Deadline_Reminder' => 'clock',
            'Overdue_Alert' => 'exclamation-triangle',
            'Status_Changed' => 'arrow-path',
            'Comment_Added' => 'chat-bubble-left',
            'Verification_Required' => 'shield-check',
            'NCR_Closed' => 'check-circle',
            default => 'bell',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return $this->icon;
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Create notification helper
     */
    public static function createNotification(
        int $recipientUserId,
        string $type,
        string $title,
        string $message,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $actionUrl = null,
        string $priority = 'Normal'
    ): self {
        return self::create([
            'recipient_user_id' => $recipientUserId,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'related_entity_type' => $entityType,
            'related_entity_id' => $entityId,
            'action_url' => $actionUrl,
            'priority' => $priority,
            'created_at' => now(),
        ]);
    }

    /**
     * Send to multiple users
     */
    public static function notifyUsers(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $actionUrl = null,
        string $priority = 'Normal'
    ): void {
        foreach ($userIds as $userId) {
            self::createNotification(
                $userId,
                $type,
                $title,
                $message,
                $entityType,
                $entityId,
                $actionUrl,
                $priority
            );
        }
    }
}
