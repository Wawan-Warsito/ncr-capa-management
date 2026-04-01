<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NCR;
use App\Models\Department;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Http\Resources\NCRResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\NCRService;

class NCRController extends Controller
{
    protected $ncrService;

    public function __construct(NCRService $ncrService)
    {
        $this->ncrService = $ncrService;
    }
    /**
     * Get list of NCRs with filters
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = NCR::with([
            'finderDepartment',
            'receiverDepartment',
            'createdBy',
            'defectCategory',
            'severityLevel',
            'assignedPic',
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $status = strtolower(trim((string) $request->status));
            if ($status === 'open') {
                $query->whereNotIn('status', ['Closed', 'Cancelled']);
            } elseif ($status === 'overdue') {
                $query->overdue();
            } elseif ($status === 'pending_approval' || $status === 'pending-approval') {
                $query->pendingApproval();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by department (based on user role)
        if (!$user->isAdmin() && !$user->isQCManager()) {
            $query->where(function($q) use ($user) {
                $q->where('finder_dept_id', $user->department_id)
                  ->orWhere('receiver_dept_id', $user->department_id)
                  ->orWhere('assigned_pic_id', $user->id)
                  ->orWhere('created_by_user_id', $user->id); // Ensure creator sees their own NCRs
            });
        }

        // Filter by finder department
        if ($request->has('finder_dept_id')) {
            $query->where('finder_dept_id', $request->finder_dept_id);
        }

        // Filter by receiver department
        if ($request->has('receiver_dept_id')) {
            $query->where('receiver_dept_id', $request->receiver_dept_id);
        }

        // Filter by assigned PIC
        if ($request->has('assigned_pic_id')) {
            $query->where('assigned_pic_id', $request->assigned_pic_id);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('date_found', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('date_found', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ncr_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('defect_description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $ncrs = $query->paginate($perPage);

        return NCRResource::collection($ncrs)->additional(['success' => true]);
    }

    /**
     * Purge all NCRs (Admin only)
     */
    public function purge(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            // Hard delete in chunks to avoid memory issues
            NCR::query()->chunkById(500, function ($items) {
                foreach ($items as $ncr) {
                    $ncr->forceDelete();
                }
            });

            DB::commit();
            return response()->json(['success' => true, 'message' => 'All NCRs purged']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to purge: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get single NCR detail
     */
    public function show($id)
    {
        $ncr = NCR::with([
            'finderDepartment',
            'receiverDepartment',
            'createdBy',
            'defectCategory',
            'severityLevel',
            'dispositionMethod',
            'finderManager',
            'qcManager',
            'receiverManager',
            'assignedPic',
            'ncrCoordinator',
            'verifiedBy',
            'closedBy',
            'capa',
            'attachments.uploadedBy',
            'comments.commentedBy',
            'activityLogs.user',
        ])->findOrFail($id);

        return (new NCRResource($ncr))->additional(['success' => true]);
    }

    /**
     * Create new NCR
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'order_number' => 'nullable|string|max:50',
            'project_name' => 'nullable|string|max:200',
            'customer_name' => 'nullable|string|max:200',
            'product_description' => 'nullable|string',
            'drawing_number' => 'nullable|string|max:100',
            'material_specification' => 'nullable|string|max:200',
            'date_found' => 'required|date',
            'location_found' => 'nullable|string|max:200',
            'quantity_affected' => 'nullable|integer',
            'finder_dept_id' => 'required|exists:departments,id',
            'receiver_dept_id' => 'required|exists:departments,id',
            'defect_category_id' => 'required|exists:defect_categories,id',
            'defect_description' => 'required|string',
            'defect_location' => 'nullable|string',
            'severity_level_id' => 'required|exists:severity_levels,id',
            'immediate_action' => 'nullable|string',
            'containment_action' => 'nullable|string',
            'is_asme_project' => 'boolean',
            'asme_code_reference' => 'nullable|string|max:100',
            // New Fields (Phase 9)
            'line_no' => 'nullable|string',
            'issued_date' => 'nullable|date',
            'last_ncr_no' => 'nullable|string',
            'project_sn' => 'nullable|string',
            'part_name' => 'nullable|string',
            'defect_mode' => 'nullable|string',
            'mh_used' => 'nullable|numeric',
            'mh_rate' => 'nullable|numeric',
            'labor_cost' => 'nullable|numeric',
            'material_cost' => 'nullable|numeric',
            'subcont_cost' => 'nullable|numeric',
            'engineering_cost' => 'nullable|numeric',
            'other_cost' => 'nullable|numeric',
            'total_cost' => 'nullable|numeric',
            'ca_finish_date' => 'nullable|date',
            'days_passed' => 'nullable|integer',
            'root_cause' => 'nullable|string',
            'preventive_action' => 'nullable|string',
            'related_document' => 'nullable|string',
            'verification_remarks' => 'nullable|string',
            'effectiveness_verified' => 'boolean',
            'evaluation_sustainability_verified' => 'boolean',
            'evaluation_issue_closed_3months' => 'boolean',
            'ir_required' => 'nullable|boolean',
            'ir_no' => 'nullable|string|max:255',
            'customer_approval_reference' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $ncr = $this->ncrService->createNCR($validated, $user);
            DB::commit();

            return (new NCRResource($ncr->load(['finderDepartment', 'receiverDepartment', 'defectCategory', 'severityLevel'])))
                ->additional([
                    'success' => true,
                    'message' => 'NCR created successfully',
                ])
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create NCR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update NCR
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ncr = NCR::findOrFail($id);

        if (!$ncr->canBeEditedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this NCR',
            ], 403);
        }

        $validated = $request->validate([
            'order_number' => 'sometimes|nullable|string|max:50',
            'project_name' => 'sometimes|nullable|string|max:200',
            'customer_name' => 'sometimes|nullable|string|max:200',
            'product_description' => 'sometimes|nullable|string',
            'drawing_number' => 'sometimes|nullable|string|max:100',
            'material_specification' => 'sometimes|nullable|string|max:200',
            'date_found' => 'sometimes|date',
            'location_found' => 'sometimes|nullable|string|max:200',
            'quantity_affected' => 'sometimes|nullable|integer',
            'finder_dept_id' => 'sometimes|required|exists:departments,id',
            'receiver_dept_id' => 'sometimes|required|exists:departments,id',
            'defect_category_id' => 'sometimes|required|exists:defect_categories,id',
            'severity_level_id' => 'sometimes|required|exists:severity_levels,id',
            'defect_description' => 'sometimes|string',
            'defect_location' => 'sometimes|nullable|string',
            'immediate_action' => 'sometimes|nullable|string',
            'containment_action' => 'sometimes|nullable|string',
            'is_asme_project' => 'sometimes|boolean',
            'asme_code_reference' => 'sometimes|nullable|string|max:100',
            // New Fields (Phase 9)
            'line_no' => 'sometimes|nullable|string',
            'issued_date' => 'sometimes|nullable|date',
            'last_ncr_no' => 'sometimes|nullable|string',
            'project_sn' => 'sometimes|nullable|string',
            'part_name' => 'sometimes|nullable|string',
            'defect_mode' => 'sometimes|nullable|string',
            'mh_used' => 'sometimes|nullable|numeric',
            'mh_rate' => 'sometimes|nullable|numeric',
            'labor_cost' => 'sometimes|nullable|numeric',
            'material_cost' => 'sometimes|nullable|numeric',
            'subcont_cost' => 'sometimes|nullable|numeric',
            'engineering_cost' => 'sometimes|nullable|numeric',
            'other_cost' => 'sometimes|nullable|numeric',
            'total_cost' => 'sometimes|nullable|numeric',
            'ca_finish_date' => 'sometimes|nullable|date',
            'days_passed' => 'sometimes|nullable|integer',
            'root_cause' => 'sometimes|nullable|string',
            'preventive_action' => 'sometimes|nullable|string',
            'related_document' => 'sometimes|nullable|string',
            'disposition_method_id' => 'sometimes|nullable|exists:disposition_methods,id',
            'assigned_pic_id' => 'sometimes|nullable|exists:users,id',
            'status' => 'sometimes|nullable|string',
            'verification_remarks' => 'sometimes|nullable|string',
            'effectiveness_verified' => 'sometimes|boolean',
            'evaluation_sustainability_verified' => 'sometimes|boolean',
            'evaluation_issue_closed_3months' => 'sometimes|boolean',
            'ir_required' => 'sometimes|nullable|boolean',
            'ir_no' => 'sometimes|nullable|string|max:255',
            'customer_approval_reference' => 'sometimes|boolean',
        ]);

        if (!$user->isAdmin() && !$user->isQCManager()) {
            $allowedForReceiverManager = [
                'disposition_method_id',
                'assigned_pic_id',
                'receiver_assignment_remarks',
                'ca_finish_date',
                'mh_used',
                'mh_rate',
                'labor_cost',
                'material_cost',
                'subcont_cost',
                'engineering_cost',
                'other_cost',
                'total_cost',
            ];

            $validated = array_intersect_key($validated, array_flip($allowedForReceiverManager));
        }

        DB::beginTransaction();
        try {
            $oldValues = $ncr->toArray();

            $hasVerificationUpdate = array_key_exists('verification_remarks', $validated)
                || array_key_exists('effectiveness_verified', $validated)
                || array_key_exists('evaluation_sustainability_verified', $validated)
                || array_key_exists('evaluation_issue_closed_3months', $validated);

            if ($hasVerificationUpdate) {
                $validated['verified_by_user_id'] = $user->id;
                $validated['verified_at'] = now();
            }

            if (array_key_exists('ir_required', $validated) && !$validated['ir_required']) {
                $validated['ir_no'] = null;
            }

            $ncr->update($validated);

            // Log activity
            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Updated',
                "NCR {$ncr->ncr_number} updated",
                $oldValues,
                $ncr->toArray(),
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'NCR updated successfully',
                'data' => $ncr->fresh([
                    'finderDepartment',
                    'receiverDepartment',
                    'createdBy',
                    'defectCategory',
                    'severityLevel',
                    'dispositionMethod',
                    'assignedPic',
                    'finderManager',
                    'qcManager',
                    'receiverManager',
                    'ncrCoordinator',
                    'verifiedBy',
                    'closedBy',
                ]),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update NCR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit NCR for approval
     */
    public function submit(Request $request, $id)
    {
        $user = $request->user();
        $ncr = NCR::findOrFail($id);

        try {
            $ncr = $this->ncrService->submitForApproval($ncr, $user);

            return (new NCRResource($ncr))->additional([
                'success' => true,
                'message' => 'NCR submitted for approval',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit NCR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve NCR (Finder Manager, QC Manager, etc.)
     */
    public function approve(Request $request, $id)
    {
        $user = $request->user();
        $ncr = NCR::findOrFail($id);

        if (!$ncr->canBeApprovedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve this NCR',
            ], 403);
        }

        $request->validate([
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $updates = [];
            $nextStatus = '';
            $notifyUser = null;

            if ($ncr->status === 'Pending_Finder_Approval') {
                $updates = [
                    'status' => 'Pending_QC_Registration',
                    'finder_manager_id' => $user->id,
                    'finder_approved_at' => now(),
                    'finder_approval_remarks' => $request->remarks,
                ];
                $nextStatus = 'Pending_QC_Registration';
                // Notify QC Manager
                $notifyUser = \App\Models\User::whereHas('role', function($q) {
                    $q->where('role_name', 'QC Manager');
                })->first();
            } elseif ($ncr->status === 'Pending_QC_Registration') {
                $updates = [
                    'status' => $ncr->is_asme_project ? 'Pending_ASME_Review' : 'Sent_To_Receiver',
                    'qc_manager_id' => $user->id,
                    'qc_registered_at' => now(),
                    'qc_registration_remarks' => $request->remarks,
                ];
                $nextStatus = $ncr->is_asme_project ? 'Pending_ASME_Review' : 'Sent_To_Receiver';
            }

            $ncr->update($updates);

            // Send notification
            if ($notifyUser) {
                Notification::createNotification(
                    $notifyUser->id,
                    'Approval_Required',
                    'NCR Registration Required',
                    "NCR {$ncr->ncr_number} requires registration",
                    'NCR',
                    $ncr->id,
                    "/ncr/{$ncr->id}",
                    'High'
                );
            }

            // Log activity
            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Approved',
                "NCR {$ncr->ncr_number} approved",
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'NCR approved successfully',
                'data' => $ncr->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve NCR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject NCR
     */
    public function reject(Request $request, $id)
    {
        $user = $request->user();
        $ncr = NCR::findOrFail($id);

        $request->validate([
            'remarks' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $ncr->update([
                'status' => 'Rejected',
            ]);

            // Notify creator
            Notification::createNotification(
                $ncr->created_by_user_id,
                'Status_Changed',
                'NCR Rejected',
                "NCR {$ncr->ncr_number} has been rejected: {$request->remarks}",
                'NCR',
                $ncr->id,
                "/ncr/{$ncr->id}",
                'High'
            );

            // Log activity
            ActivityLog::logActivity(
                'NCR',
                $ncr->id,
                'Rejected',
                "NCR {$ncr->ncr_number} rejected: {$request->remarks}",
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'NCR rejected',
                'data' => $ncr->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject NCR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete NCR (soft delete)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $ncr = NCR::findOrFail($id);

        if (!$user->isAdmin() && !$user->isQCManager()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this NCR',
            ], 403);
        }

        $ncr->delete();

        // Log activity
        ActivityLog::logActivity(
            'NCR',
            $ncr->id,
            'Deleted',
            "NCR {$ncr->ncr_number} deleted",
            null,
            null,
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'NCR deleted successfully',
        ]);
    }
}
