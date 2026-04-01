<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CAPA;
use App\Models\CAPAProgressLog;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CAPAProgressController extends Controller
{
    /**
     * Get progress logs for a CAPA
     */
    public function index($capaId)
    {
        $capa = CAPA::findOrFail($capaId);
        
        // Check permission (view access)
        // Assuming anyone who can view CAPA can view logs.
        
        $logs = $capa->progressLogs()->with('loggedBy')->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Store a newly created progress log
     */
    public function store(Request $request, $capaId)
    {
        $capa = CAPA::findOrFail($capaId);
        $user = $request->user();

        // Check permission: assigned PIC or Admin/QC
        // Using model helper
        if (!$capa->canUpdateProgress($user) && !$user->isAdmin() && !$user->isQCManager()) {
            return response()->json(['message' => 'Unauthorized'], 403);
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
            // Create log
            $log = CAPAProgressLog::create([
                'capa_id' => $capa->id,
                'progress_percentage' => $validated['progress_percentage'],
                'milestone_description' => $validated['milestone_description'],
                'activities_completed' => $request->activities_completed,
                'challenges_encountered' => $request->challenges_encountered,
                'next_steps' => $request->next_steps,
                'logged_by_user_id' => $user->id,
                'logged_at' => now(),
            ]);

            // Update CAPA
            $capa->update([
                'progress_percentage' => $validated['progress_percentage'],
                'current_status' => $validated['progress_percentage'] >= 100 ? 'Pending_Verification' : 'In_Progress',
            ]);

            // If completed, notify QC
            if ($validated['progress_percentage'] >= 100) {
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

            ActivityLog::logActivity(
                'CAPA',
                $capa->id,
                'Progress_Updated',
                "Progress updated to {$validated['progress_percentage']}%",
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'data' => $log,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress: ' . $e->getMessage(),
            ], 500);
        }
    }
}
