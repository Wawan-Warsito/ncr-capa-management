<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NCR;
use App\Models\CAPA;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class ExportController extends Controller
{
    public function exportNcr(Request $request)
    {
        $query = NCR::with([
            'finderDepartment', 
            'receiverDepartment', 
            'defectCategory', 
            'severityLevel',
            'dispositionMethod',
            'assignedPic'
        ]);

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        
        $ncrs = $query->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=ncr_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'NCR Number', 'Line No', 'Date Found', 'Issued Date', 'Finder Dept', 'Receiver Dept', 
            'Project Name', 'Project SN', 'Part Name', 'Order No',
            'Defect Category', 'Defect Mode', 'Defect Description', 'Severity', 
            'Disposition', 'Immediate Action', 'Assigned PIC',
            'Labor Cost', 'Material Cost', 'Total Cost',
            'Root Cause', 'Preventive Action', 'Status'
        ];

        $callback = function() use ($ncrs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($ncrs as $ncr) {
                fputcsv($file, [
                    $ncr->ncr_number,
                    $ncr->line_no,
                    $ncr->date_found ? $ncr->date_found->format('Y-m-d') : '',
                    $ncr->issued_date ? $ncr->issued_date->format('Y-m-d') : '',
                    $ncr->finderDepartment->department_name ?? '',
                    $ncr->receiverDepartment->department_name ?? '',
                    $ncr->project_name,
                    $ncr->project_sn,
                    $ncr->part_name,
                    $ncr->order_number,
                    $ncr->defectCategory->category_name ?? '',
                    $ncr->defect_mode,
                    $ncr->defect_description,
                    $ncr->severityLevel->level_name ?? '',
                    $ncr->dispositionMethod->method_name ?? '',
                    $ncr->immediate_action,
                    $ncr->assignedPic->name ?? '',
                    $ncr->labor_cost,
                    $ncr->material_cost,
                    $ncr->total_cost,
                    $ncr->root_cause,
                    $ncr->preventive_action,
                    $ncr->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    public function exportCapa(Request $request)
    {
        $query = CAPA::with(['ncr', 'assignedPic']);

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        
        $capas = $query->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=capa_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['CAPA Number', 'NCR Number', 'Assigned PIC', 'Status', 'Progress', 'Target Date'];

        $callback = function() use ($capas, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($capas as $capa) {
                fputcsv($file, [
                    $capa->capa_number,
                    $capa->ncr->ncr_number ?? '',
                    $capa->assignedPic->name ?? '',
                    $capa->current_status,
                    $capa->progress_percentage . '%',
                    $capa->target_completion_date ? $capa->target_completion_date->format('Y-m-d') : ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportUsers(Request $request)
    {
        return Excel::download(new UsersExport, 'users_' . date('Y-m-d') . '.xlsx');
    }
}
