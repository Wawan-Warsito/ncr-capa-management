<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NCR;
use App\Models\CAPA;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentDashboardController extends Controller
{
    /**
     * Get dashboard data for specific department
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $departmentId = $request->department_id ?? $user->department_id;

        // Permission check: Admin, QC, or user from that department
        if ($user->department_id != $departmentId && !$user->isAdmin() && !$user->isQCManager()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $dateFrom = $request->get('date_from', now()->startOfYear());
        $dateTo = $request->get('date_to', now());

        // NCR Stats for this department (as finder or receiver)
        
        $ncrStats = [
            'total_received' => NCR::where('receiver_dept_id', $departmentId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'open_received' => NCR::where('receiver_dept_id', $departmentId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
            'closed_received' => NCR::where('receiver_dept_id', $departmentId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'Closed')->count(),
            'total_found' => NCR::where('finder_dept_id', $departmentId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
        ];

        // CAPAs assigned to department users
        $departmentUserIds = User::where('department_id', $departmentId)->pluck('id');
        $capaStats = [
            'total' => CAPA::whereIn('assigned_pic_id', $departmentUserIds)
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'in_progress' => CAPA::whereIn('assigned_pic_id', $departmentUserIds)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('current_status', 'In_Progress')->count(),
            'completed' => CAPA::whereIn('assigned_pic_id', $departmentUserIds)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('current_status', 'Closed')->count(),
        ];

        // Top defect categories for this department (as receiver)
        $topDefects = NCR::where('receiver_dept_id', $departmentId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('defect_category_id', DB::raw('count(*) as total'))
            ->groupBy('defect_category_id')
            ->with('defectCategory:id,category_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
            
        // Recent NCRs involving this department
        $recentNcrs = NCR::where(function($q) use ($departmentId) {
                $q->where('finder_dept_id', $departmentId)
                  ->orWhere('receiver_dept_id', $departmentId);
            })
            ->with(['finderDepartment', 'receiverDepartment', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ncr_stats' => $ncrStats,
                'capa_stats' => $capaStats,
                'top_defects' => $topDefects,
                'recent_ncrs' => $recentNcrs,
            ],
        ]);
    }
}
