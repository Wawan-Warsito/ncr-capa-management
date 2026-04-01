<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CAPA;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CAPAVerificationController extends Controller
{
    /**
     * Verify CAPA effectiveness
     */
    public function verify(Request $request, $capaId)
    {
        $capa = CAPA::findOrFail($capaId);
        $user = $request->user();

        // Check permission: QC Manager or QC Inspector
        // Using model helper if available or manual check
        $canVerify = $user->isQCManager() || $user->hasRole('QC Inspector') || $user->isAdmin();
        
        if (!$canVerify) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Also check if status is correct
        if ($capa->current_status !== 'Pending_Verification' && !$user->isAdmin()) {
            return response()->json(['message' => 'CAPA is not pending verification'], 400);
        }

        $validated = $request->validate([
            'effectiveness_verified' => 'required|boolean',
            'verification_method' => 'required|string',
            'verification_results' => 'required|string',
            'verification_remarks' => 'nullable|string',
            'monitoring_required' => 'boolean',
            'monitoring_start_date' => 'nullable|date|required_if:monitoring_required,true',
            'monitoring_end_date' => 'nullable|date|required_if:monitoring_required,true|after:monitoring_start_date',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'effectiveness_verified' => $validated['effectiveness_verified'],
                'verified_by_user_id' => $user->id,
                'verified_at' => now(),
                'verification_method' => $validated['verification_method'],
                'verification_results' => $validated['verification_results'],
                // If effective, mark as Verified (or Closed if no monitoring). 
                // If not effective, maybe keep In_Progress but with low progress?
            ];

            if (!$validated['effectiveness_verified']) {
                $updateData['current_status'] = 'In_Progress'; // Send back for rework
                // Maybe decrease progress?
                $updateData['progress_percentage'] = 90; 
                
                // Add log explaining rejection
                ActivityLog::logActivity(
                    'CAPA',
                    $capa->id,
                    'Verification_Failed',
                    "Verification failed: " . ($validated['verification_remarks'] ?? 'No remarks'),
                    null,
                    null,
                    $user
                );
            } else {
                 $updateData['current_status'] = 'Verified';
                 
                 if ($request->monitoring_required) {
                     $updateData['monitoring_start_date'] = $validated['monitoring_start_date'];
                     $updateData['monitoring_end_date'] = $validated['monitoring_end_date'];
                 }
            }

            $capa->update($updateData);

            // Notify Assignee
            if ($capa->assigned_pic_id) {
                Notification::createNotification(
                    $capa->assigned_pic_id,
                    'CAPA_Verified',
                    'CAPA Verification Result',
                    "CAPA {$capa->capa_number} verification completed. Result: " . ($validated['effectiveness_verified'] ? 'Effective' : 'Not Effective'),
                    'CAPA',
                    $capa->id,
                    "/capa/{$capa->id}",
                    'High'
                );
            }

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Verified',
                "CAPA verification completed. Effectiveness: " . ($validated['effectiveness_verified'] ? 'Effective' : 'Not Effective'),
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'CAPA verification submitted successfully',
                'data' => $capa,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify CAPA: ' . $e->getMessage(),
            ], 500);
        }
    }
}
