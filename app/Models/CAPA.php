<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CAPA extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'capas';

    protected $fillable = [
        'capa_number',
        'ncr_id',
        'rca_method',
        'root_cause_summary',
        'why_1',
        'why_2',
        'why_3',
        'why_4',
        'why_5',
        'fishbone_people',
        'fishbone_process',
        'fishbone_material',
        'fishbone_equipment',
        'fishbone_environment',
        'fishbone_measurement',
        'corrective_action_plan',
        'preventive_action_plan',
        'expected_outcome',
        'assigned_pic_id',
        'assigned_by_user_id',
        'assigned_at',
        'target_completion_date',
        'actual_completion_date',
        'progress_percentage',
        'current_status',
        'effectiveness_verified',
        'verified_by_user_id',
        'verified_at',
        'verification_method',
        'verification_results',
        'monitoring_start_date',
        'monitoring_end_date',
        'monitoring_notes',
        'closed_by_user_id',
        'closed_at',
        'closure_remarks',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'progress_percentage' => 'integer',
        'effectiveness_verified' => 'boolean',
        'verified_at' => 'datetime',
        'monitoring_start_date' => 'date',
        'monitoring_end_date' => 'date',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function ncr(): BelongsTo
    {
        return $this->belongsTo(NCR::class);
    }

    public function assignedPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_pic_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CAPAAttachment::class, 'capa_id');
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(CAPAProgressLog::class, 'capa_id')->orderBy('logged_at', 'desc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'commentable_id')
            ->where('commentable_type', 'CAPA');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('current_status', $status);
    }

    public function scopeAssignedToPic($query, $userId)
    {
        return $query->where('assigned_pic_id', $userId);
    }

    public function scopeInProgress($query)
    {
        return $query->where('current_status', 'In_Progress');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotIn('current_status', ['Closed', 'Rejected'])
            ->where('target_completion_date', '<', now());
    }

    public function scopePendingVerification($query)
    {
        return $query->where('current_status', 'Pending_Verification');
    }

    /**
     * Accessors
     */
    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->current_status, ['Closed', 'Rejected'])) {
            return false;
        }

        return $this->target_completion_date && $this->target_completion_date->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->target_completion_date) {
            return 0;
        }

        return now()->diffInDays($this->target_completion_date, false);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->current_status) {
            'Draft' => 'gray',
            'Planned' => 'blue',
            'In_Progress' => 'indigo',
            'Pending_Verification' => 'purple',
            'Verified', 'Closed' => 'green',
            'Rejected' => 'red',
            default => 'gray',
        };
    }

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

    /**
     * Helper Methods
     */
    public function canBeEditedBy(User $user): bool
    {
        // Assigned PIC can edit
        if ($this->assigned_pic_id === $user->id && $this->current_status !== 'Closed') {
            return true;
        }

        // Admin and QC Manager can edit
        if ($user->isAdmin() || $user->isQCManager()) {
            return true;
        }

        return false;
    }

    public function canUpdateProgress(User $user): bool
    {
        return $this->assigned_pic_id === $user->id && 
               in_array($this->current_status, ['Planned', 'In_Progress']);
    }

    public function canBeVerifiedBy(User $user): bool
    {
        return ($user->isQCManager() || $user->hasRole('QC Inspector')) && 
               $this->current_status === 'Pending_Verification';
    }

    public function isOpen(): bool
    {
        return !in_array($this->current_status, ['Closed', 'Rejected']);
    }

    public function isClosed(): bool
    {
        return $this->current_status === 'Closed';
    }

    public function isComplete(): bool
    {
        return $this->progress_percentage >= 100;
    }

    /**
     * Update progress
     */
    public function updateProgress(int $percentage, string $description, User $user): void
    {
        $this->update([
            'progress_percentage' => $percentage,
            'current_status' => $percentage >= 100 ? 'Pending_Verification' : 'In_Progress',
        ]);

        // Log progress
        $this->progressLogs()->create([
            'progress_percentage' => $percentage,
            'milestone_description' => $description,
            'logged_by_user_id' => $user->id,
        ]);
    }

    /**
     * Generate CAPA Number
     * Format: CAPA-YY-NNNN
     */
    public static function generateCapaNumber(): string
    {
        $year = date('y');
        
        $lastCapa = self::where('capa_number', 'like', "CAPA-{$year}-%")
            ->orderBy('capa_number', 'desc')
            ->first();

        if ($lastCapa) {
            $parts = explode('-', $lastCapa->capa_number);
            $sequence = intval(end($parts)) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('CAPA-%s-%04d', $year, $sequence);
    }
}
