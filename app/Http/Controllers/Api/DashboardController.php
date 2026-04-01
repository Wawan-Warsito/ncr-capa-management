<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NCR;
use App\Models\CAPA;
use App\Models\Department;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function monthExpr(string $sqlExpression): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "strftime('%Y-%m', {$sqlExpression})";
        }
        return "DATE_FORMAT({$sqlExpression}, '%Y-%m')";
    }

    private function avgDaysDiffExpr(string $endColumn, string $startColumn): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "AVG(julianday({$endColumn}) - julianday({$startColumn}))";
        }
        return "AVG(DATEDIFF({$endColumn}, {$startColumn}))";
    }

    /**
     * Get company-wide dashboard metrics
     */
    public function companyDashboard(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isQCManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Date range filter: Make it truly "All Time" by default so nothing is hidden
        $dateFrom = $request->get('date_from', '2000-01-01 00:00:00');
        $dateTo = $request->get('date_to', now()->endOfDay());

        // NCR Statistics
        $ncrStats = [
            'total' => NCR::whereRaw('COALESCE(issued_date, date_found, created_at) BETWEEN ? AND ?', [$dateFrom, $dateTo])->count(),
            'open' => NCR::whereRaw('COALESCE(issued_date, date_found, created_at) BETWEEN ? AND ?', [$dateFrom, $dateTo])
                ->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
            'closed' => NCR::whereRaw('COALESCE(issued_date, date_found, created_at) BETWEEN ? AND ?', [$dateFrom, $dateTo])
                ->where('status', 'Closed')->count(),
            'overdue' => NCR::whereRaw('COALESCE(issued_date, date_found, created_at) BETWEEN ? AND ?', [$dateFrom, $dateTo])
                ->overdue()->count(),
            'pending_approval' => NCR::whereRaw('COALESCE(issued_date, date_found, created_at) BETWEEN ? AND ?', [$dateFrom, $dateTo])
                ->pendingApproval()->count(),
        ];

        // CAPA Statistics
        $capaStats = [
            'total' => CAPA::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'in_progress' => CAPA::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('current_status', 'In_Progress')->count(),
            'completed' => CAPA::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('current_status', 'Closed')->count(),
            'overdue' => CAPA::whereBetween('created_at', [$dateFrom, $dateTo])
                ->overdue()->count(),
            'pending_verification' => CAPA::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('current_status', 'Pending_Verification')->count(),
        ];

        // NCR by Department (Top 5)
        $ncrByDepartment = NCR::select('finder_dept_id', DB::raw('count(*) as total'))
            ->whereRaw('COALESCE(issued_date, date_found, created_at) BETWEEN ? AND ?', [$dateFrom, $dateTo])
            ->groupBy('finder_dept_id')
            ->with('finderDepartment:id,department_name,department_code')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // NCR by Defect Category (Top 5) based on real date
        $ncrByCategory = NCR::select('defect_category_id', DB::raw('count(*) as total'))
            ->whereRaw('COALESCE(issued_date, date_found, created_at) BETWEEN ? AND ?', [$dateFrom, $dateTo])
            ->groupBy('defect_category_id')
            ->with('defectCategory:id,category_name,category_code')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // NCR by Severity
        $ncrBySeverity = NCR::select('severity_level_id', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('severity_level_id')
            ->with('severityLevel:id,level_name,level_code,color_code')
            ->get();

        // NCR Trend (All Time) based on issue date instead of created_at
        $monthExpr = $this->monthExpr('COALESCE(issued_date, date_found, created_at)');
        $ncrTrend = NCR::select(
                DB::raw("{$monthExpr} as month"),
                DB::raw('count(*) as total')
            )
            ->whereRaw('COALESCE(issued_date, date_found, created_at) >= ?', [$dateFrom])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // CAPA Effectiveness Rate
        $capaEffectiveness = CAPA::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('current_status', 'Closed')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN effectiveness_verified = 1 THEN 1 ELSE 0 END) as effective
            ')
            ->first();

        $effectivenessRate = $capaEffectiveness->total > 0 
            ? round(($capaEffectiveness->effective / $capaEffectiveness->total) * 100, 2)
            : 0;

        // Average NCR Closure Time (in days)
        $avgClosureTime = NCR::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'Closed')
            ->whereNotNull('closed_at')
            ->selectRaw($this->avgDaysDiffExpr('closed_at', 'created_at') . ' as avg_days')
            ->value('avg_days');

        // Recent NCRs
        $recentNcrs = NCR::with(['finderDepartment', 'severityLevel', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Activities
        $recentActivities = ActivityLog::with('user')
            ->orderBy('performed_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ncr_stats' => $ncrStats,
                'capa_stats' => $capaStats,
                'ncr_by_department' => $ncrByDepartment,
                'ncr_by_category' => $ncrByCategory,
                'ncr_by_severity' => $ncrBySeverity,
                'ncr_trend' => $ncrTrend,
                'capa_effectiveness_rate' => $effectivenessRate,
                'avg_ncr_closure_time' => round($avgClosureTime ?? 0, 1),
                'recent_ncrs' => $recentNcrs,
                'recent_activities' => $recentActivities,
            ],
        ]);
    }

    /**
     * Get department-specific dashboard
     */
    public function departmentDashboard(Request $request)
    {
        $user = $request->user();
        $departmentId = $request->get('department_id', $user->department_id);

        // Check permission
        if (!$user->isAdmin() && !$user->isQCManager() && $user->department_id != $departmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        if (!$departmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Department is not set for this user.',
            ], 422);
        }

        $dateFrom = $request->get('date_from', now()->subMonths(6)->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfDay());

        $department = Department::find($departmentId);
        $departmentUserIds = User::where('department_id', $departmentId)->pluck('id');

        $openNcrCount = NCR::where(function ($q) use ($departmentId) {
                $q->where('finder_dept_id', $departmentId)
                    ->orWhere('receiver_dept_id', $departmentId);
            })
            ->whereNotIn('status', ['Closed', 'Cancelled'])
            ->count();

        $openCapaCount = CAPA::whereIn('assigned_pic_id', $departmentUserIds)
            ->whereNotIn('current_status', ['Closed', 'Rejected'])
            ->count();

        $completedThisMonth = CAPA::whereIn('assigned_pic_id', $departmentUserIds)
            ->where('current_status', 'Closed')
            ->whereNotNull('closed_at')
            ->whereYear('closed_at', now()->year)
            ->whereMonth('closed_at', now()->month)
            ->count();

        $avgResolutionTime = NCR::where(function ($q) use ($departmentId) {
                $q->where('finder_dept_id', $departmentId)
                    ->orWhere('receiver_dept_id', $departmentId);
            })
            ->where('status', 'Closed')
            ->whereNotNull('closed_at')
            ->selectRaw($this->avgDaysDiffExpr('closed_at', 'created_at') . ' as avg_days')
            ->value('avg_days');

        $months = [];
        $start = now()->startOfMonth()->subMonths(5);
        for ($i = 0; $i < 6; $i++) {
            $key = $start->copy()->addMonths($i)->format('Y-m');
            $months[$key] = [
                'name' => $key,
                'ncrs' => 0,
                'capas' => 0,
            ];
        }

        $ncrTrendRows = NCR::where(function ($q) use ($departmentId) {
                $q->where('finder_dept_id', $departmentId)
                    ->orWhere('receiver_dept_id', $departmentId);
            })
            ->whereRaw('COALESCE(issued_date, date_found, created_at) >= ?', [$start])
            ->select(
                DB::raw($this->monthExpr('COALESCE(issued_date, date_found, created_at)') . ' as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($ncrTrendRows as $row) {
            $m = (string) $row->month;
            if (isset($months[$m])) $months[$m]['ncrs'] = (int) $row->total;
        }

        $capaTrendRows = CAPA::whereIn('assigned_pic_id', $departmentUserIds)
            ->where('created_at', '>=', $start)
            ->select(
                DB::raw($this->monthExpr('created_at') . ' as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($capaTrendRows as $row) {
            $m = (string) $row->month;
            if (isset($months[$m])) $months[$m]['capas'] = (int) $row->total;
        }

        $defectRows = NCR::where(function ($q) use ($departmentId) {
                $q->where('finder_dept_id', $departmentId)
                    ->orWhere('receiver_dept_id', $departmentId);
            })
            ->whereRaw('COALESCE(issued_date, date_found, created_at) BETWEEN ? AND ?', [$dateFrom, $dateTo])
            ->select('defect_category_id', DB::raw('count(*) as total'))
            ->groupBy('defect_category_id')
            ->with('defectCategory:id,category_name,category_code')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $defectTotal = (int) $defectRows->sum('total');
        $running = 0;
        $defectPareto = $defectRows->map(function ($row) use ($defectTotal, &$running) {
            $count = (int) $row->total;
            $running += $count;
            $pct = $defectTotal > 0 ? round(($running / $defectTotal) * 100, 2) : 0;
            return [
                'name' => $row->defectCategory?->category_name ?? 'Unknown',
                'count' => $count,
                'cumulative' => $pct,
            ];
        })->values();

        $recentNcrs = NCR::where(function ($q) use ($departmentId) {
                $q->where('finder_dept_id', $departmentId)
                    ->orWhere('receiver_dept_id', $departmentId);
            })
            ->with(['finderDepartment:id,department_name', 'receiverDepartment:id,department_name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'department_id' => $departmentId,
                'department_name' => $department?->department_name,
                'open_ncrs' => $openNcrCount,
                'open_capas' => $openCapaCount,
                'completed_this_month' => $completedThisMonth,
                'avg_resolution_time' => round($avgResolutionTime ?? 0, 1),
                'monthly_trend' => array_values($months),
                'defect_pareto' => $defectPareto,
                'recent_ncrs' => $recentNcrs,
            ],
        ]);
    }

    /**
     * Get personal dashboard for logged-in user
     */
    public function personalDashboard(Request $request)
    {
        $user = $request->user();

        // My NCRs (created by me)
        $myNcrs = [
            'total' => NCR::where('created_by_user_id', $user->id)->count(),
            'open' => NCR::where('created_by_user_id', $user->id)
                ->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
            'pending_approval' => NCR::where('created_by_user_id', $user->id)
                ->pendingApproval()->count(),
        ];

        // NCRs assigned to me
        $assignedNcrs = [
            'total' => NCR::where('assigned_pic_id', $user->id)->count(),
            'open' => NCR::where('assigned_pic_id', $user->id)
                ->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
            'overdue' => NCR::where('assigned_pic_id', $user->id)->overdue()->count(),
        ];

        // My CAPAs
        $myCapas = [
            'total' => CAPA::where('assigned_pic_id', $user->id)->count(),
            'in_progress' => CAPA::where('assigned_pic_id', $user->id)
                ->where('current_status', 'In_Progress')->count(),
            'overdue' => CAPA::where('assigned_pic_id', $user->id)->overdue()->count(),
            'pending_verification' => CAPA::where('assigned_pic_id', $user->id)
                ->where('current_status', 'Pending_Verification')->count(),
        ];

        // Pending approvals (if manager)
        $pendingApprovals = 0;
        $pendingApprovalsBreakdown = [
            'finder_approval' => 0,
            'qc_registration' => 0,
            'capa_verification' => 0,
            'asme_review' => 0,
        ];
        $departmentNcrs = null;
        $departmentCapas = null;
        $departmentRecentNcrs = [];
        if ($user->isDepartmentManager()) {
            $pendingApprovalsBreakdown['finder_approval'] = NCR::where('finder_dept_id', $user->department_id)
                ->where('status', 'Pending_Finder_Approval')
                ->count();
            $pendingApprovals += $pendingApprovalsBreakdown['finder_approval'];

            $deptNcrQuery = NCR::where(function ($q) use ($user) {
                    $q->where('finder_dept_id', $user->department_id)
                        ->orWhere('receiver_dept_id', $user->department_id);
                });

            $departmentNcrs = [
                'total' => (clone $deptNcrQuery)->count(),
                'open' => (clone $deptNcrQuery)->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
            ];

            $deptUserIds = User::where('department_id', $user->department_id)->pluck('id');
            $departmentCapas = [
                'total' => CAPA::whereIn('assigned_pic_id', $deptUserIds)->count(),
                'open' => CAPA::whereIn('assigned_pic_id', $deptUserIds)->whereNotIn('current_status', ['Closed', 'Rejected'])->count(),
            ];

            $departmentRecentNcrs = (clone $deptNcrQuery)
                ->whereNotIn('status', ['Closed', 'Cancelled'])
                ->with(['finderDepartment:id,department_name', 'receiverDepartment:id,department_name', 'severityLevel:id,level_name,level_code,color_code'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        if ($user->isQCManager()) {
            $pendingApprovalsBreakdown['qc_registration'] = NCR::where('status', 'Pending_QC_Registration')->count();
            $pendingApprovalsBreakdown['capa_verification'] = CAPA::pendingVerification()->count();
            $pendingApprovals += $pendingApprovalsBreakdown['qc_registration'] + $pendingApprovalsBreakdown['capa_verification'];
        }

        if ($user->isNCRCoordinator()) {
            $pendingApprovalsBreakdown['asme_review'] = NCR::where('status', 'Pending_ASME_Review')->count();
            $pendingApprovals += $pendingApprovalsBreakdown['asme_review'];
        }

        // Recent activities
        $recentActivities = \App\Models\ActivityLog::where('user_id', $user->id)
            ->orderBy('performed_at', 'desc')
            ->limit(10)
            ->get();

        // Unread notifications
        $unreadNotifications = $user->unreadNotifications()->count();

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
                'pending_approvals_breakdown' => $pendingApprovalsBreakdown,
                'department_ncrs' => $departmentNcrs,
                'department_capas' => $departmentCapas,
                'department_recent_ncrs' => $departmentRecentNcrs,
                'recent_activities' => $recentActivities,
                'unread_notifications' => $unreadNotifications,
                'my_tasks' => $myTasks,
            ],
        ]);
    }

    /**
     * Get quick stats for header/widgets
     */
    public function quickStats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'my_pending_tasks' => NCR::where('assigned_pic_id', $user->id)
                ->whereNotIn('status', ['Closed', 'Cancelled'])
                ->count() + 
                CAPA::where('assigned_pic_id', $user->id)
                ->whereNotIn('current_status', ['Closed', 'Rejected'])
                ->count(),
            'unread_notifications' => $user->unreadNotifications()->count(),
            'overdue_items' => NCR::where('assigned_pic_id', $user->id)
                ->overdue()->count() +
                CAPA::where('assigned_pic_id', $user->id)
                ->overdue()->count(),
        ];

        if ($user->isDepartmentManager()) {
            $stats['pending_approvals'] = NCR::where('finder_dept_id', $user->department_id)
                ->where('status', 'Pending_Finder_Approval')
                ->count();
        }
        
        if ($user->isQCManager()) {
            $stats['pending_approvals'] = ($stats['pending_approvals'] ?? 0)
                + NCR::where('status', 'Pending_QC_Registration')->count()
                + CAPA::pendingVerification()->count();
        }

        if ($user->isNCRCoordinator()) {
            $stats['pending_approvals'] = ($stats['pending_approvals'] ?? 0)
                + NCR::where('status', 'Pending_ASME_Review')->count();
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
