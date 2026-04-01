<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'parent_comment_id',
        'comment_text',
        'is_internal',
        'commented_by_user_id',
        'commented_at',
        'is_edited',
        'edited_at',
        'is_deleted',
        'deleted_at',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'commented_at' => 'datetime',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commentedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commented_by_user_id');
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    /**
     * Scopes
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeExternal($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_comment_id');
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('commented_at', 'desc')->limit($limit);
    }

    /**
     * Accessors
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->commented_at->diffForHumans();
    }

    public function getHasRepliesAttribute(): bool
    {
        return $this->replies()->count() > 0;
    }

    /**
     * Helper Methods
     */
    public function canBeEditedBy(User $user): bool
    {
        // Comment owner can edit within 15 minutes
        if ($this->commented_by_user_id === $user->id) {
            return $this->commented_at->diffInMinutes(now()) <= 15;
        }

        // Admin can always edit
        return $user->isAdmin();
    }

    public function canBeDeletedBy(User $user): bool
    {
        // Comment owner can delete
        if ($this->commented_by_user_id === $user->id) {
            return true;
        }

        // Admin can delete
        return $user->isAdmin();
    }

    /**
     * Mark as edited
     */
    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Soft delete comment
     */
    public function softDelete(): void
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);
    }
}
