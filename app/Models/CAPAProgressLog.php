<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAPAProgressLog extends Model
{
    use HasFactory;

    protected $table = 'capa_progress_logs';

    public $timestamps = false;

    protected $fillable = [
        'capa_id',
        'progress_percentage',
        'milestone_description',
        'activities_completed',
        'challenges_encountered',
        'next_steps',
        'logged_by_user_id',
        'logged_at',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'logged_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function capa(): BelongsTo
    {
        return $this->belongsTo(CAPA::class, 'capa_id');
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('logged_at', 'desc')->limit($limit);
    }

    public function scopeByPercentage($query, $percentage)
    {
        return $query->where('progress_percentage', $percentage);
    }

    /**
     * Accessors
     */
    public function getProgressColorAttribute(): string
    {
        if ($this->progress_percentage >= 100) {
            return 'green';
        } elseif ($this->progress_percentage >= 75) {
            return 'blue';
        } elseif ($this->progress_percentage >= 50) {
            return 'yellow';
        } elseif ($this->progress_percentage >= 25) {
            return 'orange';
        } else {
            return 'red';
        }
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->logged_at->diffForHumans();
    }
}
