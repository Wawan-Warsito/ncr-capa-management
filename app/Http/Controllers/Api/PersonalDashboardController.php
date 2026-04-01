<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NCR;
use App\Models\CAPA;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PersonalDashboardController extends Controller
{
    /**
     * Get personal dashboard for logged-in user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // My NCRs (created by me)
        $myNcrs = [
            'total' => NCR::where('created_by_user_id', $user->id)->count(),
            'open' => NCR::where('created_by_user_id', $user->id)
                ->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
            'pending_approval' => NCR::where('created_by_user_id', $user->id)
                ->where('status', 'Pending_Finder_Approval')->count(),
        ];

        // NCRs assigned to me
        $assignedNcrs = [
            'total' => NCR::where('assigned_pic_id', $user->id)->count(),
            'open' => NCR::where('assigned_pic_id', $user->id)
                ->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
            'overdue' => NCR::where('assigned_pic_id', $user->id)
                ->where('target_closure_date', '<', now())
                ->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
        ];

        // My CAPAs
        $myCapas = [
            'total' => CAPA::where('assigned_pic_id', $user->id)->count(),
            'in_progress' => CAPA::where('assigned_pic_id', $user->id)
                ->where('current_status', 'In_Progress')->count(),
            'overdue' => CAPA::where('assigned_pic_id', $user->id)
                ->where('target_completion_date', '<', now())
                ->whereNotIn('current_status', ['Closed', 'Rejected'])->count(),
            'pending_verification' => CAPA::where('assigned_pic_id', $user->id)
                ->where('current_status', 'Pending_Verification')->count(),
        ];

        // Pending approvals (if manager)
        $pendingApprovals = 0;
        if (method_exists($user, 'isDepartmentManager') && $user->isDepartmentManager()) {
            $pendingApprovals = NCR::where('finder_dept_id', $user->department_id)
                ->where('status', 'Pending_Finder_Approval')
                ->count();
        }

        // Recent activities
        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->orderBy('performed_at', 'desc')
            ->limit(10)
            ->get();

        // My tasks (NCRs and CAPAs assigned to me)
        $myTasks = [
            'ncrs' => NCR::where('assigned_pic_id', $user->id)
                ->whereNotIn('status', ['Closed', 'Cancelled'])
                ->with(['finderDepartment', 'severityLevel'])
                ->orderBy('target_closure_date')
                ->limit(5)
                ->get(),
            'capas' => CAPA::where('assigned_pic_id', $user->id)
                ->whereNotIn('current_status', ['Closed', 'Rejected'])
                ->with(['ncr'])
                ->orderBy('target_completion_date')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'my_ncrs' => $myNcrs,
                'assigned_ncrs' => $assignedNcrs,
                'my_capas' => $myCapas,
                'pending_approvals' => $pendingApprovals,
                'recent_activities' => $recentActivities,
                'my_tasks' => $myTasks,
            ],
        ]);
    }
}
