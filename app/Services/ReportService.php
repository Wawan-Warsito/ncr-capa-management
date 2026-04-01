<?php

namespace App\Services;

use App\Models\NCR;
use App\Models\CAPA;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class ReportService
{
    private function avgDaysDiffExpr(string $endColumn, string $startColumn): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "AVG(julianday({$endColumn}) - julianday({$startColumn}))";
        }
        return "AVG(DATEDIFF({$endColumn}, {$startColumn}))";
    }

    /**
     * Generate NCR Summary
     */
    public function generateNCRSummary($dateFrom, $dateTo, $departmentId = null)
    {
        $query = NCR::whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($departmentId) {
            $query->where(function($q) use ($departmentId) {
                $q->where('finder_dept_id', $departmentId)
                  ->orWhere('receiver_dept_id', $departmentId);
            });
        }

        $total = $query->count();
        $byStatus = (clone $query)->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->toArray();
        $bySeverity = (clone $query)->select('severity_level_id', DB::raw('count(*) as count'))->groupBy('severity_level_id')->with('severityLevel')->get()->mapWithKeys(function($item) {
            return [$item->severityLevel->level_name => $item->count];
        })->toArray();
        
        // Open NCRs
        $open = (clone $query)->whereNotIn('status', ['Closed', 'Cancelled'])->count();
        
        // Avg Closure Time (for closed NCRs in period)
        $avgClosure = NCR::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'Closed')
            ->whereNotNull('closed_at')
            ->selectRaw($this->avgDaysDiffExpr('closed_at', 'created_at') . ' as avg_days')
            ->value('avg_days');

        return [
            'period' => "$dateFrom to $dateTo",
            'total_ncr' => $total,
            'open_ncr' => $open,
            'closed_ncr' => $total - $open, // Approximation or use explicit check
            'avg_closure_days' => round($avgClosure, 1),
            'by_status' => $byStatus,
            'by_severity' => $bySeverity
        ];
    }

    /**
     * Generate CAPA Report
     */
    public function generateCAPAReport($dateFrom, $dateTo, $departmentId = null)
    {
        $query = CAPA::whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($departmentId) {
            // Assuming we check assigned PIC's department or linked NCR's department
            $query->whereHas('ncr', function($q) use ($departmentId) {
                $q->where('finder_dept_id', $departmentId)
                  ->orWhere('receiver_dept_id', $departmentId);
            });
        }

        $total = $query->count();
        $byStatus = (clone $query)->select('current_status', DB::raw('count(*) as count'))->groupBy('current_status')->pluck('count', 'current_status')->toArray();
        
        // Effectiveness
        $verified = (clone $query)->where('effectiveness_verified', true)->count();
        $effectivenessRate = $total > 0 ? ($verified / $total) * 100 : 0;

        return [
            'period' => "$dateFrom to $dateTo",
            'total_capa' => $total,
            'by_status' => $byStatus,
            'effectiveness_rate' => round($effectivenessRate, 2) . '%'
        ];
    }

    /**
     * Generate Department Report
     */
    public function generateDepartmentReport($departmentId, $dateFrom, $dateTo)
    {
        $department = Department::findOrFail($departmentId);
        
        $ncrSummary = $this->generateNCRSummary($dateFrom, $dateTo, $departmentId);
        $capaSummary = $this->generateCAPAReport($dateFrom, $dateTo, $departmentId);
        
        return [
            'department' => $department->department_name,
            'ncr_metrics' => $ncrSummary,
            'capa_metrics' => $capaSummary
        ];
    }

    /**
     * Export to Excel (CSV)
     */
    public function exportToExcel($data, $type = 'ncr')
    {
        $filename = $type . '_export_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp', 'w+');
        
        if ($type === 'ncr') {
            // Header
            fputcsv($handle, ['NCR No', 'Date', 'Status', 'Finder Dept', 'Receiver Dept', 'Defect', 'Severity']);
            
            // Data
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->ncr_number,
                    $row->created_at->format('Y-m-d'),
                    $row->status,
                    $row->finderDepartment->department_code ?? '',
                    $row->receiverDepartment->department_code ?? '',
                    $row->defect_description,
                    $row->severityLevel->level_name ?? ''
                ]);
            }
        } elseif ($type === 'capa') {
            fputcsv($handle, ['CAPA No', 'NCR No', 'Status', 'PIC', 'Target Date']);
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->capa_number,
                    $row->ncr->ncr_number ?? '',
                    $row->current_status,
                    $row->assignedPic->name ?? '',
                    $row->target_completion_date ? $row->target_completion_date->format('Y-m-d') : ''
                ]);
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return [
            'filename' => $filename,
            'content' => $content,
            'headers' => [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]
        ];
    }

    /**
     * Export to PDF
     */
    public function exportToPDF($data, $view = 'reports.pdf')
    {
        // Placeholder for PDF generation
        // Requires a library like dompdf or snappy
        // return PDF::loadView($view, ['data' => $data])->download('report.pdf');
        
        return [
            'success' => false,
            'message' => 'PDF generation not implemented (requires library)'
        ];
    }
}
