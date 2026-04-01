<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NCR;
use App\Models\CAPA;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function applyNcrReportFilters($query, Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom && $dateTo) {
            $query->where(function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('issued_date', [$dateFrom, $dateTo])
                  ->orWhereBetween('date_found', [$dateFrom, $dateTo])
                  ->orWhereBetween('created_at', [$dateFrom, $dateTo]);
            });
        } elseif ($dateFrom) {
            $query->where(function($q) use ($dateFrom) {
                $q->where('issued_date', '>=', $dateFrom)
                  ->orWhere('date_found', '>=', $dateFrom)
                  ->orWhere('created_at', '>=', $dateFrom);
            });
        } elseif ($dateTo) {
            $query->where(function($q) use ($dateTo) {
                $q->where('issued_date', '<=', $dateTo)
                  ->orWhere('date_found', '<=', $dateTo)
                  ->orWhere('created_at', '<=', $dateTo);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where(function($q) use ($request) {
                $q->where('finder_dept_id', $request->department_id)
                  ->orWhere('receiver_dept_id', $request->department_id);
            });
        }

        return $query;
    }

    /**
     * Get summary report
     */
    public function summary(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfYear());
        $dateTo = $request->get('date_to', now());

        // NCR Status Summary
        $ncrQuery = NCR::query();
        $this->applyNcrReportFilters($ncrQuery, $request->merge(['date_from' => $dateFrom, 'date_to' => $dateTo]));
        $ncrStatus = (clone $ncrQuery)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // NCR by Department
        $ncrByDept = (clone $ncrQuery)->select('finder_dept_id', DB::raw('count(*) as total'))
            ->groupBy('finder_dept_id')
            ->with('finderDepartment:id,department_name')
            ->get();

        // CAPA Status Summary
        $capaStatus = CAPA::select('current_status', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('current_status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ncr_status' => $ncrStatus,
                'ncr_by_dept' => $ncrByDept,
                'capa_status' => $capaStatus,
            ],
        ]);
    }

    /**
     * Get NCR detailed report
     */
    public function ncrReport(Request $request)
    {
        $query = NCR::with([
            'finderDepartment', 
            'receiverDepartment', 
            'defectCategory', 
            'severityLevel',
            'assignedPic'
        ]);

        $this->applyNcrReportFilters($query, $request);

        $sortBy = (string) $request->get('sort_by', 'report_date');
        $sortOrder = strtolower((string) $request->get('sort_order', 'asc'));
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        if ($sortBy === 'report_date') {
            $query->orderByRaw("COALESCE(issued_date, date_found, created_at) {$sortOrder}");
            $query->orderBy('ncr_number', 'asc');
        } elseif (in_array($sortBy, ['issued_date', 'date_found', 'created_at', 'ncr_number', 'status'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByRaw("COALESCE(issued_date, date_found, created_at) {$sortOrder}");
            $query->orderBy('ncr_number', 'asc');
        }

        $ncrs = $query->get();

        return response()->json([
            'success' => true,
            'data' => $ncrs,
        ]);
    }

    /**
     * Get CAPA detailed report
     */
    public function capaReport(Request $request)
    {
        $query = CAPA::with([
            'ncr',
            'assignedPic',
        ]);

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $status = (string) $request->status;
            if ($status === 'In Progress') $status = 'In_Progress';
            if ($status === 'Pending Verification') $status = 'Pending_Verification';
            if ($status === 'Open') {
                $query->where('current_status', '!=', 'Closed');
            } else {
                $query->where('current_status', $status);
            }
        }

        $capas = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $capas,
        ]);
    }

    public function departmentPerformance(Request $request)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $departmentId = (int) $request->department_id;
        $department = Department::findOrFail($departmentId);

        $ncrQuery = NCR::query();
        $this->applyNcrReportFilters($ncrQuery, $request);

        $total = (clone $ncrQuery)->count();
        $closed = (clone $ncrQuery)->where('status', 'Closed')->count();
        $open = (clone $ncrQuery)->whereNotIn('status', ['Closed', 'Cancelled'])->count();

        $capaQuery = CAPA::query()->whereHas('ncr', function($q) use ($departmentId) {
            $q->where('finder_dept_id', $departmentId)
              ->orWhere('receiver_dept_id', $departmentId);
        });

        $capaTotal = (clone $capaQuery)->count();
        $capaClosed = (clone $capaQuery)->where('current_status', 'Closed')->count();
        $capaVerified = (clone $capaQuery)->where('effectiveness_verified', true)->count();
        $effectivenessRate = $capaTotal > 0 ? round(($capaVerified / $capaTotal) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'department' => [
                    'id' => $department->id,
                    'code' => $department->department_code,
                    'name' => $department->department_name,
                ],
                'ncr' => [
                    'total' => $total,
                    'open' => $open,
                    'closed' => $closed,
                ],
                'capa' => [
                    'total' => $capaTotal,
                    'closed' => $capaClosed,
                    'verified' => $capaVerified,
                    'effectiveness_rate' => $effectivenessRate,
                ],
            ],
        ]);
    }

    public function pareto(Request $request)
    {
        $ncrQuery = NCR::query()->with(['defectCategory']);
        $this->applyNcrReportFilters($ncrQuery, $request);

        $total = (clone $ncrQuery)->count();

        $byCategory = (clone $ncrQuery)
            ->select('defect_category_id', DB::raw('count(*) as total'))
            ->groupBy('defect_category_id')
            ->orderByDesc('total')
            ->with('defectCategory:id,category_name,category_code')
            ->limit(10)
            ->get()
            ->map(function($row) use ($total) {
                $count = (int) $row->total;
                $pct = $total > 0 ? round(($count / $total) * 100, 2) : 0;
                return [
                    'id' => $row->defect_category_id,
                    'code' => $row->defectCategory?->category_code,
                    'name' => $row->defectCategory?->category_name ?? 'Unknown',
                    'count' => $count,
                    'percentage' => $pct,
                ];
            })
            ->values();

        $byMode = (clone $ncrQuery)
            ->select('defect_mode', DB::raw('count(*) as total'))
            ->whereNotNull('defect_mode')
            ->where('defect_mode', '!=', '')
            ->groupBy('defect_mode')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function($row) use ($total) {
                $count = (int) $row->total;
                $pct = $total > 0 ? round(($count / $total) * 100, 2) : 0;
                return [
                    'name' => $row->defect_mode,
                    'count' => $count,
                    'percentage' => $pct,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_ncr' => $total,
                'by_category' => $byCategory,
                'by_mode' => $byMode,
            ],
        ]);
    }
}
