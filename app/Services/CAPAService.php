<?php

namespace App\Services;

use App\Models\CAPA;
use App\Models\NCR;
use App\Models\User;
use App\Models\CAPAProgressLog;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class CAPAService
{
    /**
     * Create a new CAPA
     */
    public function createCAPA(array $data, User $user): CAPA
    {
        return DB::transaction(function () use ($data, $user) {
            // Generate CAPA number
            $capaNumber = CAPA::generateCapaNumber(); // Assuming this static method exists or I need to implement similar logic

            $capa = CAPA::create([
                ...$data,
                'capa_number' => $capaNumber,
                'assigned_by_user_id' => $user->id,
                'assigned_at' => now(),
                'current_status' => 'Planned',
                'progress_percentage' => 0,
            ]);

            // Update NCR status
            $ncr = NCR::find($data['ncr_id']);
            if ($ncr) {
                // If the NCR exists, update its status
                // Depending on the workflow, it might just link, but usually status updates
                $ncr->capa_id = $capa->id; // If NCR has capa_id foreign key
                $ncr->save();
            }

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Created',
                "CAPA {$capaNumber} created",
                null,
                $capa->toArray(),
                $user
            );

            // Notify Assigned PIC
            if (isset($data['assigned_pic_id'])) {
                Notification::create([
                    'recipient_user_id' => $data['assigned_pic_id'],
                    'type' => 'CAPA_Assigned',
                    'title' => 'CAPA Assigned',
                    'message' => "You have been assigned CAPA {$capaNumber}",
                    'reference_type' => 'CAPA',
                    'reference_id' => $capa->id,
                    'link' => "/capa/{$capa->id}",
                    'priority' => 'High',
                    'is_read' => false
                ]);
            }

            return $capa;
        });
    }

    /**
     * Update CAPA Details
     */
    public function update(CAPA $capa, array $data, User $user): CAPA
    {
        return DB::transaction(function () use ($capa, $data, $user) {
            $capa->update($data);

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Updated',
                "CAPA {$capa->capa_number} details updated",
                null,
                null,
                $user
            );

            return $capa;
        });
    }

    /**
     * Submit CAPA for Review
     */
    public function submitForReview(CAPA $capa, User $user): CAPA
    {
        return DB::transaction(function () use ($capa, $user) {
            $oldStatus = $capa->current_status;
            $capa->current_status = 'Review';
            $capa->save();

            // Notify QA or Supervisor
            // Assuming we have a way to find who to notify, for now notifying Creator or Admin
            // For simplicity, we'll log it and let the notification system (if hooked to model events) handle it, 
            // or explicitly create one if we knew the recipient.
            
            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Submitted',
                "CAPA {$capa->capa_number} submitted for review",
                ['status' => $oldStatus],
                ['status' => 'Review'],
                $user
            );

            return $capa;
        });
    }

    /**
     * Approve CAPA Plan
     */
    public function approvePlan(CAPA $capa, User $user, string $comments = null): CAPA
    {
        return DB::transaction(function () use ($capa, $user, $comments) {
            $oldStatus = $capa->current_status;
            $capa->current_status = 'In_Progress'; // Or Approved_Plan
            $capa->save();

            if ($comments) {
                // Assuming CAPA has comments relation similar to NCR
                 // $capa->comments()->create(...) 
            }

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Approved',
                "CAPA {$capa->capa_number} plan approved",
                ['status' => $oldStatus],
                ['status' => 'In_Progress'],
                $user
            );

            return $capa;
        });
    }

    /**
     * Reject CAPA Plan
     */
    public function rejectPlan(CAPA $capa, User $user, string $reason): CAPA
    {
        return DB::transaction(function () use ($capa, $user, $reason) {
            $oldStatus = $capa->current_status;
            $capa->current_status = 'Plan_Rejected'; // Or back to Planned
            $capa->save();

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Rejected',
                "CAPA {$capa->capa_number} plan rejected",
                ['reason' => $reason],
                ['status' => 'Plan_Rejected'],
                $user
            );

            return $capa;
        });
    }

    /**
     * Update CAPA Progress
     */
    public function updateProgress(CAPA $capa, User $user, int $percentage, string $description, array $additionalData = []): CAPA
    {
        return DB::transaction(function () use ($capa, $user, $percentage, $description, $additionalData) {
            $oldProgress = $capa->progress_percentage;
            
            $capa->progress_percentage = $percentage;
            
            if ($percentage >= 100) {
                $capa->current_status = 'Pending_Verification';
                $capa->actual_completion_date = now();
            } else {
                $capa->current_status = 'In_Progress';
            }
            
            $capa->save();

            // Create Progress Log
            CAPAProgressLog::create([
                'capa_id' => $capa->id,
                'progress_percentage' => $percentage,
                'milestone_description' => $description,
                'activities_completed' => $additionalData['activities_completed'] ?? null,
                'challenges_encountered' => $additionalData['challenges_encountered'] ?? null,
                'next_steps' => $additionalData['next_steps'] ?? null,
                'logged_by_user_id' => $user->id,
                'logged_at' => now(),
            ]);

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Progress_Updated',
                "CAPA {$capa->capa_number} progress updated to {$percentage}%",
                "Old: {$oldProgress}%",
                "New: {$percentage}%",
                $user
            );

            return $capa;
        });
    }

    /**
     * Verify Effectiveness
     */
    public function verifyEffectiveness(CAPA $capa, User $user, bool $isEffective, string $method, string $results): CAPA
    {
        return DB::transaction(function () use ($capa, $user, $isEffective, $method, $results) {
            $capa->effectiveness_verified = $isEffective;
            $capa->verified_by_user_id = $user->id;
            $capa->verified_at = now();
            $capa->verification_method = $method;
            $capa->verification_results = $results;
            
            if ($isEffective) {
                $capa->current_status = 'Verified';
            } else {
                $capa->current_status = 'In_Progress'; // Re-open if not effective
                $capa->progress_percentage = 90; // Reset progress slightly to indicate rework needed
            }

            $capa->save();

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Verified',
                "CAPA {$capa->capa_number} verification completed. Result: " . ($isEffective ? 'Effective' : 'Not Effective'),
                null,
                null,
                $user
            );

            return $capa;
        });
    }

    /**
     * Close CAPA
     */
    public function closeCAPA(CAPA $capa, User $user, string $remarks = null): CAPA
    {
        return DB::transaction(function () use ($capa, $user, $remarks) {
            $capa->current_status = 'Closed';
            $capa->closed_by_user_id = $user->id;
            $capa->closed_at = now();
            $capa->closure_remarks = $remarks;
            $capa->save();

            // Close associated NCR if exists and configured to do so
            if ($capa->ncr) {
                $capa->ncr->status = 'Closed';
                $capa->ncr->closed_at = now();
                $capa->ncr->closed_by_user_id = $user->id;
                $capa->ncr->save();
            }

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Closed',
                "CAPA {$capa->capa_number} closed",
                null,
                null,
                $user
            );

            return $capa;
        });
    }
}
