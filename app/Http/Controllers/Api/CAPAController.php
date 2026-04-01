<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CAPA;
use App\Models\NCR;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Http\Resources\CAPAResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CAPAController extends Controller
{
    /**
     * Get list of CAPAs with filters
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = CAPA::with([
            'ncr.finderDepartment',
            'ncr.receiverDepartment',
            'assignedPic',
            'assignedBy',
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $status = trim((string) $request->status);
            $statusLower = strtolower($status);

            if ($statusLower === 'open') {
                $query->whereNotIn('current_status', ['Closed', 'Rejected']);
            } elseif ($statusLower === 'overdue') {
                $query->overdue();
            } else {
                if ($status === 'In Progress') $status = 'In_Progress';
                if ($status === 'Pending Verification') $status = 'Pending_Verification';
                $query->where('current_status', $status);
            }
        }

        // Filter by assigned PIC
        if ($request->has('assigned_pic_id')) {
            $query->where('assigned_pic_id', $request->assigned_pic_id);
        }

        // Filter for current user (if not admin/QC Manager)
        if (!$user->isAdmin() && !$user->isQCManager()) {
            $query->where('assigned_pic_id', $user->id);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('target_completion_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('target_completion_date', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('capa_number', 'like', "%{$search}%")
                  ->orWhere('root_cause_summary', 'like', "%{$search}%")
                  ->orWhere('corrective_action_plan', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $capas = $query->paginate($perPage);

        return CAPAResource::collection($capas)->additional(['success' => true]);
    }

    /**
     * Get single CAPA detail
     */
    public function show($id)
    {
        $capa = CAPA::with([
            'ncr',
            'assignedPic',
            'assignedBy',
            'verifiedBy',
            'closedBy',
            'attachments.uploadedBy',
            'progressLogs.loggedBy',
            'comments.commentedBy',
        ])->findOrFail($id);

        return (new CAPAResource($capa))->additional(['success' => true]);
    }

    /**
     * Create new CAPA
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'ncr_id' => 'required|exists:ncrs,id',
            'rca_method' => 'required|in:5_Why,Fishbone,Other',
            'root_cause_summary' => 'required|string',
            'why_1' => 'nullable|string',
            'why_2' => 'nullable|string',
            'why_3' => 'nullable|string',
            'why_4' => 'nullable|string',
            'why_5' => 'nullable|string',
            'fishbone_people' => 'nullable|string',
            'fishbone_process' => 'nullable|string',
            'fishbone_material' => 'nullable|string',
            'fishbone_equipment' => 'nullable|string',
            'fishbone_environment' => 'nullable|string',
            'fishbone_measurement' => 'nullable|string',
            'corrective_action_plan' => 'required|string',
            'preventive_action_plan' => 'nullable|string',
            'expected_outcome' => 'nullable|string',
            'assigned_pic_id' => 'required|exists:users,id',
            'target_completion_date' => 'required|date',
        ]);

        $ncr = NCR::findOrFail($validated['ncr_id']);
        $assignedPic = User::findOrFail($validated['assigned_pic_id']);

        $canCreate = $user->isAdmin() || $user->isQCManager()
            || ($user->isDepartmentManager() && $user->department_id === $ncr->receiver_dept_id)
            || ($ncr->assigned_pic_id && $ncr->assigned_pic_id === $user->id);

        if (!$canCreate) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create CAPA for this NCR',
            ], 403);
        }

        if (!$user->isAdmin() && !$user->isQCManager()) {
            if ($assignedPic->department_id !== $ncr->receiver_dept_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assigned PIC must be from Receiver Department',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Generate CAPA number
            $capaNumber = CAPA::generateCapaNumber();

            $capa = CAPA::create([
                ...$validated,
                'capa_number' => $capaNumber,
                'assigned_by_user_id' => $user->id,
                'assigned_at' => now(),
                'current_status' => 'Planned',
                'progress_percentage' => 0,
            ]);

            // Update NCR status
            $ncr->update(['status' => 'CAPA_In_Progress']);

            // Notify assigned PIC
            Notification::createNotification(
                $validated['assigned_pic_id'],
                'CAPA_Assigned',
                'CAPA Assigned to You',
                "CAPA {$capaNumber} has been assigned to you for NCR {$ncr->ncr_number}",
                'CAPA',
                $capa->id,
                "/capa/{$capa->id}",
                'High'
            );

            // Log activity
            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Created',
                "CAPA {$capaNumber} created for NCR {$ncr->ncr_number}",
                null,
                $capa->toArray(),
                $user
            );

            DB::commit();

            return (new CAPAResource($capa->load(['ncr', 'assignedPic'])))
                ->additional([
                    'success' => true,
                    'message' => 'CAPA created successfully',
                ])
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create CAPA: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update CAPA
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $capa = CAPA::findOrFail($id);

        if (!$capa->canBeEditedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this CAPA',
            ], 403);
        }

        $validated = $request->validate([
            'root_cause_summary' => 'sometimes|string',
            'corrective_action_plan' => 'sometimes|string',
            'preventive_action_plan' => 'sometimes|nullable|string',
            'expected_outcome' => 'sometimes|nullable|string',
            'target_completion_date' => 'sometimes|date',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $capa->toArray();
            $capa->update($validated);

            // Log activity
            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Updated',
                "CAPA {$capa->capa_number} updated",
                $oldValues,
                $capa->toArray(),
                $user
            );

            DB::commit();

            return (new CAPAResource($capa->fresh()))->additional([
                'success' => true,
                'message' => 'CAPA updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update CAPA: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update CAPA progress
     */
    public function updateProgress(Request $request, $id)
    {
        $user = $request->user();
        $capa = CAPA::findOrFail($id);

        if (!$capa->canUpdateProgress($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update progress',
            ], 403);
        }

        $validated = $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'milestone_description' => 'required|string',
            'activities_completed' => 'nullable|string',
            'challenges_encountered' => 'nullable|string',
            'next_steps' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Check if method exists on model, otherwise assume logic is handled here or in service
            // For now, let's assume we update progress directly on the model if method doesn't exist
            // Or create a ProgressLog entry
            
            $capa->progress_percentage = $validated['progress_percentage'];
            
            if ($validated['progress_percentage'] >= 100) {
                $capa->current_status = 'Pending_Verification';
            } else {
                $capa->current_status = 'In_Progress';
            }
            
            $capa->save();

            // Create progress log
            $capa->progressLogs()->create([
                'progress_percentage' => $validated['progress_percentage'],
                'milestone_description' => $validated['milestone_description'],
                'activities_completed' => $validated['activities_completed'] ?? null,
                'challenges_encountered' => $validated['challenges_encountered'] ?? null,
                'next_steps' => $validated['next_steps'] ?? null,
                'logged_by_user_id' => $user->id,
                'logged_at' => now(),
            ]);

            // If progress is 100%, update status to Pending Verification
            if ($validated['progress_percentage'] >= 100) {
                // Notify QC for verification
                $qcManager = \App\Models\User::whereHas('role', function($q) {
                    $q->where('role_name', 'QC Manager');
                })->first();

                if ($qcManager) {
                    Notification::createNotification(
                        $qcManager->id,
                        'Verification_Required',
                        'CAPA Verification Required',
                        "CAPA {$capa->capa_number} is ready for verification",
                        'CAPA',
                        $capa->id,
                        "/capa/{$capa->id}",
                        'High'
                    );
                }
            }

            // Log activity
            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Progress_Updated',
                "CAPA {$capa->capa_number} progress updated to {$validated['progress_percentage']}%",
                null,
                null,
                $user
            );

            DB::commit();

            return (new CAPAResource($capa->fresh(['progressLogs'])))->additional([
                'success' => true,
                'message' => 'Progress updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify CAPA effectiveness
     */
    public function verify(Request $request, $id)
    {
        $user = $request->user();
        $capa = CAPA::findOrFail($id);

        if (!$capa->canBeVerifiedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to verify this CAPA',
            ], 403);
        }

        $validated = $request->validate([
            'verification_method' => 'required|string',
            'verification_results' => 'required|string',
            'effectiveness_verified' => 'required|boolean',
            'monitoring_start_date' => 'nullable|date',
            'monitoring_end_date' => 'nullable|date',
            'monitoring_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $capa->update([
                ...$validated,
                'verified_by_user_id' => $user->id,
                'verified_at' => now(),
                'current_status' => $validated['effectiveness_verified'] ? 'Verified' : 'In_Progress',
            ]);

            // If verified, update NCR status
            if ($validated['effectiveness_verified']) {
                $capa->ncr->update(['status' => 'Pending_Verification']);
            }

            // Notify assigned PIC
            Notification::createNotification(
                $capa->assigned_pic_id,
                'Status_Changed',
                'CAPA Verification Complete',
                "CAPA {$capa->capa_number} has been " . ($validated['effectiveness_verified'] ? 'verified' : 'returned for revision'),
                'CAPA',
                $capa->id,
                "/capa/{$capa->id}",
                'Normal'
            );

            // Log activity
            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Verified',
                "CAPA {$capa->capa_number} verified",
                null,
                null,
                $user
            );

            DB::commit();

            return (new CAPAResource($capa->fresh()))->additional([
                'success' => true,
                'message' => 'CAPA verified successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify CAPA: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Close CAPA
     */
    public function close(Request $request, $id)
    {
        $user = $request->user();
        $capa = CAPA::findOrFail($id);

        if (!$user->isQCManager() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only QC Manager can close CAPA',
            ], 403);
        }

        $request->validate([
            'closure_remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $capa->update([
                'current_status' => 'Closed',
                'closed_by_user_id' => $user->id,
                'closed_at' => now(),
                'closure_remarks' => $request->closure_remarks,
                'actual_completion_date' => now(),
            ]);

            // Update NCR status to Verified
            $capa->ncr->update(['status' => 'Verified']);

            // Notify assigned PIC
            Notification::createNotification(
                $capa->assigned_pic_id,
                'Status_Changed',
                'CAPA Closed',
                "CAPA {$capa->capa_number} has been closed",
                'CAPA',
                $capa->id,
                "/capa/{$capa->id}",
                'Normal'
            );

            // Log activity
            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Closed',
                "CAPA {$capa->capa_number} closed",
                null,
                null,
                $user
            );

            DB::commit();

            return (new CAPAResource($capa->fresh()))->additional([
                'success' => true,
                'message' => 'CAPA closed successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to close CAPA: ' . $e->getMessage(),
            ], 500);
        }
    }
}
