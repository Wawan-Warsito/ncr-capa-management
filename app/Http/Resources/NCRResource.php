<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NCRResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Prepare timeline
        $timeline = collect();

        if ($this->relationLoaded('activityLogs')) {
            foreach ($this->activityLogs as $log) {
                $timeline->push([
                    'id' => 'log_' . $log->id,
                    'type' => 'log',
                    'action' => $log->action_type,
                    'comment' => $log->action_description,
                    'user_name' => $log->user ? $log->user->name : 'System',
                    'created_at' => $log->performed_at ? $log->performed_at->toIso8601String() : null,
                ]);
            }
        }

        if ($this->relationLoaded('comments')) {
            foreach ($this->comments as $comment) {
                $timeline->push([
                    'id' => 'comment_' . $comment->id,
                    'type' => 'comment',
                    'action' => 'Commented',
                    'comment' => $comment->comment_text,
                    'user_name' => $comment->commentedBy ? $comment->commentedBy->name : 'Unknown',
                    'created_at' => $comment->created_at ? $comment->created_at->toIso8601String() : null,
                ]);
            }
        }

        $sortedTimeline = $timeline->sortByDesc('created_at')->values()->all();

        return [
            'id' => $this->id,
            'ncrNumber' => $this->ncr_number,
            'lineNo' => $this->line_no, // New
            'issuedDate' => $this->issued_date ? $this->issued_date->toDateString() : null, // New
            'lastNcrNo' => $this->last_ncr_no, // New
            'projectSn' => $this->project_sn, // New
            'partName' => $this->part_name, // New
            'defectMode' => $this->defect_mode, // New
            
            'orderNumber' => $this->order_number,
            'projectName' => $this->project_name,
            'customerName' => $this->customer_name,
            'productDescription' => $this->product_description,
            'drawingNumber' => $this->drawing_number,
            'materialSpecification' => $this->material_specification,
            'dateFound' => $this->date_found ? $this->date_found->toDateString() : null,
            'locationFound' => $this->location_found,
            'quantityAffected' => $this->quantity_affected,
            'finderDeptId' => $this->finder_dept_id,
            'receiverDeptId' => $this->receiver_dept_id,
            'createdByUserId' => $this->created_by_user_id,
            'defectCategoryId' => $this->defect_category_id,
            'defectDescription' => $this->defect_description,
            'defectLocation' => $this->defect_location,
            'severityLevelId' => $this->severity_level_id,
            'dispositionMethodId' => $this->disposition_method_id,
            'dispositionDetails' => $this->disposition_details,
            'dispositionApprovedBy' => $this->disposition_approved_by,
            'dispositionApprovedAt' => $this->disposition_approved_at ? $this->disposition_approved_at->toIso8601String() : null,
            'immediateAction' => $this->immediate_action,
            'containmentAction' => $this->containment_action,
            
            // Costs
            'mhUsed' => $this->mh_used,
            'mhRate' => $this->mh_rate,
            'laborCost' => $this->labor_cost,
            'materialCost' => $this->material_cost,
            'subcontCost' => $this->subcont_cost,
            'engineeringCost' => $this->engineering_cost,
            'otherCost' => $this->other_cost,
            'totalCost' => $this->total_cost,
            
            'caFinishDate' => $this->ca_finish_date ? $this->ca_finish_date->toDateString() : null,
            'daysPassed' => $this->days_passed,
            'rootCause' => $this->root_cause,
            'preventiveAction' => $this->preventive_action,
            'relatedDocument' => $this->related_document,

            'estimatedCost' => $this->estimated_cost,
            'actualCost' => $this->actual_cost,
            'status' => $this->status,
            'createdAt' => $this->created_at->toIso8601String(), // Ensure created_at is available
            'finderManagerId' => $this->finder_manager_id,
            'finderApprovedAt' => $this->finder_approved_at ? $this->finder_approved_at->toIso8601String() : null,
            'finderApprovalRemarks' => $this->finder_approval_remarks,
            'qcManagerId' => $this->qc_manager_id,
            'qcRegisteredAt' => $this->qc_registered_at ? $this->qc_registered_at->toIso8601String() : null,
            'qcRegistrationRemarks' => $this->qc_registration_remarks,
            'receiverManagerId' => $this->receiver_manager_id,
            'receiverAssignedAt' => $this->receiver_assigned_at ? $this->receiver_assigned_at->toIso8601String() : null,
            'receiverAssignmentRemarks' => $this->receiver_assignment_remarks,
            'assignedPicId' => $this->assigned_pic_id,
            'picAssignedAt' => $this->pic_assigned_at ? $this->pic_assigned_at->toIso8601String() : null,
            'isAsmeProject' => $this->is_asme_project,
            'asmeCodeReference' => $this->asme_code_reference,
            'ncrCoordinatorId' => $this->ncr_coordinator_id,
            'asmeReviewedAt' => $this->asme_reviewed_at ? $this->asme_reviewed_at->toIso8601String() : null,
            'asmeReviewRemarks' => $this->asme_review_remarks,
            'verifiedByUserId' => $this->verified_by_user_id,
            'verifiedAt' => $this->verified_at ? $this->verified_at->toIso8601String() : null,
            'verificationRemarks' => $this->verification_remarks,
            'effectivenessVerified' => $this->effectiveness_verified,
            'evaluationSustainabilityVerified' => $this->evaluation_sustainability_verified,
            'evaluationIssueClosed3Months' => $this->evaluation_issue_closed_3months,
            'irRequired' => $this->ir_required,
            'irNo' => $this->ir_no,
            'customerApprovalReference' => $this->customer_approval_reference,
            'closedByUserId' => $this->closed_by_user_id,
            'closedAt' => $this->closed_at ? $this->closed_at->toIso8601String() : null,
            'closureRemarks' => $this->closure_remarks,
            'isRecurring' => $this->is_recurring,
            'parentNcrId' => $this->parent_ncr_id,
            'recurrenceCount' => $this->recurrence_count,
            'submittedAt' => $this->submitted_at ? $this->submitted_at->toIso8601String() : null,
            'targetClosureDate' => $this->target_closure_date ? $this->target_closure_date->toDateString() : null,
            'actualClosureDate' => $this->actual_closure_date ? $this->actual_closure_date->toDateString() : null,
            'createdAt' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            
            // Relationships (if loaded)
            'finderDepartment' => $this->whenLoaded('finderDepartment'),
            'receiverDepartment' => $this->whenLoaded('receiverDepartment'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'defectCategory' => $this->whenLoaded('defectCategory'),
            'severityLevel' => $this->whenLoaded('severityLevel'),
            'dispositionMethod' => $this->whenLoaded('dispositionMethod'),
            'assignedPic' => $this->whenLoaded('assignedPic'),
            'finderManager' => $this->whenLoaded('finderManager'),
            'qcManager' => $this->whenLoaded('qcManager'),
            'receiverManager' => $this->whenLoaded('receiverManager'),
            'ncrCoordinator' => $this->whenLoaded('ncrCoordinator'),
            'verifiedBy' => $this->whenLoaded('verifiedBy'),
            'closedBy' => $this->whenLoaded('closedBy'),
            'comments' => $this->whenLoaded('comments'),
            'attachments' => $this->whenLoaded('attachments', function() {
                return $this->attachments->map(function($attachment) {
                    return [
                        'id' => $attachment->id,
                        'fileName' => $attachment->file_name,
                        'fileSize' => $attachment->file_size,
                        'fileType' => $attachment->file_type,
                        'uploadedByUserId' => $attachment->uploaded_by_user_id,
                        'uploadedBy' => $attachment->uploadedBy ? $attachment->uploadedBy->name : 'Unknown',
                        'uploadedAt' => $attachment->created_at ? $attachment->created_at->toIso8601String() : null,
                        'filePath' => $attachment->file_path,
                    ];
                });
            }),
            'timeline' => $sortedTimeline,
        ];
    }
}
