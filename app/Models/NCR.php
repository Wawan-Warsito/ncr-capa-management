<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NCR extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_BY = 'created_by_user_id';
    const UPDATED_BY = 'updated_by_user_id';
    
    protected $table = 'ncrs';

    protected $fillable = [
        'ncr_number',
        'order_number',
        'project_name',
        'customer_name',
        'product_description',
        'drawing_number',
        'material_specification',
        'date_found',
        'location_found',
        'quantity_affected',
        'finder_dept_id',
        'receiver_dept_id',
        'created_by_user_id',
        'defect_category_id',
        'defect_description',
        'defect_location',
        'severity_level_id',
        'disposition_method_id',
        'disposition_details',
        'disposition_approved_by',
        'disposition_approved_at',
        'immediate_action',
        'containment_action',
        'estimated_cost',
        'actual_cost',
        'status',
        'finder_manager_id',
        'finder_approved_at',
        'finder_approval_remarks',
        'qc_manager_id',
        'qc_registered_at',
        'qc_registration_remarks',
        'receiver_manager_id',
        'receiver_assigned_at',
        'receiver_assignment_remarks',
        'assigned_pic_id',
        'pic_assigned_at',
        'is_asme_project',
        'asme_code_reference',
        'ncr_coordinator_id',
        'asme_reviewed_at',
        'asme_review_remarks',
        'verified_by_user_id',
        'verified_at',
        'verification_remarks',
        'effectiveness_verified',
        'evaluation_sustainability_verified',
        'evaluation_issue_closed_3months',
        'ir_required',
        'ir_no',
        'customer_approval_reference',
        'closed_by_user_id',
        'closed_at',
        'closure_remarks',
        'is_recurring',
        'parent_ncr_id',
        'recurrence_count',
        'submitted_at',
        'target_closure_date',
        'actual_closure_date',
        // New Fields (Phase 9)
        'line_no',
        'issued_date',
        'last_ncr_no',
        'project_sn',
        'part_name',
        'defect_mode',
        'mh_used',
        'mh_rate',
        'labor_cost',
        'material_cost',
        'subcont_cost',
        'engineering_cost',
        'other_cost',
        'total_cost',
        'ca_finish_date',
        'days_passed',
        'root_cause',
        'preventive_action',
        'related_document',
    ];

    protected $casts = [
        'date_found' => 'date',
        'issued_date' => 'date',
        'ca_finish_date' => 'date',
        'quantity_affected' => 'integer',
        'days_passed' => 'integer',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'mh_used' => 'decimal:2',
        'mh_rate' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'material_cost' => 'decimal:2',
        'subcont_cost' => 'decimal:2',
        'engineering_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'disposition_approved_at' => 'datetime',
        'finder_approved_at' => 'datetime',
        'qc_registered_at' => 'datetime',
        'receiver_assigned_at' => 'datetime',
        'pic_assigned_at' => 'datetime',
        'asme_reviewed_at' => 'datetime',
        'verified_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_asme_project' => 'boolean',
        'effectiveness_verified' => 'boolean',
        'evaluation_sustainability_verified' => 'boolean',
        'evaluation_issue_closed_3months' => 'boolean',
        'ir_required' => 'boolean',
        'customer_approval_reference' => 'boolean',
        'is_recurring' => 'boolean',
        'recurrence_count' => 'integer',
        'submitted_at' => 'datetime',
        'target_closure_date' => 'date',
        'actual_closure_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function finderDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'finder_dept_id');
    }

    public function receiverDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'receiver_dept_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function defectCategory(): BelongsTo
    {
        return $this->belongsTo(DefectCategory::class);
    }

    public function severityLevel(): BelongsTo
    {
        return $this->belongsTo(SeverityLevel::class);
    }

    public function dispositionMethod(): BelongsTo
    {
        return $this->belongsTo(DispositionMethod::class);
    }

    public function finderManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finder_manager_id');
    }

    public function qcManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qc_manager_id');
    }

    public function receiverManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_manager_id');
    }

    public function assignedPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_pic_id');
    }

    public function ncrCoordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ncr_coordinator_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function parentNcr(): BelongsTo
    {
        return $this->belongsTo(NCR::class, 'parent_ncr_id');
    }

    public function childNcrs(): HasMany
    {
        return $this->hasMany(NCR::class, 'parent_ncr_id');
    }

    public function capa(): HasOne
    {
        return $this->hasOne(CAPA::class, 'ncr_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(NCRAttachment::class, 'ncr_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'entity_id')
                    ->where('entity_type', 'NCR')
                    ->orderBy('performed_at', 'desc');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByFinderDepartment($query, $departmentId)
    {
        return $query->where('finder_dept_id', $departmentId);
    }

    public function scopeByReceiverDepartment($query, $departmentId)
    {
        return $query->where('receiver_dept_id', $departmentId);
    }

    public function scopeAssignedToPic($query, $userId)
    {
        return $query->where('assigned_pic_id', $userId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by_user_id', $userId);
    }

    public function scopeAsmeProjects($query)
    {
        return $query->where('is_asme_project', true);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', ['Closed', 'Cancelled'])
            ->where('target_closure_date', '<', now());
    }

    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', [
            'Pending_Finder_Approval',
            'Pending_QC_Registration',
            'Pending_ASME_Review',
        ]);
    }

    /**
     * Accessors
     */
    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->status, ['Closed', 'Cancelled'])) {
            return false;
        }

        return $this->target_closure_date && $this->target_closure_date->isPast();
    }

    public function getDaysOpenAttribute(): int
    {
        $endDate = $this->closed_at ?? now();
        return $this->created_at->diffInDays($endDate);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'Draft' => 'gray',
            'Submitted', 'Pending_Finder_Approval', 'Pending_QC_Registration', 'Pending_ASME_Review' => 'yellow',
            'Finder_Approved', 'QC_Registered', 'ASME_Approved' => 'blue',
            'Sent_To_Receiver', 'Assigned_To_PIC', 'CAPA_In_Progress' => 'indigo',
            'Pending_Verification' => 'purple',
            'Verified', 'Closed' => 'green',
            'Rejected', 'Cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Helper Methods
     */
    public function canBeEditedBy(User $user): bool
    {
        // Draft can be edited by creator
        if ($this->status === 'Draft' && $this->created_by_user_id === $user->id) {
            return true;
        }

        // Admin and QC Manager can edit
        if ($user->isAdmin() || $user->isQCManager()) {
            return true;
        }

        // Receiver Department Manager can update receiver-side fields (PIC/Disposition/Remarks)
        if ($user->isDepartmentManager() && $user->department_id === $this->receiver_dept_id) {
            return true;
        }

        return false;
    }

    public function canBeApprovedBy(User $user): bool
    {
        // Finder Manager approval
        if ($this->status === 'Pending_Finder_Approval' && 
            $user->isDepartmentManager() && 
            $user->department_id === $this->finder_dept_id) {
            return true;
        }

        // QC Manager registration
        if ($this->status === 'Pending_QC_Registration' && $user->isQCManager()) {
            return true;
        }

        // ASME review
        if ($this->status === 'Pending_ASME_Review' && $user->isNCRCoordinator()) {
            return true;
        }

        return false;
    }

    public function isOpen(): bool
    {
        return !in_array($this->status, ['Closed', 'Cancelled', 'Rejected']);
    }

    public function isClosed(): bool
    {
        return $this->status === 'Closed';
    }

    /**
     * Generate NCR Number
     * Format: YY.PXX-DDD-NN
     * YY = Year, PXX = Project code, DDD = Department code, NN = Sequential number
     */
    public static function generateNcrNumber($departmentCode, $projectCode = null): string
    {
        $year = date('y');
        $project = $projectCode ?? 'P00';
        
        // Get last NCR number for this year and department
        $lastNcr = self::where('ncr_number', 'like', "{$year}.{$project}-{$departmentCode}-%")
            ->orderBy('ncr_number', 'desc')
            ->first();

        if ($lastNcr) {
            // Extract sequence number and increment
            $parts = explode('-', $lastNcr->ncr_number);
            $sequence = intval(end($parts)) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s.%s-%s-%02d', $year, $project, $departmentCode, $sequence);
    }
}
