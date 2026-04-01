<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CAPAResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'capaNumber' => $this->capa_number,
            'ncrId' => $this->ncr_id,
            'rcaMethod' => $this->rca_method,
            'rootCauseSummary' => $this->root_cause_summary,
            'why1' => $this->why_1,
            'why2' => $this->why_2,
            'why3' => $this->why_3,
            'why4' => $this->why_4,
            'why5' => $this->why_5,
            'fishbonePeople' => $this->fishbone_people,
            'fishboneProcess' => $this->fishbone_process,
            'fishboneMaterial' => $this->fishbone_material,
            'fishboneEquipment' => $this->fishbone_equipment,
            'fishboneEnvironment' => $this->fishbone_environment,
            'fishboneMeasurement' => $this->fishbone_measurement,
            'correctiveActionPlan' => $this->corrective_action_plan,
            'preventiveActionPlan' => $this->preventive_action_plan,
            'expectedOutcome' => $this->expected_outcome,
            'assignedPicId' => $this->assigned_pic_id,
            'assignedByUserId' => $this->assigned_by_user_id,
            'assignedAt' => $this->assigned_at ? $this->assigned_at->toIso8601String() : null,
            'targetCompletionDate' => $this->target_completion_date ? $this->target_completion_date->toDateString() : null,
            'actualCompletionDate' => $this->actual_completion_date ? $this->actual_completion_date->toDateString() : null,
            'progressPercentage' => $this->progress_percentage,
            'currentStatus' => $this->current_status,
            'effectivenessVerified' => $this->effectiveness_verified,
            'verifiedByUserId' => $this->verified_by_user_id,
            'verifiedAt' => $this->verified_at ? $this->verified_at->toIso8601String() : null,
            'verificationMethod' => $this->verification_method,
            'verificationResults' => $this->verification_results,
            'monitoringStartDate' => $this->monitoring_start_date ? $this->monitoring_start_date->toDateString() : null,
            'monitoringEndDate' => $this->monitoring_end_date ? $this->monitoring_end_date->toDateString() : null,
            'monitoringNotes' => $this->monitoring_notes,
            'closedByUserId' => $this->closed_by_user_id,
            'closedAt' => $this->closed_at ? $this->closed_at->toIso8601String() : null,
            'closureRemarks' => $this->closure_remarks,
            'createdAt' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'deletedAt' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,

            // Relationships
            'ncr' => new NCRResource($this->whenLoaded('ncr')),
            'assignedPic' => $this->whenLoaded('assignedPic'),
            'assignedBy' => $this->whenLoaded('assignedBy'),
            'verifiedBy' => $this->whenLoaded('verifiedBy'),
            'closedBy' => $this->whenLoaded('closedBy'),
            'attachments' => $this->whenLoaded('attachments'),
            'progressLogs' => $this->whenLoaded('progressLogs'),
            'comments' => $this->whenLoaded('comments'),
        ];
    }
}
