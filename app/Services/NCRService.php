<?php

namespace App\Services;

use App\Models\NCR;
use App\Models\Department;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Events\NCRCreated;
use App\Events\NCRStatusChanged;
use Illuminate\Support\Facades\DB;

class NCRService
{
    /**
     * Create a new NCR
     */
    public function createNCR(array $data, User $user): NCR
    {
        return DB::transaction(function () use ($data, $user) {
            // Generate NCR number
            $finderDept = Department::find($data['finder_dept_id']);
            $ncrNumber = NCR::generateNcrNumber($finderDept->department_code);

            $ncr = NCR::create([
                ...$data,
                'ncr_number' => $ncrNumber,
                'created_by_user_id' => $user->id,
                'status' => 'Draft',
            ]);

            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Created',
                "NCR {$ncrNumber} created",
                null,
                $ncr->toArray(),
                $user
            );

            NCRCreated::dispatch($ncr);

            return $ncr;
        });
    }

    /**
     * Submit NCR for Approval
     */
    public function submitForApproval(NCR $ncr, User $user): NCR
    {
        return DB::transaction(function () use ($ncr, $user) {
            $oldStatus = $ncr->status;
            // Workflow: Draft -> Pending_Finder_Approval
            $ncr->status = 'Pending_Finder_Approval';
            $ncr->submitted_at = now();
            $ncr->save();

            // Notify Finder Manager (Department Manager of Finder Dept)
            // Implementation detail: Finding the user with role 'Department Manager' in finder_dept_id
            
            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Submitted',
                "NCR {$ncr->ncr_number} submitted for approval",
                ['status' => $oldStatus],
                ['status' => 'Pending_Finder_Approval'],
                $user
            );

            NCRStatusChanged::dispatch($ncr, $oldStatus, 'Pending_Finder_Approval');

            return $ncr;
        });
    }

    /**
     * Approve NCR
     */
    public function approveNCR(NCR $ncr, User $user, string $comments = null): NCR
    {
        return DB::transaction(function () use ($ncr, $user, $comments) {
            $oldStatus = $ncr->status;
            
            if ($ncr->status === 'Pending_Finder_Approval') {
                $ncr->status = 'Pending_QC_Registration';
                $ncr->approved_by_user_id = $user->id;
                $ncr->approved_at = now();
            } elseif ($ncr->status === 'Pending_QC_Registration') {
                $ncr->status = 'Pending_ASME_Review';
                $ncr->qc_manager_id = $user->id;
                $ncr->qc_registration_date = now();
            } elseif ($ncr->status === 'Pending_ASME_Review') {
                $ncr->status = 'Sent_To_Receiver';
                $ncr->ncr_coordinator_id = $user->id;
                $ncr->asme_review_date = now();
            }

            $ncr->save();

            if ($comments) {
                $ncr->comments()->create([
                    'comment_text' => $comments,
                    'commented_by_user_id' => $user->id,
                ]);
            }

            // Notify Receiver Department
            if ($ncr->assigned_pic_id) {
                 Notification::create([
                    'recipient_user_id' => $ncr->assigned_pic_id,
                    'notification_type' => 'NCR_Assigned',
                    'title' => 'NCR Assigned',
                    'message' => "NCR {$ncr->ncr_number} has been assigned to you.",
                    'related_entity_type' => 'NCR',
                    'related_entity_id' => $ncr->id,
                    'action_url' => "/ncrs/{$ncr->id}",
                    'priority' => 'High',
                    'is_read' => false
                ]);
            }

            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Approved',
                "NCR {$ncr->ncr_number} approved",
                ['status' => $oldStatus],
                ['status' => $ncr->status],
                $user
            );

            NCRStatusChanged::dispatch($ncr, $oldStatus, $ncr->status);

            return $ncr;
        });
    }

    /**
     * Reject NCR
     */
    public function rejectNCR(NCR $ncr, User $user, string $reason): NCR
    {
        return DB::transaction(function () use ($ncr, $user, $reason) {
            $oldStatus = $ncr->status;
            $ncr->status = 'Draft'; // Return to draft
            $ncr->save();

            // Add rejection comment
            $ncr->comments()->create([
                'comment_text' => "Rejected: " . $reason,
                'commented_by_user_id' => $user->id,
            ]);

            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Rejected',
                "NCR {$ncr->ncr_number} rejected",
                ['reason' => $reason],
                ['status' => $ncr->status],
                $user
            );

            NCRStatusChanged::dispatch($ncr, $oldStatus, $ncr->status);

            return $ncr;
        });
    }

    /**
     * Route NCR to Receiver
     */
    public function routeToReceiver(NCR $ncr, User $user): NCR
    {
        return DB::transaction(function () use ($ncr, $user) {
            $oldStatus = $ncr->status;
            $ncr->status = 'Sent_To_Receiver';
            $ncr->save();

            // Notify Receiver Department (PIC if assigned)
            if ($ncr->assigned_pic_id) {
                 Notification::create([
                    'recipient_user_id' => $ncr->assigned_pic_id,
                    'notification_type' => 'NCR_Routed',
                    'title' => 'NCR Routed to You',
                    'message' => "NCR {$ncr->ncr_number} has been routed to you.",
                    'related_entity_type' => 'NCR',
                    'related_entity_id' => $ncr->id,
                    'action_url' => "/ncrs/{$ncr->id}",
                    'priority' => 'High',
                    'is_read' => false
                ]);
            }
            
            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Routed',
                "NCR {$ncr->ncr_number} routed to receiver",
                ['status' => $oldStatus],
                ['status' => 'Sent_To_Receiver'],
                $user
            );

            return $ncr;
        });
    }

    /**
     * Close NCR
     */
    public function closeNCR(NCR $ncr, User $user): NCR
    {
        return DB::transaction(function () use ($ncr, $user) {
            $ncr->status = 'Closed';
            $ncr->closed_at = now();
            $ncr->closed_by_user_id = $user->id; // Assuming column exists
            $ncr->save();

            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Closed',
                "NCR {$ncr->ncr_number} closed",
                [],
                [],
                $user
            );

            return $ncr;
        });
    }
}
